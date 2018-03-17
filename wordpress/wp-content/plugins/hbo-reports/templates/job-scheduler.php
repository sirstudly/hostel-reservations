<?php 
    // user must be logged in to access this page
    if ( false === is_user_logged_in() ) { 
        header( 'Location: '.home_url()."/wp-login.php?redirect_to=".urlencode($_SERVER['REQUEST_URI']) ) ;

    } 
    else {
        $js = new ScheduledJobView();
        get_header();
        $js->doView(); // update the view
        echo $js->toHtml();
        get_footer(); 
    }
?>
