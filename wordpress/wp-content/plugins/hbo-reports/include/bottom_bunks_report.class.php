<?php

/**
 * Display controller for reports page.
 */
class BottomBunksReport extends XslTransform {

    const JOB_TYPE = "com.macbackpackers.jobs.AllocationScraperJob";

    var $bottomBunksReport;  // the view of the latest bottom bunks report
    var $lastSubmittedAllocScraperJob; // date/time of last submitted allocation scraper job that hasn't run yet
    var $lastCompletedAllocScraperJob; // date/time of last completed allocation scraper job
    var $lastJob; // the last job of this type that has run

    /**
     * Default constructor.
     */
    function __construct() {
        
    }

    /**
     * Updates the view using the current selection date.
     */
    function doView() {
        $this->bottomBunksReport = LilHotelierDBO::getBottomBunksReport();
        $this->lastSubmittedAllocScraperJob = LilHotelierDBO::getOutstandingAllocationScraperJob();
        $this->lastCompletedAllocScraperJob = LilHotelierDBO::getLastCompletedAllocationScraperJob();
        $this->lastJob = LilHotelierDBO::getDetailsOfLastJob( self::JOB_TYPE );
    }

    /**
     * Inserts an allocation scraper job into the jobs table.
     */
    function submitAllocationScraperJob() {
        LilHotelierDBO::insertAllocationScraperJob();
        LilHotelierDBO::runProcessor();
    }
    
    /**
     * Adds this object to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this object will be added
     */
    function addSelfToDocument($domtree, $parentElement) {

        if( $this->lastSubmittedAllocScraperJob ) {
            $recordRoot = $parentElement->appendChild($domtree->createElement('last_submitted_job', 
                DateTime::createFromFormat('Y-m-d H:i:s', $this->lastSubmittedAllocScraperJob)->format('D, d M Y H:i:s')));
        }

        if( $this->lastCompletedAllocScraperJob ) {
            $parentElement->appendChild($domtree->createElement('last_completed_job', 
                DateTime::createFromFormat('Y-m-d H:i:s', $this->lastCompletedAllocScraperJob)->format('D, d M Y H:i:s')));
        }

        $parentElement->appendChild($domtree->createElement('property_manager', get_option('hbo_property_manager')));

        // did the last job fail to run?
        if( $this->lastJob ) {
            $parentElement->appendChild($domtree->createElement('last_job_id', $this->lastJob['jobId'] ));
            $parentElement->appendChild($domtree->createElement('last_job_status', $this->lastJob['status'] ));
            $parentElement->appendChild($domtree->createElement('check_credentials', $this->lastJob['lastJobFailedDueToCredentials'] ? 'true' : 'false' ));
            $parentElement->appendChild($domtree->createElement('last_job_error_log', 
                get_option('hbo_log_directory_url') . $this->lastJob['jobId'] ));
        }

        if ( $this->bottomBunksReport ) {
            foreach( $this->bottomBunksReport as $record ) {
                $recordRoot = $parentElement->appendChild($domtree->createElement('record'));
                $recordRoot->appendChild($domtree->createElement('reservation_id', $record->reservation_id));
                $recordRoot->appendChild($domtree->createElement('room', $record->room));
                $recordRoot->appendChild($domtree->createElement('bed_name', htmlspecialchars(html_entity_decode($record->bed_name, ENT_COMPAT, "UTF-8" ))));
                $recordRoot->appendChild($domtree->createElement('guest_name', htmlspecialchars(html_entity_decode($record->guest_name, ENT_COMPAT, "UTF-8" ))));
                $recordRoot->appendChild($domtree->createElement('checkin_date', DateTime::createFromFormat('Y-m-d H:i:s', $record->checkin_date)->format('D, d M Y')));
                $recordRoot->appendChild($domtree->createElement('checkin_date_yyyymmdd', DateTime::createFromFormat('Y-m-d H:i:s', $record->checkin_date)->format('Y-m-d')));
                $recordRoot->appendChild($domtree->createElement('checkin_datetime', DateTime::createFromFormat('Y-m-d H:i:s', $record->checkin_date)->getTimestamp()));
                $recordRoot->appendChild($domtree->createElement('checkout_date', DateTime::createFromFormat('Y-m-d H:i:s', $record->checkout_date)->format('D, d M Y')));
                $recordRoot->appendChild($domtree->createElement('checkout_datetime', DateTime::createFromFormat('Y-m-d H:i:s', $record->checkout_date)->getTimestamp()));
                $recordRoot->appendChild($domtree->createElement('data_href', $record->data_href));
                $recordRoot->appendChild($domtree->createElement('status', $record->lh_status));
                $recordRoot->appendChild($domtree->createElement('booking_reference', $record->booking_reference));
                $recordRoot->appendChild($domtree->createElement('booking_source', htmlspecialchars(html_entity_decode($record->booking_source, ENT_COMPAT, "UTF-8" ))));
                if ( isset( $record->notes ) ) {
                    $recordRoot->appendChild( $domtree->createElement( 'notes', htmlspecialchars( $record->notes ) ) );
                }
                if ( isset( $record->comments ) ) {
                    $recordRoot->appendChild( $domtree->createElement( 'comments', htmlspecialchars( $record->comments ) ) );
                }
            }
        }
    }
    
    /** 
      Generates the following xml:
        <view>
            <last_submitted_job>2015-05-24 13:22:58</last_submitted_job>
            <record>
                <reservation_id>123456</reservation_id>
                <room>41</room>
                <bed_name>09-G-String</bed_name>
                <guest_name>Joe Bloggs</guest_name>
                <checkin_date>Mon, 18 May 2015</checkin_date>
                <checkout_date>Wed, 20 May 2015</checkout_date>
                <data_href>/extranet/properties/533/reservations/1046289/edit</data_href>
                <notes>Arriving late</notes>
                <created_date>Sun, 17 May 2015 03:57:19</created_date>
            </record>
            <record>
                ...
            </record>
            ...
        </view>
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
        return HBO_PLUGIN_DIR. '/include/bottom_bunks_report.xsl';
    }

}

?>