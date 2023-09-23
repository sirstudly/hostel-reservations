<?php 

    $selectionDate = new DateTime('now', new DateTimeZone('Europe/London'));
    $selectionDate->sub(new DateInterval('P1D')); // default to 1 day in the past

    // if date is defined, update the date
    if (isset($_POST['selectiondate']) && trim($_POST['selectiondate']) != '') {
        $selectionDate = DateTime::createFromFormat(
            '!Y-m-d', $_POST['selectiondate'], new DateTimeZone('Europe/London'));
    }

    if( strpos(get_option('hbo_lilho_username'), 'castlerock') === 0 ) {
        $bc = new BedCountsNew( $selectionDate );
    }
    else {
        $bc = new BedCounts( $selectionDate );
    }

    if (isset($_POST['bedcount_job']) && trim($_POST['bedcount_job']) == 'true' ) {
        $bc->submitRefreshJob();
        wp_redirect( get_permalink() ); // redirect after POST to avoid resubmissions
        exit;
    }
    else {
        get_header();
        $bc->updateBedcounts();
        echo $bc->toHtml();
        get_footer(); 
    }

?>
