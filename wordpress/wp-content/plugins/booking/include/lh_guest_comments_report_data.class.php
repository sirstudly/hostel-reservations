<?php

/**
 * Display controller for reports page.
 */
class LHGuestCommentsReportData extends XslTransform {

    var $guestCommentsReport;  // the view of the latest report
    var $lastSubmittedJob; // date/time of last submitted report job that hasn't run yet
    var $lastCompletedJob; // date/time of last completed job
    var $lastFailedJob; // date/time of last failed job
    var $includeAcknowledged = FALSE; // true to include acknowledged comments

    // this is the job we're interested in
    const JOB_TYPE = 'com.macbackpackers.jobs.GuestCommentsReportJob';

    /**
     * Default constructor.
     */
    function LHGuestCommentsReportData() {
        
    }

    /**
     * Updates the view using the current selection date.
     * $includeAcknowledged : true to include acknowledged comments
     */
    function doView( $includeAcknowledged = FALSE ) {
        $this->guestCommentsReport = LilHotelierDBO::getGuestCommentsReport();
        $this->includeAcknowledged = $includeAcknowledged;
        $this->filterGuestCommentsReport();
        $this->lastCompletedJob = LilHotelierDBO::getLastCompletedJob( self::JOB_TYPE );
        $this->lastSubmittedJob = LilHotelierDBO::getDateTimeOfLastOutstandingJob( self::JOB_TYPE );
        $this->lastFailedJob = LilHotelierDBO::getLastFailedJob( self::JOB_TYPE );
    }

    /**
     * Removes all the cruft (automated messages, etc..) from the guest comments report.
     */
    function filterGuestCommentsReport() {
        $newReport = array();

        // perform any substitutions
        foreach( $this->guestCommentsReport as $record ) {
            $comment = trim( preg_replace( '/smoking preference: Non-Smoking/s', '', $record->comments ));
            $comment = trim( preg_replace( '/Macb[s]? Paid([ -]*RMB)?/si', '', $comment ));
            $comment = trim( preg_replace( '/Paid Macb[s]? ([ -]*RMB)?/si', '', $comment ));
            $comment = trim( preg_replace( '/Hostel World Booking Ref.*\(GBP\)/s', '', $comment ));
            $comment = trim( preg_replace( '/\*{3} Genius booker \*{3}/s', '', $comment ));
            $comment = trim( preg_replace( '/Approximate time of arrival:.* hours( the next day.)?/s', '', $comment ));

            // only include if there is something to say...
            if( strlen( $comment ) > 0 ) {
                if( $this->includeAcknowledged || ( ! $this->includeAcknowledged && $record->acknowledged_date === NULL )) {
                    $newReport[] = $record;
                }
            }
        }
        $this->guestCommentsReport = $newReport;
    }

    /**
     * Acknowledges a particular LH reservation.
     * $reservationId : ID of LH reservation to acknowledge.
     */
    function acknowledgeComment( $reservationId ) {
        LilHotelierDBO::acknowledgeGuestComment( $reservationId );
    }

    /**
     * Unacknowledges a particular LH reservation.
     * $reservationId : ID of LH reservation to unacknowledge.
     */
    function unacknowledgeComment( $reservationId ) {
        LilHotelierDBO::unacknowledgeGuestComment( $reservationId );
    }

    /**
     * Adds this object to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this object will be added
     */
    function addSelfToDocument($domtree, $parentElement) {

        if( $this->lastSubmittedJob ) {
            $recordRoot = $parentElement->appendChild($domtree->createElement('last_submitted_job', 
                DateTime::createFromFormat('Y-m-d H:i:s', $this->lastSubmittedJob)->format('D, d M Y H:i:s')));
        }

        if( $this->lastCompletedJob ) {
            $parentElement->appendChild($domtree->createElement('last_completed_job', 
                DateTime::createFromFormat('Y-m-d H:i:s', $this->lastCompletedJob)->format('D, d M Y H:i:s')));
        }
        if( $this->lastFailedJob ) {
            // only include failed job if it occurs *after* the last completed job
            if( $this->lastCompletedJob == NULL || $this->lastFailedJob > $this->lastCompletedJob ) {
                $parentElement->appendChild($domtree->createElement('last_failed_job', 
                    DateTime::createFromFormat('Y-m-d H:i:s', $this->lastFailedJob)->format('D, d M Y H:i:s')));
            }
        }
        $parentElement->appendChild($domtree->createElement('show_acknowledged', $this->includeAcknowledged ? 'true' : 'false' ));

        if ( $this->guestCommentsReport ) {
            foreach( $this->guestCommentsReport as $record ) {
                $recordRoot = $parentElement->appendChild($domtree->createElement('record'));
                $recordRoot->appendChild($domtree->createElement('reservation_id', $record->reservation_id));
                // remove any remaining ampersands after HTML translation as they fuck up the DOM tree
                $recordRoot->appendChild($domtree->createElement('guest_name', str_replace( '&', '', html_entity_decode($record->guest_name, ENT_COMPAT, "UTF-8" ))));
                $recordRoot->appendChild($domtree->createElement('booking_reference', $record->booking_reference));
                $recordRoot->appendChild($domtree->createElement('booking_source', $record->booking_source));
                $recordRoot->appendChild($domtree->createElement('checkin_date', DateTime::createFromFormat('Y-m-d H:i:s', $record->checkin_date)->format('D, d M Y')));
                $recordRoot->appendChild($domtree->createElement('checkout_date', DateTime::createFromFormat('Y-m-d H:i:s', $record->checkout_date)->format('D, d M Y')));
                if( $record->booked_date ) {
                    $recordRoot->appendChild($domtree->createElement('booked_date', DateTime::createFromFormat('Y-m-d H:i:s', $record->booked_date)->format('D, d M Y')));
                }
                $recordRoot->appendChild($domtree->createElement('num_guests', $record->num_guests));
                $recordRoot->appendChild($domtree->createElement('data_href', $record->data_href));
                if( $record->notes ) {
                    $recordRoot->appendChild($domtree->createElement('notes', $record->notes));
                }
                $recordRoot->appendChild($domtree->createElement('comments', htmlspecialchars($record->comments, ENT_XML1, 'UTF-8')));
                if( $record->acknowledged_date ) {
                    $recordRoot->appendChild($domtree->createElement('acknowledged_date', DateTime::createFromFormat('Y-m-d H:i:s', $record->booked_date)->format('D, d M Y')));
                }
            }
        }
    }
    
    /** 
      Generates the following xml:
        <view>
            <last_submitted_job>2015-05-24 13:22:58</last_submitted_job>
            <show_acknowledged>true</show_acknowledged>
            <record>
                <reservation_id>123456</reservation_id>
                <guest_name>Joe Bloggs</guest_name>
                <booking_reference>192121</booking_reference>
                <booking_source>Extranet</booking_source>
                <checkin_date>Mon, 18 May 2015</checkin_date>
                <checkout_date>Wed, 20 May 2015</checkout_date>
                <booked_date>Fri, 13 Apr 2015</booked_date>
                <payment_outstanding>215.34</payment_outstanding>
                <data_href>/extranet/properties/533/reservations/1046289/edit</data_href>
                <num_guests>10</num_guests>
                <notes>Arriving late</notes>
                <comments>Could you please put me in a lower bunk?</comments>
                <acknowledged_date>Mon, 16 Apr 2015</acknowledged_date>
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
        return WPDEV_BK_PLUGIN_DIR. '/include/lh_guest_comments_report_data.xsl';
    }

}

?>