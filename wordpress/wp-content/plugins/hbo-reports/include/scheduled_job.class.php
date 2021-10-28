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
	    $result = array_merge( $result, [
		    [
			    'classname'  => 'com.macbackpackers.jobs.HousekeepingJob',
			    'name'       => 'Update Housekeeping Report',
			    'parameters' => [ 'selected_date' => 'TODAY' ]
		    ],
		    [
			    'classname'  => 'com.macbackpackers.jobs.AllocationScraperJob',
			    'name'       => 'Update Booking Reports',
			    'parameters' => [ 'start_date' => 'TODAY', 'days_ahead' => '140' ]
		    ],
		    [
			    'classname'  => 'com.macbackpackers.jobs.BedCountJob',
			    'name'       => 'Update Bed Counts',
			    'parameters' => [ 'selected_date' => 'TODAY-1' ]
		    ],
		    [
			    'classname'  => 'com.macbackpackers.jobs.DbPurgeJob',
			    'name'       => 'Purge Old Database Entries',
			    'parameters' => [ 'days' => '90' ]
		    ],
		    [
			    'classname'  => 'com.macbackpackers.jobs.CreateDepositChargeJob',
			    'name'       => 'Charge Deposits',
			    'parameters' => [ 'days_back' => '1' ]
		    ],
		    [
			    'classname'  => 'com.macbackpackers.jobs.CreatePrepaidChargeJob',
			    'name'       => 'Charge Pre-Paid Bookings',
			    'parameters' => []
		    ],
		    [
			    'classname'  => 'com.macbackpackers.jobs.CreateAgodaChargeJob',
			    'name'       => 'Charge Past Agoda Bookings',
			    'parameters' => [ 'days_back' => '7' ]
		    ],
		    [
			    'classname'  => 'com.macbackpackers.jobs.CreateChargeHostelworldLateCancellationJob',
			    'name'       => 'Charge HWL Late Cancellations',
			    'parameters' => []
		    ],
		    [
			    'classname'  => 'com.macbackpackers.jobs.CreateCopyCardDetailsToCloudbedsJob',
			    'name'       => 'Copy Card Details from Hostelworld',
			    'parameters' => [ 'booking_date' => 'TODAY-1', 'days_ahead' => '1' ]
		    ],
		    [
			    'classname'  => 'com.macbackpackers.jobs.CreateChargeNonRefundableBookingJob',
			    'name'       => 'Charge Non-Refundable Bookings',
			    'parameters' => [ 'booking_date' => 'TODAY-3', 'days_ahead' => '4' ]
		    ],
		    [
			    'classname'  => 'com.macbackpackers.jobs.CreateRefreshStripeRefundTransactionJob',
			    'name'       => 'Refresh Pending Refunds (Stripe)',
			    'parameters' => []
		    ],
		    [
			    'classname'  => 'com.macbackpackers.jobs.CreateSendCovidPrestayEmailJob',
			    'name'       => 'Send Covid Pre-Stay Email',
			    'parameters' => [ 'days_before' => '1' ]
		    ],
		    [
			    'classname'  => 'com.macbackpackers.jobs.CreateFixedRateLongTermReservationsJob',
			    'name'       => 'Create Long-Term Reservations Job',
			    'parameters' => [ 'selected_date' => 'TODAY+1', 'days' => '7', 'rate_per_day' => '10' ]
		    ]
	    ] );
	    if ( strpos( get_option( 'siteurl' ), 'castlerock' ) !== false ) {
		    $result = array_merge( $result, [
			    'classname' => 'com.macbackpackers.jobs.CreateSendHogmanayEmailJob',
			    'name'      => 'Send Hogmanay Emails'
		    ], [
			    'classname' => 'com.macbackpackers.jobs.CreateSendChristmasArrivalEmailJob',
			    'name'      => 'Send Christmas Arrival Emails'
		    ], [
			    'classname' => 'com.macbackpackers.jobs.CreateSendChristmasLunchEmailJob',
			    'name'      => 'Send Christmas Lunch Emails'
		    ], [
			    'classname' => 'com.macbackpackers.jobs.VerifyAlexaLoggedInJob',
			    'name'      => 'Verify Alexa is Logged in'
		    ], [
			    'classname' => 'com.macbackpackers.jobs.VerifyGoogleAssistantLoggedInJob',
			    'name'      => 'Verify Google Assistant is Logged in'
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