<?php

/**
 * Display controller for creating/editing job schedules.
 */
class ScheduledJobViewData extends XslTransform {

    var $jobSchedules;  // array of current job schedule

    /**
     * Default constructor.
     */
    function __construct() {
    }

    /**
     * Updates the view.
     */
    function doView() {
        $this->jobSchedules = LilHotelierDBO::fetchJobSchedules();
    }

   /**
    * Convenience method for creating a new scheduled job.
    * Default parameters for each job will be provided.
    * $classname : fully qualified name of (job) class to run
    * 
    * Only provide one of the following:
    * $repeatMin : number of minutes to repeat
    * $dailyAt : time to run daily (24 hour clock)
    */
    function addScheduledJob( $classname, $repeatMin, $dailyAt ) {
        $params = array();
        if( $classname == 'com.macbackpackers.jobs.ScrapeReservationsBookedOnJob' ) {
            $params = array( 'booked_on_date' => 'TODAY' );
        }
        elseif( $classname == 'com.macbackpackers.jobs.HousekeepingJob' ) {
            $params = array( 'selected_date' => 'TODAY' );
        }
        elseif( $classname == 'com.macbackpackers.jobs.AllocationScraperJob' ) {
            $params = array( 'start_date' => 'TODAY', 'days_ahead' => '140' );
        }
        elseif( $classname == 'com.macbackpackers.jobs.BedCountJob' ) {
            $params = array( 'selected_date' => 'TODAY-1' );
        }
        elseif( $classname == 'com.macbackpackers.jobs.DbPurgeJob' ) {
            $params = array( 'days' => '90' );
        }
        elseif( $classname == 'com.macbackpackers.jobs.CreateDepositChargeJob' ) {
            $params = array( 'days_back' => '3' );
        }
        elseif( $classname == 'com.macbackpackers.jobs.CreatePrepaidChargeJob' ) {
            $params = array();
        }
        elseif( $classname == 'com.macbackpackers.jobs.CreateChargeHostelworldLateCancellationJob' ) {
            $params = array();
        }
        elseif( $classname == 'com.macbackpackers.jobs.CreateSendHogmanayEmailJob' ) {
            $params = array();
        }
        elseif( $classname == 'com.macbackpackers.jobs.CreateSendChristmasArrivalEmailJob' ) {
            $params = array();
        }
        elseif( $classname == 'com.macbackpackers.jobs.CreateSendChristmasLunchEmailJob' ) {
            $params = array();
        }
        elseif( $classname == 'com.macbackpackers.jobs.CreateAgodaChargeJob' ) {
            $params = array( 'days_back' => '7' );
        }
        elseif( $classname == 'com.macbackpackers.jobs.CreateCopyCardDetailsToCloudbedsJob' ) {
            $params = array( 'booking_date' => 'TODAY-1', 'days_ahead' => 1 );
        }
        elseif( $classname == 'com.macbackpackers.jobs.CreateChargeNonRefundableBookingJob' ) {
            $params = array( 'booking_date' => 'TODAY-3', 'days_ahead' => 4 );
        }
        elseif( $classname == 'com.macbackpackers.jobs.CreateRefreshStripeRefundTransactionJob' ) {
            $params = array();
        }
        elseif( $classname == 'com.macbackpackers.jobs.CreateSendCovidPrestayEmailJob' ) {
            $params = array( 'days_before' => '3' );
        }
        else {
            throw new ValidationException( "Unsupported job type: $classname" );
        }

        if( false === empty( $repeatMin )) {
            $this->addScheduledJobRepeatForever( $classname, $params, $repeatMin );
        }
        elseif( false === empty( $dailyAt )) {
            $this->addDailyScheduledJob( $classname, $params, $dailyAt );
        }
        else {
            throw new ValidationException( "Nothing to do!" );
        }
    }

   /**
    * Creates a new scheduled job that repeats every X minutes.
    * $classname : job to run
    * $params : array of job parameters
    * $minutes : whole number of minutes between jobs
    */
    function addScheduledJobRepeatForever( $classname, $params, $minutes ) {

        if ( empty( $classname )) {
            throw new ValidationException( "Class name cannot be blank." );
        }
        if ( empty( $minutes )) {
            throw new ValidationException( "Minutes cannot be blank." );
        }
        if ( ! preg_match("/^[0-9]+$/", $minutes )) {
            throw new ValidationException( "Minutes must be a whole number" );
        }

        LilHotelierDBO::addScheduledJobRepeatForever( $classname, $params, $minutes );
    }

   /**
    * Creates a new scheduled job that runs at the same time everyday.
    * $classname : job to run
    * $params : array of job parameters
    * $time : time in 24 hour format HH:MM. e.g. 23:00
    */
    function addDailyScheduledJob( $classname, $params, $time ) {

        if ( empty( $classname )) {
            throw new ValidationException( "Class name cannot be blank." );
        }
        if ( empty( $time )) {
            throw new ValidationException( "Time cannot be blank." );
        }
        if ( ! preg_match("/^([0-1][0-9]|2[0-4]):[0-5][0-9]$/", $time )) {
            throw new ValidationException( "Time must be in 24 hour clock. eg. 23:00" );
        }

        LilHotelierDBO::addDailyScheduledJob( $classname, $params, $time );
    }

    /**
     * Enables/disables a scheduled job.
     * $scheduledJobId : primary key of scheduled job to update
     */
    function toggleScheduledJob( $scheduledJobId ) {
        LilHotelierDBO::toggleScheduledJob( $scheduledJobId );
    }

    /**
     * Deletes a scheduled job.
     * $scheduledJobId : primary key of scheduled job to delete
     */
    function deleteScheduledJob( $scheduledJobId ) {
        LilHotelierDBO::deleteScheduledJob( $scheduledJobId );
    }

    /**
     * Adds this object to the DOMDocument/XMLElement specified.
     * $domtree : DOM document root
     * $parentElement : DOM element where this object will be added
     */
    function addSelfToDocument( $domtree, $parentElement ) {
        $classnameMapRoot = $parentElement->appendChild($domtree->createElement('classnamemap'));
        foreach( ScheduledJob::getClassnameMap() as $key => $value ) {
            $entryRoot = $classnameMapRoot->appendChild($domtree->createElement('entry'));
            $entryRoot->appendChild($domtree->createElement('classname', $key));
            $entryRoot->appendChild($domtree->createElement('selectionname', $value));
        }

        if ( $this->jobSchedules ) {
            foreach( $this->jobSchedules as $record ) {
                $record->addSelfToDocument($domtree, $parentElement);
            }
        }
    }
    
    /** 
     * Generates XML for this view.
     */
    function toXml() {
        // create a dom document with encoding utf8
        $domtree = new DOMDocument('1.0', 'UTF-8');
        $xmlRoot = $domtree->appendChild($domtree->createElement('view'));
        $this->addSelfToDocument($domtree, $xmlRoot);
        $xml = $domtree->saveXML();
        return $xml;
    }
    
    /**
     * Returns the filename for the stylesheet to use during transform.
     */
    function getXslFilename() {
        return HBO_PLUGIN_DIR. '/include/scheduled_job_view_data.xsl';
    }

}

?>