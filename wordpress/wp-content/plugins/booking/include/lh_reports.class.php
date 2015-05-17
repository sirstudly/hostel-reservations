<?php

/**
 * Display controller for reports page.
 */
class LHReports extends XslTransform {

    var $splitRoomReport;  // the view of the latest split room report

    /**
     * Default constructor.
     */
    function LHReports() {
        
    }

    /**
     * Updates the view using the current selection date.
     */
    function doView() {
        $this->splitRoomReport = LilHotelierDBO::getSplitRoomReservationsReport();
    }
    
    /**
     * Adds this object to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this object will be added
     */
    function addSelfToDocument($domtree, $parentElement) {
        if ( $this->splitRoomReport ) {
            foreach( $this->splitRoomReport as $record ) {
                $recordRoot = $parentElement->appendChild($domtree->createElement('record'));
                $recordRoot->appendChild($domtree->createElement('reservation_id', $record->reservation_id));
                $recordRoot->appendChild($domtree->createElement('guest_name', $record->guest_name));
                $recordRoot->appendChild($domtree->createElement('checkin_date', DateTime::createFromFormat('Y-m-d H:i:s', $record->checkin_date)->format('D, d M Y')));
                $recordRoot->appendChild($domtree->createElement('checkout_date', DateTime::createFromFormat('Y-m-d H:i:s', $record->checkout_date)->format('D, d M Y')));
                $recordRoot->appendChild($domtree->createElement('data_href', $record->data_href));
                $recordRoot->appendChild($domtree->createElement('notes', $record->notes));
                $recordRoot->appendChild($domtree->createElement('created_date', DateTime::createFromFormat('Y-m-d H:i:s', $record->created_date)->format('D, d M Y H:i:s')));
            }
        }
    }
    
    /** 
      Generates the following xml:
        <view>
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
        return WPDEV_BK_PLUGIN_DIR. '/include/lh_reports.xsl';
    }

}

?>