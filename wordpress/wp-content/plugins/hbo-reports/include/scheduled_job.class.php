<?php

/**
 * A single scheduled job.
 */
abstract class ScheduledJob {

    /**
     * Returns map of classname -> user friendly name
     */
    static function getClassnameMap() {
        return array(
            'com.macbackpackers.jobs.ScrapeReservationsBookedOnJob' => 'Confirm Hostelworld Deposits',
            'com.macbackpackers.jobs.HousekeepingJob' => 'Update Housekeeping Report',
            'com.macbackpackers.jobs.AllocationScraperJob' => 'Update Booking Reports',
            'com.macbackpackers.jobs.BedCountJob' => 'Update Bed Counts',
            'com.macbackpackers.jobs.DbPurgeJob' => 'Purge Old Database Entries',
            'com.macbackpackers.jobs.CreateDepositChargeJob' => 'Charge Deposits',
            'com.macbackpackers.jobs.CreatePrepaidChargeJob' => 'Charge Pre-Paid Bookings',
            'com.macbackpackers.jobs.CreateAgodaChargeJob' => 'Charge Past Agoda Bookings'
        );
    }

    /**
     * Adds this object to the DOMDocument/XMLElement specified.
     * $domtree : DOM document root
     * $parentElement : DOM element where this object will be added
     */
    abstract function addSelfToDocument($domtree, $parentElement);
    
}

?>