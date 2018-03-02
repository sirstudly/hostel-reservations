<?php 

    $hk = new HouseKeeping();
    if (isset($_POST['housekeeping_job'])) {
        $hk->submitRefreshJob();
        wp_redirect( get_permalink() ); // redirect after POST to avoid resubmissions
        exit;
    } 
    else {
        get_header();
        $hk->doView(); // update the view
        echo $hk->toHtml();
        get_footer(); 
    }

?>
