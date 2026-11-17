<?php 

    // user must be logged in to access this page
    if ( false === is_user_logged_in() ) { 
        header( 'Location: '.home_url()."/wp-login.php?redirect_to=".urlencode($_SERVER['REQUEST_URI']) ) ;

    } else {
        if (isset($_SESSION['AIRBNB_PAYMENTS_CONTROLLER'])) {
            $ctrl = $_SESSION['AIRBNB_PAYMENTS_CONTROLLER'];
        }
        else {
            $ctrl = new AirbnbPaymentsController();
            $_SESSION['AIRBNB_PAYMENTS_CONTROLLER'] = $ctrl;
        }

        get_header();
        $ctrl->doView();
        echo $ctrl->toHtml();
        get_footer(); 
    }
?>
