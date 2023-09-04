<?php

/**
 * Abstract controller for bedcounts report.
 */
abstract class AbstractBedCounts extends XslTransform {

    const JOB_TYPE = "com.macbackpackers.jobs.BedCountJob";

    protected $selectionDate;  // DateTime of selected date

    protected $lastCompletedAllocScraperJob; // date/time of last completed allocation scraper job for selectionDate
    protected $bedcountData;  // array() of BedCountEntry
    protected $isRefreshJobInProgress = false;
    protected $lastJob; // the last job of this type that has run

    /**
     * Default constructor.
     * $selectionDate : date to display bedcounts for (DateTime) (defaults to now)
     */
    function __construct($selectionDate = null) {
        $this->selectionDate = $selectionDate == null ? new DateTime() : $selectionDate;
    }
    
    /**
     * Updates the data required for the given date.
     */
    function updateBedcounts() {
        // get last succcessful bedcount job id
        $allocJobRec = LilHotelierDBO::getLastCompletedBedCountJob($this->selectionDate);
        if( $allocJobRec == null ) {
            $this->bedcountData = array();
            $this->lastCompletedAllocScraperJob = null;
        }
        else {
            $this->bedcountData                 = LilHotelierDBO::getBedcountReport( $this->selectionDate );
            $this->lastCompletedAllocScraperJob = empty( $this->bedcountData ) ? null : $this->bedcountData[0]->created_date;
        }
        $this->isRefreshJobInProgress = LilHotelierDBO::isExistsIncompleteJobOfType( self::JOB_TYPE );
        $this->lastJob = LilHotelierDBO::getDetailsOfLastJob( self::JOB_TYPE );
    }
    
    /**
     * Adds this object to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this object will be added
     */
    function addSelfToDocument($domtree, $parentElement) {
        $parentElement->appendChild($domtree->createElement('homeurl', home_url()));
        $parentElement->appendChild($domtree->createElement('selectiondate', $this->selectionDate->format('Y-m-d')));
        $parentElement->appendChild($domtree->createElement('selectiondate_long', $this->selectionDate->format('D, d M Y')));

        if( $this->lastCompletedAllocScraperJob ) {
            $parentElement->appendChild($domtree->createElement('last_completed_job', 
                DateTime::createFromFormat('Y-m-d H:i:s', $this->lastCompletedAllocScraperJob)->format('D, d M Y H:i:s')));
        }

        if( $this->isRefreshJobInProgress ) {
            $parentElement->appendChild($domtree->createElement('job_in_progress', 'true' ));
        }

        // did the last job fail to run?
        if( $this->lastJob ) {
            $parentElement->appendChild($domtree->createElement('last_job_id', $this->lastJob['jobId'] ));
            $parentElement->appendChild($domtree->createElement('last_job_status', $this->lastJob['status'] ));
            $parentElement->appendChild($domtree->createElement('check_credentials', $this->lastJob['lastJobFailedDueToCredentials'] ? 'true' : 'false' ));
            $parentElement->appendChild($domtree->createElement('last_job_error_log', 
                get_option('hbo_log_directory_url') . $this->lastJob['jobId'] ));
        }

        $xmlRoot = $parentElement->appendChild($domtree->createElement('bedcounts'));
        foreach( $this->bedcountData as $row ) {
            $roomRoot = $xmlRoot->appendChild($domtree->createElement('room'));
            $roomRoot->appendChild($domtree->createElement('id', htmlspecialchars($row->room)));
            $roomRoot->appendChild($domtree->createElement('capacity', $row->capacity));
            $roomRoot->appendChild($domtree->createElement('room_type', $row->room_type));
            $roomRoot->appendChild($domtree->createElement('num_empty', $row->num_empty));
            $roomRoot->appendChild($domtree->createElement('num_staff', $row->num_staff));
            $roomRoot->appendChild($domtree->createElement('num_paid', $row->num_paid));
            $roomRoot->appendChild($domtree->createElement('num_noshow', $row->num_noshow));
        }
    }
    
    /** 
      Generates the following xml:
        <view>
            <homeurl>/domain</homeurl>
            <selectiondate>21.05.2015</selectiondate>
            <selectiondate_long>Mon, 21 May 2015</selectiondate_long>
            <last_completed_job>Sun, 20 May 2015</last_completed_job>
            <bedcounts>
                <room>
                    <id>10</id>
                    <capacity>12</capacity>
                    <room_type>LT_MALE</room_type>
                    <num_empty>3</num_empty>
                    <num_staff>8</num_staff>
                    <num_paid>1</num_paid>
                    <num_noshow>0</num_noshow>
                </room>
                 ...
        </view>
     */
    function toXml() {
        // create a dom document with encoding utf8
        $domtree = new DOMDocument('1.0', 'UTF-8');
        $xmlRoot = $domtree->appendChild($domtree->createElement('view'));
        $this->addSelfToDocument($domtree, $xmlRoot);
        return $domtree->saveXML();
    }

}

?>