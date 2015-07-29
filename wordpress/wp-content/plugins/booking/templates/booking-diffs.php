<?php 
    get_header();

    if (false === isset($_SESSION['WP_HOSTELBACKOFFICE'])) {
        $_SESSION['WP_HOSTELBACKOFFICE'] = new WP_HostelBackoffice();
    }
    $_SESSION['WP_HOSTELBACKOFFICE']->content_of_booking_diffs_report_page(); 

    get_footer(); 
?>
