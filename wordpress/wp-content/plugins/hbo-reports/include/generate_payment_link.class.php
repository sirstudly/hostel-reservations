<?php

/**
 * Display controller for generating payment links.
 */
class GeneratePaymentLinkController extends XslTransform {

    // all allowable characters for lookup key
    const LOOKUPKEY_CHARSET = "2345678ABCDEFGHJKLMNPQRSTUVWXYZ";
    const LOOKUPKEY_LENGTH = 7;

    // currently loaded booking
    var $booking;

    // generated invoice key
    var $invoice_lookup_key;

    /**
     * Default constructor.
     */
    function __construct() {
        
    }

    /**
     * Reloads the view details.
     */
    function doView() {
    }
   
    /**
     * Looks up the cloudbeds booking ("identifier") and generates:
     *   - the booking details (name, checkin-date, checkout-date, number of guests, total, amount due)
     *   - a generated link to the payment portal for this booking
     * or
     *   - an error message if booking doesn't exist
     *
     * @param $booking_ref string cloudbeds identifier
     * @param $deposit_only boolean true to request deposit amount only, false for total outstanding
     */
    function generatePaymentLink($booking_ref, $deposit_only) {

        $response = $this->lookupReservation($booking_ref);

        // sum all rates matching checkin-date
        $total_deposit = 0;
        if (! empty($response['booking_rooms'])) {
            foreach( $response['booking_rooms'] as $room) {
                $rates = json_decode($room['detailed_rates'], TRUE);
                foreach ($rates as $rate) {
                    if( $response['checkin_date'] == $rate['date'] ) {
                        $total_deposit += $rate['rate'];
                    }
                }
            }
            $response['amount_first_night'] = $total_deposit;
        }
        $this->booking = $response;

        // this is used for generating a short URL
        $this->booking['lookup_key'] = $this->generateRandomLookupKey(self::LOOKUPKEY_LENGTH);
        LilHotelierDBO::insertLookupKeyForBooking($response['reservation_id'], $this->booking['lookup_key'],
            $deposit_only && $total_deposit > 0 ? $total_deposit : null);
    }

    /**
     * Finds a cloudbeds booking by ID.
     * @param $booking_identifier string cloudbeds identifier as it appears on the page
     * @return JSON response
     * @throws Exception
     */
    function lookupReservation($booking_identifier) {
        $PROPERTY_ID = get_option('hbo_cloudbeds_property_id');
        $ENDPOINT = "https://hotels.cloudbeds.com/connect/reservations/get_reservation";
        $data = array(
            "id" => $booking_identifier,
            "is_identifier" => "1",
            "property_id" => $PROPERTY_ID,
            "group_id" => $PROPERTY_ID,
            "version" => $this->get_cloudbeds_version_for_endpoint($ENDPOINT),
        );

        $response = $this->cloudbeds_api_request($ENDPOINT, $data);
        if ($response['success'] != 'true') {
            error_log('Unexpected error looking up booking ' . $booking_identifier);
            throw new Exception("Failed to load reservation.");
        }
        return $response;
    }

    /**
     * Generates a lookup key for a cloudbeds booking so it can be accessed on either
     * bookings.macbackpackers.com or pay.macbackpackers.com.
     * Returns the Cloudbeds response with the lookup key saved under the key 'lookup_key'.
     * @param $booking_identifier string cloudbeds booking identifier
     * @return string booking URL
     * @throws Exception on lookup failure
     */
    function loadBookingWithLookupKey($booking_identifier) {
        $response = $this->lookupReservation($booking_identifier);
        $lookup_key = $this->generateRandomLookupKey(self::LOOKUPKEY_LENGTH);
        LilHotelierDBO::insertLookupKeyForBooking(
            $response['reservation_id'], $lookup_key, null);
        $response['lookup_key'] = $lookup_key;
        return $response;
    }

    /**
     * Records the invoice details and generates a payment link.
     * $name : recipient name
     * $email : recipient email
     * $amount : amount to be paid
     * $description : payment description
     * $notes : staff notes
     */
    function generateInvoiceLink($name, $email, $amount, $description, $notes) {
        if (empty($name)) {
            throw new ValidationException("Name cannot be blank.");
        }
        if (empty($email)) {
            throw new ValidationException("Email cannot be blank.");
        }
        if (empty($amount)) {
            throw new ValidationException("Amount cannot be blank.");
        }
        if (empty($description)) {
            throw new ValidationException("Description cannot be blank.");
        }
        if (empty($notes)) {
            throw new ValidationException("Staff notes cannot be blank.");
        }
        if (! preg_match('/^\s*(\d+)(\.\d{2})?\s*$/', $amount)) {
            throw new ValidationException("Invalid amount.");
        }

        $lookup_key = $this->generateRandomLookupKey(self::LOOKUPKEY_LENGTH);
        LilHotelierDBO::insertPaymentInvoice($name, $email, $amount, $description,
            $notes, $lookup_key);
        $this->invoice_lookup_key = $lookup_key;
    }

    /**
     * Returns a random lookup key with the given length.
     * @param int $keylen length of lookup key
     * @return string generated key
     */
    function generateRandomLookupKey($keylen) {
        $end_index = strlen(self::LOOKUPKEY_CHARSET) - 1;
        $result = "";
        for ($i = 0; $i < $keylen; $i ++) {
            $result .= substr(self::LOOKUPKEY_CHARSET, mt_rand(0, $end_index), 1);
        }
        return $result;
    }

    /**
     * Adds this object to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this object will be added
     */
    function addSelfToDocument($domtree, $parentElement) {
        $parentElement->appendChild($domtree->createElement('homeurl', home_url()));
        $parentElement->appendChild($domtree->createElement('payment_history_url', home_url() . get_option("hbo_payment_history_url")));
        if( null !== get_page_by_path( get_option("hbo_payment_history_inv_url"), OBJECT ) ) {
            $parentElement->appendChild($domtree->createElement('payment_history_inv_url', home_url() . '/' . get_option("hbo_payment_history_inv_url")));
        }
        // payment description is 80 characters max
        $parentElement->appendChild($domtree->createElement('payment_description_max_length', 80 - (strlen(get_option("hbo_sagepay_transaction_description")) + self::LOOKUPKEY_LENGTH + 1)));
        if($this->booking) {
            $bookingRoot = $parentElement->appendChild($domtree->createElement('booking'));
            $bookingRoot->appendChild($domtree->createElement('identifier', $this->booking['identifier']));
            $bookingRoot->appendChild($domtree->createElement('third_party_identifier', $this->booking['third_party_identifier']));
            $bookingRoot->appendChild($domtree->createElement('name', $this->booking['name']));
            $bookingRoot->appendChild($domtree->createElement('email', $this->booking['email']));
            $bookingRoot->appendChild($domtree->createElement('booking_date_server_time', $this->booking['booking_date_server_time']));
            $bookingRoot->appendChild($domtree->createElement('checkin_date', $this->booking['checkin_date']));
            $bookingRoot->appendChild($domtree->createElement('checkout_date', $this->booking['checkout_date']));
            $bookingRoot->appendChild($domtree->createElement('status', $this->booking['status']));
            $bookingRoot->appendChild($domtree->createElement('num_guests', intval($this->booking['adults_number']) + intval($this->booking['kids_number'])));
            $bookingRoot->appendChild($domtree->createElement('grand_total', number_format($this->booking['grand_total'], 2)));
            $bookingRoot->appendChild($domtree->createElement('balance_due', number_format($this->booking['balance_due'], 2)));
            $bookingRoot->appendChild($domtree->createElement('amount_first_night', number_format($this->booking['amount_first_night'], 2)));
            $bookingRoot->appendChild($domtree->createElement('payment_url', get_option("hbo_booking_payments_url") . $this->booking['lookup_key']));
            $parentElement->appendChild($bookingRoot);
        }
        if($this->invoice_lookup_key) {
            $invoiceRoot = $parentElement->appendChild($domtree->createElement('invoice'));
            $invoiceRoot->appendChild($domtree->createElement('payment_url', get_option("hbo_invoice_payments_url") . $this->invoice_lookup_key));
        }
    }
    
    /** 
      Generates the following xml:
        <view>
           <booking>
               ...
           </booking>
           <invoice_lookup_key>
               ...
           </invoice_lookup_key>
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
        return HBO_PLUGIN_DIR. '/include/generate_payment_link.xsl';
    }

}

?>