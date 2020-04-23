<?php

/**
 * Display controller viewing prior refunds.
 */
class RefundHistoryController extends XslTransform {

    var $refunds = array(); // array of existing refunds

    /**
     * Default constructor.
     */
    function RefundHistoryController() {
        
    }

    /**
     * Reloads the view details.
     */
    function doView() {
        $this->refunds = LilHotelierDBO::getRefundHistory();
    }
   
    /**
     * Adds this object to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this object will be added
     */
    function addSelfToDocument($domtree, $parentElement) {
        $parentElement->appendChild($domtree->createElement('homeurl', home_url()));
        if( $this->refunds ) {
            $refundsRoot = $parentElement->appendChild($domtree->createElement('refunds'));
            $propId = get_option('hbo_cloudbeds_property_id');
            foreach( $this->refunds as $refund ) {
                $refundRoot = $parentElement->appendChild($domtree->createElement('refund'));
                foreach( array("reservation_id", "booking_reference", "email", "first_name", "last_name", "email", "amount", "description", "charge_id", "auth_vendor_tx_code", "refund_status", "refund_status_detail", "last_updated_date") as &$fieldname ) {
                    if( !empty($refund->$fieldname) ) {
                        $refundRoot->appendChild($domtree->createElement($fieldname, htmlspecialchars($refund->$fieldname)));
                    }
                }
                $refundRoot->appendChild($domtree->createElement("data-href", "https://hotels.cloudbeds.com/connect/" . $propId . "#/reservations/" . $refund->reservation_id));
                $refundRoot->appendChild($domtree->createElement("last_updated_datetime", DateTime::createFromFormat('Y-m-d H:i:s', $refund->last_updated_date)->getTimestamp()));
                $refundsRoot->appendChild($refundRoot);
            }
            $parentElement->appendChild($refundsRoot);
        }
    }
    
    /** 
      Generates the following xml:
        <view>
          <refunds>
           <refund>
               ...
           </refund>
           <refund>
               ...
           </refund>
           ...
         </refunds>
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
        return HBO_PLUGIN_DIR. '/include/refund_history.xsl';
    }

}

?>