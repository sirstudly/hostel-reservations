<?php
    // user must be logged in to access this page
    if ( false === is_user_logged_in() ) { 
        header( 'Location: '.home_url()."/wp-login.php?redirect_to=".urlencode($_SERVER['REQUEST_URI']) ) ;

    } else {
        header('Content-Type: text/plain');
        if( array_key_exists('job_id', $_GET) && preg_match('/^[0-9]+$/', $_GET['job_id']) ) {
            $logfile = "logs/job-" . $_GET['job_id'] . ".log";
            if(file_exists($logfile)) {
                echo file_get_contents( $logfile );
            }
            else {
                echo "File not found.";
            }
        }
    }
?>
