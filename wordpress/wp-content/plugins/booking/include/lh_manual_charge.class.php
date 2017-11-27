<?php

/**
 * Display controller for reports settings page.
 */
class LHManualCharge extends XslTransform {

    var $lastTransactions;  // array of latest previous manual transactions

    const JOB_TYPE = "com.macbackpackers.jobs.ManualChargeJob";

    /**
     * Default constructor.
     */
    function LHManualCharge() {
        
    }

    /**
     * Updates the view of the latest manual transactions.
     */
    function doView() {
        $this->lastTransactions = LilHotelierDBO::fetchLastManualTransactions();
   }

   /**
    * Creates a new manual charge job.
    * $bookingRef : booking reference e.g. HWL-551-123456789
    * $amount : amount of booking. e.g. 12.44
    * $note : note to append to LH notes
    * $overrideCardDetails : true to use LH card details
    */
   function submitManualChargeJob( $bookingRef, $amount, $note, $overrideCardDetails ) {

       if( empty( $bookingRef )) {
           throw new ValidationException( "Booking Reference cannot be blank." );
       }
       if( empty( $amount )) {
           throw new ValidationException( "Amount cannot be blank." );
       }
       if ( ! preg_match("/^\d*\.{0,1}\d*$/", $amount )) {
           throw new ValidationException( "Incorrect amount format. e.g. 12.32" );
       }
       if( empty( $note )) {
           throw new ValidationException( "Note cannot be blank." );
       }

       LilHotelierDBO::insertJobOfType( self::JOB_TYPE,
           array( "booking_ref" => trim( strtoupper( $bookingRef ) ),
                  "amount" => $amount,
                  "message" => $note,
                  "use_lh_card_details" => $overrideCardDetails ? "true" : "false" ) );
       LilHotelierDBO::runProcessor();
   }

    /**
     * Adds this object to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this object will be added
     */
    function addSelfToDocument($domtree, $parentElement) {

        if ( $this->lastTransactions ) {
            $logDirectory = get_option( 'hbo_log_directory' );
            $logDirectoryUrl = get_option( 'hbo_log_directory_url' );
            $txnRoot = $parentElement->appendChild($domtree->createElement('transactions'));
            foreach( $this->lastTransactions as $record ) {
                $recordRoot = $txnRoot->appendChild($domtree->createElement('transaction'));
                $recordRoot->appendChild($domtree->createElement('booking-ref', $record->booking_reference));
                if( $record->checkin_date ) {
                    $recordRoot->appendChild($domtree->createElement('checkin-date', DateTime::createFromFormat('Y-m-d H:i:s', $record->checkin_date)->format('Ymd')));
                }
                $recordRoot->appendChild($domtree->createElement('transaction-date', $record->post_date));
                if( $record->data_href ) {
                    $recordRoot->appendChild($domtree->createElement('data-href', $record->data_href));
                }
                $recordRoot->appendChild($domtree->createElement('card-number', $record->masked_card_number));
                $recordRoot->appendChild($domtree->createElement('payment-amount', $record->payment_amount));
                $recordRoot->appendChild($domtree->createElement('details', $record->help_text));
                $recordRoot->appendChild($domtree->createElement('success', $record->successful == 1 ? 'yes' : 'no' ));
                $recordRoot->appendChild($domtree->createElement('job-status', $record->status ));
                $recordRoot->appendChild($domtree->createElement('last-updated-date', $record->last_updated_date));
                $recordRoot->appendChild($domtree->createElement('job_id', $record->job_id));

                // only include logfile if it exists
                $jobLogFilename = "job-" . $record->job_id . ".log";
                if( file_exists( $logDirectory . "/" . $jobLogFilename )) {
                    $recordRoot->appendChild($domtree->createElement('log_file', $logDirectoryUrl . $record->job_id ));
                }
            }
        }
    }
    
    /** 
      Generates the following xml:
        <view>
            <transactions>
                <transaction>
                    <booking-ref>HWL-551-123456789</booking-ref>
                    <checkin-date>20170620</checkin-date>
                    <tranansaction-date>2017-06-28 21:30:09</transaction-date>
                    <card-number>446291........24</card-number>
                    <payment-amount>16.00</payment-amount>
                    <details>Transaction Approved</details>
                    <success>true</success>
                    <job-status>completed</job-status>
                </transaction>
                <transaction>
                    <booking-ref>HWL-551-2233445566</booking-ref>
                    <payment-amount>16.00</payment-amount>
                    <job-status>submitted</job-status>
                </transaction>
                ...
            </transactions>
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
        return WPDEV_BK_PLUGIN_DIR. '/include/lh_manual_charge.xsl';
    }

}

?>