<?php

/**
 * REST callbacks for bed locks set from the Cloudbeds calendar (CloudbedsBedLock.js userscript).
 * Locks live in wp_lh_bed_lock; the processor (BedLockMonitor) writes wp_lh_bed_lock_violation
 * when the calendar WebSocket moves a locked reservation off its bed.
 */
class BedLocks {

    /**
     * Active locks (not unlocked, stay not yet over) with any open violation.
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    function list_locks( $request ) {
        global $wpdb;
        $rows = $wpdb->get_results(
            "SELECT l.id, l.reservation_id, l.room_id, l.room, l.bed_name, l.calendar_event_id, l.guest_name,
                    l.checkin_date, l.checkout_date, l.locked_by, l.locked_date,
                    v.id AS violation_id, v.to_room_id, v.to_room, v.to_bed_name, v.detected_date
               FROM wp_lh_bed_lock l
               LEFT OUTER JOIN wp_lh_bed_lock_violation v
                 ON v.bed_lock_id = l.id AND v.resolved_date IS NULL
              WHERE l.unlocked_date IS NULL
                AND ( l.checkout_date IS NULL OR l.checkout_date >= CURDATE() - INTERVAL 1 DAY )
              ORDER BY l.id" );
        if ( $wpdb->last_error ) {
            return $this->error( $wpdb->last_error, 500 );
        }
        return $this->json( $rows );
    }

    /**
     * Locks a reservation to a bed. Idempotent: returns the existing active lock for the same
     * reservation and room_id.
     * @param WP_REST_Request $request JSON body: reservation_id, room_id, calendar_event_id,
     *          guest_name, checkin_date, checkout_date, locked_by
     * @return WP_REST_Response
     */
    function lock( $request ) {
        global $wpdb;
        $p = $request->get_json_params();
        $reservation_id = isset( $p['reservation_id'] ) ? $p['reservation_id'] : null;
        $room_id = isset( $p['room_id'] ) ? trim( $p['room_id'] ) : '';
        if ( false === ctype_digit( (string) $reservation_id ) ) {
            return $this->error( 'reservation_id is required', 400 );
        }
        if ( false === (bool) preg_match( '/^\d+-\d+$/', $room_id ) ) {
            return $this->error( 'room_id must look like roomTypeId-bedId', 400 );
        }

        $existing = $this->fetch_active_lock( $reservation_id, $room_id );
        if ( $existing ) {
            return $this->json( $existing );
        }

        $room = $wpdb->get_row( $wpdb->prepare(
            "SELECT room, bed_name FROM wp_lh_rooms WHERE id = %s", $room_id ) );

        if ( false === $wpdb->insert( 'wp_lh_bed_lock',
                array( 'reservation_id'    => $reservation_id,
                       'room_id'           => $room_id,
                       'room'              => $room ? $room->room : null,
                       'bed_name'          => $room ? $room->bed_name : null,
                       'calendar_event_id' => $this->param( $p, 'calendar_event_id', 64 ),
                       'guest_name'        => $this->param( $p, 'guest_name', 255 ),
                       'checkin_date'      => $this->date_param( $p, 'checkin_date' ),
                       'checkout_date'     => $this->date_param( $p, 'checkout_date' ),
                       'locked_by'         => $this->param( $p, 'locked_by', 255 ) ) ) ) {
            error_log( $wpdb->last_error . " executing sql: " . $wpdb->last_query );
            return $this->error( $wpdb->last_error, 500 );
        }
        return $this->json( $this->fetch_active_lock( $reservation_id, $room_id ) );
    }

    /**
     * Unlocks a bed lock and resolves any open violation for it.
     * @param WP_REST_Request $request path param id; optional JSON body: unlocked_by
     * @return WP_REST_Response
     */
    function unlock( $request ) {
        global $wpdb;
        $id = $request->get_param( 'id' );
        $p = $request->get_json_params();

        $updated = $wpdb->query( $wpdb->prepare(
            "UPDATE wp_lh_bed_lock SET unlocked_date = NOW(), unlocked_by = %s
              WHERE id = %d AND unlocked_date IS NULL",
            $this->param( $p, 'unlocked_by', 255 ), $id ) );
        if ( false === $updated ) {
            error_log( $wpdb->last_error . " executing sql: " . $wpdb->last_query );
            return $this->error( $wpdb->last_error, 500 );
        }
        if ( false === $wpdb->query( $wpdb->prepare(
                "UPDATE wp_lh_bed_lock_violation SET resolved_date = NOW(), resolution = 'unlocked'
                  WHERE bed_lock_id = %d AND resolved_date IS NULL", $id ) ) ) {
            error_log( $wpdb->last_error . " executing sql: " . $wpdb->last_query );
            return $this->error( $wpdb->last_error, 500 );
        }
        return $this->json( array( 'id' => (int) $id, 'unlocked' => $updated > 0 ) );
    }

    private function fetch_active_lock( $reservation_id, $room_id ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT id, reservation_id, room_id, room, bed_name, calendar_event_id, guest_name,
                    checkin_date, checkout_date, locked_by, locked_date
               FROM wp_lh_bed_lock
              WHERE reservation_id = %s AND room_id = %s AND unlocked_date IS NULL
              ORDER BY id DESC LIMIT 1",
            $reservation_id, $room_id ) );
    }

    private function param( $p, $key, $max_length ) {
        if ( empty( $p[ $key ] ) ) {
            return null;
        }
        return substr( trim( (string) $p[ $key ] ), 0, $max_length );
    }

    private function date_param( $p, $key ) {
        $value = $this->param( $p, $key, 10 );
        return $value && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ? $value : null;
    }

    private function json( $data ) {
        $response = new WP_REST_Response( $data, 200 );
        $response->header( 'Content-type', 'application/json' );
        return $response;
    }

    private function error( $message, $status ) {
        return new WP_REST_Response( array( 'error' => $message ), $status );
    }
}
