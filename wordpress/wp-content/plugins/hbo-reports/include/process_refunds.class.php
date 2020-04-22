<?php

/**
 * Display controller for doing refund-y stuff.
 */
class ProcessRefundsController extends XslTransform {

    // currently loaded booking
    var $booking;
    var $PROPERTY_ID, $HEADERS, $VERSION;
    var $showDialogTxnId; // if set, generates the refund dialog on toXml()

    /**
     * Default constructor.
     */
    function ProcessRefundsController() {
        $this->PROPERTY_ID = get_option('hbo_cloudbeds_property_id');
        $this->HEADERS = array(
            "Accept: application/json, text/javascript, */*; q=0.01",
            "Content-Type: application/x-www-form-urlencoded; charset=UTF-8",
            "Referer: https://hotels.cloudbeds.com/connect/" . $this->PROPERTY_ID,
            "Accept-Language: en-GB,en-US;q=0.9,en;q=0.8",
            "Accept-Encoding: gzip, deflate, br",
            "X-Requested-With: XMLHttpRequest",
            "X-Used-Method: common.ajax",
            "Cache-Control: max-age=0",
            "Origin: https://hotels.cloudbeds.com",
            "User-Agent: " . get_option('hbo_cloudbeds_useragent'),
            "Cookie: " . get_option('hbo_cloudbeds_cookies'),
        );
        $this->VERSION = get_option('hbo_cloudbeds_version');
    }

    /**
     * Creates a record in the appropriate refund table for processing.
     * @param float $amount amount to be refunded
     * @param string $description refund note (optional)
     */
    function submitRefund($amount, $description) {
        $amount = trim($amount);
        if(! is_numeric($amount) || floatval($amount) <= 0) {
            throw new ValidationException("Amount needs to be a numerical value greater than 0");
        }
        $txn = $this->getSelectedTransaction();
        if (floatval($amount) > $txn['paid']) {
            throw new ValidationException("You cannot refund more than what was charged");
        }

        LilHotelierDBO::insertRefundRecord(
            $this->booking['reservation_id'], 
            $this->booking['first_name'],
            $this->booking['last_name'],
            $this->booking['email'], 
            floatval($amount), 
            $description, 
            $txn['id'], 
            $txn['vendor_tx_code']);
    }

    /**
     * Sets the "show dialog" flag so calling toHtml() will generate the HTML for the given transaction. 
     * @param integer $id transaction id to show refund dialog for
     */
    function showRefundDialog($id) {
        $this->showDialogTxnId = $id;
    }

    /**
     * Reloads the view details.
     */
    function doView() {
        $this->showDialogTxnId = null; // reset
        $this->booking = null;
    }
   
    /**
     * Looks up the cloudbeds booking ("identifier") and generates:
     *   - the booking details (name, checkin-date, checkout-date, number of guests, total, amount due)
     * or
     *   - an error message if booking doesn't exist
     *
     * @param $booking_ref string cloudbeds identifier
     */
    function lookupBooking($booking_ref) {
        $this->showDialogTxnId = null; // reset
        $this->booking = $this->doCallAPI(
            "https://hotels.cloudbeds.com/connect/reservations/get_reservation",
            array(
                "id" => $booking_ref,
                "is_identifier" => "1",
                "property_id" => $this->PROPERTY_ID,
                "group_id" => $this->PROPERTY_ID,
                "version" => $this->VERSION,
            ));
        $this->booking['transactions'] = $this->getTransactionsForBooking($this->booking['reservation_id']);

        // we need to mark any transactions done against a VCC 
        if($this->booking['channel_name'] == 'Booking.com') {
            $cc_cache = array();
            foreach( $this->booking['transactions']['records'] as &$tx) {
                if($tx['paid'] && $tx['credit_card_id'] && $tx['credit_card_id'] != '0') {
                    if(!$cc_cache[$tx['credit_card_id']]) {
                        $cc_cache[$tx['credit_card_id']] = $this->getCreditCardInfo($tx['credit_card_id'], $this->booking['reservation_id']);
                    }
                    $tx['is_vcc'] = $cc_cache[$tx['credit_card_id']]['card_info']['token_data']['value']['cardholder_name'] == 'Bookingcom Agent';
                }
            }
        }

        foreach( $this->booking['transactions']['records'] as &$tx) {
            if ($tx['paid'] && strpos($tx['notes'], "VendorTxCode:") !== false && floatval($tx['debit']) > 0) {
                $tx['vendor_tx_code'] = substr($tx['notes'], 14, strpos($tx['notes'], ',') - 14);
            }
        }
    }

    /**
     * Looks up all transactions against cloudbeds booking ("identifier")
     *
     * @param $booking_ref string cloudbeds identifier
     * @return array transactions for the given booking
     */
    function getTransactionsForBooking($booking_ref) {
        error_log("Looking up transactions for " . $booking_ref);
        return $this->doCallAPI(
            "https://hotels.cloudbeds.com/connect/reports/transactions_by_reservation",
            array(
                "booking_id" => $booking_ref,
                "options" => "{\"filters\":{\"from\":\"\",\"to\":\"\",\"filter\":\"\",\"user\":\"all\",\"posted\":[\"1\"],\"description\":[]},\"group\":{\"main\":\"\",\"sub\":\"\"},\"sort\":{\"column\":\"datetime_transaction\",\"order\":\"desc\"},\"loaded_filter\":1}",
                "property_id" => $this->PROPERTY_ID,
                "group_id" => $this->PROPERTY_ID,
                "version" => $this->VERSION,
            ));
    }

    /**
     * Looks up credit card details against cloudbeds booking ("identifier")
     *
     * @param $credit_card_id string card identifier
     * @param $booking_ref string cloudbeds identifier
     * @return array response
     */
    function getCreditCardInfo($credit_card_id, $booking_ref) {
        error_log("Looking up credit card $credit_card_id  for $booking_ref");
        return $this->doCallAPI(
            "https://hotels.cloudbeds.com/cc_passwords/get_credit_card_info",
            array(
                "id" => $credit_card_id,
                "booking_id" => $booking_ref,
                "property_id" => $this->PROPERTY_ID,
                "group_id" => $this->PROPERTY_ID,
                "version" => $this->VERSION,
            ));
    }

    /**
     * Returns true iff the transaction matches the id of $showDialogTxnId
     * 
     * @param transaction object $txn
     * @return boolean
     */
    function _selectTransaction($txn) {
        return $this->showDialogTxnId == $txn['id'];
    }
    
    /**
     * Returns the selected transaction we're doing a refund on. Throws exception if we don't have
     * a selected transaction - which shouldn't happen!
     */
    function getSelectedTransaction() {
        $txn = array_filter($this->booking['transactions']['records'], array($this, '_selectTransaction'));
        if(count($txn)) {
            return array_pop($txn);
        }
        throw new ValidationException("Something weird happened.. No transaction selected.");
    }

    /**
     * Adds this object to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this object will be added
     */
    function addSelfToDocument($domtree, $parentElement) {
        $parentElement->appendChild($domtree->createElement('homeurl', home_url()));
        if($this->showDialogTxnId) {
            $dialogRoot = $parentElement->appendChild($domtree->createElement('refund_dialog'));
            $dialogRoot->appendChild($domtree->createElement('grand_total', number_format($this->booking['grand_total'], 2)));
            $dialogRoot->appendChild($domtree->createElement('amount_paid', number_format($this->booking['paid_value'], 2)));

            $txn = $this->getSelectedTransaction();
            $dialogRoot->appendChild($domtree->createElement('txn_id', $txn['id']));
            $dialogRoot->appendChild($domtree->createElement('paid', $txn['paid']));
            $dialogRoot->appendChild($domtree->createElement('gateway_name', $txn['gateway_name'] ? $txn['gateway_name'] : 'Sagepay'));
            $dialogRoot->appendChild($domtree->createElement('default_refund', number_format($txn['paid'] * 0.9, 2)));
        }
        else if($this->booking) {
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
            $bookingRoot->appendChild($domtree->createElement('amount_paid', number_format($this->booking['paid_value'], 2)));
            $bookingRoot->appendChild($domtree->createElement('payment_url', get_option("hbo_booking_payments_url") . $this->booking['lookup_key']));
            $transactionsRoot = $bookingRoot->appendChild($domtree->createElement('transactions'));

            foreach ($this->booking['transactions']['records'] as $txn) {
                if ($txn['paid']) {
                    $txnRoot = $transactionsRoot->appendChild($domtree->createElement('transaction'));
                    $txnRoot->appendChild($domtree->createElement('id', $txn['id']));
                    $txnRoot->appendChild($domtree->createElement('datetime_transaction', $txn['datetime_transaction_server_time']));
                    $txnRoot->appendChild($domtree->createElement('description', $txn['description']));
                    $txnRoot->appendChild($domtree->createElement('notes', $txn['notes']));
                    $txnRoot->appendChild($domtree->createElement('vendor_tx_code', $txn['vendor_tx_code']));
                    $txnRoot->appendChild($domtree->createElement('original_description', $txn['original_description']));
                    $txnRoot->appendChild($domtree->createElement('paid', $txn['paid']));
                    if ($txn['is_vcc']) {
                        $txnRoot->appendChild($domtree->createElement('is_vcc', $txn['is_vcc']));
                    }
                    else if (floatval($txn['debit']) > 0) {
                        if ($txn['gateway_name'] == 'Stripe' && $txn['refunded_value'] != $txn['paid']) {
                            $txnRoot->appendChild($domtree->createElement('is_refundable', 'true'));
                        }
                        else if ($txn['vendor_tx_code']) {
                            $txnRoot->appendChild($domtree->createElement('is_refundable', 'true'));
                        }
                    }
                    if (floatval($txn['debit']) <= 0) {
                        $txnRoot->appendChild($domtree->createElement('is_refund', 'true'));
                    }
                }
            }
        }
    }
    
    /** 
      Generates the following xml:
        <view>
           <booking>
               ...
           </booking>
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
        return HBO_PLUGIN_DIR. '/include/process_refunds.xsl';
    }

    /**
     * Make a Cloudbeds (POST) API call to the given URL.
     * 
     * @param String $url target
     * @param Array $data HTTP request parameters
     * @throws RuntimeException
     */
    function doCallAPI($url, $data) {
        try {
            $start_ms = time();
            $make_call = $this->callAPI('POST', $url, $this->HEADERS, $data);
            error_log("Cloudbeds request took " . (time() - $start_ms) . "ms.");
            $response = json_decode($make_call, true);
        }
        catch (Exception $ex) {
            error_log($ex->getMessage());
            throw new RuntimeException('Error attempting to retrieve booking. Please try again later.');
        }
        
        if( $response['success'] != 'true' ) {
            error_log('Unexpected error calling ' . $url);
            error_log('request: ' . json_encode($data));
            error_log('response: ' . $make_call);
            if( strlen($response['message']) && strpos($response['message'], 'you are not using the latest version') !== false) {
                throw new RuntimeException("Cloudbeds version sync error. Please try again later.");
            }
            throw new RuntimeException('Error loading booking.');
        }
        return $response;
    }

    function callAPI($method, $url, $headers, $data = NULL) {
        error_log('callAPI');
        error_log("method: $method");
        error_log("url: $url");
        error_log("headers: " . json_encode($headers));
        error_log("data: " . var_export($data, TRUE));
        $curl = curl_init($url);
        
        switch ($method){
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
                    break;
            case "PUT":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
                    break;
            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
        }
        
        // OPTIONS:
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_ENCODING, "");
        
        // EXECUTE:
        $result = curl_exec($curl);
        if (curl_error($curl)) {
            $error_msg = "Connection Failure: " . curl_error($curl);
        }
        
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        error_log('callAPI HTTP status ' . $http_status);
        curl_close($curl);
        
        if (isset($error_msg)) {
            throw new RuntimeException($error_msg);
        }
        return $result;
    }
}

?>