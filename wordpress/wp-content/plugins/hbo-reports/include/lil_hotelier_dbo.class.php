<?php

/**
 * Database object for little hotelier tables.
 */
class LilHotelierDBO {

    private static $INSTANCE = null;
    private $SHARED_DB = null;
    const STATUS_COMPLETED = 'completed';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_PROCESSING = 'processing';
    const STATUS_FAILED = 'failed';

    // prevent outside instantiation
    private function __construct() {
        $this->SHARED_DB = new wpdb(SHARED_DB_USER, SHARED_DB_PASSWORD, SHARED_DB_NAME, SHARED_DB_HOST);
    }

    // The object is created from within the class itself
    // only if the class has no instance.
    /**
     * @return LilHotelierDBO
     */
    public static function getInstance() {
        if (self::$INSTANCE == null) {
            self::$INSTANCE = new LilHotelierDBO();
        }
        return LilHotelierDBO::$INSTANCE;
    }

    /**
     * Returns all bedsheet data for the given date.
     * $selectedDate : DateTime object
     * Returns raw resultset
     */
    static function fetchBedSheetsFrom($selectedDate, $jobId) {
        global $wpdb;

	    $n_day_change = get_option('hbo_bedsheets_change_after_days');
	    $n_day_change = empty( $n_day_change ) ? 1000 : $n_day_change; // set to an arbitrarily large value if not defined so it doesn't kick in

	    // query all our resources (in order)
		$resultset = $wpdb->get_results($wpdb->prepare(
            "SELECT r.room, r.bed_name, r.room_type, r.capacity, c.job_id, c.guest_name, c.checkin_date, 
                    IFNULL( c2.checkout_date, c.checkout_date ) AS `checkout_date`,
                    MAX(c.data_href) as data_href, -- room closures can sometimes have more than one
                    CASE WHEN c.lh_status = 'confirmed' THEN 'EMPTY' -- not checked-in
                         WHEN IFNULL( c2.checkout_date, c.checkout_date ) = constants.selected_date THEN 'CHANGE'
                         WHEN MOD(DATEDIFF(constants.selected_date, c.checkin_date), %d) = 0
                           -- don't do a N-day change if they're checking out the following day
                          AND DATEDIFF(IFNULL( c2.checkout_date, c.checkout_date ), constants.selected_date) > 1 THEN 'N DAY CHANGE'
                         WHEN IFNULL( c2.checkout_date, c.checkout_date ) > constants.selected_date THEN 'NO CHANGE'
                         ELSE 'EMPTY' END AS bedsheet
               FROM ( SELECT STR_TO_DATE( '%s', '%%Y-%%m-%%d' ) AS `selected_date` ) `constants`
               JOIN wp_lh_rooms r ON 1 = 1
               LEFT OUTER JOIN wp_lh_calendar c
                 ON r.id = c.room_id
                AND c.checkout_date >= constants.selected_date
                AND c.checkin_date < constants.selected_date
                AND c.job_id = %d
                    -- check if the following reservation is also the same guest
               LEFT OUTER JOIN wp_lh_calendar c2
                 ON c2.room_id = c.room_id
                AND c2.checkin_date = c.checkout_date
                AND c2.job_id = c.job_id
                AND c2.guest_name = c.guest_name
              WHERE r.room_type NOT IN ('LT_MALE', 'LT_FEMALE', 'LT_MIXED', 'OVERFLOW')
                AND r.active_yn = 'Y'
              GROUP BY r.room, r.bed_name, r.room_type, r.capacity, c.job_id, c.guest_name, c.checkin_date, c.checkout_date, c.lh_status,
                       constants.selected_date, c2.room, c2.bed_name, c2.checkin_date, c2.checkout_date, c2.job_id, c2.guest_name
              ORDER BY IF(r.room = 'TMNT', 'T3MNT', r.room), r.bed_name",
		      $n_day_change, $selectedDate->format('Y-m-d'), $jobId));

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
               FROM wp_lh_jobs
              WHERE end_date IN (
                    SELECT MAX(end_date) 
                      FROM wp_lh_jobs t
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
               FROM wp_lh_jobs
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
        if (false === $wpdb->insert("wp_lh_jobs", 
                array( 'classname' => $jobName, 
                       'status' => self::STATUS_SUBMITTED, 
                       'last_updated_date' => current_time('mysql', 1) ), 
                array( '%s', '%s', '%s' ))) {
            error_log($wpdb->last_error." executing sql: ".$wpdb->last_query);
            throw new DatabaseException( $wpdb->last_error );
        }

        $jobId = $wpdb->insert_id;
        foreach( $jobParams as $jobParamKey => $jobParamValue ) {
            if (false === $wpdb->insert("wp_lh_job_param", 
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
               FROM wp_lh_jobs
              WHERE classname = %s 
                AND status IN ( %s, %s )", $jobName, self::STATUS_SUBMITTED, self::STATUS_PROCESSING ));

        return ! empty( $resultset );        
    }

    /**
     * Returns all room/beds currently configured.
     * @return mixed resultset
     * @throws DatabaseException
     */
    static function listRoomBeds() {
        global $wpdb;
        $resultset = $wpdb->get_results(
            "SELECT id, room, bed_name
               FROM wp_lh_rooms
              WHERE room NOT IN ('Unallocated', 'PB')
                AND active_yn = 'Y'
              ORDER BY room, bed_name" );

        if ( $wpdb->last_error ) {
            throw new DatabaseException( $wpdb->last_error );
        }

        return $resultset;
    }

    /**
     * Returns all bookings for the given allocation scraper job ID.
     * @param $allocScraperJobId
     * @param $startDate
     * @param $endDate
     *
     * @return mixed
     * @throws DatabaseException
     */
    static function getAllBookings( $allocScraperJobId, $startDate, $endDate ) {
        global $wpdb;
        $resultset = $wpdb->get_results( $wpdb->prepare(
            "SELECT DISTINCT reservation_id, room_id, guest_name, email, checkin_date, checkout_date, num_guests, payment_total, 
                             payment_outstanding, lh_status, booking_reference, booking_source
               FROM wp_lh_calendar
              WHERE job_id = %d
                AND checkin_date <= %s
                AND checkout_date >= %s
                AND data_href <> 'room_closures'
              ORDER BY checkin_date",
            $allocScraperJobId, $endDate, $startDate ) );

        if ( $wpdb->last_error ) {
            throw new DatabaseException( $wpdb->last_error );
        }

        return $resultset;
    }

    /**
     * Returns all completed AllocationScraperJobs
     * @return mixed resultset with job_id, end_date
     * @throws DatabaseException
     */
    static function getAllCompletedAllocationScraperJobIds() {
        global $wpdb;
        $resultset = $wpdb->get_results( $wpdb->prepare(
            "SELECT job_id, end_date
                  FROM wp_lh_jobs 
                 WHERE classname IN ('com.macbackpackers.jobs.AllocationScraperJob')
                   AND status IN ( %s )
                 ORDER BY end_date DESC",
            self::STATUS_COMPLETED ) );

        if ( $wpdb->last_error ) {
            throw new DatabaseException( $wpdb->last_error );
        }

        return $resultset;
    }

    /**
     * Returns report where a reservation is split between rooms of the same type.
     */
    static function getSplitRoomReservationsReport() {
        global $wpdb;
        $resultset = $wpdb->get_results(
            "SELECT reservation_id, guest_name, checkin_date, checkout_date, data_href, lh_status, 
                    booking_reference, booking_source, booked_date, eta, viewed_yn, notes, created_date
               FROM wp_lh_rpt_split_rooms
              WHERE job_id IN (SELECT CAST(value AS UNSIGNED) FROM wp_lh_job_param WHERE name = 'allocation_scraper_job_id' AND job_id = (SELECT MAX(job_id) FROM wp_lh_jobs WHERE classname = 'com.macbackpackers.jobs.SplitRoomReservationReportJob' AND status = 'completed'))
              ORDER BY checkin_date");

        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        return $resultset;
    }

    /**
     * Returns report where a consecutive bookings for the same guest are in different rooms (for the same room type).
     */
    static function getSplitRoomMultipleReservationsReport() {
        global $wpdb;

        $alloc_scraper_job = self::getLatestJobOfType( "com.macbackpackers.jobs.AllocationScraperJob" );

        if ( ! $alloc_scraper_job ) {
            return array();
        }
        $job_id = $alloc_scraper_job->job_id;

        $resultset = $wpdb->get_results(
            "SELECT c1.guest_name, c1.booking_reference AS booking_ref_left, c1.data_href AS data_href_left, c1.checkin_date AS checkin_date_left,
                 c1.checkout_date AS checkout_date_left, c1.booked_date AS booked_date_left,
                 GROUP_CONCAT(DISTINCT CONCAT(rm1.room, ' ', rm1.bed_name) ORDER BY rm1.room, rm1.bed_name SEPARATOR ', ') AS room_beds_left,
                 c2.booking_reference AS booking_ref_right, c2.data_href AS data_href_right, c2.checkin_date AS checkin_date_right,
                 c2.checkout_date AS checkout_date_right, c2.booked_date AS booked_date_right,
                 GROUP_CONCAT(DISTINCT CONCAT(rm2.room, ' ', rm2.bed_name) ORDER BY rm2.room, rm2.bed_name SEPARATOR ', ') AS room_beds_right
            FROM (SELECT DISTINCT booking_reference, room_id, guest_name, data_href, checkin_date, checkout_date, booked_date FROM wp_lh_calendar WHERE job_id = $job_id AND reservation_id > 0) c1
            JOIN (SELECT DISTINCT booking_reference, room_id, guest_name, data_href, checkin_date, checkout_date, booked_date FROM wp_lh_calendar WHERE job_id = $job_id AND reservation_id > 0) c2
              ON c1.guest_name = c2.guest_name AND c1.checkout_date = c2.checkin_date
            JOIN wp_lh_rooms rm1 ON c1.room_id = rm1.id 
            JOIN wp_lh_rooms rm2 ON c2.room_id = rm2.id 
           WHERE rm1.room_type NOT IN ('LT_MALE', 'LT_FEMALE')
             AND rm2.room_type NOT IN ('LT_MALE', 'LT_FEMALE')
             AND rm1.room_type_id = rm2.room_type_id AND rm1.id <> rm2.id -- different bookings, different room, same room type
             -- unless the subsequent booking is already booked by that guest (eg 2 beds -> 1 bed)
             AND NOT EXISTS(
                 SELECT 1 FROM wp_lh_calendar c1a
                  WHERE c1a.job_id = $job_id 
                    AND c1a.guest_name = c1.guest_name
                    AND c1a.checkout_date = c1.checkout_date 
                    AND c1a.room_id = c2.room_id)
             -- unless the former booking is already booked by that guest (eg 1 bed -> 2 beds)
             AND NOT EXISTS(
                 SELECT 1 FROM wp_lh_calendar c1b
                  WHERE c1b.job_id = $job_id     
                    AND c1b.guest_name = c2.guest_name
                    AND c1b.checkin_date = c2.checkin_date 
                    AND c1b.room_id = c1.room_id)
           GROUP BY c1.guest_name, c1.booking_reference, c1.data_href, c1.checkin_date, c1.checkout_date, c1.booked_date,
                    c2.booking_reference, c2.data_href, c2.checkin_date, c2.checkout_date, c2.booked_date");

        if ( $wpdb->last_error ) {
            throw new DatabaseException( $wpdb->last_error );
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
               FROM wp_lh_group_bookings
              WHERE job_id IN (SELECT CAST(value AS UNSIGNED) FROM wp_lh_job_param WHERE name = 'allocation_scraper_job_id' AND job_id = (SELECT MAX(job_id) FROM wp_lh_jobs WHERE classname = 'com.macbackpackers.jobs.GroupBookingsReportJob' AND status = 'completed'))
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
               FROM wp_lh_rpt_unpaid_deposit
              WHERE job_id IN (SELECT CAST(value AS UNSIGNED) FROM wp_lh_job_param WHERE name = 'allocation_scraper_job_id' AND job_id = (SELECT MAX(job_id) FROM wp_lh_jobs WHERE classname = 'com.macbackpackers.jobs.UnpaidDepositReportJob' AND status = 'completed'))
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
                     FROM wp_lh_calendar c
			         JOIN wp_lh_rpt_guest_comments g
                       ON c.reservation_id = g.reservation_id
                    WHERE c.job_id IN (
					      -- retrieve the last run allocation scraper job id
					      SELECT MAX(j.job_id) 
                            FROM wp_lh_jobs j 
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
     * Returns report where a booking has a note indicating bottom bunk/bed but not assigned to a bottom bed/bunk.
     * @throws DatabaseException
     */
    static function getBottomBunksReport() {
        global $wpdb;
        $allocScraperJobId = LilHotelierDBO::getLastCompletedAllocationScraperJobId();
        if ( $allocScraperJobId == null ) {
            return array();
        }
        $resultset = $wpdb->get_results($wpdb->prepare(
            "SELECT DISTINCT reservation_id, room, bed_name, guest_name, checkin_date, checkout_date, data_href, lh_status, 
                             booking_reference, booking_source, booked_date, eta, viewed_yn, notes, comments
               FROM wp_lh_calendar
              WHERE job_id = %d
                AND reservation_id IN 
                    ( SELECT DISTINCT c.reservation_id FROM wp_lh_calendar c
                       WHERE c.job_id = %d
                         AND (LOWER(c.notes) LIKE '%bottom bunk%' OR LOWER(c.notes) LIKE '%lower bunk%'
                                OR LOWER(c.notes) LIKE '%bottom bed%' OR LOWER(c.notes) LIKE '%lower bed%'
                                OR LOWER(c.comments) LIKE '%bottom bunk%' OR LOWER(c.comments) LIKE '%lower bunk%'
                                OR LOWER(c.comments) LIKE '%bottom bed%' OR LOWER(c.comments) LIKE '%lower bed%')
                         AND MOD(CAST(SUBSTR(c.bed_name, 1, 2) AS UNSIGNED), 2) > 0) -- odd numbers are top bunks
              ORDER BY checkin_date",
            $allocScraperJobId, $allocScraperJobId));

        if ( $wpdb->last_error ) {
            throw new DatabaseException( $wpdb->last_error );
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
            "wp_lh_rpt_guest_comments",
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

        // attempting to use $wpdb directly to update timestamp to null
        // results in it being set to "0000-00-00 00:00:00"
        // so using direct SQL instead
        $dblink = new DbTransaction();
        try {
            $stmt = $dblink->mysqli->prepare(
                    "UPDATE wp_lh_rpt_guest_comments
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
            throw $ex;
        }

        $dblink->mysqli->commit();
        $dblink->mysqli->close();
    }

    /**
     * Inserts a lookup key for a given booking.
     * $reservationId : ID of reservation
     * $lookupKey : unique key for this reservation
     * $payment_requested : (optional) amount to pre-populate payment form
     */
    static function insertLookupKeyForBooking( $reservationId, $lookupKey, $payment_requested ) {
        global $wpdb;
        if (false === $wpdb->insert("wp_booking_lookup_key",
            array( 'reservation_id' => $reservationId, 'lookup_key' => $lookupKey, 'payment_requested' => $payment_requested ),
                array( '%s', '%s', '%f' ))) {
            error_log($wpdb->last_error . " executing sql: " . $wpdb->last_query);
            throw new DatabaseException($wpdb->last_error);
        }
        return $wpdb->insert_id;
    }
    
    /**
     * Create a new payment invoice.
     * $name : recipient name
     * $email : recipient email
     * $amount : amount to be paid
     * $description : payment description
     * $notes : staff notes
     * $lookup_key : unique lookup key
     */
    static function insertPaymentInvoice($name, $email, $amount, $description, $notes, $lookupKey) {
        global $wpdb;
        if (false === $wpdb->insert("wp_invoice", array(
                    'recipient_name' => $name,
                    'email' => $email,
                    'payment_amount' => $amount,
                    'payment_description' => $description,
                    'lookup_key' => $lookupKey),
                array( '%s', '%s', '%f', '%s', '%s'))) {
            error_log($wpdb->last_error . " executing sql: " . $wpdb->last_query);
            throw new DatabaseException($wpdb->last_error);
        }

        $inv_id = $wpdb->insert_id;
        if (false === $wpdb->insert("wp_invoice_notes",
                array('invoice_id' => $inv_id, 'notes' => $notes),
                array( '%d', '%s'))) {
            error_log($wpdb->last_error . " executing sql: " . $wpdb->last_query);
            throw new DatabaseException($wpdb->last_error);
        }
    }

    /**
    /**
     * Create a new refund record.
     * @param integer $reservationId cloudbeds identifier
     * @param string $bookingRef cloudbeds booking reference
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     * @param float $amount refund amount
     * @param string $description (optional) note to add to booking
     * @param string $txnId cloudbeds transaction (Stripe)
     * @param string $vendorTxCode
     * @param string $gateway (Stripe/Sagepay)
     * @throws DatabaseException
     */
    static function insertRefundRecord($reservationId, $bookingRef, $firstName, $lastName, $email, $amount, $description, $txnId, $vendorTxCode, $gateway) {
        global $wpdb;
        if (false === $wpdb->insert("wp_tx_refund", array(
                        'reservation_id' => $reservationId,
                        'booking_reference' => $bookingRef,
                        'email' => $email,
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'amount' => $amount,
                        'description' => $description,
                        'last_updated_date' => current_time('mysql')),
                    array( '%d', '%s', '%s', '%s', '%s', '%f', '%s', '%s'))) {
                error_log($wpdb->last_error . " executing sql: " . $wpdb->last_query);
                throw new DatabaseException($wpdb->last_error);
            }
            
        $refId = $wpdb->insert_id;
	    if ( $gateway == "Sagepay" ) {
            if (false === $wpdb->insert("wp_sagepay_tx_refund",
                array('id' => $refId,
                      'auth_vendor_tx_code' => $vendorTxCode,
                      'last_updated_date' => current_time('mysql')),
                array('%d', '%s', '%s'))) {
                    error_log($wpdb->last_error . " executing sql: " . $wpdb->last_query);
                    throw new DatabaseException($wpdb->last_error);
            }
        }
	    else if ( false === empty( $txnId ) ) {
            if (false === $wpdb->insert("wp_stripe_tx_refund",
                array('id' => $refId, 'cloudbeds_tx_id' => $txnId, 'last_updated_date' => current_time('mysql')),
                array('%d', '%s', '%s'))) {
                    error_log($wpdb->last_error . " executing sql: " . $wpdb->last_query);
                    throw new DatabaseException($wpdb->last_error);
            }
        }
        else {
            throw new DatabaseException("Either txnId or vendorTxnCode must be provided.");
        }
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
     * Inserts a new UpdateLittleHotelierSettingsJob.
     * Returns id of inserted job id
     * Throws DatabaseException on insert error
     */
    static function insertUpdateLittleHotelierSettingsJob() {
        return self::insertJobOfType( 'com.macbackpackers.jobs.UpdateLittleHotelierSettingsJob' );
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
    
        $stmt = $mysqli->prepare(
            "INSERT INTO wp_lh_job_param(job_id, name, value)
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
                  FROM wp_lh_jobs 
                 WHERE classname IN (
                           'com.macbackpackers.jobs.AllocationScraperJob', 
                           'com.macbackpackers.jobs.AllocationScraperWorkerJob', 
                           'com.macbackpackers.jobs.CloudbedsAllocationScraperWorkerJob', 
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
                  FROM wp_lh_jobs 
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
     * Returns the ID of the last allocation scraper job that
     * ran succesfully.
     */
    static function getLastCompletedAllocationScraperJobId() {
        global $wpdb;
        $resultset = $wpdb->get_results(
            "SELECT MAX(job_id) `job_id`
                  FROM wp_lh_jobs j
                 WHERE classname = 'com.macbackpackers.jobs.AllocationScraperJob'
                   AND status IN ( 'completed' )
                   AND NOT EXISTS(
                       SELECT 1 FROM wp_lh_jobs j1 LEFT OUTER JOIN wp_lh_job_param p1 ON j1.job_id = p1.job_id
                        WHERE classname = 'com.macbackpackers.jobs.CloudbedsAllocationScraperWorkerJob'
                          AND status IN ( 'submitted', 'processing' )
                          AND p1.name = 'allocation_scraper_job_id' AND p1.value = j.job_id)");

        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        // guaranteed null or int
        $rec = array_shift($resultset);

        // if null, then no job exists
        if( $rec->job_id == null) {
            return null;
        }
        return $rec->job_id;
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
                  FROM wp_lh_jobs 
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
                  FROM wp_lh_jobs
                 WHERE classname = %s
                   AND job_id = (SELECT MAX(job_id) 
                                   FROM wp_lh_jobs 
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
                  FROM wp_lh_jobs j
                  JOIN wp_lh_job_param p ON j.job_id = p.job_id AND p.name = 'selected_date'
                  JOIN (SELECT %s `selected_date`) const
                 WHERE j.classname = 'com.macbackpackers.jobs.BedCountJob'
                   AND j.status IN ( %s )
                   AND STR_TO_DATE(p.value, '%%Y-%%m-%%d') = const.selected_date
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
                  FROM wp_lh_jobs 
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
                  FROM wp_lh_jobs 
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
     */
    static function getBedcountReport( $selectedDate ) {
        global $wpdb;

        $sql = "SELECT room, capacity, room_type, num_empty, num_staff, num_paid, num_noshow, created_date
                  FROM wp_lh_bedcounts
                 WHERE report_date = %s";

        if( get_option('blogname') == 'High Street Hostel Bookings' ) {
            $sql .= " ORDER BY capacity";
        }
        else {
            $sql .= " ORDER BY room";
        }

        $resultset = $wpdb->get_results( $wpdb->prepare(
            $sql, $selectedDate->format( 'Y-m-d' ) ) );

        if ( $wpdb->last_error ) {
            throw new DatabaseException( $wpdb->last_error );
        }

        return $resultset;
    }

    /**
     * Returns bedcount report.
     *
     * @param $fromDate DateTime selection date start (inclusive)
     * @param $toDate DateTime selection date end (exclusive)
     *
     * @throws DatabaseException
     */
    static function getBedcountReportWeekly( DateTime $fromDate, DateTime $toDate ) {
        global $wpdb;

        $sql = "SELECT report_date, room, capacity, room_type, num_empty, num_staff, num_paid, num_noshow, created_date
                  FROM wp_lh_bedcounts
                 WHERE report_date >= %s AND report_date < %s";

        if( get_option('blogname') == 'High Street Hostel Bookings' ) {
            $sql .= " ORDER BY capacity, report_date";
        }
        else {
            $sql .= " ORDER BY room, report_date";
        }

        $resultset = $wpdb->get_results( $wpdb->prepare(
            $sql, $fromDate->format( 'Y-m-d' ), $toDate->format( 'Y-m-d' ) ) );

        if ( $wpdb->last_error ) {
            throw new DatabaseException( $wpdb->last_error );
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
                  FROM wp_lh_jobs j
                  JOIN wp_lh_job_param p ON j.job_id = p.job_id AND p.name = 'checkin_date'
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
                      (SELECT COUNT(DISTINCT e.room_type_id) FROM wp_hw_booking_dates e WHERE e.hw_booking_id = b.id ) `num_room_types`, -- keep track of bookings that contain more than one room type
		              MIN(d.booked_date) `checkin_date`, DATE_ADD(MAX(d.booked_date), INTERVAL 1 DAY) `checkout_date`
                 FROM wp_hw_booking b
                 JOIN wp_hw_booking_dates d ON b.id = d.hw_booking_id
                 LEFT OUTER JOIN (SELECT DISTINCT room_type_id, room_type, capacity FROM wp_lh_rooms) r ON r.room_type_id = d.room_type_id
                GROUP BY b.booking_reference, b.booking_source, b.guest_name, b.booked_date, b.persons, b.payment_outstanding, d.persons, d.room_type_id, d.room_type, r.room_type, r.capacity
               HAVING MIN(d.booked_date) = %s -- checkin date
             ) y
             LEFT OUTER JOIN (
               -- all unique LH records for the given job_id
               SELECT c.booking_reference, c.guest_name, c.booked_date, c.lh_status, c.room_type_id, c.checkin_date, c.checkout_date, c.data_href, c.payment_outstanding, c.notes, r.room_type, r.capacity,
                      IF(c.lh_status = 'cancelled', c.num_guests, SUM(IFNULL((SELECT MAX(r.capacity) FROM wp_lh_rooms r WHERE r.room_type IN ('DBL', 'TWIN', 'TRIPLE', 'QUAD') AND r.room_type_id = c.room_type_id), 1 ))) `lh_persons`
                 FROM wp_lh_calendar c 
                 JOIN (SELECT DISTINCT room_type_id, room_type, capacity FROM wp_lh_rooms) r ON r.room_type_id = c.room_type_id
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
               "SELECT job_id, MAX(booking_reference) AS booking_reference, MAX(post_date) AS post_date, 
                       MAX(masked_card_number) AS masked_card_number, MAX(payment_amount) AS payment_amount, 
	                   MAX(successful) AS successful, MAX(help_text) AS help_text, MAX(status) AS status, 
	                   MAX(data_href) AS data_href,
	                   MAX(checkin_date) AS checkin_date,
	                   MAX(last_updated_date) AS last_updated_date
                  FROM (
                    SELECT j.job_id, jp1.value AS booking_reference, p.post_date, p.masked_card_number, 
                           COALESCE(p.payment_amount, CAST(jp2.value AS DECIMAL(10,2))) AS payment_amount, 
		                   p.successful, p.help_text, j.status, 
                           (SELECT MAX(c.data_href) FROM wp_lh_calendar c WHERE c.booking_reference = jp1.value) AS data_href,
                           (SELECT MAX(c.checkin_date) FROM wp_lh_calendar c WHERE c.booking_reference = jp1.value) AS checkin_date,
                           COALESCE(j.last_updated_date, j.created_date) AS last_updated_date
                      FROM wp_lh_jobs j
                      JOIN wp_lh_job_param jp1 ON j.job_id = jp1.job_id AND jp1.name = 'booking_ref'
                      JOIN wp_lh_job_param jp2 ON j.job_id = jp2.job_id AND jp2.name = 'amount'
                      LEFT OUTER JOIN wp_pxpost_transaction p ON p.job_id = j.job_id
                     WHERE j.classname IN ('com.macbackpackers.jobs.ManualChargeJob')
                 ) t 
                 GROUP BY job_id
                 ORDER BY last_updated_date DESC");

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
     * Returns previous payments made for bookings to Sagepay/Stripe.
     */
    static function getPaymentBookingHistory() {
        global $wpdb;
        $resultset = $wpdb->get_results(
            "SELECT t.reservation_id, t.booking_reference, t.first_name, t.last_name, t.email, t.vendor_tx_code, t.payment_amount, a.auth_status, a.auth_status_detail, a.card_type, a.last_4_digits, a.processed_date
               FROM wp_sagepay_transaction t
              INNER JOIN wp_sagepay_tx_auth a ON t.vendor_tx_code = a.vendor_tx_code
              WHERE t.reservation_id IS NOT NULL
              UNION ALL 
             SELECT reservation_id, booking_reference, first_name, last_name, email, vendor_tx_code,  
                    payment_amount, auth_status, auth_status_detail, card_type, last_4_digits, processed_date
               FROM wp_stripe_transaction
              WHERE processed_date IS NOT NULL
                AND booking_reference IS NOT NULL
              ORDER BY processed_date DESC
              LIMIT 100" );

        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }
        return $resultset;
    }

    /**
     * Returns previous invoice payments made to Sagepay/Stripe.
     * @param int $invoice_id (optional) PK of invoice
     * @param boolean $show_acknowledged (optional) show acknowledged records
     */
    static function getPaymentInvoiceHistory($invoice_id = null, $show_acknowledged = FALSE) {
        global $wpdb;
        if($invoice_id != null && !is_numeric($invoice_id)) {
            // probably not the cleanest way to avoid SQL injection...
            throw new DatabaseException("Ha! Nice try. Stop passing me junk. INV ID: " . $invoice_id);
        }
        $where_clause = $invoice_id == null ? "WHERE 1 = 1" : "WHERE id = $invoice_id";
        $where_clause .= $show_acknowledged ? "" : " AND acknowledged_date IS NULL";
        $invoice_rs = $wpdb->get_results(
            "SELECT i.id AS `invoice_id`, i.recipient_name, i.email AS `recipient_email`, 
                    i.payment_description, i.payment_amount AS `payment_requested`, i.lookup_key, i.acknowledged_date
               FROM wp_invoice i 
             $where_clause
              ORDER BY i.id DESC
              LIMIT 100" );
        
        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        // all transactions for those invoices matched above
        $transaction_rs = $wpdb->get_results(
            "SELECT * FROM (
                 SELECT tx.id AS `txn_id`, tx.invoice_id, tx.first_name, tx.last_name, tx.email, tx.vendor_tx_code, tx.payment_amount,
                        txa.id AS `txn_auth_id`, txa.auth_status, txa.auth_status_detail, txa.card_type, txa.last_4_digits, txa.processed_date, txa.created_date
                   FROM wp_sagepay_transaction tx
                  INNER JOIN (SELECT id FROM wp_invoice $where_clause ORDER BY id DESC LIMIT 100) i ON (i.id = tx.invoice_id) 
                   LEFT OUTER JOIN wp_sagepay_tx_auth txa ON txa.vendor_tx_code = tx.vendor_tx_code
                  UNION ALL 
                 SELECT NULL AS `txn_id`, invoice_id, first_name, last_name, email, vendor_tx_code, payment_amount, NULL as txn_auth_id, auth_status, auth_status_detail, card_type, last_4_digits, processed_date, created_date
                   FROM wp_stripe_transaction tx
                  INNER JOIN (SELECT id FROM wp_invoice $where_clause ORDER BY id DESC LIMIT 100) i ON (i.id = tx.invoice_id)
              ) x
              ORDER BY processed_date DESC" );
        
        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        $notes_rs = $wpdb->get_results(
            "SELECT n.invoice_id, n.notes AS `note_text`, n.created_date
               FROM wp_invoice_notes n
              INNER JOIN (SELECT id FROM wp_invoice $where_clause ORDER BY id DESC LIMIT 100) i ON (i.id = n.invoice_id) 
              ORDER BY n.id" );
        
        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }
        
        foreach( $invoice_rs as $inv ) {
            foreach( $transaction_rs as $txn ) {
                // push transaction onto invoice if matched
                if( $txn->invoice_id === $inv->invoice_id ) {
                    if( ! isset( $inv->transactions )) {
                        $inv->transactions = array();
                    }
                    $inv->transactions[] = $txn;
                }
            }
            foreach( $notes_rs as $note ) {
                if( $inv->invoice_id === $note->invoice_id ) {
                    if( ! isset( $inv->notes )) {
                        $inv->notes = array();
                    }
                    $inv->notes[] = $note;
                }
            }
        }
        return $invoice_rs;
    }
    
    /**
     * Inserts a note on the given invoice.
     * @param int $invoice_id PK on invoice table
     * @param string $note_text note to add
     */
    static function addInvoiceNote($invoice_id, $note_text) {
        global $wpdb;
        if (false === $wpdb->insert("wp_invoice_notes",
                array( 'invoice_id' => $invoice_id, 'notes' => $note_text ),
                array( '%d', '%s'))) {
            error_log($wpdb->last_error . " executing sql: " . $wpdb->last_query);
            throw new DatabaseException($wpdb->last_error);
        }
        return $wpdb->insert_id;
    }

    /**
     * Sets the acknowledge date on the given invoice
     * @param integer $invoice_id PK of invoice
     */
    static function acknowledgeInvoice($invoice_id) {
        global $wpdb;
        $returnval = $wpdb->update(
            "wp_invoice",
            array( 'acknowledged_date' => current_time('mysql', 1) ),
            array( 'id' => $invoice_id ) );
        
        if(false === $returnval) {
            throw new DatabaseException("Error occurred during UPDATE");
        }
    }

    /**
     * Unsets the acknowledge date on the given invoice
     * @param integer $invoice_id PK of invoice
     */
    function unacknowledgeInvoice($invoice_id) {

        // attempting to use $wpdb directly to update timestamp to null
        // results in it being set to "0000-00-00 00:00:00"
        // so using direct SQL instead
        $dblink = new DbTransaction();
        try {
            $stmt = $dblink->mysqli->prepare(
                "UPDATE wp_invoice
                        SET acknowledged_date = NULL
                      WHERE id = ?");
            $stmt->bind_param('i', $invoice_id);
            if(false === $stmt->execute()) {
                throw new DatabaseException("Error occurred updating wp_invoice: ".$dblink->mysqli->error);
            }
            $stmt->close();
            
        } catch(Exception $ex) {
            $dblink->mysqli->rollback();
            $dblink->mysqli->close();
            throw $ex;
        }
        
        $dblink->mysqli->commit();
        $dblink->mysqli->close();
    }
        
    /**
     * Returns previously made refunds.
     */
    static function getRefundHistory() {
        global $wpdb;
        $resultset = $wpdb->get_results(
            "SELECT r.id, r.reservation_id, r.booking_reference, r.email, r.first_name, r.last_name, r.amount, r.description, 
                    sf.charge_id, sr.auth_vendor_tx_code, COALESCE(sf.ref_status, sr.ref_status) AS refund_status, 
                    sr.refund_status_detail, COALESCE(sf.ref_response, sr.ref_response) AS refund_response,
                    COALESCE(sf.last_updated_date, sr.last_updated_date, r.last_updated_date) AS last_updated_date 
               FROM wp_tx_refund r 
               LEFT OUTER JOIN wp_stripe_tx_refund sf ON sf.id = r.id
               LEFT OUTER JOIN wp_sagepay_tx_refund sr ON sr.id = r.id
               ORDER BY r.id DESC" );
                    
        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }
        return $resultset;
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
               FROM wp_lh_jobs
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
               FROM wp_lh_job_param
              WHERE job_id = %d", $jobId));

        $jobParams = array();
        foreach( $resultset as $record ) {
            $jobParams[$record->name] = $record->value;
        }
        return $jobParams;
    }

   /**
    * Creates a new scheduled job that repeats every X minutes. Returns the created job ID.
    * $classname : job to run
    * $params : array of job parameters
    * $minutes : whole number of minutes between jobs
    */
   static function addScheduledJobRepeatForever( $classname, $params, $minutes ) {
        global $wpdb;
        if (false === $wpdb->insert("job_scheduler", 
                array( 'classname' => $classname, 
                       'repeat_time_minutes' => $minutes, 
                       'active_yn' => 'Y'), 
                array( '%s', '%d', '%s' ))) {
            error_log($wpdb->last_error." executing sql: ".$wpdb->last_query);
            throw new DatabaseException( $wpdb->last_error );
        }

        $jobId = $wpdb->insert_id;
        foreach( $params as $jobParamKey => $jobParamValue ) {
            if (false === $wpdb->insert("job_scheduler_param", 
                    array( 'job_id' => $jobId, 'name' => $jobParamKey, 'value' => $jobParamValue ), 
                    array( '%d', '%s', '%s' ))) {
                error_log($wpdb->last_error." executing sql: ".$wpdb->last_query);
                throw new DatabaseException( $wpdb->last_error );
            }
        }
        return $jobId;
    }

   /**
    * Creates a new scheduled job that runs at the same time everyday.
    * Returns the created job ID
    * $classname : job to run
    * $params : array of job parameters
    * $time : time in 24 hour format. e.g. 23:00:00
    */
    static function addDailyScheduledJob( $classname, $params, $time ) {
        global $wpdb;
        if (false === $wpdb->insert("job_scheduler", 
                array( 'classname' => $classname, 
                       'repeat_daily_at' => $time,
                       'active_yn' => 'Y' ), 
                array( '%s', '%s', '%s' ))) {
            error_log($wpdb->last_error." executing sql: ".$wpdb->last_query);
            throw new DatabaseException( $wpdb->last_error );
        }

        $jobId = $wpdb->insert_id;
        foreach( $params as $jobParamKey => $jobParamValue ) {
            if (false === $wpdb->insert("job_scheduler_param", 
                    array( 'job_id' => $jobId, 'name' => $jobParamKey, 'value' => $jobParamValue ), 
                    array( '%d', '%s', '%s' ))) {
                error_log($wpdb->last_error." executing sql: ".$wpdb->last_query);
                throw new DatabaseException( $wpdb->last_error );
            }
        }
        return $jobId;
    }

    /**
     * Enables/disables a scheduled job.
     * $scheduledJobId : primary key of scheduled job to update
     */
    static function toggleScheduledJob( $scheduledJobId ) {

        // find existing job
        $job = self::fetchJobSchedule( $scheduledJobId );

        if( $job ) {
            global $wpdb;
            $returnval = $wpdb->update(
                "job_scheduler",
                array( 'last_updated_date' => current_time('mysql', 1),
                       'active_yn' => $job->active_yn == 'Y' ? 'N' : 'Y' ),
                array( 'job_id' => $scheduledJobId ) );
        
            if(false === $returnval) {
                throw new DatabaseException("Error occurred during UPDATE");
            }
        }
    }

    /**
     * Deletes a scheduled job.
     * $scheduledJobId : primary key of scheduled job to delete
     */
    static function deleteScheduledJob( $scheduledJobId ) {
        global $wpdb;
        if (false === $wpdb->delete("job_scheduler_param", 
                array( 'job_id' => $scheduledJobId ), 
                array( '%d' ))) {
            error_log($wpdb->last_error." executing sql: ".$wpdb->last_query);
            throw new DatabaseException( $wpdb->last_error );
        }

        if (false === $wpdb->delete("job_scheduler", 
                array( 'job_id' => $scheduledJobId ), 
                array( '%d' ))) {
            error_log($wpdb->last_error." executing sql: ".$wpdb->last_query);
            throw new DatabaseException( $wpdb->last_error );
        }
    }

    /**
     * Retrieves all schedule jobs.
     * Returns non-null array of ScheduledJob.
     */
    static function fetchJobSchedules() {
        global $wpdb;
        $resultset = $wpdb->get_results(
           " SELECT job_id, classname, repeat_time_minutes, repeat_daily_at, active_yn, last_run_date 
               FROM job_scheduler
           ORDER BY job_id");

        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        $schedule = array();
        foreach( $resultset as $record ) {
            if( false === empty( $record->repeat_time_minutes ) ) {
                $schedule[] = new ScheduledJobRepeat(
                    $record->job_id, 
                    $record->classname, 
                    $record->repeat_time_minutes, 
                    $record->active_yn == 'Y',
                    $record->last_run_date,
                    self::fetchJobScheduleParameters( $record->job_id ));
            }
            else if( false === empty( $record->repeat_daily_at ) ) {
                $schedule[] = new ScheduledJobDaily(
                    $record->job_id, 
                    $record->classname, 
                    $record->repeat_daily_at,
                    $record->active_yn == 'Y',
                    $record->last_run_date,
                    self::fetchJobScheduleParameters( $record->job_id ));
            }
        }
        return $schedule;
    }

    /**
     * Retrieves the given ScheduledJob.
     * $jobId : PK of ScheduledJob
     * Returns null if not found.
     */
    static function fetchJobSchedule( $jobId ) {
        global $wpdb;
        $resultset = $wpdb->get_results($wpdb->prepare(
            "SELECT job_id, classname, repeat_time_minutes, repeat_daily_at, active_yn, last_run_date 
               FROM job_scheduler
              WHERE job_id = %d", $jobId));

        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        foreach( $resultset as $record ) {
            return $record;
        }
        return null;
    }

    /**
     * Retrieves all parameters for the given ScheduledJob.
     * $jobId : PK of ScheduledJob
     * Returns non-null array of parameter values keyed by param name.
     */
    static function fetchJobScheduleParameters( $jobId ) {
        global $wpdb;
        $resultset = $wpdb->get_results($wpdb->prepare(
            "SELECT name, value 
               FROM job_scheduler_param
              WHERE job_id = %d
           ORDER BY name", $jobId));

        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        $jobParams = array();
        foreach( $resultset as $record ) {
            $jobParams[$record->name] = $record->value;
        }
        return $jobParams;
    }

    /**
     * Changes the status of a job back to submitted.
     * $jobId : PK of Job
     */
    static function resubmitIncompleteJob( $jobId ) {
        global $wpdb;
        $returnval = $wpdb->update(
            "wp_lh_jobs",
            array( 'last_updated_date' => current_time('mysql', 1),
                   'status' => self::STATUS_SUBMITTED),
            array( 'job_id' => $jobId ) );

        if (false === $returnval) {
            throw new DatabaseException("Error occurred during UPDATE");
        }
        if (0 === $returnval) {
            throw new DatabaseException("Job ID $jobId does not exist?");
        }
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

    /**
     * Returns list of blacklisted guests.
     * @return array [BlacklistEntry]
     * @throws DatabaseException
     */
    function getBlacklist() {
        $entry_rs = $this->SHARED_DB->get_results(
            "SELECT id AS blacklist_id, first_name, last_name, email, notes, created_date, last_updated_date FROM hbo_blacklist
             ORDER BY blacklist_id, last_name, first_name, email");

        if ( $this->SHARED_DB->last_error ) {
            throw new DatabaseException( $this->SHARED_DB->last_error );
        }

        $alias_rs = $this->SHARED_DB->get_results(
            "SELECT id AS alias_id, blacklist_id, first_name, last_name, email
               FROM hbo_blacklist_alias WHERE deleted_date IS NULL");

        if ( $this->SHARED_DB->last_error ) {
            throw new DatabaseException( $this->SHARED_DB->last_error );
        }

        $mugshot_rs = $this->SHARED_DB->get_results(
            "SELECT id AS mugshot_id, blacklist_id, filename
               FROM hbo_blacklist_mugshot WHERE deleted_date IS NULL");

        if ( $this->SHARED_DB->last_error ) {
            throw new DatabaseException($this->SHARED_DB->last_error);
        }

        $blacklist = array();
        foreach ( $entry_rs as $record ) {
            $entry = new BlacklistEntry( $record->blacklist_id, $record->first_name, $record->last_name, $record->email,
                $record->notes, $record->created_date, $record->last_updated_date );
            foreach ( $alias_rs as $alias ) {
                if ( $alias->blacklist_id == $record->blacklist_id ) {
                    $entry->add_alias( $alias );
                }
            }
            foreach ( $mugshot_rs as $mugshot ) {
                if ( $mugshot->blacklist_id == $record->blacklist_id ) {
                    $entry->add_mugshot( $mugshot );
                }
            }
            $blacklist[] = $entry;
        }
        return $blacklist;
    }

    /**
     * Inserts/Updates an entry in the blacklist table.
     * @param $id int null or zero for new record, existing id to update
     * @param $first_name
     * @param $last_name
     * @param $email
     * @param $notes
     *
     * @return void
     * @throws DatabaseException
     */
    function saveBlacklistEntry($id, $first_name, $last_name, $email, $notes) {
        if ($id) {
            $returnval = $this->SHARED_DB->update( "hbo_blacklist",
                array( 'last_updated_date' => current_time('mysql', 1),
                       'first_name' => $first_name,
                       'last_name' => $last_name,
                       'email' => $email,
                       'notes' => $notes),
                array( 'id' => $id ) );

            if (false === $returnval) {
                error_log($this->SHARED_DB->last_error." executing sql: " . $this->SHARED_DB->last_query);
                throw new DatabaseException("Error occurred during UPDATE");
            }
        }
        else {
            if (false === $this->SHARED_DB->insert( "hbo_blacklist",
                    array( 'first_name' => $first_name,
                           'last_name' => $last_name,
                           'email' => $email,
                           'notes' => $notes),
                    array( '%s', '%s', '%s', '%s' ))) {
                error_log($this->SHARED_DB->last_error . " executing sql: " . $this->SHARED_DB->last_query);
                throw new DatabaseException( $this->SHARED_DB->last_error );
            }
        }
    }

    /**
     * Inserts a new blacklist alias for an existing blacklist entry.
     * @param $id int PK of blacklist entry
     * @param $first_name string
     * @param $last_name string
     * @param $email string|null
     *
     * @return void
     * @throws DatabaseException
     */
    function saveBlacklistAlias( $id, $first_name, $last_name, $email ) {
        $this->validateBlacklistId( $id );
        if (false === $this->SHARED_DB->insert( "hbo_blacklist_alias",
                array( 'blacklist_id' => $id,
                       'first_name' => $first_name,
                       'last_name' => $last_name,
                       'email' => $email ),
                array( '%d', '%s', '%s', '%s' ))) {
            error_log($this->SHARED_DB->last_error . " executing sql: " . $this->SHARED_DB->last_query);
            throw new DatabaseException( $this->SHARED_DB->last_error );
        }
    }

    /**
     * Deletes an existing blacklist alias.
     * @param $alias_id int PK of blacklist alias
     *
     * @return void
     * @throws DatabaseException
     */
    function deleteBlacklistAlias($alias_id) {
        $returnval = $this->SHARED_DB->update( "hbo_blacklist_alias",
            array( 'deleted_date' => current_time('mysql', 1)),
            array( 'id' => $alias_id ));

        if (false === $returnval) {
            error_log($this->SHARED_DB->last_error . " executing sql: " . $this->SHARED_DB->last_query);
            throw new DatabaseException("Error occurred during UPDATE");
        }
    }

    /**
     * Saves a reference to the recently uploaded image for a blacklist entry.
     * @param $id int PK of blacklist entry
     * @param $filename string name of file on local filesystem
     *
     * @return void
     * @throws DatabaseException
     */
    function saveBlacklistImage( $id, $filename ) {
        $this->validateBlacklistId( $id );
        if (false === $this->SHARED_DB->insert( "hbo_blacklist_mugshot",
                array( 'blacklist_id' => $id,
                       'filename' => $filename ),
                array( '%d', '%s' ))) {
            error_log($this->SHARED_DB->last_error . " executing sql: " . $this->SHARED_DB->last_query);
            throw new DatabaseException( $this->SHARED_DB->last_error );
        }
    }

    /**
     * Verifies the blacklist_id exists.
     * @param $id int PK of blacklist entry
     *
     * @return void
     * @throws DatabaseException if blacklist does not exist
     */
    function validateBlacklistId( $id ) {
        $rowcount = $this->SHARED_DB->get_var($this->SHARED_DB->prepare(
            "SELECT COUNT(1)
               FROM hbo_blacklist
              WHERE id = %d", $id));

        if($this->SHARED_DB->last_error) {
            throw new DatabaseException($this->SHARED_DB->last_error);
        }

        if ($rowcount == 0) {
            throw new DatabaseException( "Unable to find blacklist id $id" );
        }
    }
}
