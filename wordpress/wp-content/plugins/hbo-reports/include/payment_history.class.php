<?php

/**
 * Display controller viewing prior payments.
 */
class PaymentHistoryController extends XslTransform {

    var $payments = array(); // array of existing payments

    /**
     * Default constructor.
     */
    function PaymentHistoryController() {
        
    }

    /**
     * Reloads the view details.
     */
    function doView() {
        $this->payments = LilHotelierDBO::getSagepayPaymentBookingHistory();
    }
   
    /**
     * Adds this object to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this object will be added
     */
    function addSelfToDocument($domtree, $parentElement) {
        $parentElement->appendChild($domtree->createElement('homeurl', home_url()));
        if( $this->payments ) {
            $paymentsRoot = $parentElement->appendChild($domtree->createElement('payments'));
            foreach( $this->payments as $payment ) {
                $paymentRoot = $parentElement->appendChild($domtree->createElement('payment'));
                foreach( array("reservation_id", "booking_reference", "email", "vendor_tx_code", "payment_amount", "auth_status", "auth_status_detail", "card_type", "last_4_digits", "processed_date") as &$fieldname ) {
                    if( !empty($payment->$fieldname) ) {
                        $paymentRoot->appendChild($domtree->createElement($fieldname, $payment->$fieldname));
                    }
                }
                $paymentRoot->appendChild($domtree->createElement("data-href", "https://hotels.cloudbeds.com/connect/" . get_option('hbo_cloudbeds_property_id') . "#/reservations/" . $payment->reservation_id));
                $paymentsRoot->appendChild($paymentRoot);
            }
            $parentElement->appendChild($paymentsRoot);
        }
    }
    
    /** 
      Generates the following xml:
        <view>
           <payment>
               ...
           </payment>
           <payment>
               ...
           </payment>
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
        return HBO_PLUGIN_DIR. '/include/payment_history.xsl';
    }

}

?>