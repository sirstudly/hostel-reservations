<?php 

    // user must be logged in to access this page
    if ( false === is_user_logged_in() ) { 
        header( 'Location: '.home_url()."/wp-login.php?redirect_to=".urlencode($_SERVER['REQUEST_URI']) ) ;

    } else {
        // use the same page controller if already defined
        if (isset($_SESSION['GUEST_COMMENTS_CONTROLLER'])) {
            $rep = $_SESSION['GUEST_COMMENTS_CONTROLLER'];
        }
        else {
            $rep = new LHGuestCommentsReport();
            $_SESSION['GUEST_COMMENTS_CONTROLLER'] = $rep;
        }

        if (isset($_POST['reload_data'])) {
            $rep->submitReportJob();
            wp_redirect( get_permalink() ); // redirect after POST to avoid resubmissions
            exit;
        }
        else {
            get_header();
            $rep->doView(); // update the view
            echo $rep->toHtml();
            get_footer(); 
        }
    }
?>
