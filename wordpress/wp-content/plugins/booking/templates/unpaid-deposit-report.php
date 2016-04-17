<?php 

    $rep = new LHUnpaidDepositReport();
    if (isset($_POST['reload_data'])) {
        $rep->submitAllocationScraperJob();
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
