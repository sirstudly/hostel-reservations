<?php

/**
 * Display controller for reports page.
 */
class LHSplitRoomReport extends XslTransform {

    const JOB_TYPE = "com.macbackpackers.jobs.AllocationScraperJob";
    const REPORT_JOB_TYPE = "com.macbackpackers.jobs.SplitRoomReservationReportJob";
    const HINT_JOB_TYPE = "com.macbackpackers.jobs.BedShuffleHintJob";

    var $splitRoomReport;  // the view of the latest split room report
    var $multipleBookingReport; // the view of multiple contiguous bookings report
    var $lastSubmittedAllocScraperJob; // date/time of last submitted allocation scraper job that hasn't run yet
    var $lastCompletedAllocScraperJob; // date/time of last completed allocation scraper job
    var $lastJob; // the last job of this type that has run
    var $hintsInProgress; // true if shuffle hints are still being (re)calculated

    /**
     * Default constructor.
     */
    function __construct() {
        
    }

    /**
     * Updates the view using the current selection date.
     */
    function doView() {
        $this->splitRoomReport = LilHotelierDBO::getSplitRoomReservationsReport();
        $this->multipleBookingReport = LilHotelierDBO::getSplitRoomMultipleReservationsReport();
        $this->lastSubmittedAllocScraperJob = LilHotelierDBO::getOutstandingAllocationScraperJob();
        $this->lastCompletedAllocScraperJob = LilHotelierDBO::getLastCompletedAllocationScraperJob();
        $this->lastJob = LilHotelierDBO::getDetailsOfLastJob( self::JOB_TYPE );
        $this->hintsInProgress = LilHotelierDBO::isExistsIncompleteJobOfType( self::REPORT_JOB_TYPE )
            || LilHotelierDBO::isExistsIncompleteJobOfType( self::HINT_JOB_TYPE );
    }

    /**
     * Inserts an allocation scraper job into the jobs table.
     */
    function submitAllocationScraperJob() {
        LilHotelierDBO::insertAllocationScraperJob();
        LilHotelierDBO::runProcessor();
    }

    /**
     * Re-runs the latest split room report from the current booking assignments (without re-scraping),
     * which recreates both tables and queues a fresh bed shuffle hint for every row.
     */
    function submitRecalculateHintsJob() {
        $allocationScraperJobId = LilHotelierDBO::getLatestSplitRoomReportAllocationScraperJobId();
        if ( $allocationScraperJobId ) {
            LilHotelierDBO::insertJobOfType( self::REPORT_JOB_TYPE,
                array( 'allocation_scraper_job_id' => $allocationScraperJobId ) );
            LilHotelierDBO::runProcessor();
        }
    }

    /**
     * Adds the bed shuffle hint elements for one report row.
     * $recordRoot : DOM element of the row
     * $reservationId : reservation (of the pair, the one checking in) the hint is for
     * $checkinDate : its checkin date (Y-m-d H:i:s)
     * $record : row with shuffle_status / shuffle_hint
     */
    private function addShuffleHint( $domtree, $recordRoot, $reservationId, $checkinDate, $record ) {
        // a bed shuffle hint job is queued for every row checking in today or later
        if ( $reservationId > 0 && substr( $checkinDate, 0, 10 ) >= current_time( 'Y-m-d' ) ) {
            $recordRoot->appendChild( $domtree->createElement( 'hint_eligible', 'true' ) );
        }
        if ( isset( $record->shuffle_status ) ) {
            $recordRoot->appendChild( $domtree->createElement( 'shuffle_status', $record->shuffle_status ) );
            if ( $record->shuffle_status == 'FOUND' && isset( $record->shuffle_hint ) ) {
                $recordRoot->appendChild( $domtree->createElement( 'shuffle_move_count',
                    preg_match_all( '/^\s*\d+\.\s/m', $record->shuffle_hint ) ) );
            }
        }
        if ( isset( $record->shuffle_hint ) ) {
            $recordRoot->appendChild( $domtree->createElement( 'shuffle_hint', htmlspecialchars( $record->shuffle_hint ) ) );
        }
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

        if ( $this->hintsInProgress ) {
            $parentElement->appendChild( $domtree->createElement( 'hints_in_progress', 'true' ) );
        }

        // did the last job fail to run?
        if( $this->lastJob ) {
            $parentElement->appendChild($domtree->createElement('last_job_id', $this->lastJob['jobId'] ));
            $parentElement->appendChild($domtree->createElement('last_job_status', $this->lastJob['status'] ));
            $parentElement->appendChild($domtree->createElement('check_credentials', $this->lastJob['lastJobFailedDueToCredentials'] ? 'true' : 'false' ));
            $parentElement->appendChild($domtree->createElement('last_job_error_log', 
                get_option('hbo_log_directory_url') . $this->lastJob['jobId'] ));
        }

        if ( $this->splitRoomReport ) {
            $splitRoomReportRoot = $parentElement->appendChild($domtree->createElement('split_room_report'));
            foreach( $this->splitRoomReport as $record ) {
                $recordRoot = $splitRoomReportRoot->appendChild($domtree->createElement('record'));
                $recordRoot->appendChild($domtree->createElement('reservation_id', $record->reservation_id));
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
                if( $record->booked_date ) {
                    $recordRoot->appendChild($domtree->createElement('booked_date', DateTime::createFromFormat('Y-m-d H:i:s', $record->booked_date)->format('D, d M Y')));
                    $recordRoot->appendChild($domtree->createElement('booked_datetime', DateTime::createFromFormat('Y-m-d H:i:s', $record->booked_date)->getTimestamp()));
                }
                if ( isset( $record->notes ) ) {
                    $recordRoot->appendChild( $domtree->createElement( 'notes', htmlspecialchars( $record->notes ) ) );
                }
                $this->addShuffleHint( $domtree, $recordRoot, $record->reservation_id, $record->checkin_date, $record );
            }
        }


        if ( $this->multipleBookingReport ) {
            $multipleBookingReportRoot = $parentElement->appendChild( $domtree->createElement( 'multiple_booking_report' ) );
            foreach ( $this->multipleBookingReport as $record ) {
                $recordRoot = $multipleBookingReportRoot->appendChild( $domtree->createElement( 'record' ) );
                $recordRoot->appendChild( $domtree->createElement( 'guest_name', htmlspecialchars( html_entity_decode( $record->guest_name, ENT_COMPAT, "UTF-8" ) ) ) );
                $recordRoot->appendChild( $domtree->createElement( 'booking_ref_left', $record->booking_ref_left ) );
                $recordRoot->appendChild( $domtree->createElement( 'checkin_date_left', DateTime::createFromFormat( 'Y-m-d H:i:s', $record->checkin_date_left )->format( 'd M Y' ) ) );
                $recordRoot->appendChild( $domtree->createElement( 'checkin_datetime_left', DateTime::createFromFormat( 'Y-m-d H:i:s', $record->checkin_date_left )->getTimestamp() ) );
                $recordRoot->appendChild( $domtree->createElement( 'checkout_date_left', DateTime::createFromFormat( 'Y-m-d H:i:s', $record->checkout_date_left )->format( 'd M Y' ) ) );
                $recordRoot->appendChild( $domtree->createElement( 'checkout_datetime_left', DateTime::createFromFormat( 'Y-m-d H:i:s', $record->checkout_date_left )->getTimestamp() ) );
                $recordRoot->appendChild( $domtree->createElement( 'data_href_left', $record->data_href_left ) );
                if ( $record->booked_date_left ) {
                    $recordRoot->appendChild( $domtree->createElement( 'booked_date_left', DateTime::createFromFormat( 'Y-m-d H:i:s', $record->booked_date_left )->format( 'd M Y' ) ) );
                    $recordRoot->appendChild( $domtree->createElement( 'booked_datetime_left', DateTime::createFromFormat( 'Y-m-d H:i:s', $record->booked_date_left )->getTimestamp() ) );
                }
                $recordRoot->appendChild( $domtree->createElement( 'room_beds_left', htmlspecialchars( html_entity_decode( $record->room_beds_left, ENT_COMPAT, "UTF-8" ) ) ) );

                $recordRoot->appendChild( $domtree->createElement( 'booking_ref_right', $record->booking_ref_right ) );
                $recordRoot->appendChild( $domtree->createElement( 'checkin_date_right', DateTime::createFromFormat( 'Y-m-d H:i:s', $record->checkin_date_right )->format( 'd M Y' ) ) );
                $recordRoot->appendChild( $domtree->createElement( 'checkin_datetime_right', DateTime::createFromFormat( 'Y-m-d H:i:s', $record->checkin_date_right )->getTimestamp() ) );
                $recordRoot->appendChild( $domtree->createElement( 'checkout_date_right', DateTime::createFromFormat( 'Y-m-d H:i:s', $record->checkout_date_right )->format( 'd M Y' ) ) );
                $recordRoot->appendChild( $domtree->createElement( 'checkout_datetime_right', DateTime::createFromFormat( 'Y-m-d H:i:s', $record->checkout_date_right )->getTimestamp() ) );
                $recordRoot->appendChild( $domtree->createElement( 'data_href_right', $record->data_href_right ) );
                if ( $record->booked_date_right ) {
                    $recordRoot->appendChild( $domtree->createElement( 'booked_date_right', DateTime::createFromFormat( 'Y-m-d H:i:s', $record->booked_date_right )->format( 'd M Y' ) ) );
                    $recordRoot->appendChild( $domtree->createElement( 'booked_datetime_right', DateTime::createFromFormat( 'Y-m-d H:i:s', $record->booked_date_right )->getTimestamp() ) );
                }
                $recordRoot->appendChild( $domtree->createElement( 'room_beds_right', htmlspecialchars( html_entity_decode( $record->room_beds_right, ENT_COMPAT, "UTF-8" ) ) ) );
                $this->addShuffleHint( $domtree, $recordRoot, $record->reservation_id_right, $record->checkin_date_right, $record );
            }
        }
    }
    
    /** 
      Generates the following xml:
        <view>
            <last_submitted_job>2015-05-24 13:22:58</last_submitted_job>
            <hints_in_progress>true</hints_in_progress>
            <split_room_report>
                <record>
                    <reservation_id>123456</reservation_id>
                    <guest_name>Joe Bloggs</guest_name>
                    <checkin_date>Mon, 18 May 2015</checkin_date>
                    <checkout_date>Wed, 20 May 2015</checkout_date>
                    <data_href>/extranet/properties/533/reservations/1046289/edit</data_href>
                    <notes>Arriving late</notes>
                    <hint_eligible>true</hint_eligible>
                    <shuffle_status>FOUND</shuffle_status>
                    <shuffle_move_count>2</shuffle_move_count>
                    <shuffle_hint>  1. Move ...</shuffle_hint>
                    <created_date>Sun, 17 May 2015 03:57:19</created_date>
                </record>
                <record>
                    ...
                </record>
            </split_room_report>
            <multiple_booking_report>
                <record>
                    <guest_name>Joe Bloggs</guest_name>
                    <booking_ref_left>12345678</booking_ref_left>
                    <checkin_date_left>Mon, 18 May 2015</checkin_date_left>
                    <checkout_date_left>Wed, 20 May 2015</checkout_date_left>
                    <data_href_left>/extranet/properties/533/reservations/1046289/edit</data_href/left>
                    ...
                    <hint_eligible>true</hint_eligible>
                    <shuffle_status>FOUND</shuffle_status>
                    <shuffle_move_count>2</shuffle_move_count>
                    <shuffle_hint>  1. Move ...</shuffle_hint>
                </record>
                <record>
                    ...
                </record>
            </multiple_booking_report>
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
        return HBO_PLUGIN_DIR. '/include/lh_split_room_report.xsl';
    }

}

?>