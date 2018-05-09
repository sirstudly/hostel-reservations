<?php
    // user must be logged in to access this page
    if ( false === is_user_logged_in() ) { 
        header( 'Location: '.home_url()."/wp-login.php?redirect_to=".urlencode($_SERVER['REQUEST_URI']) ) ;

    } else {
        header('Content-Type: text/plain');
        if( array_key_exists('job_id', $_GET) && preg_match('/^[0-9]+$/', $_GET['job_id']) ) {
            $logfile = "logs/job-" . $_GET['job_id'] . ".log";
            $gz_logfile = "logs/job-" . $_GET['job_id'] . ".gz";
            if( file_exists( $logfile )) {
                echo file_get_contents( $logfile );
            }
            elseif( file_exists( $gz_logfile )) {
                echo gzinflate( substr( file_get_contents( $gz_logfile ), 10, -8 )); 
            }
            else {
                echo "File not found.";
            }
        }
    }
?>
