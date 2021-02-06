<?php

/**
 * Display controller viewing prior payments for invoices.
 */
class PaymentHistoryInvoiceController extends XslTransform {

    var $invoices = array(); // array of existing invoices
    var $selected_invoice_id; // if we're viewing a single invoice
    var $show_acknowledged = FALSE; // hide acknowledged records by default
    var $reload_table_only = FALSE; // if true, generate HTML only for the data table

    /**
     * Default constructor.
     */
    function __construct() {
        
    }

    /**
     * Reloads the view details.
     * @param int $invoice_id (optional) selected invoice
     * @param boolean $show_acknowledged (optional) show acknowledged records
     */
    function doView($invoice_id = null, $show_acknowledged = FALSE) {
        $this->invoices = LilHotelierDBO::getPaymentInvoiceHistory($invoice_id, $show_acknowledged);
        $this->selected_invoice_id = $invoice_id;
        $this->show_acknowledged = $show_acknowledged;
        $this->reload_table_only = FALSE;
    }

    /**
     * Reloads the invoice table only.
     * @param boolean $show_acknowledged (optional) show acknowledged records
     */
    function doViewReloadTable($show_acknowledged = FALSE) {
        $this->doView(null, $show_acknowledged);
        $this->reload_table_only = TRUE;
    }

    /**
     * Inserts a note on the given invoice.
     * @param int $invoice_id PK on invoice table
     * @param string $note_text note to add
     */
    function addInvoiceNote($invoice_id, $note_text) {
        if (empty(trim($note_text))) {
            throw new ValidationException("Stop messing around and write something...");
        }
        LilHotelierDBO::addInvoiceNote($invoice_id, $note_text);
    }
    
    /**
     * Sets the acknowledge date on the given invoice
     * @param integer $invoice_id PK of invoice
     */
    function acknowledgeInvoice($invoice_id) {
        if(! is_numeric($invoice_id)) {
            throw new ValidationException("invoice_id NAN");
        }
        LilHotelierDBO::acknowledgeInvoice($invoice_id);
    }
   
    /**
     * Unsets the acknowledge date on the given invoice
     * @param integer $invoice_id PK of invoice
     */
    function unacknowledgeInvoice($invoice_id) {
        if(! is_numeric($invoice_id)) {
            throw new ValidationException("invoice_id NAN");
        }
        LilHotelierDBO::unacknowledgeInvoice($invoice_id);
    }
    
    /**
     * Adds this object to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this object will be added
     */
    function addSelfToDocument($domtree, $parentElement) {
        $parentElement->appendChild($domtree->createElement('homeurl', home_url()));
        $parentElement->appendChild($domtree->createElement('show_acknowledged', $this->show_acknowledged ? 'true' : 'false'));
        $parentElement->appendChild($domtree->createElement('reload_table_only', $this->reload_table_only ? 'true' : 'false'));

        if( $this->invoices ) {
            $invoicesRoot = $parentElement->appendChild($domtree->createElement('invoices'));
            foreach( $this->invoices as $invoice ) {
                $invoiceRoot = $parentElement->appendChild($domtree->createElement('invoice'));
                if( $this->selected_invoice_id == $invoice->invoice_id ) {
                    $invoiceRoot->setAttribute('selected', 'true');
                }
                foreach( array("invoice_id", "recipient_name", "recipient_email", "payment_description", "payment_requested", "acknowledged_date") as &$fieldname ) {
                    if( !empty($invoice->$fieldname) ) {
                        $invoiceRoot->appendChild($domtree->createElement($fieldname, htmlspecialchars($invoice->$fieldname)));
                    }
                }
                $invoiceRoot->appendChild($domtree->createElement('invoice_url', get_option("hbo_invoice_payments_url") . $invoice->lookup_key));
                if(isset($invoice->transactions)) {
                    $txnsRoot = $parentElement->appendChild($domtree->createElement('transactions'));
                    $total_paid = 0;
                    foreach( $invoice->transactions as $txn ) {
                        $txnRoot = $parentElement->appendChild($domtree->createElement('transaction'));
                        foreach( array("txn_id", "first_name", "last_name", "email", "vendor_tx_code",
                                    "txn_auth_id", "auth_status", "auth_status_detail", "card_type", "last_4_digits") as &$fieldname ) {
                            if( !empty($txn->$fieldname)) {
                                $txnRoot->appendChild($domtree->createElement($fieldname, htmlspecialchars($txn->$fieldname)));
                            }
                        }
                        // processed_date is actually the date the processor sends out the email
                        // we want to display the date the transaction was made
                        $txnRoot->appendChild($domtree->createElement("processed_date", $txn->created_date));
                            
                        if( !empty($txn->payment_amount)) {
                            $txnRoot->appendChild($domtree->createElement("payment_amount", number_format($txn->payment_amount, 2)));
                        }
                        if($txn->auth_status == 'OK') {
                            $total_paid += $txn->payment_amount;
                        }
                        $txnsRoot->appendChild($txnRoot);
                    }
                    $invoiceRoot->appendChild($domtree->createElement('total_paid', number_format($total_paid, 2)));
                    $invoiceRoot->appendChild($txnsRoot);
                }
                if(isset($invoice->notes)) {
                    $notesRoot = $parentElement->appendChild($domtree->createElement('notes'));
                    foreach( $invoice->notes as $note ) {
                        $noteRoot = $parentElement->appendChild($domtree->createElement('note'));
                        foreach( array("note_text", "created_date") as &$fieldname ) {
                            $noteRoot->appendChild($domtree->createElement($fieldname, htmlspecialchars($note->$fieldname)));
                        }
                        $notesRoot->appendChild($noteRoot);
                    }
                    $invoiceRoot->appendChild($notesRoot);
                }
                $invoicesRoot->appendChild($invoiceRoot);
            }
            $parentElement->appendChild($invoicesRoot);
        }
    }
    
    /** 
      Generates the following xml:
        <view>
          <invoices>
            <invoice>
              ...
              <transaction>
                ...
              </transaction>
              ...
              <note>
                 ...
              </note>
            </invoice>
            <invoice>
              ...
            </invoice>
            ...
          </invoices>
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
        return HBO_PLUGIN_DIR. '/include/payment_history_inv.xsl';
    }

}

?>