<?php 
    // user must be logged in to access this page
    if ( false === is_user_logged_in() ) { 
        header( 'Location: '.home_url()."/wp-login.php?redirect_to=".urlencode($_SERVER['REQUEST_URI']) ) ;

    } else {

        $mc = new LHManualCharge();
        if (isset($_POST['charge_amount'])) {
            $mc->submitManualChargeJob( $_POST['booking_ref'], $_POST['charge_amount'], $_POST['charge_note'] );
            wp_redirect( get_permalink() ); // redirect after POST to avoid resubmissions
            exit;
        } 
        else {
            get_header();
            $mc->doView(); // update the view
            echo $mc->toHtml();
            get_footer(); 
        }
    }
?>
