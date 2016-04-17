<?php 

    $selectionDate = new DateTime('now', new DateTimeZone('UTC'));

    // if date is defined, update the date
    if (isset($_POST['selectiondate']) && trim($_POST['selectiondate']) != '') {
        $selectionDate = DateTime::createFromFormat(
            '!Y-m-d', $_POST['selectiondate'], new DateTimeZone('UTC'));
    }

    $rep = new LHBookingsDiffsReport( $selectionDate );

    if (isset($_POST['reload_data']) && trim($_POST['reload_data']) == 'true') {
        $rep->submitBookingDiffsJob();
        wp_redirect( get_permalink() ); // redirect after POST to avoid resubmissions
        exit;
    }
    else {
        get_header();
        $rep->doView(); // update the view
        echo $rep->toHtml();
        get_footer(); 
    }

?>
