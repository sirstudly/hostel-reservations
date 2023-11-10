<?php 
    // user must be logged in to access this page
    if ( false === is_user_logged_in() ) { 
        header( 'Location: '.home_url()."/wp-login.php?redirect_to=".urlencode($_SERVER['REQUEST_URI']) ) ;

    } else {
        $rep = new HelpPage();
        get_header();
        echo $rep->toHtml();
        get_footer();
    }
?>
