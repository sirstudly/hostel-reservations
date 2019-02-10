// Scroll to script
function makeScroll(object_name) {
        var targetOffset = jQuery( object_name ).offset().top;
        targetOffset = targetOffset - 50;
        if (targetOffset<0) targetOffset = 0;
        jQuery('html,body').animate({scrollTop: targetOffset}, 1000);
}

// Show Yes/No dialog
function are_you_sure( message_question ){
        var answer = confirm( message_question );
        if ( answer) { return true; }
        else         { return false;}
}

// Display error message directly under a form element
// element : form element in error
// errorMessage : message to display
function showErrorMessage( element , errorMessage) {

    jQuery("[name='"+ element.name +"']")
            .fadeOut( 350 ).fadeIn( 300 )
            .fadeOut( 350 ).fadeIn( 400 )
            .animate( {opacity: 1}, 4000 )
    ;  // mark red border
    jQuery("[name='"+ element.name +"']")
            .after('<div class="wpdev-help-message">'+ errorMessage +'</div>'); // Show message
    jQuery(".wpdev-help-message")
            .css( {'color' : 'red'} )
            .animate( {opacity: 1}, 10000 )
            .fadeOut( 2000 );   // hide message
    element.focus();    // make focus to elemnt
    return;
}

// guest comments report
// reservation_id : id of LH reservation to confirm
function acknowledge_guest_comment( reservation_id ) {

    jQuery.ajax({                                           // Start Ajax Sending
        url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
        type:'POST',
        success: function (data, textStatus){ /* no update to page */ },
        error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Oops sorry.. we messed up somewhere...');}},
        data:{
            ajax_action : 'ACKNOWLEDGE_GUEST_COMMENT',
            reservation_id : reservation_id
        }
    });
}

// guest comments report
// reservation_id : id of LH reservation to unconfirm
function unacknowledge_guest_comment( reservation_id ) {

    jQuery.ajax({                                           // Start Ajax Sending
        url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
        type:'POST',
        success: function (data, textStatus){ /* no update to page */ },
        error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Oops sorry.. we messed up somewhere...');}},
        data:{
            ajax_action : 'UNACKNOWLEDGE_GUEST_COMMENT',
            reservation_id : reservation_id
        }
    });
}

// updates the guest comments report view
// include_acknowledged : true to include acknowledged comments
function update_guest_comments_report_view( include_acknowledged ) {

    jQuery.ajax({                                           // Start Ajax Sending
        url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
        type:'POST',
        success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_respond').html( data ) ;},
        error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Oops sorry.. we messed up somewhere...');}},
        data:{
            ajax_action : 'UPDATE_GUEST_COMMENTS_VIEW',
            include_acknowledged : include_acknowledged
        }
    });
}

// saves the login details for little hotelier
// username : LH username
// password : LH password
function save_little_hotelier_settings( username, password, lh_session ) {

    jQuery('#ajax_respond_lh').html('<div style="margin-left:80px;"><img src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif"></div>');

    jQuery.ajax({                                           // Start Ajax Sending
        url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
        type:'POST',
        success: function (data, textStatus){if( textStatus == 'success') jQuery('#ajax_respond_lh').html( data ); },
        error:function (XMLHttpRequest, textStatus, errorThrown){ window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Oops sorry.. we messed up somewhere...');}},
        data:{
            ajax_action : 'SAVE_LITTLE_HOTELIER_SETTINGS',
            username : username,
            password : password,
            lh_session : lh_session
        }
    });
}

// saves the session details for cloudbeds
// req_headers : request headers either from cURL (Chrome) or line delimitted (Firefox)
function save_cloudbeds_settings( req_headers ) {

    jQuery('#ajax_respond_cb').html('<div style="margin-left:80px;"><img src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif"></div>');

    jQuery.ajax({                                           // Start Ajax Sending
        url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
        type:'POST',
        success: function (data, textStatus){if( textStatus == 'success') jQuery('#ajax_respond_cb').html( data ); },
        error:function (XMLHttpRequest, textStatus, errorThrown){ window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Oops sorry.. we messed up somewhere...');}},
        data:{
            ajax_action : 'SAVE_CLOUDBEDS_SETTINGS',
            req_headers : req_headers
        }
    });
}

// saves the login details for hostelworld
// username : HW username
// password : HW password
function save_hostelworld_settings( username, password ) {

    jQuery('#ajax_respond_hw').html('<div style="margin-left:80px;"><img src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif"></div>');

    jQuery.ajax({                                           // Start Ajax Sending
        url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
        type:'POST',
        success: function (data, textStatus){if( textStatus == 'success') jQuery('#ajax_respond_hw').html( data ); },
        error:function (XMLHttpRequest, textStatus, errorThrown){ window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Oops sorry.. we messed up somewhere...');}},
        data:{
            ajax_action : 'SAVE_HOSTELWORLD_SETTINGS',
            username : username,
            password : password
        }
    });
}

// saves the login details for agoda
// username : agoda username
// password : agoda password
function save_agoda_settings( username, password ) {

    jQuery('#ajax_respond_agoda').html('<div style="margin-left:80px;"><img src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif"></div>');

    jQuery.ajax({                                           // Start Ajax Sending
        url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
        type:'POST',
        success: function (data, textStatus){if( textStatus == 'success') jQuery('#ajax_respond_agoda').html( data ); },
        error:function (XMLHttpRequest, textStatus, errorThrown){ window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Oops sorry.. we messed up somewhere...');}},
        data:{
            ajax_action : 'SAVE_AGODA_SETTINGS',
            username : username,
            password : password
        }
    });
}

// saves the login details for hostelworld
// group_booking_size : group size in report
// include_5_guests_in_6bed_dorm : checkbox value to include 5 guests in 6 bed dorm
function save_group_bookings_report_settings( group_booking_size, include_5_guests_in_6bed_dorm ) {

    jQuery('#ajax_respond_group_bookings_rpt').html('<div style="margin-left:80px;"><img src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif"></div>');

    jQuery.ajax({                                           // Start Ajax Sending
        url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
        type:'POST',
        success: function (data, textStatus){if( textStatus == 'success') jQuery('#ajax_respond_group_bookings_rpt').html( data ); },
        error:function (XMLHttpRequest, textStatus, errorThrown){ window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Oops sorry.. we messed up somewhere...');}},
        data:{
            ajax_action : 'SAVE_GROUP_BOOKINGS_REPORT_SETTINGS',
            group_booking_size : group_booking_size,
            include_5_guests_in_6bed_dorm : include_5_guests_in_6bed_dorm
        }
    });
}

// saves the email template for guest checkouts
// email_subject : email subject
// email_template : raw (HTML) email template
function save_guest_checkout_template( email_subject, email_template ) {

    jQuery('#ajax_respond_guest_email_template').html('<div style="margin-left:80px;"><img src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif"></div>');

    jQuery.ajax({                                           // Start Ajax Sending
        url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
        type:'POST',
        success: function (data, textStatus){if( textStatus == 'success') jQuery('#ajax_respond_guest_email_template').html( data ); },
        error:function (XMLHttpRequest, textStatus, errorThrown){ window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Oops sorry.. we messed up somewhere...');}},
        data:{
            ajax_action : 'SAVE_CHECKOUT_EMAIL_TEMPLATE',
            email_subject : escape(email_subject),
            email_template : btoa(email_template) // there is a security error when parsing <HTML> so escape it first
        }
    });
}

// send a test email using the template for guest checkouts
// first_name : first name of email
// last_name : last name of email
// recipient_email : email to be sent to
function send_test_response_email( first_name, last_name, recipient_email ) {

    jQuery.ajax({                                           // Start Ajax Sending
        url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
        type:'POST',
        success: function (data, textStatus){if( textStatus == 'success') jQuery('#ajax_respond_guest_email_template').html( data ); },
        error:function (XMLHttpRequest, textStatus, errorThrown){ window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Oops sorry.. we messed up somewhere...');}},
        data:{
            ajax_action : 'SEND_TEST_RESPONSE_EMAIL',
            first_name : first_name,
            last_name : last_name,
            recipient_email : recipient_email
        }
    });
}

// creates a new manual charge job
// bookingRef : booking reference. e.g. HWL-551-123456789
// amount : amount to charge e.g. 14.32
// note : note to leave in LH notes
// override_card_details : true to use LH card details
function submit_manual_charge( bookingRef, amount, note, override_card_details ) {

    jQuery('#ajax_response').html('<div style="margin-left:80px;"><img src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif"></div>');

    jQuery.ajax({                                           // Start Ajax Sending
        url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
        type:'POST',
        success: function (data, textStatus){if( textStatus == 'success') jQuery('#ajax_response').html( data ); },
        error:function (XMLHttpRequest, textStatus, errorThrown){ window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Oops sorry.. we messed up somewhere...');}},
        data:{
            ajax_action : 'SUBMIT_MANUAL_CHARGE_JOB',
            booking_ref : bookingRef,
            charge_amount : amount,
            charge_note : note,
            override_card_details : override_card_details ? 1 : 0
        }
    });
}

//Looks up a booking and generates a new payment link
//booking_ref : the cloudbeds booking reference ("identifier" in get_reservation request)
function generate_payment_link( booking_ref ) {

 jQuery('#ajax_response').html('<div style="margin-left:80px;"><img src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif"></div>');

 jQuery.ajax({                                           // Start Ajax Sending
     url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
     type:'POST',
     success: function (data, textStatus){if( textStatus == 'success') jQuery('#ajax_response').html( data ); },
     error:function (XMLHttpRequest, textStatus, errorThrown){ window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Oops sorry.. we messed up somewhere...');}},
     data:{
         ajax_action : 'GENERATE_PAYMENT_LINK',
         booking_ref : booking_ref
     }
 });
}

// Adds a new scheduled job. Only pass one of the two parameters.
// classname : name of (job) class to run
// repeat_every_min : number of minutes to repeat job
// daily_at : run daily at this time (24 hour clock)
function add_scheduled_job( classname, repeat_every_min, daily_at ) {

    jQuery('#ajax_loader')
        .html('<img src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif">')
        .show();
    jQuery('#add_job_button').hide();

    jQuery.ajax({                                           // Start Ajax Sending
        url: wpdev_bk_plugin_url + '/' + wpdev_bk_plugin_filename,
        type: 'POST',
        success: function (data, textStatus) {
            if (textStatus == 'success') {
                // reload table and reset form elements
                jQuery('#ajax_response').html(data);
                jQuery('#ajax_loader').hide();
                jQuery('#add_job_button').show();
                jQuery('input[type="text"]').val("");
                jQuery('input[name="schedule_type"]').removeAttr("checked");
                jQuery('select[name="classname"]')[0].selectedIndex = 0;
            }
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            window.status = 'Ajax sending Error status:' + textStatus;
            alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);
            if (XMLHttpRequest.status == 500) {
                alert('Oops sorry.. we messed up somewhere...');
            }
            jQuery('#ajax_loader').hide();
            jQuery('#add_job_button').show();
        },
        data: {
            ajax_action: 'ADD_SCHEDULED_JOB',
            classname: classname,
            repeat_every_min: repeat_every_min,
            daily_at: daily_at
        }
    });
}

// enable/disable a scheduled job
// scheduled_job_id : id of scheduled job to enable/disable
function toggle_scheduled_job( scheduled_job_id ) {

    jQuery.ajax({                                           // Start Ajax Sending
        url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
        type:'POST',
        success: function (data, textStatus){ /* no update to page */ },
        error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Oops sorry.. we messed up somewhere...');}},
        data:{
            ajax_action : 'TOGGLE_SCHEDULED_JOB',
            scheduled_job_id : scheduled_job_id
        }
    });
}

// deletes a scheduled job
// scheduled_job_id : id of scheduled job to enable/disable
function delete_scheduled_job( scheduled_job_id ) {

    jQuery('#delete_scheduled_job_' + scheduled_job_id)
        .html('<img src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif">');

    jQuery.ajax({                                           // Start Ajax Sending
        url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
        type:'POST',
        success: function (data, textStatus){if( textStatus == 'success') jQuery('#ajax_response').html( data ); },
        error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Oops sorry.. we messed up somewhere...');}},
        data:{
            ajax_action : 'DELETE_SCHEDULED_JOB',
            scheduled_job_id : scheduled_job_id
        }
    });
}

