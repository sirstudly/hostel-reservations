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
        if (strpos(get_option('siteurl'), 'castlerock') !== false) {
            $result['com.macbackpackers.jobs.CreateSendHogmanayEmailJob'] = 'Send Hogmanay Emails';
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
            'com.macbackpackers.jobs.CreateChargeNonRefundableBookingJob' => 'Charge Non-Refundable Bookings'
        ));
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