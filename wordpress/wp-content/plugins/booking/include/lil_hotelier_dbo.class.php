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
        $resultset = $wpdb->get_results(
            "SELECT r.room, r.bed_name, c.job_id, c.guest_name, c.checkin_date, c.checkout_date, c.created_date,
                    MAX(c.data_href) as data_href, -- room closures can sometimes have more than one
                    CASE WHEN c.checkout_date = dr.a_date THEN 'CHANGE'
                         WHEN MOD(DATEDIFF(dr.a_date, c.checkin_date), 3) = 0 THEN '3 DAY CHANGE'
                         WHEN c.checkout_date > dr.a_date THEN 'NO CHANGE'
                         ELSE 'EMPTY' END AS bedsheet
               FROM ".$wpdb->prefix."daterange dr 
               JOIN ".$wpdb->prefix."lh_rooms r ON 1 = 1
               LEFT OUTER JOIN ".$wpdb->prefix."lh_calendar c
	             ON r.id = c.room_id 
                AND c.checkout_date >= dr.a_date
                AND c.checkin_date < dr.a_date
              WHERE dr.a_date = '" . $selectedDate->format('Y-m-d') . "'
	            AND (c.job_id = $jobId OR c.job_id IS NULL)
              GROUP BY r.room, r.bed_name, c.job_id, c.guest_name, c.checkin_date, c.checkout_date, 
                    c.created_date, dr.a_date
              ORDER BY r.room, r.bed_name");

error_log( "QUERY: " . $wpdb->last_query );
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
              WHERE GREATEST(created_date, last_updated_date) IN (
	                SELECT MAX(GREATEST(created_date, last_updated_date)) 
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
            "SELECT job_id, name, status, created_date, last_updated_date
               FROM ".$wpdb->prefix."lh_jobs
              WHERE job_id = %d", $rec->job_id));
        
        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        return array_shift($resultset);
    }

    /**
     * Inserts a new job with the given name at the status of 'submitted'.
     */
    static function insertJobOfType( $jobName ) {
        global $wpdb;
        if (false === $wpdb->insert($wpdb->prefix ."lh_jobs", 
                array( 'name' => $jobName, 'status' => self::STATUS_SUBMITTED ), array( '%s', '%s' ))) {
            error_log($wpdb->last_error." executing sql: ".$wpdb->last_query);
            throw new DatabaseException( $wpdb->last_error );
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
              WHERE name = %s 
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
                    booking_reference, booking_source, booked_date, eta, viewed_yn, notes, 
                    DATE_ADD( created_date, INTERVAL 7 HOUR ) `created_date` -- easiest way to sync to correct for timezone
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
                    booking_source, booked_date, notes, viewed_yn,
                    DATE_ADD( created_date, INTERVAL 7 HOUR ) `created_date` -- easiest way to sync to correct for timezone
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
        $endDate = new DateTime();
        $endDate->add(new DateInterval('P4M'));  // get data for the next 4 months

        self::insertJobParameter($mysqli, $jobId, 'start_date', $startDate->format('Y-m-d'));
        self::insertJobParameter($mysqli, $jobId, 'end_date', $endDate->format('Y-m-d'));

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
               "SELECT DATE_ADD( MIN(created_date), INTERVAL 7 HOUR ) `created_date` -- easiest way to sync to correct for timezone
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
               "SELECT DATE_ADD( MAX(end_date), INTERVAL 7 HOUR ) `end_date` -- easiest way to sync to correct for timezone
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
}

?>