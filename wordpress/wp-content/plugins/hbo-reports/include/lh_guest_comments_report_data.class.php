<?php

/**
 * Display controller for reports page.
 */
class LHGuestCommentsReportData extends XslTransform {

    var $guestCommentsReport;  // the view of the latest report
    var $lastSubmittedJob; // date/time of last submitted report job that hasn't run yet
    var $lastCompletedJob; // date/time of last completed job
    var $includeAcknowledged = FALSE; // true to include acknowledged comments
    var $unclassifiedCount = 0; // bookings whose comments are still awaiting guest request extraction
    var $lastJob; // the last job of this type that has run

    // this is the job we're interested in
    const JOB_TYPE = "com.macbackpackers.jobs.AllocationScraperJob";

    // this is the job this report is depended on
    const ALLOC_SCRAPER_JOB_TYPE = "com.macbackpackers.jobs.AllocationScraperJob";

    /**
     * Default constructor.
     */
    function __construct() {
        
    }

    /**
     * Updates the view using the current selection date.
     * $includeAcknowledged : true to include acknowledged comments
     */
    function doView( $includeAcknowledged = FALSE ) {
        $this->guestCommentsReport = LilHotelierDBO::getGuestCommentsReport();
        $this->includeAcknowledged = $includeAcknowledged;
        $this->filterAcknowledged();
        $this->unclassifiedCount = LilHotelierDBO::getUnclassifiedGuestCommentsCount();
        $this->lastCompletedJob = LilHotelierDBO::getLastCompletedJob( self::JOB_TYPE );
        $this->lastSubmittedJob = LilHotelierDBO::getDateTimeOfLastOutstandingJob( self::JOB_TYPE );
        $this->lastJob = LilHotelierDBO::getDetailsOfLastJob( self::ALLOC_SCRAPER_JOB_TYPE );
    }

    /**
     * Removes acknowledged comments unless they've been asked for.
     */
    function filterAcknowledged() {
        if( $this->includeAcknowledged ) {
            return;
        }
        $newReport = array();
        foreach( $this->guestCommentsReport as $record ) {
            if( $record->acknowledged_date === NULL ) {
                $newReport[] = $record;
            }
        }
        $this->guestCommentsReport = $newReport;
    }

    /**
     * Acknowledges a particular LH reservation.
     * $reservationId : ID of LH reservation to acknowledge.
     */
    function acknowledgeComment( $reservationId ) {
        LilHotelierDBO::acknowledgeGuestComment( $reservationId );
    }

    /**
     * Unacknowledges a particular LH reservation.
     * $reservationId : ID of LH reservation to unacknowledge.
     */
    function unacknowledgeComment( $reservationId ) {
        LilHotelierDBO::unacknowledgeGuestComment( $reservationId );
    }

    /**
     * Adds this object to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this object will be added
     */
    function addSelfToDocument($domtree, $parentElement) {

        if( $this->lastSubmittedJob ) {
            $recordRoot = $parentElement->appendChild($domtree->createElement('last_submitted_job', 
                DateTime::createFromFormat('Y-m-d H:i:s', $this->lastSubmittedJob)->format('D, d M Y H:i:s')));
        }

        if( $this->lastCompletedJob ) {
            $parentElement->appendChild($domtree->createElement('last_completed_job', 
                DateTime::createFromFormat('Y-m-d H:i:s', $this->lastCompletedJob)->format('D, d M Y H:i:s')));
        }

        // did the last job fail to run?
        if( $this->lastJob ) {
            $parentElement->appendChild($domtree->createElement('last_job_id', $this->lastJob['jobId'] ));
            $parentElement->appendChild($domtree->createElement('last_job_status', $this->lastJob['status'] ));
            $parentElement->appendChild($domtree->createElement('check_credentials', $this->lastJob['lastJobFailedDueToCredentials'] ? 'true' : 'false' ));
            $parentElement->appendChild($domtree->createElement('last_job_error_log', 
                get_option('hbo_log_directory_url') . $this->lastJob['jobId'] ));
        }

        $parentElement->appendChild($domtree->createElement('show_acknowledged', $this->includeAcknowledged ? 'true' : 'false' ));
        $parentElement->appendChild($domtree->createElement('unclassified_count', $this->unclassifiedCount ));
        $parentElement->appendChild($domtree->createElement('property_manager', get_option('hbo_property_manager')));

        if ( $this->guestCommentsReport ) {
            foreach( $this->guestCommentsReport as $record ) {
                $recordRoot = $parentElement->appendChild($domtree->createElement('record'));
                $recordRoot->appendChild($domtree->createElement('reservation_id', $record->reservation_id));
                // remove any remaining ampersands after HTML translation as they fuck up the DOM tree
                $recordRoot->appendChild($domtree->createElement('guest_name', htmlspecialchars(html_entity_decode($record->guest_name, ENT_COMPAT, "UTF-8" ))));
                $recordRoot->appendChild($domtree->createElement('booking_reference', $record->booking_reference));
                $recordRoot->appendChild($domtree->createElement('booking_source', $record->booking_source));
                $recordRoot->appendChild($domtree->createElement('checkin_date_yyyymmdd', DateTime::createFromFormat('Y-m-d H:i:s', $record->checkin_date)->format('Y-m-d')));
                $recordRoot->appendChild($domtree->createElement('checkin_date', DateTime::createFromFormat('Y-m-d H:i:s', $record->checkin_date)->format('D, d M Y')));
                $recordRoot->appendChild($domtree->createElement('checkout_date', DateTime::createFromFormat('Y-m-d H:i:s', $record->checkout_date)->format('D, d M Y')));
                if( $record->booked_date ) {
                    $recordRoot->appendChild($domtree->createElement('booked_date', DateTime::createFromFormat('Y-m-d H:i:s', $record->booked_date)->format('D, d M Y')));
                }
                $recordRoot->appendChild($domtree->createElement('num_guests', $record->num_guests));
                $recordRoot->appendChild($domtree->createElement('data_href', $record->data_href));
                if ( isset( $record->notes ) ) {
                    $recordRoot->appendChild($domtree->createElement('notes', htmlspecialchars($record->notes)));
                }
                if ( isset( $record->guest_request ) ) {
                    $recordRoot->appendChild( $domtree->createElement( 'guest_request', htmlspecialchars( $record->guest_request, ENT_XML1, "UTF-8" ) ) );
                }
                if ( isset( $record->comments ) ) {
                    $recordRoot->appendChild( $domtree->createElement( 'comments', htmlspecialchars( preg_replace( '/<br *\/>/si', ' ', $record->comments ), ENT_XML1, "UTF-8" ) ) );
                }
                if( isset( $record->acknowledged_date ) ) {
                    $recordRoot->appendChild($domtree->createElement('acknowledged_date', DateTime::createFromFormat('Y-m-d H:i:s', $record->acknowledged_date)->format('D, d M Y')));
                }
            }
        }
    }
    
    /** 
      Generates the following xml:
        <view>
            <last_submitted_job>2015-05-24 13:22:58</last_submitted_job>
            <show_acknowledged>true</show_acknowledged>
            <unclassified_count>0</unclassified_count>
            <record>
                <reservation_id>123456</reservation_id>
                <guest_name>Joe Bloggs</guest_name>
                <booking_reference>192121</booking_reference>
                <booking_source>Extranet</booking_source>
                <checkin_date>Mon, 18 May 2015</checkin_date>
                <checkout_date>Wed, 20 May 2015</checkout_date>
                <booked_date>Fri, 13 Apr 2015</booked_date>
                <payment_outstanding>215.34</payment_outstanding>
                <data_href>/extranet/properties/533/reservations/1046289/edit</data_href>
                <num_guests>10</num_guests>
                <notes>Arriving late</notes>
                <guest_request>Could you please put me in a lower bunk?</guest_request>
                <comments>*** Genius booker *** ... Could you please put me in a lower bunk? The virtual card expires on ...</comments>
                <acknowledged_date>Mon, 16 Apr 2015</acknowledged_date>
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
        return HBO_PLUGIN_DIR. '/include/lh_guest_comments_report_data.xsl';
    }

}

?>