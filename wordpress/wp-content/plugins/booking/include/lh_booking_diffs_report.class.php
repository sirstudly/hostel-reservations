<?php

/**
 * Display controller for reports page.
 */
class LHBookingsDiffsReport extends XslTransform {

    const JOB_TYPE = "com.macbackpackers.jobs.DiffBookingEnginesJob";
    private $selectionDate;  // DateTime of selected date
    private $bookingDiffsReport;  // the view of the latest report
    private $lastCompletedJob; // date/time of last completed job for selectionDate
    private $isRefreshJobInProgress = false;
    private $lastJob; // the last job of this type that has run

    /**
     * Default constructor.
     * $selectionDate : the arrival date for this report
     */
    function LHBookingsDiffsReport($selectionDate = null) {
        $this->selectionDate = $selectionDate == null ? new DateTime() : $selectionDate;
    }

    /**
     * Updates the view using the current selection date.
     */
    function doView() {
        $this->lastCompletedJob = LilHotelierDBO::getLastCompletedBookingDiffsJob( $this->selectionDate );
        if( $this->lastCompletedJob ) {
            $this->bookingDiffsReport = LilHotelierDBO::getBookingDiffsReport( 
                $this->selectionDate, $this->lastCompletedJob->job_id );
        }
        $this->isRefreshJobInProgress = LilHotelierDBO::isExistsIncompleteJobOfType( self::JOB_TYPE );
        $this->lastJob = LilHotelierDBO::getDetailsOfLastJob( self::JOB_TYPE );
    }

    /**
     * Inserts a booking diffs job into the jobs table.
     */
    function submitBookingDiffsJob() {
        LilHotelierDBO::insertJobOfType( self::JOB_TYPE,
            array( "checkin_date" => $this->selectionDate->format('Y-m-d') ) );
        LilHotelierDBO::runProcessor();
    }
    
    /**
     * Adds this object to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this object will be added
     */
    function addSelfToDocument($domtree, $parentElement) {

        $parentElement->appendChild($domtree->createElement('homeurl', home_url()));

        if( $this->lastCompletedJob ) {
            $parentElement->appendChild($domtree->createElement('last_completed_job', 
                DateTime::createFromFormat('Y-m-d H:i:s', $this->lastCompletedJob->end_date)->format('D, d M Y H:i:s')));
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
                get_option('hbo_log_directory_url') . "/job-" . $this->lastJob['jobId'] . ".txt" ));
        }

        $parentElement->appendChild($domtree->createElement('selection_date_long', 
            $this->selectionDate->format('d M Y')));
        $parentElement->appendChild($domtree->createElement('selection_date_uri', 
            $this->selectionDate->format('d+M+Y')));
        $parentElement->appendChild($domtree->createElement('selection_date_hb', 
            $this->selectionDate->format('d-F-Y')));
        $parentElement->appendChild($domtree->createElement('selection_date', 
            $this->selectionDate->format('Y-m-d')));

        if ( $this->bookingDiffsReport ) {
            foreach( $this->bookingDiffsReport as $record ) {
                $recordRoot = $parentElement->appendChild($domtree->createElement('record'));
                $recordRoot->appendChild($domtree->createElement('guest_name', html_entity_decode($record->guest_name, ENT_COMPAT, "UTF-8" )));
                $recordRoot->appendChild($domtree->createElement('hw_room_type', $record->hw_room_type));
                $recordRoot->appendChild($domtree->createElement('hw_checkin_date', DateTime::createFromFormat('Y-m-d H:i:s', $record->hw_checkin_date)->format('d M Y')));
                $recordRoot->appendChild($domtree->createElement('hw_checkout_date', DateTime::createFromFormat('Y-m-d H:i:s', $record->hw_checkout_date)->format('d M Y')));
                $recordRoot->appendChild($domtree->createElement('hw_persons', $record->hw_persons));
                $recordRoot->appendChild($domtree->createElement('hw_payment_outstanding', $record->hw_payment_outstanding));
                $recordRoot->appendChild($domtree->createElement('booked_date', DateTime::createFromFormat('Y-m-d H:i:s', $record->booked_date)->format('d M Y H:i:s')));
                $recordRoot->appendChild($domtree->createElement('booking_source', $record->booking_source));
                $recordRoot->appendChild($domtree->createElement('booking_reference', $record->booking_reference));
                $recordRoot->appendChild($domtree->createElement('lh_room_type', $record->lh_room_type));
                $recordRoot->appendChild($domtree->createElement('lh_status', $record->lh_status));
                $recordRoot->appendChild($domtree->createElement('lh_checkin_date', $record->lh_checkin_date == null ? null : DateTime::createFromFormat('Y-m-d H:i:s', $record->lh_checkin_date)->format('d M Y')));
                $recordRoot->appendChild($domtree->createElement('lh_checkout_date', $record->lh_checkout_date == null ? null : DateTime::createFromFormat('Y-m-d H:i:s', $record->lh_checkout_date)->format('d M Y')));
                $recordRoot->appendChild($domtree->createElement('lh_persons', $record->lh_persons));
                $recordRoot->appendChild($domtree->createElement('lh_payment_outstanding', $record->lh_payment_outstanding));
                $recordRoot->appendChild($domtree->createElement('lh_data_href', $record->data_href));
                $recordRoot->appendChild($domtree->createElement('notes', $record->notes));
                $recordRoot->appendChild($domtree->createElement('matched_persons', $record->matched_persons));
                $recordRoot->appendChild($domtree->createElement('matched_room_type', $record->matched_room_type));
                $recordRoot->appendChild($domtree->createElement('matched_checkin_date', $record->matched_checkin_date));
                $recordRoot->appendChild($domtree->createElement('matched_checkout_date', $record->matched_checkout_date));
                $recordRoot->appendChild($domtree->createElement('matched_payment_outstanding', $record->matched_payment_outstanding));
            }
        }
    }
    
    /** 
      Generates the following xml:
        <view>
            <last_completed_job>2015-05-24 13:22:58</last_completed_job>
            <record>
                <guest_name>Joe Bloggs</guest_name>
                <hw_room_type>10MX</hw_room_type>
                <hw_checkin_date>Mon, 18 May 2015</hw_checkin_date>
                <hw_checkout_date>Wed, 20 May 2015</hw_checkout_date>
                <hw_persons>Female: 2</hw_persons>
                <booked_date>Fri, 13 Apr 2015 10:58:08</booked_date>
                <booking_reference>145888200</booking_reference>
                <lh_room_type>10MX</lh_room_type>
                <lh_checkin_date>Mon, 18 May 2015</lh_checkin_date>
                <lh_checkout_date>Wed, 20 May 2015</lh_checkout_date>
                <lh_persons>2</lh_persons>
                <notes>Arriving late</notes>
                <matched_persons>Y</matched_persons>
                <matched_room_type>Y</matched_room_type>
                <matched_checkin_date>Y</matched_checkin_date>
                <matched_checkout_date>Y</matched_checkout_date>
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
        return WPDEV_BK_PLUGIN_DIR. '/include/lh_booking_diffs_report.xsl';
    }

}

?>