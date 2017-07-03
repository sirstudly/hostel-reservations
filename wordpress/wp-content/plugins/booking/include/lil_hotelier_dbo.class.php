<?php

/**
 * Database object for little hotelier tables.
 */
class LilHotelierDBO {

    const STATUS_COMPLETED = 'completed';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_PROCESSING = 'processing';
    const STATUS_FAILED = 'failed';

    /**
     * Returns all bedsheet data for the given date.
     * $selectedDate : DateTime object
     * Returns raw resultset
     */
    static function fetchBedSheetsFrom($selectedDate, $jobId) {
        global $wpdb;

        // query all our resources (in order)
        $resultset = $wpdb->get_results($wpdb->prepare(
            "SELECT r.room, r.bed_name, r.room_type, r.capacity, c.job_id, c.guest_name, c.checkin_date, 
                    IFNULL( c2.checkout_date, c.checkout_date ) AS `checkout_date`,
                    MAX(c.data_href) as data_href, -- room closures can sometimes have more than one
                    CASE WHEN IFNULL( c2.checkout_date, c.checkout_date ) = constants.selected_date THEN 'CHANGE'
                         WHEN MOD(DATEDIFF(constants.selected_date, c.checkin_date), 3) = 0
                           -- don't do a 3-day change if they're checking out the following day
                          AND DATEDIFF(IFNULL( c2.checkout_date, c.checkout_date ), constants.selected_date) > 1 THEN '3 DAY CHANGE'
                         WHEN IFNULL( c2.checkout_date, c.checkout_date ) > constants.selected_date THEN 'NO CHANGE'
                         ELSE 'EMPTY' END AS bedsheet
               FROM ( SELECT STR_TO_DATE( '%s', '%%Y-%%m-%%d' ) AS `selected_date` ) `constants`
               JOIN ".$wpdb->prefix."lh_rooms r ON 1 = 1
               LEFT OUTER JOIN ".$wpdb->prefix."lh_calendar c
                 ON r.id = c.room_id
                AND c.checkout_date >= constants.selected_date
                AND c.checkin_date < constants.selected_date
                AND c.job_id = %d
                    -- check if the following reservation is also the same guest
               LEFT OUTER JOIN ".$wpdb->prefix."lh_calendar c2
                 ON c2.room_id = c.room_id
                AND c2.checkin_date = c.checkout_date
                AND c2.job_id = c.job_id
                AND c2.guest_name = c.guest_name
              WHERE r.room_type NOT IN ('LT_MALE', 'LT_FEMALE', 'OVERFLOW')
                AND r.active_yn = 'Y'
              GROUP BY r.room, r.bed_name, r.room_type, r.capacity, c.job_id, c.guest_name, c.checkin_date, c.checkout_date, constants.selected_date,
                       c2.room, c2.bed_name, c2.checkin_date, c2.checkout_date, c2.job_id, c2.guest_name
              ORDER BY r.room, r.bed_name",
              $selectedDate->format('Y-m-d'), $jobId));

        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        return $resultset;
    }

    /**
     * Returns the latest job with the given type.
     * $jobName name of job, e.g. bedsheets, bedcount
     * Returns matching job recordset or null if no jobs of type found
     */
    static function getLatestJobOfType($jobName) {

        global $wpdb;

        // first find the job id for the most recent job of the given type
        $resultset = $wpdb->get_results($wpdb->prepare(
            "SELECT MAX(job_id) AS job_id
               FROM ".$wpdb->prefix."lh_jobs
              WHERE end_date IN (
                    SELECT MAX(end_date) 
                      FROM ".$wpdb->prefix."lh_jobs t
                     WHERE t.classname = %s
                       AND t.status = %s)", $jobName, self::STATUS_COMPLETED));
        
        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }
        // guaranteed null or int
        $rec = array_shift($resultset);

        // if null, then job hasn't been run yet
        if( $rec->job_id == null) {
            return null;
        }

        // otherwise, retrieve the job details
        $resultset = $wpdb->get_results($wpdb->prepare(
            "SELECT job_id, classname, status, start_date, end_date
               FROM ".$wpdb->prefix."lh_jobs
              WHERE job_id = %d", $rec->job_id));
        
        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        return array_shift($resultset);
    }

    /**
     * Inserts a new job with the given name at the status of 'submitted'.
     * Returns the job id of the newly created job.
     * $jobName : classname of Job to create
     * $jobParams : (optional) associative array of param name => param value for Job
     */
    static function insertJobOfType( $jobName, $jobParams = array() ) {
        global $wpdb;
        if (false === $wpdb->insert($wpdb->prefix ."lh_jobs", 
                array( 'classname' => $jobName, 
                       'status' => self::STATUS_SUBMITTED, 
                       'last_updated_date' => current_time('mysql', 1) ), 
                array( '%s', '%s', '%s' ))) {
            error_log($wpdb->last_error." executing sql: ".$wpdb->last_query);
            throw new DatabaseException( $wpdb->last_error );
        }

        $jobId = $wpdb->insert_id;
        foreach( $jobParams as $jobParamKey => $jobParamValue ) {
            if (false === $wpdb->insert($wpdb->prefix ."lh_job_param", 
                    array( 'job_id' => $jobId, 'name' => $jobParamKey, 'value' => $jobParamValue ), 
                    array( '%d', '%s', '%s' ))) {
                error_log($wpdb->last_error." executing sql: ".$wpdb->last_query);
                throw new DatabaseException( $wpdb->last_error );
            }
        }
        return $jobId;
    }

    /**
     * Returns true iff a job exists with the given name in a submitted or processing state.
     */
    static function isExistsIncompleteJobOfType( $jobName ) {
        global $wpdb;
        $resultset = $wpdb->get_results($wpdb->prepare(
            "SELECT job_id
               FROM ".$wpdb->prefix."lh_jobs
              WHERE classname = %s 
                AND status IN ( %s, %s )", $jobName, self::STATUS_SUBMITTED, self::STATUS_PROCESSING ));

        return ! empty( $resultset );        
    }
    
    /**
     * Returns report where a reservation is split between rooms of the same type.
     */
    static function getSplitRoomReservationsReport() {
        global $wpdb;
        $resultset = $wpdb->get_results(
            "SELECT reservation_id, guest_name, checkin_date, checkout_date, data_href, lh_status, 
                    booking_reference, booking_source, booked_date, eta, viewed_yn, notes, created_date
               FROM ".$wpdb->prefix."lh_rpt_split_rooms
              WHERE job_id IN (SELECT CAST(value AS UNSIGNED) FROM ".$wpdb->prefix."lh_job_param WHERE name = 'allocation_scraper_job_id' AND job_id = (SELECT MAX(job_id) FROM ".$wpdb->prefix."lh_jobs WHERE classname = 'com.macbackpackers.jobs.SplitRoomReservationReportJob' AND status = 'completed'))
              ORDER BY checkin_date");

        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        return $resultset;
    }

    /**
     * Returns report for all group bookings.
     */
    static function getGroupBookingsReport() {
        global $wpdb;
        $resultset = $wpdb->get_results($wpdb->prepare(
            "SELECT reservation_id, guest_name, booking_reference, booking_source, checkin_date, checkout_date, 
                    booked_date, payment_outstanding, num_guests, data_href, notes, viewed_yn 
               FROM ".$wpdb->prefix."lh_group_bookings
              WHERE job_id IN (SELECT CAST(value AS UNSIGNED) FROM ".$wpdb->prefix."lh_job_param WHERE name = 'allocation_scraper_job_id' AND job_id = (SELECT MAX(job_id) FROM ".$wpdb->prefix."lh_jobs WHERE classname = 'com.macbackpackers.jobs.GroupBookingsReportJob' AND status = 'completed'))
                AND ( num_guests >= %d " .
                       (get_option('hbo_include_5_guests_in_6bed_dorm') == 'true' ? ' OR num_guests = 5' : '' ) . "
                    )
              ORDER BY checkin_date", get_option('hbo_group_booking_size')));

        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        return $resultset;
    }

    /**
     * Returns report of bookings which have unpaid deposits.
     */
    static function getUnpaidDepositReport() {
        global $wpdb;
        $resultset = $wpdb->get_results(
            "SELECT guest_name, checkin_date, checkout_date, payment_total, data_href, booking_reference, 
                    booking_source, booked_date, notes, viewed_yn, created_date
               FROM ".$wpdb->prefix."lh_rpt_unpaid_deposit
              WHERE job_id IN (SELECT CAST(value AS UNSIGNED) FROM ".$wpdb->prefix."lh_job_param WHERE name = 'allocation_scraper_job_id' AND job_id = (SELECT MAX(job_id) FROM ".$wpdb->prefix."lh_jobs WHERE classname = 'com.macbackpackers.jobs.UnpaidDepositReportJob' AND status = 'completed'))
              ORDER BY checkin_date");

        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        return $resultset;
    }

    /**
     * Returns report with all guest comments.
     */
    static function getGuestCommentsReport() {
        global $wpdb;
        $resultset = $wpdb->get_results(
            "SELECT job_id, reservation_id, GROUP_CONCAT(DISTINCT guest_name SEPARATOR ', ') `guest_name`, booking_reference, booking_source, checkin_date, checkout_date, booked_date, payment_outstanding, data_href, COUNT(num_guests) `num_guests`, notes, viewed_yn, comments, acknowledged_date
               FROM ( -- some duplicates may occur; remove them first
                   SELECT c.job_id, c.room, c.bed_name, c.reservation_id, c.guest_name, c.booking_reference, c.booking_source, c.checkin_date, c.checkout_date, c.booked_date, c.payment_outstanding, c.data_href, c.num_guests, c.notes, c.viewed_yn, g.comments, g.acknowledged_date
                     FROM ".$wpdb->prefix."lh_calendar c
			         JOIN ".$wpdb->prefix."lh_rpt_guest_comments g
                       ON c.reservation_id = g.reservation_id
                    WHERE c.job_id IN (
					      -- retrieve the last run allocation scraper job id
					      SELECT MAX(j.job_id) 
                            FROM ".$wpdb->prefix."lh_jobs j 
					   	   WHERE j.status = 'completed' 
						     AND j.classname = 'com.macbackpackers.jobs.AllocationScraperJob' )
                      AND g.comments IS NOT NULL ) x
              GROUP BY reservation_id, booking_reference, booking_source, checkin_date, checkout_date, booked_date, payment_outstanding, data_href, notes, viewed_yn, comments, acknowledged_date 
              ORDER BY checkin_date, booking_reference");

        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        return $resultset;
    }

    /**
     * Confirms a guest comment has been looked at.
     * $reservationId : ID of LH reservation
     */
    static function acknowledgeGuestComment( $reservationId ) {
        global $wpdb;
        $returnval = $wpdb->update(
            $wpdb->prefix."lh_rpt_guest_comments",
            array( 'acknowledged_date' => current_time('mysql', 1) ),
            array( 'reservation_id' => $reservationId ) );
        
        if(false === $returnval) {
            throw new DatabaseException("Error occurred during UPDATE");
        }
    }

    /**
     * Clears a previous acknowledgement.
     * $reservationId : ID of LH reservation
     */
    static function unacknowledgeGuestComment( $reservationId ) {
        global $wpdb;

        // attempting to use $wpdb directly to update timestamp to null
        // results in it being set to "0000-00-00 00:00:00"
        // so using direct SQL instead
        $dblink = new DbTransaction();
        try {
            $stmt = $dblink->mysqli->prepare(
                    "UPDATE ".$wpdb->prefix ."lh_rpt_guest_comments
                        SET acknowledged_date = NULL
                      WHERE reservation_id = ?");
            $stmt->bind_param('i', $reservationId);
            if(false === $stmt->execute()) {
                throw new DatabaseException("Error occurred updating lh_rpt_guest_comments: ".$dblink->mysqli->error);
            }
            $stmt->close();

        } catch(Exception $ex) {
            $dblink->mysqli->rollback();
            $dblink->mysqli->close();
            throw $e;
        }

        $dblink->mysqli->commit();
        $dblink->mysqli->close();
    }

    /**
     * Inserts a new AllocationScraperJob.
     * Returns id of inserted job id
     * Throws DatabaseException on insert error
     */
    static function insertAllocationScraperJob() {
        $startDate = new DateTime();
        return self::insertJobOfType( 'com.macbackpackers.jobs.AllocationScraperJob',
            array( "start_date" => $startDate->format('Y-m-d'),
                   "days_ahead" => '140' ) ); // get data for next 4-5 months
    }

    /**
     * Inserts a new AllocationScraperJob.
     * Returns id of inserted job id
     * Throws DatabaseException on insert error
     */
    static function insertGuestCommentsReportJob() {
        return self::insertJobOfType( 'com.macbackpackers.jobs.GuestCommentsReportJob');
    }

    /**
     * Inserts a new UpdateLittleHotelierSettingsJob.
     * Returns id of inserted job id
     * Throws DatabaseException on insert error
     */
    static function insertUpdateLittleHotelierSettingsJob( $username, $password ) {
        return self::insertJobOfType( 'com.macbackpackers.jobs.UpdateLittleHotelierSettingsJob',
            array( "username" => $username,
                   "password" => $password ) );
    }

    /**
     * Inserts a new UpdateHostelworldSettingsJob.
     * Returns id of inserted job id
     * Throws DatabaseException on insert error
     */
    static function insertUpdateHostelworldSettingsJob( $username, $password ) {
        return self::insertJobOfType( 'com.macbackpackers.jobs.UpdateHostelworldSettingsJob',
            array( "username" => $username,
                   "password" => $password ) );
    }

    /**
     * Inserts a new UpdateHostelbookersSettingsJob.
     * Returns id of inserted job id
     * Throws DatabaseException on insert error
     */
    static function insertUpdateHostelbookersSettingsJob( $username, $password ) {
        return self::insertJobOfType( 'com.macbackpackers.jobs.UpdateHostelbookersSettingsJob',
            array( "username" => $username,
                   "password" => $password ) );
    }

    /**
     * Inserts a new CreateTestGuestCheckoutEmailJob.
     * Returns id of inserted job id
     * Throws DatabaseException on insert error
     */
    static function insertCreateTestGuestCheckoutEmailJob( $firstName, $lastName, $emailAddress ) {
        return self::insertJobOfType( 'com.macbackpackers.jobs.CreateTestGuestCheckoutEmailJob',
            array( "first_name" => empty($firstName) ? "" : $firstName,
                   "last_name" => empty($lastName) ? "" : $lastName,
                   "email_address" => $emailAddress ) );
    }

    /**
     * Inserts a new SendAllUnsentEmailJob.
     * Returns id of inserted job id
     * Throws DatabaseException on insert error
     */
    static function insertSendAllUnsentEmailJob() {
        return self::insertJobOfType( 'com.macbackpackers.jobs.SendAllUnsentEmailJob');
    }

    /**
     * Inserts a new AllocationScraperJob parameter.
     * $mysqli : manual db connection (for transaction handling)
     * $jobId : id of job
     * $paramName : name of parameter
     * $paramValue : value of parameter
     * Returns id of inserted job param id
     * Throws DatabaseException on insert error
     */
    static function insertJobParameter($mysqli, $jobId, $paramName, $paramValue) {
    
        global $wpdb;
        $stmt = $mysqli->prepare(
            "INSERT INTO ".$wpdb->prefix."lh_job_param(job_id, name, value)
             VALUES(?, ?, ?)");
        $stmt->bind_param('iss', 
            $jobId,
            $paramName, 
            $paramValue);
        
        if(false === $stmt->execute()) {
            throw new DatabaseException("Error during INSERT: " . $mysqli->error);
        }
        $stmt->close();

        return $mysqli->insert_id;
    }
    
    /**
     * Returns the date of the last allocation scraper job that
     * hasn't been run/completed yet or null if none exists.
     */
    static function getOutstandingAllocationScraperJob() {
        global $wpdb;
        $resultset = $wpdb->get_results($wpdb->prepare(
               "SELECT MIN(created_date) `created_date`
                  FROM ".$wpdb->prefix."lh_jobs 
                 WHERE classname IN (
                           'com.macbackpackers.jobs.AllocationScraperJob', 
                           'com.macbackpackers.jobs.AllocationScraperWorkerJob', 
                           'com.macbackpackers.jobs.CreateAllocationScraperReportsJob', 
                           'com.macbackpackers.jobs.BookingScraperJob', 
                           'com.macbackpackers.jobs.SplitRoomReservationReportJob',
                           'com.macbackpackers.jobs.UnpaidDepositReportJob',
                           'com.macbackpackers.jobs.GroupBookingsReportJob' )
                   AND status IN ( %s, %s )",  
                self::STATUS_SUBMITTED, self::STATUS_PROCESSING ));

        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        // guaranteed null or int
        $rec = array_shift($resultset);

        // if null, then no job exists
        if( $rec->created_date == null) {
            return null;
        }
        return $rec->created_date;
    }

    /**
     * Returns the date of the last allocation scraper job that
     * ran succesfully.
     */
    static function getLastCompletedAllocationScraperJob() {
        global $wpdb;
        $resultset = $wpdb->get_results($wpdb->prepare(
               "SELECT MAX(end_date) `end_date`
                  FROM ".$wpdb->prefix."lh_jobs 
                 WHERE classname IN (
                           'com.macbackpackers.jobs.AllocationScraperJob', 
                           'com.macbackpackers.jobs.BookingScraperJob', 
                           'com.macbackpackers.jobs.SplitRoomReservationReportJob',
                           'com.macbackpackers.jobs.UnpaidDepositReportJob' )
                   AND status IN ( %s )",  
                self::STATUS_COMPLETED ));

        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        // guaranteed null or int
        $rec = array_shift($resultset);

        // if null, then no job exists
        if( $rec->end_date == null) {
            return null;
        }
        return $rec->end_date;
    }

    /**
     * Returns the date of the last job that hasn't been run/completed yet
     * or null if none exists.
     * $jobName : name of job to query
     */
    static function getDateTimeOfLastOutstandingJob( $jobName ) {
        global $wpdb;
        $resultset = $wpdb->get_results($wpdb->prepare(
               "SELECT MIN(created_date) `created_date`
                  FROM ".$wpdb->prefix."lh_jobs 
                 WHERE classname = %s
                   AND status IN ( %s, %s )",  
                $jobName, self::STATUS_SUBMITTED, self::STATUS_PROCESSING ));

        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        // guaranteed null or int
        $rec = array_shift($resultset);

        // if null, then no job exists
        if( $rec->created_date == null) {
            return null;
        }
        return $rec->created_date;
    }

    /**
     * Returns the details for the last finished job.
     * $jobName : the fully qualified name of the job
     * Returns array with the following keys (or null if last job doesn't exist):
     *   jobId : PK of job
     *   status : job status
     *   lastJobFailedDueToCredentials : true if status = 'failed' and failure due to incorrect credentials, false otherwise
     */
    static function getDetailsOfLastJob( $jobName ) {

        // first, determine the status of the job
        global $wpdb;
        $resultset = $wpdb->get_results($wpdb->prepare(
               "SELECT job_id, status
                  FROM ".$wpdb->prefix."lh_jobs
                 WHERE classname = %s
                   AND job_id = (SELECT MAX(job_id) 
                                   FROM ".$wpdb->prefix."lh_jobs 
				                  WHERE classname = %s
                                    AND status NOT IN (%s, %s))",  
                $jobName, $jobName, self::STATUS_SUBMITTED, self::STATUS_PROCESSING ));

        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        // no job has finished running yet
        if( empty( $resultset )) {
            return null;
        }

        $jobDetailsRow = array_shift($resultset);
        return array(
                'jobId' => $jobDetailsRow->job_id,
                'status' => $jobDetailsRow->status,
                'lastJobFailedDueToCredentials' => self::isCredentialsValidErrorMessageForJob( $jobDetailsRow->job_id )
            );        
    }

    /**
     * This is probably not the best way to do this, but it'll do for now.
     * Sift through the log messages for the given job and look for the
     * error message where LH credentials don't seem to be valid.
     * Returns true if error message found, false otherwise.
     */
    static function isCredentialsValidErrorMessageForJob( $jobId ) {

        if (substr(php_uname(), 0, 7) != "Windows") {
            $logDirectory = get_option( 'hbo_log_directory' );
            if( empty($logDirectory )) {
                throw new ValidationException( "log_directory not specified" );
            }

            $command = "grep -q 'Current credentials not valid' $logDirectory/job-$jobId.txt";
            $returnval = 0;
            $output = array();
            exec( $command, $output, $returnval );
            if ( $returnval == 0 ) {
                return true;
            }

            $command = "grep -q 'Incorrect password' $logDirectory/job-$jobId.txt";
            exec( $command, $output, $returnval );
            if ( $returnval == 0 ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns the date of the last allocation scraper job that
     * ran succesfully before then given date or null if none found.
     *
     * $selectedDate : do not include jobs after this DateTime
     * Returns recordset (job_id, end_date)
     */
    static function getLastCompletedBedCountJob( $selectedDate ) {
        global $wpdb;
        $resultset = $wpdb->get_results($wpdb->prepare(
               "SELECT j.job_id, j.end_date
                  FROM ".$wpdb->prefix."lh_jobs j
                  JOIN ".$wpdb->prefix."lh_job_param p ON j.job_id = p.job_id AND p.name = 'selected_date'
                  JOIN (SELECT %s `selected_date`) const
                 WHERE j.classname = 'com.macbackpackers.jobs.BedCountJob'
                   AND j.status IN ( %s )
                   -- 7 days is hard-coded in the BedCountJob (number of days to query data from)
                   AND DATE_ADD(STR_TO_DATE(p.value, '%%Y-%%m-%%d'), INTERVAL -7 DAY ) <= const.selected_date
                   AND STR_TO_DATE(p.value, '%%Y-%%m-%%d') >= const.selected_date
                 ORDER BY j.end_date desc
                 LIMIT 1",
                $selectedDate->format('Y-m-d'), self::STATUS_COMPLETED ) );

        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        // if empty, then no job exists
        if(empty($resultset)) {
            return null;
        }

        // return single row
        $rec = array_shift($resultset);
        return $rec;
    }

    /**
     * Returns the date of the last job that ran succesfully.
     * Returns null if none found.
     */
    static function getLastCompletedJob( $jobName ) {
        return self::getLastRunJobOfType( $jobName, self::STATUS_COMPLETED );
    }

    /**
     * Returns the date of the last job that failed.
     * Returns null if none found.
     */
    static function getLastFailedJob( $jobName ) {
        return self::getLastRunJobOfType( $jobName, self::STATUS_FAILED );
    }

    /**
     * Returns the date/time of the last job of the given type and status.
     * If none found, this function will return NULL.
     */
    static function getLastRunJobOfType( $jobName, $status ) {
        global $wpdb;
        $resultset = $wpdb->get_results($wpdb->prepare(
               "SELECT MAX(end_date) `end_date`
                  FROM ".$wpdb->prefix."lh_jobs 
                 WHERE classname = %s
                   AND status = %s",  
                $jobName, $status ));

        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        // guaranteed null or int
        $rec = array_shift($resultset);

        // if null, then no job exists
        if( $rec->end_date == null) {
            return null;
        }
        return $rec->end_date;
    }

    /**
     * Returns the status of the given job.
     * If job not found, this function will throw a DatabaseException.
     * $jobId : id of job
     */
    static function getStatusOfJob( $jobId ) {
        return self::getJobDetails( $jobId )->status;
    }

    /**
     * Returns the properties of the given job.
     * If job not found, this function will throw a DatabaseException.
     * $jobId : id of job
     */
    static function getJobDetails( $jobId ) {
        global $wpdb;
        $resultset = $wpdb->get_results($wpdb->prepare(
               "SELECT job_id, classname, status, start_date, end_date
                  FROM ".$wpdb->prefix."lh_jobs 
                 WHERE job_id = %d",  
                $jobId ));

        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        // if empty, then no job exists
        if(empty($resultset)) {
            throw new DatabaseException( "Job $jobId not found." );
        }

        // return single row
        return array_shift($resultset);
    }

    /**
     * Returns bedcount report.
     * $selectedDate : DateTime for selection date
     * $allocJobId : completed AllocationScraperJobId to use for querying data
     */
    static function getBedcountReport( $selectedDate, $allocJobId ) {
        global $wpdb;

        $sql = "SELECT room, capacity, room_type, 
                    -- these are the room types in the bedcounts for HSH
					CASE WHEN capacity = 2 THEN 'Double/Twin'
					WHEN capacity = 4 THEN 'Quad/4 Bed Dorm'
					WHEN capacity BETWEEN 16 AND 18 THEN '16-18 Bed Dorm'
					WHEN capacity BETWEEN 6 AND 12 THEN '6-12 Bed Dorm'
					ELSE 'Unknown' END AS hsh_room_type,
                    -- magnify private rooms based on size of room
                    IF(room_type IN ('DBL','TRIPLE','QUAD','TWIN'), num_empty * capacity, num_empty) `num_empty`, 
                    IF(room_type IN ('DBL','TRIPLE','QUAD','TWIN'), num_staff * capacity, num_staff) `num_staff`, 
                    IF(room_type IN ('DBL','TRIPLE','QUAD','TWIN'), num_paid * capacity, num_paid) `num_paid`, 
                    IF(room_type IN ('DBL','TRIPLE','QUAD','TWIN'), num_noshow * capacity, num_noshow) `num_noshow`
              FROM (
               -- room 30 is split into separate rooms for some reason; collapse them
               SELECT IF(p.room_type = 'OVERFLOW', '30', p.room) `room`, IF(p.room_type = 'OVERFLOW', 7, p.capacity) `capacity`, p.room_type,
                      SUM(IF(p.reservation_id IS NULL AND p.room_type != 'OVERFLOW', 1, 0)) `num_empty`,
                      SUM(IF(p.reservation_id = 0, 1, 0)) `num_staff`, 
                      SUM(IF(p.lh_status IN ('checked-in', 'checked-out') AND p.reservation_id > 0, 1, 0)) `num_paid`, 
                      SUM(IF(IFNULL(p.lh_status, '') NOT IN ('checked-in', 'checked-out') AND p.reservation_id > 0, 1, 0)) `num_noshow`
                 FROM (
                   SELECT rm.room, rm.bed_name, rm.capacity, rm.room_type, c.reservation_id, c.payment_outstanding, c.guest_name, c.notes, c.lh_status
                     FROM ".$wpdb->prefix."lh_rooms rm
                     LEFT OUTER JOIN 
                       ( SELECT cal.* FROM ".$wpdb->prefix."lh_calendar cal, (select %s AS selection_date) const
                          WHERE cal.job_id = %d -- the job_id to use data for
                            AND cal.checkin_date <= const.selection_date
                            AND cal.checkout_date > const.selection_date
                       ) c 
                       -- if unallocated (room_id = null), then ignore this join field and match on room_type_id
                       ON IFNULL(c.room_id, rm.id) = rm.id AND IFNULL(c.room, 'Unallocated') = rm.room AND c.room_type_id = rm.room_type_id
					WHERE rm.active_yn = 'Y' OR rm.room_type = 'OVERFLOW' OR rm.room = 'Unallocated'
              ) p
             GROUP BY IF(p.room_type = 'OVERFLOW', '30', p.room), p.capacity, p.room_type
          ) t
          -- only include OVERFLOW or Unallocated if we have something to report
         WHERE (room_type != 'OVERFLOW' AND room != 'Unallocated')
            OR ((room_type = 'OVERFLOW' OR room = 'Unallocated') AND (num_staff > 0 OR num_paid > 0 OR num_noshow > 0))
         ORDER BY room";

        // HSH bedcounts are actually by room type
        if( get_option('hbo_lilho_username') == 'highstreet' ) {
            $sql = "SELECT GROUP_CONCAT(room ORDER BY room SEPARATOR ', ') AS room,
                           hsh_room_type AS room_type, 
                           SUM(capacity) AS capacity,
		                   SUM(num_empty) AS num_empty, SUM(num_staff) AS num_staff, SUM(num_paid) AS num_paid, SUM(num_noshow) AS num_noshow
                      FROM ( $sql ) hsh
                     GROUP BY hsh_room_type
                     ORDER BY capacity";
        }
         
        $resultset = $wpdb->get_results($wpdb->prepare(
            $sql, $selectedDate->format('Y-m-d H:i:s'), $allocJobId ));

        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        return $resultset;
    }

    /**
     * Returns the date of the last booking diffs job that
     * ran succesfully for the given date or null if none found.
     *
     * $selectedDate : do not include jobs after this DateTime
     * Returns recordset (job_id, end_date)
     */
    static function getLastCompletedBookingDiffsJob( $selectedDate ) {
        global $wpdb;
        $resultset = $wpdb->get_results($wpdb->prepare(
               "SELECT j.job_id, j.end_date
                  FROM ".$wpdb->prefix."lh_jobs j
                  JOIN ".$wpdb->prefix."lh_job_param p ON j.job_id = p.job_id AND p.name = 'checkin_date'
                  JOIN (SELECT %s `selected_date`) const
                 WHERE j.classname = 'com.macbackpackers.jobs.DiffBookingEnginesJob'
                   AND j.status IN ( %s )
                   -- include a 7 day window from the start of each booking diff job
                   -- this corresponds with the 'days-ahead' we look in the actual job when scraping data
                   AND const.selected_date <= DATE_ADD(STR_TO_DATE(p.value, '%%Y-%%m-%%d'), INTERVAL 7 DAY )
                   AND STR_TO_DATE(p.value, '%%Y-%%m-%%d') <= const.selected_date
                 ORDER BY j.end_date desc
                 LIMIT 1",
                $selectedDate->format('Y-m-d'), self::STATUS_COMPLETED ) );

        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        // if empty, then no job exists
        if(empty($resultset)) {
            return null;
        }

        // return single row
        $rec = array_shift($resultset);
        return $rec;
    }

    /**
     * Returns booking engine diff report.
     * $selectedDate : DateTime for selection date
     * $jobId : completed jobId to use for querying data
     */
    static function getBookingDiffsReport( $selectedDate, $jobId ) {
        global $wpdb;

        $resultset = $wpdb->get_results($wpdb->prepare(
            "SELECT y.guest_name, 
                    IF( y.room_type IN ('DBL','TRIPLE','QUAD','TWIN'), y.room_type, IF(y.room_type_id IS NULL, y.room_type, CONVERT(CONCAT(y.capacity, y.room_type) USING utf8))) `hw_room_type`,
                    y.checkin_date `hw_checkin_date`, y.checkout_date `hw_checkout_date`, y.hw_persons, y.payment_outstanding `hw_payment_outstanding`, y.booked_date, y.booking_source,
                    y.booking_reference, 
	                IF( z.room_type IN ('DBL','TRIPLE','QUAD','TWIN'), z.room_type, CONVERT(CONCAT(z.capacity, z.room_type) USING utf8)) `lh_room_type`, z.lh_status,
                    z.checkin_date `lh_checkin_date`, z.checkout_date `lh_checkout_date`, z.lh_persons, z.payment_outstanding `lh_payment_outstanding`, z.data_href, z.notes,
                    IF( IFNULL(y.hw_person_count,0) = IFNULL(z.lh_persons,0), 'Y', 'N' ) `matched_persons`,
	                IF( IFNULL(y.room_type_id,-1) = IFNULL(z.room_type_id,0), 'Y', 'N' ) `matched_room_type`, -- if room type id not matched, this will always be N
                    IF( IFNULL(y.checkin_date,0) = IFNULL(z.checkin_date,0), 'Y', 'N') `matched_checkin_date`,
                    IF( IFNULL(y.checkout_date,0) = IFNULL(z.checkout_date,0), 'Y', 'N') `matched_checkout_date`,
                    IF( IFNULL(z.lh_status, 'null') IN ('checked-in', 'checked-out') OR IFNULL(y.payment_outstanding,'null') = IFNULL(z.payment_outstanding,'null') OR z.payment_outstanding = 0, 'Y', 'N') `matched_payment_outstanding`
             FROM (
               -- all unique HW records for the given job_id
               SELECT b.booking_reference, b.booking_source, b.guest_name, b.booked_date, b.persons `hw_persons`, b.payment_outstanding, d.persons `hw_person_count`, d.room_type_id, IF(d.room_type_id IS NULL, d.room_type, r.room_type) `room_type`, r.capacity,
                      (SELECT COUNT(DISTINCT e.room_type_id) FROM ".$wpdb->prefix."hw_booking_dates e WHERE e.hw_booking_id = b.id ) `num_room_types`, -- keep track of bookings that contain more than one room type
		              MIN(d.booked_date) `checkin_date`, DATE_ADD(MAX(d.booked_date), INTERVAL 1 DAY) `checkout_date`
                 FROM ".$wpdb->prefix."hw_booking b
                 JOIN ".$wpdb->prefix."hw_booking_dates d ON b.id = d.hw_booking_id
                 LEFT OUTER JOIN (SELECT DISTINCT room_type_id, room_type, capacity FROM ".$wpdb->prefix."lh_rooms) r ON r.room_type_id = d.room_type_id
                GROUP BY b.booking_reference, b.booking_source, b.guest_name, b.booked_date, b.persons, b.payment_outstanding, d.persons, d.room_type_id, d.room_type, r.room_type, r.capacity
               HAVING MIN(d.booked_date) = %s -- checkin date
             ) y
             LEFT OUTER JOIN (
               -- all unique LH records for the given job_id
               SELECT c.booking_reference, c.guest_name, c.booked_date, c.lh_status, c.room_type_id, c.checkin_date, c.checkout_date, c.data_href, c.payment_outstanding, c.notes, r.room_type, r.capacity,
                      IF(c.lh_status = 'cancelled', c.num_guests, SUM(IFNULL((SELECT MAX(r.capacity) FROM ".$wpdb->prefix."lh_rooms r WHERE r.room_type IN ('DBL', 'TWIN', 'TRIPLE', 'QUAD') AND r.room_type_id = c.room_type_id), 1 ))) `lh_persons`
                 FROM ".$wpdb->prefix."lh_calendar c 
                 JOIN (SELECT DISTINCT room_type_id, room_type, capacity FROM ".$wpdb->prefix."lh_rooms) r ON r.room_type_id = c.room_type_id
                WHERE c.job_id = %d
                  AND ( c.booking_source = 'Hostelbookers' OR c.booking_source LIKE 'Hostelworld%%' )
                GROUP BY c.booking_reference, c.guest_name, c.booked_date, c.lh_status, c.room_type_id, c.checkin_date, c.checkout_date, c.data_href, c.payment_outstanding, c.notes, r.room_type, r.capacity
             ) z ON CONCAT(IF(y.booking_source = 'Hostelbookers', 'HBK-', 'HWL-551-'), y.booking_reference) = z.booking_reference 
                -- if there is only 1 room type, then match by booking ref only
                AND IFNULL(y.room_type_id, 0) = IF(y.num_room_types > 1, z.room_type_id, IFNULL(y.room_type_id, 0))", 
         $selectedDate->format('Y-m-d'), $jobId ));

        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        return $resultset;
    }

    /**
     * Returns array of all ManualChargeJobs.
     */
    static function fetchLastManualTransactions() {
        global $wpdb;
        $resultset = $wpdb->get_results(
               "SELECT * FROM (
                    SELECT jp1.value AS booking_reference, NULL as post_date, NULL AS masked_card_number, CAST(jp2.value AS DECIMAL(10,2)) AS payment_amount, 
                           NULL as successful, NULL AS help_text, j.status, 
                           (SELECT MAX(c.data_href) FROM ".$wpdb->prefix."lh_calendar c WHERE c.booking_reference = jp1.value) AS data_href,
                           (SELECT MAX(c.checkin_date) FROM ".$wpdb->prefix."lh_calendar c WHERE c.booking_reference = jp1.value) AS checkin_date,
                           COALESCE(j.last_updated_date, j.created_date) AS last_updated_date
                      FROM ".$wpdb->prefix."lh_jobs j
                      JOIN ".$wpdb->prefix."lh_job_param jp1 ON j.job_id = jp1.job_id AND jp1.name = 'booking_ref'
                      JOIN ".$wpdb->prefix."lh_job_param jp2 ON j.job_id = jp2.job_id AND jp2.name = 'amount'
                     WHERE j.classname IN ('com.macbackpackers.jobs.NoShowChargeJob', 'com.macbackpackers.jobs.ManualChargeJob')
                       AND jp1.value NOT IN (SELECT p.booking_reference FROM wp_pxpost_transaction p WHERE p.booking_reference LIKE 'HWL-%')
                     UNION ALL
                    SELECT p.booking_reference, p.post_date, p.masked_card_number, p.payment_amount, p.successful, p.help_text, j.status,
                           (SELECT MAX(c.data_href) FROM ".$wpdb->prefix."lh_calendar c WHERE c.booking_reference = p.booking_reference) AS data_href,
                           (SELECT MAX(c.checkin_date) FROM ".$wpdb->prefix."lh_calendar c WHERE c.booking_reference = p.booking_reference) AS checkin_date,
                           COALESCE(j.last_updated_date, j.created_date, p.last_updated_date, p.created_date) AS last_updated_date
                      FROM ".$wpdb->prefix."pxpost_transaction p
                      LEFT OUTER JOIN ".$wpdb->prefix."lh_job_param jp ON jp.name = 'booking_ref' AND jp.value = p.booking_reference
                      JOIN ".$wpdb->prefix."lh_jobs j ON jp.job_id = j.job_id AND j.classname IN ('com.macbackpackers.jobs.NoShowChargeJob', 'com.macbackpackers.jobs.ManualChargeJob')
                     WHERE p.booking_reference LIKE 'HWL-%'
                 ) t ORDER BY last_updated_date DESC");

        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        // if empty, then no jobs exists
        if(empty($resultset)) {
            return null;
        }
        return $resultset;
    }

    /**
     * Adds a new cleaner to the roster.
     */
    static function addCleaner($firstName, $lastName) {
         global $wpdb;
        if (false === $wpdb->insert($wpdb->prefix ."lh_cleaner", 
                array( 'first_name' => $firstName, 
                       'last_name' => $lastName,
                       'active_yn' => 'Y' ), 
                array( '%s', '%s' ))) {
            error_log($wpdb->last_error." executing sql: ".$wpdb->last_query);
            throw new DatabaseException( $wpdb->last_error );
        }

        return $wpdb->insert_id;
   }

    /**
     * Assigns a bed to the given cleaner for the given dates.
     * cleanerId : unique id of cleaner to update
     * roomId : unique id of room to assign to
     * checkinDate : datetime of checkin
     * checkoutDate : datetime of checkout
     * Returns id of inserted assignment record
     */
    static function addCleanerBedAssignment($cleanerId, $roomId, $checkinDate, $checkoutDate) {
        global $wpdb;

        if( $checkinDate >= $checkoutDate ) {
            throw new ValidationException( "checkout date must be after checkin date" );
        }

        // first check if we have a date overlap
        $resultset = $wpdb->get_results($wpdb->prepare(
            "SELECT 1 FROM ".$wpdb->prefix."lh_cleaner_bed_assign
              WHERE lh_cleaner_id = %d
                AND %s < end_date
                AND %s > start_date",
            $cleanerId,
            $checkinDate->format('Y-m-d'),
            $checkoutDate->format('Y-m-d') ) );

        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        // if not empty, then an existing assignment date overlap exists
        if(false === empty($resultset)) {
            throw new ValidationException( "Date overlap detected" );
        }

        if (false === $wpdb->insert($wpdb->prefix ."lh_cleaner_bed_assign", 
                array( 'lh_cleaner_id' => $cleanerId, 
                       'room_id' => $roomId,
                       'start_date' => $checkinDate->format('Y-m-d'),
                       'end_date' => $checkoutDate->format('Y-m-d') ), 
                array( '%s', '%s', '%s', '%s' ))) {
            error_log($wpdb->last_error." executing sql: ".$wpdb->last_query);
            throw new DatabaseException( $wpdb->last_error );
        }

        return $wpdb->insert_id;
   }

    /**
     * Returns all cleaners including bed assignments (array of LHCleaner)
     */
    static function getCleaners() {
        global $wpdb;
        $resultset = $wpdb->get_results(
           "SELECT id, first_name, last_name, active_yn
              FROM ".$wpdb->prefix."lh_cleaner
             ORDER BY id");

        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        $cleaners = array();
        foreach( $resultset as $record ) {
            $cleaner = new LHCleaner( $record->id, $record->first_name, $record->last_name, $record->active_yn == 'Y' );
            $cleaner->loadBedAssignments();
            $cleaners[] = $cleaner;
        }
        return $cleaners;
    }

    /**
     * Returns the bed assignments on the specified cleaner.
     * $cleanerId : cleaner ID to query
     */
    static function getBedAssignmentsForCleaner( $cleanerId ) {
        global $wpdb;
        $resultset = $wpdb->get_results($wpdb->prepare(
           "SELECT cba.id, DATE_FORMAT( cba.start_date, '%%Y-%%m-%%d' ) AS start_date, 
                   DATE_FORMAT( cba.end_date, '%%Y-%%m-%%d' ) AS end_date, 
                   cba.room_id, r.room, r.bed_name
              FROM ".$wpdb->prefix."lh_cleaner_bed_assign cba
              JOIN ".$wpdb->prefix."lh_rooms r ON cba.room_id = r.id
             WHERE cba.lh_cleaner_id = %d
             ORDER BY cba.start_date", $cleanerId ));

        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        return $resultset;
    }

    /**
     * Fetches a list of all beds available for cleaners.
     */
    static function getAllAssignableCleanerBeds() {
        global $wpdb;
        $resultset = $wpdb->get_results(
           " SELECT id, room, bed_name 
               FROM ".$wpdb->prefix."lh_rooms
              WHERE active_yn = 'Y'
                AND room_type NOT IN ( 'DBL', 'QUAD', 'TRIPLE', 'TWIN' )
           ORDER BY room, bed_name");

        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        $cleanerBeds = array();
        foreach( $resultset as $record ) {
            $cleanerBeds[] = new LHBedAssignment($record->id, $record->room, $record->bed_name);
        }
        return $cleanerBeds;
    }

    /**
     * Adds a new cleaner task. Returns id of created task.
     * $name : name of task
     * $description : description of task
     * $defaultHours : (int) default number of hours for this tak
     * $active : (boolean) true if active, false if not
     * $showInDailyTasks : (boolean; default false) true if task should be shown in the daily tasks page
     * $sortOrder : (int; default 0) the order this task appears on the daily tasks page
     * $frequency : (int; default 1) the number of times this task appears on the daily tasks page
     */
    static function addCleanerTask( $name, $description, $defaultHours, $active, $showInDailyTasks = false, $sortOrder = 0, $frequency = 1 ) {
        global $wpdb;
        if (false === $wpdb->insert($wpdb->prefix ."lh_cleaner_task", 
                array( 'name' => $name, 
                       'description' => $description,
                       'default_hours' => $defaultHours,
                       'active_yn' => $active ? 'Y' : 'N',
                       'show_in_daily_tasks_yn' => $showInDailyTasks ? 'Y' : 'N',
                       'sort_order' => $sortOrder,
                       'frequency' => $frequency,
                       'last_updated_date' => current_time('mysql', 1) ), 
                array( '%s', '%s', '%d', '%s', '%s', '%d' ))) {
            error_log($wpdb->last_error." executing sql: ".$wpdb->last_query);
            throw new DatabaseException( $wpdb->last_error );
        }

        return $wpdb->insert_id;
   }

    /**
     * Updates an existing cleaner task.
     * $id : id of task to edit
     * $name : name of task
     * $description : description of task
     * $defaultHours : (int) default number of hours for this task
     * $active : (boolean) true if active, false if not
     * $showInDailyTasks : (boolean; default false) true if task should be shown in the daily tasks page
     * $sortOrder : (int; default 0) the order this task appears on the daily tasks page
     * $frequency : (int; default 1) the number of times this task appears on the daily tasks page
     */
    static function updateCleanerTask( $id, $name, $description, $defaultHours, $active, $showInDailyTasks = false, $sortOrder = 0, $frequency = 1 ) {
        global $wpdb;
        $returnval = $wpdb->update(
            $wpdb->prefix."lh_cleaner_task",
                array( 'name' => $name,
                       'description' => $description,
                       'default_hours' => $defaultHours,
                       'active_yn' => $active ? 'Y' : 'N',
                       'show_in_daily_tasks_yn' => $showInDailyTasks ? 'Y' : 'N',
                       'sort_order' => $sortOrder,
                       'frequency' => $frequency,
                       'last_updated_date' => current_time('mysql', 1)),
                array( 'id' => $id ) );
        
        if(false === $returnval) {
            throw new DatabaseException("Error occurred during UPDATE");
        }
    }

    /**
     * Returns all the wp_lh_cleaner_task records.
     */
    static function getCleanerTasks() {

        global $wpdb;
        $resultset = $wpdb->get_results(
            "SELECT id, name, description, default_hours, active_yn, show_in_daily_tasks_yn, sort_order, frequency
               FROM ".$wpdb->prefix."lh_cleaner_task
              ORDER BY id");

        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        return $resultset;
    }

    /**
     * Returns the wp_lh_cleaner_task record for the given unique task id.
     * $taskId : PK of cleaner task table
     */
    static function getCleanerTask( $taskId ) {

        global $wpdb;
        $resultset = $wpdb->get_results($wpdb->prepare(
            "SELECT id, name, description, default_hours, active_yn, show_in_daily_tasks_yn, sort_order, frequency
               FROM ".$wpdb->prefix."lh_cleaner_task
              WHERE id = %d", $taskId ));

        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        // if empty, then no record exists
        if(empty($resultset)) {
            throw new DatabaseException( 'Invalid cleaner task: ' . $taskId );
        }

        // return single row
        $rec = array_shift($resultset);
        return $rec;
    }

    /**
     * Returns the wp_lh_jobs records for the past number of days in reverse chrono order.
     * $numberOfDays : number of days to include in the past
     * $maxNumRecords : maximum number of records to include (optional)
     */
    static function getJobHistory( $numberOfDays, $maxNumRecords = null ) {

        // include those records from the given number of days
        global $wpdb;
        $resultset = $wpdb->get_results($wpdb->prepare(
            "SELECT job_id, classname, status, start_date, end_date
               FROM ".$wpdb->prefix."lh_jobs
              WHERE IFNULL(last_updated_date, created_date) > NOW() - INTERVAL %d DAY
              ORDER BY job_id DESC " . 
              ($maxNumRecords != null ? "LIMIT $maxNumRecords" : ""), $numberOfDays));

        $jobHistories = array();
        foreach( $resultset as $record ) {
            $jobHistories[$record->job_id] = $record;
        }
        return $jobHistories;
    }

    /**
     * Returns the job parameters for the given job
     * $jobId : PK of job
     * Returns array keyed by job parameter name containing value
     */
    static function getJobParameters( $jobId ) {

        // include those records from the given number of days
        global $wpdb;
        $resultset = $wpdb->get_results($wpdb->prepare(
            "SELECT name, value
               FROM ".$wpdb->prefix."lh_job_param
              WHERE job_id = %d", $jobId));

        $jobParams = array();
        foreach( $resultset as $record ) {
            $jobParams[$record->name] = $record->value;
        }
        return $jobParams;
    }

    /**
     * Executes the processor in the background from the command line.
     */
    static function runProcessor() {
        $process_cmd = get_option('hbo_run_processor_cmd');
        if (substr(php_uname(), 0, 7) != "Windows" && false === empty($process_cmd)) {
            $command = "nohup $process_cmd > /dev/null 2>&1 &";
            exec( $command );
        }
    }

    /**
     * Executes the processor from the command line and waits for its completion.
     */
    static function runProcessorAndWait() {
        $process_cmd = get_option('hbo_run_processor_cmd');
        if (substr(php_uname(), 0, 7) != "Windows" && false === empty($process_cmd)) {
            $command = "$process_cmd > /dev/null 2>&1";
            exec( $command );
        }
    }
}

?>