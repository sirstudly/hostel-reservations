<?php

/**
 * Database object for little hotelier tables.
 */
class LilHotelierDBO {

    const STATUS_COMPLETED = 'completed';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_PROCESSING = 'processing';

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
                         WHEN MOD(DATEDIFF(constants.selected_date, c.checkin_date), 3) = 0 THEN '3 DAY CHANGE'
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
     * $jobName : classname of Job to create
     * $jobParams : associative array of param name => param value for Job
     */
    static function insertJobOfType( $jobName, $jobParams ) {
        global $wpdb;
        if (false === $wpdb->insert($wpdb->prefix ."lh_jobs", 
                array( 'classname' => $jobName, 
                       'status' => self::STATUS_SUBMITTED, 
                       'last_updated_date' => current_time('mysql') ), 
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
              WHERE job_id = (SELECT MAX(job_id) FROM ".$wpdb->prefix."lh_rpt_split_rooms)
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
        $resultset = $wpdb->get_results(
            "SELECT reservation_id, guest_name, booking_reference, booking_source, checkin_date, checkout_date, 
                    booked_date, payment_outstanding, num_guests, data_href, notes, viewed_yn 
               FROM ".$wpdb->prefix."lh_group_bookings
              WHERE job_id = (SELECT MAX(job_id) FROM ".$wpdb->prefix."lh_group_bookings)
              ORDER BY checkin_date");

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
              WHERE job_id = (SELECT MAX(job_id) FROM ".$wpdb->prefix."lh_rpt_unpaid_deposit)
              ORDER BY checkin_date");

        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        return $resultset;
    }

    /**
     * Inserts a new AllocationScraperJob.
     * Returns id of inserted job id
     * Throws DatabaseException on insert error
     */
    static function insertAllocationScraperJob() {
        $dblink = new DbTransaction();
        try{
            $jobId = self::doInsertAllocationScraperJob($dblink->mysqli);
            $dblink->mysqli->commit();
            $dblink->mysqli->close();
            return $jobId;

        } catch(Exception $e) {
            $dblink->mysqli->rollback();
            $dblink->mysqli->close();
            throw $e;
        }
    }

    /**
     * Inserts a new AllocationScraperJob.
     * $mysqli : manual db connection (for transaction handling)
     * Returns id of inserted job id
     * Throws DatabaseException on insert error
     */
    static function doInsertAllocationScraperJob($mysqli) {
    
        global $wpdb;
        $stmt = $mysqli->prepare(
            "INSERT INTO ".$wpdb->prefix."lh_jobs(classname, `status`, created_date, last_updated_date)
             VALUES('com.macbackpackers.jobs.AllocationScraperJob', 'submitted', NOW(), NOW())");
        
        if(false === $stmt->execute()) {
            throw new DatabaseException("Error during INSERT: " . $mysqli->error);
        }
        $stmt->close();

        // insert the start end - end dates as parameters
        $jobId = $mysqli->insert_id;

        $startDate = new DateTime();
        self::insertJobParameter($mysqli, $jobId, 'start_date', $startDate->format('Y-m-d'));
        self::insertJobParameter($mysqli, $jobId, 'days_ahead', '140'); // get data for next 4-5 months

        return $jobId;
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
                           'com.macbackpackers.jobs.BookingScraperJob', 
                           'com.macbackpackers.jobs.SplitRoomReservationReportJob',
                           'com.macbackpackers.jobs.UnpaidDepositReportJob' )
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
     * Returns bedcount report.
     * $selectedDate : DateTime for selection date
     * $allocJobId : completed AllocationScraperJobId to use for querying data
     */
    static function getBedcountReport( $selectedDate, $allocJobId ) {
        global $wpdb;

        $resultset = $wpdb->get_results($wpdb->prepare(
            "SELECT room, capacity, room_type, 
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
              ) p
             GROUP BY IF(p.room_type = 'OVERFLOW', '30', p.room), p.capacity, p.room_type
          ) t
          -- only include OVERFLOW or Unallocated if we have something to report
         WHERE (room_type != 'OVERFLOW' AND room != 'Unallocated')
            OR ((room_type = 'OVERFLOW' OR room = 'Unallocated') AND (num_staff > 0 OR num_paid > 0 OR num_noshow > 0))
         ORDER BY room", 
         $selectedDate->format('Y-m-d H:i:s'), $allocJobId ));

        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        return $resultset;
    }

}

?>