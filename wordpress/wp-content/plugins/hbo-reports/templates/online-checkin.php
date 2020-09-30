<?php

// user must be logged in to access this page
if (false === is_user_logged_in()) {
    header('Location: ' . home_url() . "/wp-login.php?redirect_to=" . urlencode($_SERVER['REQUEST_URI']));
}
else {
    $oc = new OnlineCheckin();
    get_header();
    $oc->doView(); // update the view
    echo $oc->toHtml();
    get_footer();
}

?>
