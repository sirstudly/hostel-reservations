<?php
  /* Needed this hack to get around the redirect loop error when linking to Hostelworld?
   * Not sure why.. putting the same link on a separate page seems to work. 
   * Probably WordPress related but couldn't figure it out.
   */

  // saved before being redirected by template rewrite
  $url_path = explode("/", $_SESSION['url_path']);

  if (sizeof($url_path) > 1) {
    if ($url_path[1] === "HW_CustID" && sizeof($url_path) > 2) {
      $link = "https://inbox.hostelworld.com/booking/view/".$url_path[2];
    }
    elseif ($url_path[1] === "HW_ListBookingsByArrivalDate" && sizeof($url_path) > 2) {
      $link = "https://secure.hostelworld.com/inbox/bookings/searchresults.php?StartDate=".$url_path[2]."&EndDate=".$url_path[2]."&Category=ArrivalDate&Type=arrivaldate";
    }
    elseif( $url_path[1] === "LH_RemainingCheckinsForToday" ) {
      $now = new DateTime();
      $today = $now->format('d+M+Y');
      $link = "https://app.littlehotelier.com/extranet/properties/533/reservations?utf8=%E2%9C%93&reservation_filter[guest_last_name]=&reservation_filter[booking_reference_id]=&reservation_filter[date_type]=CheckIn&reservation_filter[date_from_display]=$today&reservation_filter[date_from]=$today&reservation_filter[date_to_display]=$today&reservation_filter[date_to]=$today&reservation_filter[status]=confirmed&commit=Search";
    }
  }

  if(isset($link)) {
?>
<html>
<head>
    <meta http-equiv="refresh" content="0; URL='<?php echo $link;?>'" />
</head>
<body>
    <p>Please wait... redirecting.</p>
    <p>Click on <a href="<?php echo $link;?>">this link</a> if you aren't automatically redirected.</p>
</body>
</html>
<?php
  } else {
?>
<html>
<body>
    Missing redirect target.
</body>
</html>
<?php
  }
?>
