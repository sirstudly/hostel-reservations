<?php

/**
 * Display controller for doing refund-y stuff.
 */
class ProcessRefundsController extends XslTransform {

    // currently loaded booking
    var $booking;
    var $showDialogTxnId; // if set, generates the refund dialog on toXml()

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
            $this->booking['identifier'],
            $this->booking['first_name'],
            $this->booking['last_name'],
            $this->booking['email'], 
            floatval($amount), 
            $description, 
            $txn['id'], 
            $txn['vendor_tx_code']);
        LilHotelierDBO::runProcessor();
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
        $PROPERTY_ID = get_option('hbo_cloudbeds_property_id');
        $this->booking = $this->doCloudbedsPOST(
            "https://hotels.cloudbeds.com/connect/reservations/get_reservation",
            array(
                "id" => $booking_ref,
                "is_identifier" => "1",
                "property_id" => $PROPERTY_ID,
                "group_id" => $PROPERTY_ID,
                "version" => get_option('hbo_cloudbeds_version'),
            ));
        $this->booking['transactions'] = $this->getTransactionsForBooking($this->booking['reservation_id']);

        // identify any sagepay records
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
        $PROPERTY_ID = get_option('hbo_cloudbeds_property_id');
        return $this->doCloudbedsPOST(
            "https://hotels.cloudbeds.com/connect/reports/transactions_by_reservation",
            array(
                "booking_id" => $booking_ref,
                "options" => "{\"filters\":{\"from\":\"\",\"to\":\"\",\"filter\":\"\",\"user\":\"all\",\"posted\":[\"1\"],\"description\":[]},\"group\":{\"main\":\"\",\"sub\":\"\"},\"sort\":{\"column\":\"datetime_transaction\",\"order\":\"desc\"},\"loaded_filter\":1}",
                "property_id" => $PROPERTY_ID,
                "group_id" => $PROPERTY_ID,
                "version" => get_option('hbo_cloudbeds_version'),
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
            $dialogRoot->appendChild($domtree->createElement('gateway_name', isset($txn['gateway_name']) ? $txn['gateway_name'] : 'Sagepay'));
            if (isset($txn['vendor_tx_code'])) {
                $dialogRoot->appendChild($domtree->createElement('vendor_tx_code', $txn['vendor_tx_code']));
            }
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
            $bookingRoot->appendChild($domtree->createElement('amount_paid', number_format($this->booking['paid_value'], 2)));
            $transactionsRoot = $bookingRoot->appendChild($domtree->createElement('transactions'));

            foreach ($this->booking['transactions']['records'] as $txn) {
                if ($txn['paid']) {
                    $txnRoot = $transactionsRoot->appendChild($domtree->createElement('transaction'));
                    $txnRoot->appendChild($domtree->createElement('id', $txn['id']));
                    $txnRoot->appendChild($domtree->createElement('datetime_transaction', $txn['datetime_transaction_server_time']));
                    $txnRoot->appendChild($domtree->createElement('description', $txn['description']));
                    $txnRoot->appendChild($domtree->createElement('notes', $txn['notes']));
                    $txnRoot->appendChild($domtree->createElement('original_description', $txn['original_description']));
                    $txnRoot->appendChild($domtree->createElement('paid', $txn['paid']));

                    // we can't figure out whether a transaction was against a VCC if it's been deleted
                    // so disable refunds against all "channel collect" BDC bookings
                    if($this->booking['channel_name'] == 'Booking.com' && $this->booking['channel_payment_type'] == 'Channel') {
                        $txnRoot->appendChild($domtree->createElement('is_vcc', 'true'));
                    }
                    if (floatval($txn['debit']) > 0) {
                        if (isset($txn['gateway_name']) && $txn['gateway_name'] == 'Stripe' && $txn['refunded_value'] != $txn['paid']) {
                            $txnRoot->appendChild($domtree->createElement('is_refundable', 'true'));
                        }
                        else if (isset($txn['vendor_tx_code'])) {
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

}

?>