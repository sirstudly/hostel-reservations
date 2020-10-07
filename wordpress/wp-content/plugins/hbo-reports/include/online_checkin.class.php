<?php

/**
 * Display controller for online checkin page.
 */
class OnlineCheckin extends XslTransform {

    var $booking; // the currently displayed booking
	var $resetView; // set to display default view

    /**
     * Reloads the view details.
     */
    function doView() {
    	$this->booking = NULL;
    	$this->resetView = NULL;
    }

	/**
	 * Generate the default panel.
	 */
	function resetView() {
		$this->resetView = TRUE;
	}

	/**
     * Loads a cloudbeds booking.
     * @param $booking_identifier string cloudbeds booking id as it appears on the page
     * @throws Exception on lookup failure
     */
    function loadBooking($booking_identifier) {
        $controller = new GeneratePaymentLinkController();
        $this->booking = $controller->loadBookingWithLookupKey($booking_identifier);
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
        $parentElement->appendChild($domtree->createElement('notifyurl', get_option("hbo_checkin_notify_wss_url")));
	    switch(get_option("hbo_cloudbeds_property_id")) {
		    case '17363':
			    $parentElement->appendChild($domtree->createElement('hostel', 'Castle Rock'));
			    $parentElement->appendChild($domtree->createElement('logo', 'https://www.castlerockedinburgh.com/wp-content/themes/castlerock/castlerock-large.svg'));
			    break;
		    case '17959':
			    $parentElement->appendChild($domtree->createElement('hostel', 'High Street Hostel'));
			    $parentElement->appendChild($domtree->createElement('logo', 'https://www.highstreethostel.com/wp-content/themes/highstreethostel/highstreethostel-large.svg'));
			    break;
		    case '18137':
			    $parentElement->appendChild($domtree->createElement('hostel', 'Lochside'));
			    $parentElement->appendChild($domtree->createElement('logo', 'http://lochsidehostel.com/wp-content/uploads/2017/07/Logo-Bigger-Drop-Show.png'));
			    break;
		    case '18265':
			    $parentElement->appendChild($domtree->createElement('hostel', 'Royal Mile Backpackers'));
			    $parentElement->appendChild($domtree->createElement('logo', '/royalmile/RMB-Small-Logo.png'));
			    break;
	    };

	    if ( $this->resetView ) {
		    $parentElement->appendChild( $domtree->createElement( 'reset_view', "true" ) );
	    }
	    elseif ( $this->booking ) {
	        $bookingRoot = $parentElement->appendChild($domtree->createElement('booking'));
	        $bookingRoot->appendChild($domtree->createElement('identifier', $this->booking['identifier']));
	        $bookingRoot->appendChild($domtree->createElement('third_party_identifier', $this->booking['third_party_identifier']));
	        $bookingRoot->appendChild($domtree->createElement('booking_source', htmlspecialchars(html_entity_decode($this->booking['source_name'], ENT_COMPAT, "UTF-8" ))));
	        $bookingRoot->appendChild($domtree->createElement('name', $this->booking['name']));
	        $bookingRoot->appendChild($domtree->createElement('checkin_date', DateTime::createFromFormat('Y-m-d', $this->booking['checkin_date'])->format('D M d')));
	        $bookingRoot->appendChild($domtree->createElement('checkout_date', DateTime::createFromFormat('Y-m-d', $this->booking['checkout_date'])->format('D M d')));
	        $bookingRoot->appendChild($domtree->createElement('num_guests', intval($this->booking['adults_number']) + intval($this->booking['kids_number'])));
	        $bookingRoot->appendChild($domtree->createElement('grand_total', number_format($this->booking['grand_total'], 2)));
	        $bookingRoot->appendChild($domtree->createElement('balance_due', number_format($this->booking['balance_due'], 2)));
	        $bookingRoot->appendChild($domtree->createElement('booking_url', get_option("hbo_bookings_url") . $this->booking['lookup_key']));
	        $parentElement->appendChild($bookingRoot);
        }
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