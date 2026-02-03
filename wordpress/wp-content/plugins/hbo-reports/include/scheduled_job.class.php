<?php

/**
 * A single scheduled job.
 */
abstract class ScheduledJob {

    /**
     * Returns array of all available jobs
     */
    static function getClassnameMap() {
        $result = array();
        if ('cloudbeds' != get_option('hbo_property_manager')) {
            // little hotelier specific
	        $result[] = [
		        'classname'  => 'com.macbackpackers.jobs.ScrapeReservationsBookedOnJob',
		        'name'       => 'Confirm Hostelworld Deposits',
		        'parameters' => [ 'booked_on_date' => 'TODAY' ]
	        ];
        }
	    array_push( $result, [
		    'classname'  => 'com.macbackpackers.jobs.HousekeepingJob',
		    'name'       => 'Update Housekeeping Report',
		    'parameters' => [ 'selected_date' => 'TODAY' ]
	    ], [
		    'classname'  => 'com.macbackpackers.jobs.AllocationScraperJob',
		    'name'       => 'Update Booking Reports',
		    'parameters' => [ 'start_date' => 'TODAY', 'days_ahead' => '140' ]
	    ], [
		    'classname'  => 'com.macbackpackers.jobs.BedCountJob',
		    'name'       => 'Update Bed Counts',
		    'parameters' => [ 'selected_date' => 'TODAY-1' ]
	    ], [
		    'classname'  => 'com.macbackpackers.jobs.DbPurgeJob',
		    'name'       => 'Purge Old Database Entries',
		    'parameters' => [ 'days' => '90' ]
	    ], [
		    'classname'  => 'com.macbackpackers.jobs.CreateDepositChargeJob',
		    'name'       => 'Charge Deposits',
		    'parameters' => [ 'days_back' => '1' ]
	    ], [
		    'classname'  => 'com.macbackpackers.jobs.CreatePrepaidChargeJob',
		    'name'       => 'Charge Pre-Paid Bookings',
		    'parameters' => []
	    ], [
		    'classname'  => 'com.macbackpackers.jobs.CreatePrepaidRefundJob',
		    'name'       => 'Refund Pre-Paid Bookings',
		    'parameters' => []
	    ], [
		    'classname'  => 'com.macbackpackers.jobs.CreateChargeHostelworldLateCancellationJob',
		    'name'       => 'Charge HWL Late Cancellations',
		    'parameters' => []
	    ], [
		    'classname'  => 'com.macbackpackers.jobs.CreateCopyCardDetailsToCloudbedsJob',
		    'name'       => 'Copy Card Details from Hostelworld',
		    'parameters' => [ 'booking_date' => 'TODAY-1', 'days_ahead' => '1' ]
	    ], [
		    'classname'  => 'com.macbackpackers.jobs.CreateChargeNonRefundableBookingJob',
		    'name'       => 'Charge Non-Refundable Bookings',
		    'parameters' => [ 'booking_date' => 'TODAY-3', 'days_ahead' => '4' ]
	    ], [
		    'classname'  => 'com.macbackpackers.jobs.CreateRefreshStripeRefundTransactionJob',
		    'name'       => 'Refresh Pending Refunds (Stripe)',
		    'parameters' => []
	    ], [
		    'classname'  => 'com.macbackpackers.jobs.CreateFixedRateLongTermReservationsJob',
		    'name'       => 'Create Long-Term Reservations Job',
		    'parameters' => [ 'selected_date' => 'TODAY+1', 'days' => '7', 'rate_per_day' => '10' ]
	    ], [
		    'classname'  => 'com.macbackpackers.jobs.CreateSendGroupBookingApprovalRequiredEmailJob',
		    'name'       => 'Send Group Booking Approval Required Job',
		    'parameters' => [ 'booking_date' => 'TODAY-3' ]
	    ], [
		    'classname'  => 'com.macbackpackers.jobs.CreateSendGroupBookingPaymentReminderEmailJob',
		    'name'       => 'Send Group Booking Payment Reminder Job',
		    'parameters' => [ 'days_before' => '7' ]
	    ], [
		    'classname'  => 'com.macbackpackers.jobs.CreateSendBulkEmailJob',
		    'name'       => 'Send Bulk Email Job',
		    'parameters' => [ 'email_template' => '',
		                      'booking_date_start' => '', 'booking_date_end' => '',
		                      'stay_date_start' => '', 'stay_date_end' => '',
		                      'checkin_date_start' => 'TODAY+1', 'checkin_date_end' => '2021-12-20',
		                      'statuses' => 'confirmed,not_confirmed' ]
	    ], [
		    'classname'  => 'com.macbackpackers.jobs.CreateSendHogmanayAdvancedPaymentEmailJob',
		    'name'       => 'Send Hogmanay Payment Reminder Email',
		    'parameters' => []
	    ], [
		    'classname'  => 'com.macbackpackers.jobs.CreateChargeHogmanayBookingJob',
		    'name'       => 'Charge Hogmanay Bookings Job',
		    'parameters' => []
	    ] );

	    if ( strpos( get_option( 'siteurl' ), 'castlerock' ) !== false
	         || strpos( get_option( 'siteurl' ), 'localhost' ) !== false ) { // for testing
		    array_push( $result, [
			    'classname'  => 'com.macbackpackers.jobs.VerifyAlexaLoggedInJob',
			    'name'       => 'Verify Alexa is Logged in',
			    'parameters' => []
		    ], [
			    'classname'  => 'com.macbackpackers.jobs.VerifyGoogleAssistantLoggedInJob',
			    'name'       => 'Verify Google Assistant is Logged in',
			    'parameters' => []
            ], [
                'classname'  => 'com.macbackpackers.jobs.VerifyCastleRockJambotOnlineJob',
                'name'       => 'Verify Castle Rock Jambot is Online',
                'parameters' => []
		    ] );
	    }
        return $result;
    }

	/**
	 * Returns the user friendly job name for a given classname
	 *
	 * @param $haystack array see self::getClassnameMap
	 * @param $classname string class to match
	 *
	 * @return string job name or classname if not found
	 */
	static function getJobNameForClassname( $haystack, $classname ) {
		foreach ( $haystack as $job ) {
			if ( $job['classname'] == $classname ) {
				return $job['name'];
			}
		}
		return $classname;
	}

    /**
     * Adds this object to the DOMDocument/XMLElement specified.
     * $domtree : DOM document root
     * $parentElement : DOM element where this object will be added
     */
    abstract function addSelfToDocument($domtree, $parentElement);
    
}

?>