<?php

/**
 * Display controller for online checkin page.
 */
class OnlineCheckin extends XslTransform {

    /**
     * Reloads the view details.
     */
    function doView() {
    }

    /**
     * Creates a booking URL from a booking.
     * @param $booking_identifier string cloudbeds booking id as it appears on the page
     * @throws Exception on lookup failure
     */
    function generateBookingURL($booking_identifier) {
        $controller = new GeneratePaymentLinkController();
        return $controller->generateBookingURL($booking_identifier);
    }

    /**
     * Adds this object to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this object will be added
     */
    function addSelfToDocument($domtree, $parentElement) {
        $parentElement->appendChild($domtree->createElement('homeurl', home_url()));
        $parentElement->appendChild($domtree->createElement('pluginurl', HBO_PLUGIN_URL));
    }
    
    /** 
      Generates the following xml:
        <view>
            <homeurl>/yourwebapp/</homeurl>
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
        return HBO_PLUGIN_DIR. '/include/online_checkin.xsl';
    }

}

?>