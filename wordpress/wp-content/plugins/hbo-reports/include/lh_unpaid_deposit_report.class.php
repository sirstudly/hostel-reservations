<?php

/**
 * Display controller for unpaid deposit report.
 */
class LHUnpaidDepositReport extends XslTransform {

    const JOB_TYPE = "com.macbackpackers.jobs.AllocationScraperJob";

    var $unpaidDepositReport;  // the view of the latest unpaid deposit report
    var $lastSubmittedAllocScraperJob; // date/time of last submitted allocation scraper job that hasn't run yet
    var $lastCompletedAllocScraperJob; // date/time of last completed allocation scraper job
    var $lastJob; // the last job of this type that has run
    var $cancelBookingHours; // hours after final payment reminder before cancel (blank to disable)
    var $cancelBookingMinDays; // min days prior to checkin to allow cancel
    var $chargeNonRefundableJobActive; // true if CreateChargeNonRefundableBookingJob is active
    var $cancelExemptReferences; // array of booking_reference strings exempt from cancel
    var $hwPasswordConfigured; // true if hbo_hw_password is set (cancel UI requires HW credentials)

    /**
     * Default constructor.
     */
    function __construct() {
        
    }

    /**
     * Updates the view using the current selection date.
     */
    function doView() {
        $this->unpaidDepositReport = LilHotelierDBO::getUnpaidDepositReport();
        $this->lastSubmittedAllocScraperJob = LilHotelierDBO::getOutstandingAllocationScraperJob();
        $this->lastCompletedAllocScraperJob = LilHotelierDBO::getLastCompletedAllocationScraperJob();
        $this->lastJob = LilHotelierDBO::getDetailsOfLastJob( self::JOB_TYPE );
        $this->cancelBookingHours = get_option( 'hbo_hwl_cancel_booking_hours' );
        $this->cancelBookingMinDays = get_option( 'hbo_hwl_cancel_booking_min_days' );
        $this->chargeNonRefundableJobActive = LilHotelierDBO::isChargeNonRefundableBookingJobActive();
        $this->cancelExemptReferences = LilHotelierDBO::getCancelBookingExemptReferences();
        $this->hwPasswordConfigured = ! empty( get_option( 'hbo_hw_password' ) );
    }

    /**
     * Inserts an allocation scraper job into the jobs table.
     */
    function submitAllocationScraperJob() {
        LilHotelierDBO::insertAllocationScraperJob();
        LilHotelierDBO::runProcessor();
    }

    /**
     * Saves cancel-booking monitoring settings.
     * $hours : hours after final payment reminder (blank to disable); positive integer if set
     * $minDays : minimum days prior to checkin; required positive integer >= 1
     */
    function saveCancelBookingSettings( $hours, $minDays ) {
        if ( false === empty( $hours ) ) {
            if ( ctype_digit( $hours ) === false ) {
                throw new ValidationException( "Cancel booking hours must be a positive integer" );
            }
            else if ( intval( $hours ) < 1 ) {
                throw new ValidationException( "Cancel booking hours must be a positive integer" );
            }
        }

        if ( empty( $minDays ) ) {
            throw new ValidationException( "Minimum days prior to checkin cannot be blank" );
        }
        if ( ctype_digit( $minDays ) === false ) {
            throw new ValidationException( "Minimum days prior to checkin must be a positive integer" );
        }
        else if ( intval( $minDays ) < 1 ) {
            throw new ValidationException( "Minimum days prior to checkin must be at least 1" );
        }

        update_option( 'hbo_hwl_cancel_booking_hours', empty( $hours ) ? '' : $hours );
        update_option( 'hbo_hwl_cancel_booking_min_days', $minDays );
    }

    /**
     * Marks a booking as exempt from automated cancelation.
     * $bookingReference : booking reference key
     */
    function setCancelExempt( $bookingReference ) {
        LilHotelierDBO::setCancelBookingExempt( $bookingReference );
    }

    /**
     * Removes a booking from the cancel-exempt list.
     * $bookingReference : booking reference key
     */
    function unsetCancelExempt( $bookingReference ) {
        LilHotelierDBO::unsetCancelBookingExempt( $bookingReference );
    }
    
    /**
     * Adds this object to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this object will be added
     */
    function addSelfToDocument($domtree, $parentElement) {

        if( $this->lastSubmittedAllocScraperJob ) {
            $recordRoot = $parentElement->appendChild($domtree->createElement('last_submitted_job', 
                DateTime::createFromFormat('Y-m-d H:i:s', $this->lastSubmittedAllocScraperJob)->format('D, d M Y H:i:s')));
        }

        if( $this->lastCompletedAllocScraperJob ) {
            $parentElement->appendChild($domtree->createElement('last_completed_job', 
                DateTime::createFromFormat('Y-m-d H:i:s', $this->lastCompletedAllocScraperJob)->format('D, d M Y H:i:s')));
        }

        $parentElement->appendChild($domtree->createElement('property_manager', get_option('hbo_property_manager')));
        $parentElement->appendChild($domtree->createElement('cancel_booking_hours', $this->cancelBookingHours === false || $this->cancelBookingHours === null ? '' : $this->cancelBookingHours ));
        $parentElement->appendChild($domtree->createElement('cancel_booking_min_days', $this->cancelBookingMinDays === false || $this->cancelBookingMinDays === null ? '' : $this->cancelBookingMinDays ));
        $parentElement->appendChild($domtree->createElement('charge_non_refundable_job_active', $this->chargeNonRefundableJobActive ? 'true' : 'false' ));
        $parentElement->appendChild($domtree->createElement('hw_password_configured', $this->hwPasswordConfigured ? 'true' : 'false' ));

        // did the last job fail to run?
        if( $this->lastJob ) {
            $parentElement->appendChild($domtree->createElement('last_job_id', $this->lastJob['jobId'] ));
            $parentElement->appendChild($domtree->createElement('last_job_status', $this->lastJob['status'] ));
            $parentElement->appendChild($domtree->createElement('check_credentials', $this->lastJob['lastJobFailedDueToCredentials'] ? 'true' : 'false' ));
            $parentElement->appendChild($domtree->createElement('last_job_error_log', 
                get_option('hbo_log_directory_url') . $this->lastJob['jobId'] ));
        }

        $exemptLookup = array();
        if ( $this->cancelExemptReferences ) {
            foreach ( $this->cancelExemptReferences as $ref ) {
                $exemptLookup[ $ref ] = true;
            }
        }

        if ( $this->unpaidDepositReport ) {
            foreach( $this->unpaidDepositReport as $record ) {
                $recordRoot = $parentElement->appendChild($domtree->createElement('record'));
                $recordRoot->appendChild($domtree->createElement('guest_name', htmlspecialchars(html_entity_decode($record->guest_name, ENT_COMPAT, "UTF-8" ))));
                $recordRoot->appendChild($domtree->createElement('checkin_date', DateTime::createFromFormat('Y-m-d H:i:s', $record->checkin_date)->format('D, d M Y')));
                $recordRoot->appendChild($domtree->createElement('checkin_date_yyyymmdd', DateTime::createFromFormat('Y-m-d H:i:s', $record->checkin_date)->format('Y-m-d')));
                $recordRoot->appendChild($domtree->createElement('checkin_datetime', DateTime::createFromFormat('Y-m-d H:i:s', $record->checkin_date)->getTimestamp()));
                $recordRoot->appendChild($domtree->createElement('checkout_date', DateTime::createFromFormat('Y-m-d H:i:s', $record->checkout_date)->format('D, d M Y')));
                $recordRoot->appendChild($domtree->createElement('checkout_datetime', DateTime::createFromFormat('Y-m-d H:i:s', $record->checkout_date)->getTimestamp()));
                $recordRoot->appendChild($domtree->createElement('data_href', $record->data_href));
                $recordRoot->appendChild($domtree->createElement('booking_reference', $record->booking_reference));
                $recordRoot->appendChild($domtree->createElement('booking_source', htmlspecialchars($record->booking_source)));
                if( $record->booked_date ) {
                    $recordRoot->appendChild($domtree->createElement('booked_date', DateTime::createFromFormat('Y-m-d H:i:s', $record->booked_date)->format('D, d M Y')));
                    $recordRoot->appendChild($domtree->createElement('booked_datetime', DateTime::createFromFormat('Y-m-d H:i:s', $record->booked_date)->getTimestamp()));
                }
                $recordRoot->appendChild($domtree->createElement('viewed_yn', $record->viewed_yn));
                if ( isset( $record->notes ) ) {
                    $recordRoot->appendChild($domtree->createElement('notes', str_replace(array("\r\n", "\n", "\r"), "<br/>", htmlspecialchars($record->notes))));
                }
                $recordRoot->appendChild($domtree->createElement('created_date', DateTime::createFromFormat('Y-m-d H:i:s', $record->created_date)->format('D, d M Y H:i:s')));
                $recordRoot->appendChild($domtree->createElement('cancel_exempt', isset( $exemptLookup[ $record->booking_reference ] ) ? 'Y' : 'N' ));
            }
        }
    }
    
    /** 
      Generates the following xml:
        <view>
            <last_submitted_job>2015-05-24 13:22:58</last_submitted_job>
            <last_completed_job>2015-05-23 12:15:21</last_completed_job>
            <cancel_booking_hours>24</cancel_booking_hours>
            <cancel_booking_min_days>2</cancel_booking_min_days>
            <charge_non_refundable_job_active>true</charge_non_refundable_job_active>
            <record>
                <guest_name>Joe Bloggs</guest_name>
                <checkin_date>Mon, 18 May 2015</checkin_date>
                <checkout_date>Wed, 20 May 2015</checkout_date>
                <data_href>/extranet/properties/533/reservations/1046289/edit</data_href>
                <notes>Arriving late</notes>
                <created_date>Sun, 17 May 2015 03:57:19</created_date>
                <cancel_exempt>N</cancel_exempt>
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
        return HBO_PLUGIN_DIR. '/include/lh_unpaid_deposit_report.xsl';
    }

}

?>
