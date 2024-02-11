<?php

/**
 * Display controller for calendar snapshot page.
 */
class CalendarSnapshots extends XslTransform {

    /**
     * Default constructor.
     */
    function __construct() {
        // nothing to do
    }

    /**
     * Updates the view
     */
    function doView() {
        // nothing to do
    }

    /**
     * Returns all rooms/beds as a JSON response.
     * @return WP_REST_Response
     * @throws DatabaseException
     */
    function list_room_beds() {
        $data = array(); // 'parent' rooms (indexed by room)
        foreach ( LilHotelierDBO::listRoomBeds() as $row ) {
            // private rooms don't have bed names
            if ( $row->bed_name == null ) {
                $data[ $row->room ] = [
                    'id'    => $row->id,
                    'title' => $row->room
                ];
            }
            // parent room already exists; add (child) bed to it
            elseif ( isset( $data[ $row->room ] ) ) {
                $data[ $row->room ]['children'][] = [
                    'id'    => $row->id,
                    'title' => $row->bed_name
                ];
            }
            else {
                // create parent room and add (child) bed to it
                $data[ $row->room ] = [
                    'id'       => substr( $row->id, 0, strpos( $row->id, '-' ) ), // everything before the first dash
                    'title'    => $row->room,
                    'children' => [[
                        'id'    => $row->id,
                        'title' => $row->bed_name
                    ]]
                ];
            }
        }
        $response = new WP_REST_Response( array_values( $data ), 200 );
        $response->header( 'Content-type', 'application/json' );
        return $response;
    }

    /**
     * Returns all bookings for a specific job_id and start/end dates as a JSON response.
     * @param $request WP_REST_Request
     * @return WP_REST_Response
     * @throws DatabaseException
     */
    function fetch_all_bookings($request) {
        $data     = array_map( function ( $row ) {
            return [
                'title'      => $row->guest_name . ($row->num_guests > 1 ? " ($row->num_guests)" : ""),
                'start'      => DateTime::createFromFormat( 'Y-m-d H:i:s', $row->checkin_date )->format( 'Y-m-d' ),
                'end'        => DateTime::createFromFormat( 'Y-m-d H:i:s', $row->checkout_date )->format( 'Y-m-d' ),
                'resourceId' => $row->room_id,
                'description' => "$row->guest_name <br>" .
                                 "Booking Source: $row->booking_source <br>" .
                                 "Booking Ref: $row->booking_reference <br>" .
                                 "Total Outstanding: $row->payment_outstanding",
                'backgroundColor' => $row->lh_status == "checked_in" ? "#169e49" :
                        ($row->lh_status == "checked_out" ? "#888888" :
                        ($row->lh_status == "confirmed" ? "#00aeef" : "#ed1c24")),
                // TODO: show unallocated bookings (as a separate group at the end?)
            ];
        }, LilHotelierDBO::getAllBookings( $request['job_id'], $request['start'], $request['end'] ) );
        $response = new WP_REST_Response( array_values( $data ), 200 );
        $response->header( 'Content-type', 'application/json' );

        return $response;
    }

    /**
     * Adds this object to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this object will be added
     */
    function addSelfToDocument($domtree, $parentElement) {

        $parentElement->appendChild($domtree->createElement('homeurl', home_url()));

        // needs to be passed to secured rest endpoints to verify user has been authenticated
        $parentElement->appendChild( $domtree->createElement( 'wpnonce', wp_create_nonce( 'wp_rest' ) ) );

        $jobRoot = $parentElement->appendChild( $domtree->createElement( 'allocation_scraper_jobs' ) );
        foreach ( LilHotelierDBO::getAllCompletedAllocationScraperJobIds() as $record ) {
            $recordRoot = $jobRoot->appendChild( $domtree->createElement( 'record' ) );
            $recordRoot->appendChild( $domtree->createElement( 'job_id', $record->job_id ) );
            $recordRoot->appendChild( $domtree->createElement( 'completed_date',
                DateTime::createFromFormat('Y-m-d H:i:s', $record->end_date)->format('D, d M Y H:i:s') ) );
        }
    }

    /**
    Generates the following xml:
    <view>
        <wpnonce>ABCDEFGH1234567</wpnonce>
        <allocation_scraper_jobs>
            <record>
                <job_id>12345</job_id>
                <completed_date>Sun, 17 May 2015 03:57:19</completed_date>
            </record>
            ...
        </allocation_scraper_jobs>
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
        return HBO_PLUGIN_DIR. '/include/calendar_snapshots.xsl';
    }

}
