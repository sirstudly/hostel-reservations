<?php

/**
 * Display controller for reports page.
 */
class LHSplitRoomReport extends XslTransform {

    var $splitRoomReport;  // the view of the latest split room report
    var $lastSubmittedAllocScraperJob; // date/time of last submitted allocation scraper job that hasn't run yet

    /**
     * Default constructor.
     */
    function LHSplitRoomReport() {
        
    }

    /**
     * Updates the view using the current selection date.
     */
    function doView() {
        $this->splitRoomReport = LilHotelierDBO::getSplitRoomReservationsReport();
        $this->lastSubmittedAllocScraperJob = LilHotelierDBO::getOutstandingAllocationScraperJob();
    }

    /**
     * Inserts an allocation scraper job into the jobs table.
     */
    function submitAllocationScraperJob() {
        LilHotelierDBO::insertAllocationScraperJob();
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

        if ( $this->splitRoomReport ) {
            foreach( $this->splitRoomReport as $record ) {
                $recordRoot = $parentElement->appendChild($domtree->createElement('record'));
                $recordRoot->appendChild($domtree->createElement('reservation_id', $record->reservation_id));
                $recordRoot->appendChild($domtree->createElement('guest_name', $record->guest_name));
                $recordRoot->appendChild($domtree->createElement('checkin_date', DateTime::createFromFormat('Y-m-d H:i:s', $record->checkin_date)->format('D, d M Y')));
                $recordRoot->appendChild($domtree->createElement('checkout_date', DateTime::createFromFormat('Y-m-d H:i:s', $record->checkout_date)->format('D, d M Y')));
                $recordRoot->appendChild($domtree->createElement('data_href', $record->data_href));
                $recordRoot->appendChild($domtree->createElement('status', $record->lh_status));
                $recordRoot->appendChild($domtree->createElement('booking_reference', $record->booking_reference));
                $recordRoot->appendChild($domtree->createElement('booking_source', $record->booking_source));
                if( $record->booked_date ) {
                    $recordRoot->appendChild($domtree->createElement('booked_date', DateTime::createFromFormat('Y-m-d H:i:s', $record->booked_date)->format('D, d M Y')));
                }
                $recordRoot->appendChild($domtree->createElement('eta', $record->eta));
                $recordRoot->appendChild($domtree->createElement('viewed_yn', $record->viewed_yn));
                $recordRoot->appendChild($domtree->createElement('notes', $record->notes));
                $recordRoot->appendChild($domtree->createElement('created_date', DateTime::createFromFormat('Y-m-d H:i:s', $record->created_date)->format('D, d M Y H:i:s')));
            }
        }
    }
    
    /** 
      Generates the following xml:
        <view>
            <last_submitted_job>2015-05-24 13:22:58</last_submitted_job>
            <record>
                <reservation_id>123456</reservation_id>
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
        return WPDEV_BK_PLUGIN_DIR. '/include/lh_split_room_report.xsl';
    }

}

?>