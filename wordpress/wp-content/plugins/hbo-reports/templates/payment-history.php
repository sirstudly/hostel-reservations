<?php 

    // user must be logged in to access this page
    if ( false === is_user_logged_in() ) { 
        header( 'Location: '.home_url()."/wp-login.php?redirect_to=".urlencode($_SERVER['REQUEST_URI']) ) ;

    } else {
        // use the same page controller if already defined
        if (isset($_SESSION['PAYMENT_HISTORY_CONTROLLER'])) {
            $ctrl = $_SESSION['PAYMENT_HISTORY_CONTROLLER'];
        }
        else {
            $ctrl = new PaymentHistoryController();
            $_SESSION['PAYMENT_HISTORY_CONTROLLER'] = $ctrl;
        }

        get_header();
        $ctrl->doView(); // update the view
        echo $ctrl->toHtml();
        get_footer(); 
    }
?>
