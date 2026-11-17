<?php

/**
 * Airbnb Payments import / reconcile page.
 */
class AirbnbPaymentsController extends XslTransform {

    var $rows = array();
    var $filterStatus = '';
    var $message = '';
    var $error = '';

    function __construct() {
    }

    function doView() {
        if ( isset( $_POST['save_airbnb_post_field'] ) && isset( $_POST['hbo_airbnb_post_payment_field'] ) ) {
            $field = $_POST['hbo_airbnb_post_payment_field'] === 'gross_earnings' ? 'gross_earnings' : 'amount';
            update_option( 'hbo_airbnb_post_payment_field', $field );
            $this->message = 'Saved post payment field: ' . $field;
        }
        $this->filterStatus = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '';
        $this->rows = LilHotelierDBO::getAirbnbPayments( $this->filterStatus );
    }

    function addSelfToDocument( $domtree, $parentElement ) {
        $parentElement->appendChild( $domtree->createElement( 'homeurl', home_url() ) );
        $parentElement->appendChild( $domtree->createElement( 'post_payment_field',
            get_option( 'hbo_airbnb_post_payment_field', 'amount' ) ) );
        $parentElement->appendChild( $domtree->createElement( 'filter_status', $this->filterStatus ) );
        if ( $this->message ) {
            $parentElement->appendChild( $domtree->createElement( 'message', $this->message ) );
        }
        if ( $this->error ) {
            $parentElement->appendChild( $domtree->createElement( 'error', $this->error ) );
        }
        $propertyId = get_option( 'hbo_cloudbeds_property_id' );
        $rowsRoot = $parentElement->appendChild( $domtree->createElement( 'rows' ) );
        foreach ( $this->rows as $row ) {
            $r = $rowsRoot->appendChild( $domtree->createElement( 'row' ) );
            foreach ( array(
                'reservation_id', 'confirmation_code', 'guest_name', 'checkin_date', 'checkout_date',
                'booked_date', 'amount', 'service_fee', 'gross_earnings', 'booking_status',
                'processing_status', 'status_reason', 'matched_yn', 'data_href'
            ) as $field ) {
                if ( isset( $row->$field ) && $row->$field !== null && $row->$field !== '' ) {
                    $r->appendChild( $domtree->createElement( $field, htmlspecialchars( $row->$field ) ) );
                }
            }
            if ( ! empty( $row->reservation_id ) && $propertyId ) {
                $href = 'https://hotels.cloudbeds.com/connect/' . $propertyId
                    . '#/reservations/' . $row->reservation_id;
                $r->appendChild( $domtree->createElement( 'cloudbeds_href', $href ) );
            }
            if ( ! empty( $row->checkin_date ) ) {
                $ts = strtotime( $row->checkin_date );
                if ( $ts ) {
                    $r->appendChild( $domtree->createElement( 'checkin_datetime', $ts ) );
                }
            }
            if ( ! empty( $row->checkout_date ) ) {
                $ts = strtotime( $row->checkout_date );
                if ( $ts ) {
                    $r->appendChild( $domtree->createElement( 'checkout_datetime', $ts ) );
                }
            }
            if ( ! empty( $row->booked_date ) ) {
                $ts = strtotime( $row->booked_date );
                if ( $ts ) {
                    $r->appendChild( $domtree->createElement( 'booked_datetime', $ts ) );
                }
            }
        }
    }

    function toXml() {
        $domtree = new DOMDocument( '1.0', 'UTF-8' );
        $xmlRoot = $domtree->appendChild( $domtree->createElement( 'view' ) );
        $this->addSelfToDocument( $domtree, $xmlRoot );
        return $domtree->saveXML();
    }

    function getXslFilename() {
        return HBO_PLUGIN_DIR . '/include/airbnb_payments.xsl';
    }
}

?>
