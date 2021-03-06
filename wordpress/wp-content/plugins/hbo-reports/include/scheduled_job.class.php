<?php

/**
 * A single scheduled job.
 */
abstract class ScheduledJob {

    /**
     * Returns map of classname -> user friendly name
     */
    static function getClassnameMap() {
        $result = array();
        if ('cloudbeds' != get_option('hbo_property_manager')) {
            // little hotelier specificc
            $result['com.macbackpackers.jobs.ScrapeReservationsBookedOnJob'] = 'Confirm Hostelworld Deposits';
        }
        $result = array_merge( $result, array(
            'com.macbackpackers.jobs.HousekeepingJob' => 'Update Housekeeping Report',
            'com.macbackpackers.jobs.AllocationScraperJob' => 'Update Booking Reports',
            'com.macbackpackers.jobs.BedCountJob' => 'Update Bed Counts',
            'com.macbackpackers.jobs.DbPurgeJob' => 'Purge Old Database Entries',
            'com.macbackpackers.jobs.CreateDepositChargeJob' => 'Charge Deposits',
            'com.macbackpackers.jobs.CreatePrepaidChargeJob' => 'Charge Pre-Paid Bookings',
            'com.macbackpackers.jobs.CreateAgodaChargeJob' => 'Charge Past Agoda Bookings',
            'com.macbackpackers.jobs.CreateChargeHostelworldLateCancellationJob' => 'Charge HWL Late Cancellations',
            'com.macbackpackers.jobs.CreateCopyCardDetailsToCloudbedsJob' => 'Copy Card Details from Hostelworld',
            'com.macbackpackers.jobs.CreateChargeNonRefundableBookingJob' => 'Charge Non-Refundable Bookings',
            'com.macbackpackers.jobs.CreateRefreshStripeRefundTransactionJob' => 'Refresh Pending Refunds (Stripe)',
            'com.macbackpackers.jobs.CreateSendCovidPrestayEmailJob' => 'Send Covid Pre-Stay Email',
	        'com.macbackpackers.jobs.CreateFixedRateLongTermReservationsJob' => 'Create Long-Term Reservations Job'
        ));
        if (strpos(get_option('siteurl'), 'castlerock') !== false) {
            $result['com.macbackpackers.jobs.CreateSendHogmanayEmailJob'] = 'Send Hogmanay Emails';
            $result['com.macbackpackers.jobs.CreateSendChristmasArrivalEmailJob'] = 'Send Christmas Arrival Emails';
            $result['com.macbackpackers.jobs.CreateSendChristmasLunchEmailJob'] = 'Send Christmas Lunch Emails';
	        $result['com.macbackpackers.jobs.VerifyAlexaLoggedInJob'] = 'Verify Alexa is Logged in';
	        $result['com.macbackpackers.jobs.VerifyGoogleAssistantLoggedInJob'] = 'Verify Google Assistant is Logged in';
        }
        return $result;
    }

    /**
     * Adds this object to the DOMDocument/XMLElement specified.
     * $domtree : DOM document root
     * $parentElement : DOM element where this object will be added
     */
    abstract function addSelfToDocument($domtree, $parentElement);
    
}

?>