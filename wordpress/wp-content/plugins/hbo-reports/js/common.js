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

// converts an array of name/value pairs to an indexed array
function unindexed_array_to_indexed_array(arr) {
    var indexed_array = {};

    jQuery.map(arr, function (n, i) {
        indexed_array[n['name']] = n['value'];
    });

    return indexed_array;
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

//reset the session details for cloudbeds
// username : CB username
// password : CB password
function reset_cloudbeds_login(username, password) {
 jQuery.ajax({                                           // Start Ajax Sending
     url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
     type:'POST',
     success: function (data, textStatus){if( textStatus == 'success') jQuery('#ajax_respond_cb').html( data ); },
     error:function (XMLHttpRequest, textStatus, errorThrown){ window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Oops sorry.. we messed up somewhere...');}},
     data:{
         ajax_action : 'RESET_CLOUDBEDS_LOGIN',
         username : username,
         password : password
     }
 });
}

//saves the 2FA code for cloudbeds
//cb_2facode : 6 digit 2FA code for cloudbeds
function update_cloudbeds_2facode( cb_2facode ) {
 jQuery.ajax({                                           // Start Ajax Sending
     url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
     type:'POST',
     success: function (data, textStatus){if( textStatus == 'success') jQuery('#ajax_respond_cb').html( data ); },
     error:function (XMLHttpRequest, textStatus, errorThrown){ window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Oops sorry.. we messed up somewhere...');}},
     data:{
         ajax_action : 'UPDATE_CLOUDBEDS_2FACODE',
         cb_2fa_code : cb_2facode
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

// saves the login details for bdc
// username : bdc username
// password : bdc password
function save_bdc_settings( username, password ) {

    jQuery('#ajax_respond_bdc').html('<div style="margin-left:80px;"><img src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif"></div>');

    jQuery.ajax({                                           // Start Ajax Sending
        url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
        type:'POST',
        success: function (data, textStatus){if( textStatus == 'success') jQuery('#ajax_respond_bdc').html( data ); },
        error:function (XMLHttpRequest, textStatus, errorThrown){ window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Oops sorry.. we messed up somewhere...');}},
        data:{
            ajax_action : 'SAVE_BDC_SETTINGS',
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

// saves housekeeping report settings
// bedsheet_change_days : number of days to change bedsheets on a continuous stay (null/blank to disable)
function save_housekeeping_report_settings( bedsheet_change_days ) {

    jQuery('#ajax_respond_bedsheets_change_after_days').html('<div style="margin-left:80px;"><img src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif"></div>');

    jQuery.ajax({                                           // Start Ajax Sending
        url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
        type:'POST',
        success: function (data, textStatus){if( textStatus == 'success') jQuery('#ajax_respond_bedsheets_change_after_days').html( data ); },
        error:function (XMLHttpRequest, textStatus, errorThrown){ window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Oops sorry.. we messed up somewhere...');}},
        data:{
            ajax_action : 'SAVE_HOUSEKEEPING_REPORT_SETTINGS',
            bedsheet_change_days : bedsheet_change_days
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

// shows or hides the password field based on a checkbox field
// http://www.experts-exchange.com/articles/19779/Passwords-in-HTML-Forms-Allow-the-Client-to-Show-or-Hide.html
// chkbox_selector : checkbox field
// password_selector : password field
function show_hide_password(chkbox_selector, password_selector) {
    jQuery(chkbox_selector).click(function () {
        if (jQuery(chkbox_selector).is(":checked")) {
            jQuery(password_selector).clone()
                .attr("type", "text").insertAfter(password_selector)
                .prev().remove();
        }
        else {
            jQuery(password_selector).clone()
                .attr("type", "password").insertAfter(password_selector)
                .prev().remove();
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
//payment_type : one of first_night, balance_due, custom_amount
//amount : boolean/number (true to pre-populate just the amount of first night, false for total outstanding, or specific custom amount)
function generate_payment_link( booking_ref, payment_type, amount ) {

    if(payment_type == 'first_night') {
        amount = true;
    }
    else if(payment_type == 'balance_due') {
        amount = false;
    }
    else if (amount != '' && isNaN(parseFloat(amount))) {
        jQuery('#payment_amount').addClass('form-control is-invalid')
            .after('<div class="invalid-feedback">Please enter a valid amount (eg. 12.99)</div>');
        return;
    }

    // reset page elements
    jQuery('#payment_url_block').hide();
    jQuery('#copied_to_clipboard').hide();
    jQuery('.is-invalid').removeClass("is-invalid form-control");

    jQuery.ajax({
        url: wpdev_bk_plugin_url + '/' + wpdev_bk_plugin_filename,
        type: 'POST',
        success: function (data, textStatus) {
            if (textStatus == 'success') {
                if (data.paymentUrl) {
                    jQuery('#payment_url_block').show();
                    jQuery('#paymentUrl').val(data.paymentUrl);
                }
                else if (data.error) {
                    jQuery('#payment_amount').addClass('is-invalid')
                        .after('<div class="invalid-feedback">' + data.error + '</div>');
                }
            }
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            window.status = 'Ajax sending Error status:' + textStatus;
            alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);
            if (XMLHttpRequest.status == 500) {
                alert('Oops sorry.. we messed up somewhere...');
            }
        },
        data: {
            ajax_action: 'GENERATE_PAYMENT_LINK',
            booking_ref: booking_ref,
            amount: amount
        }
    });
}

//Creates a new invoice payment link
//name : recipient name
//email : recipient email
//amount : amount of payment
//description : transaction description
//notes : staff notes
function generate_invoice_link( name, email, amount, description, notes ) {

	jQuery('#ajax_response_inv').html('<div style="margin-left:80px;"><img src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif"></div>');
	
	jQuery.ajax({                                           // Start Ajax Sending
	   url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
	   type:'POST',
	   success: function (data, textStatus){if( textStatus == 'success') jQuery('#ajax_response_inv').html( data ); },
	   error:function (XMLHttpRequest, textStatus, errorThrown){ window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Oops sorry.. we messed up somewhere...');}},
	   data:{
	       ajax_action : 'GENERATE_INVOICE_LINK',
	       invoice_name : name,
	       invoice_email : email,
	       invoice_amount : amount,
	       invoice_description : description,
	       invoice_notes : notes
	   }
	});
}

//Update the invoice detail dialog after adding a note.
//invoice_id : PK of wp_invoice
//note_text : new note to insert
function add_invoice_note( invoice_id, note_text ) {

	jQuery('#ajax_response-' + invoice_id).html('<div style="margin-left:80px;"><img src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif"></div>');
	
	jQuery.ajax({                                           // Start Ajax Sending
		 url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
		 type:'POST',
		 success: function (data, textStatus){if( textStatus == 'success') jQuery('#inv-detail-' + invoice_id).html( data ); },
		 error:function (XMLHttpRequest, textStatus, errorThrown){ window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Oops sorry.. we messed up somewhere...');}},
		 data:{
		     ajax_action : 'ADD_INVOICE_NOTE',
		     invoice_id : invoice_id,
		     note_text : note_text
	 }
	});
}

//update the invoice table on the invoice payments page
//include_acknowledged : true to include all records
function update_invoice_payment_view(include_acknowledged) {

    jQuery.ajax({                                           // Start Ajax Sending
        url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
        type:'POST',
        success: function (data, textStatus){if( textStatus == 'success')   jQuery('#invoice_view').html( data ) ;},
        error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Oops sorry.. we messed up somewhere...');}},
        data:{
            ajax_action : 'UPDATE_INVOICE_PAYMENT_VIEW',
            include_acknowledged : include_acknowledged
        }
    });
	
}

//hide invoice payment
//invoice_id : id of invoice to hide
function acknowledge_invoice_payment( invoice_id ) {
	 jQuery.ajax({                                           // Start Ajax Sending
	     url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
	     type:'POST',
	     success: function (data, textStatus){ /* no update to page */ },
	     error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Oops sorry.. we messed up somewhere...');}},
	     data:{
	         ajax_action : 'ACKNOWLEDGE_INVOICE_PAYMENT',
	         invoice_id : invoice_id
	     }
	 });
}

//show invoice payment
//invoice_id : id of invoice to show
function unacknowledge_invoice_payment( invoice_id ) {
	jQuery.ajax({                                           // Start Ajax Sending
		   url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
		   type:'POST',
		   success: function (data, textStatus){ /* no update to page */ },
		   error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Oops sorry.. we messed up somewhere...');}},
		   data:{
		       ajax_action : 'UNACKNOWLEDGE_INVOICE_PAYMENT',
		       invoice_id : invoice_id
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
                onchange_job(jQuery("#new_job_select option:selected").val());
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
            daily_at: daily_at,
            params: unindexed_array_to_indexed_array(jQuery('input[id^=params]').serializeArray())
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

// saves a new or updates an existing blacklist entry
// id : PK of blacklist entry (optional)
// firstname : first name
// lastname : last name
// email : email (optional)
// notes : (optional)
function save_blacklist( id, firstname, lastname, email, notes ) {

    jQuery.ajax({                                           // Start Ajax Sending
        url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
        type:'POST',
        success: function (data, textStatus){if( textStatus == 'success')   jQuery(id ? '#ajax_response-' + id : '#ajax_response').html( data );},
        error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Oops sorry.. we messed up somewhere...');}},
        data:{
            ajax_action : 'SAVE_BLACKLIST',
            id : id,
            first_name : firstname,
            last_name : lastname,
            email : email,
            notes: notes
        }
    });
}

// saves a new blacklist alias
// id : PK of blacklist entry
// firstname : first name
// lastname : last name
// email : email (optional)
function save_blacklist_alias( id, firstname, lastname, email ) {

    jQuery.ajax({                                           // Start Ajax Sending
        url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
        type:'POST',
        success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_response-' + id).html( data );},
        error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Oops sorry.. we messed up somewhere...');}},
        data:{
            ajax_action : 'SAVE_BLACKLIST_ALIAS',
            id : id,
            first_name : firstname,
            last_name : lastname,
            email : email
        }
    });
}

// Deletes an existing blacklist alias
// blacklist_id : PK of blacklist entry
// alias_id : PK of blacklist alias
function delete_blacklist_alias( blacklist_id, alias_id ) {

    jQuery.ajax({                                           // Start Ajax Sending
        url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
        type:'POST',
        success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_response-' + blacklist_id).html( data ) ;},
        error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Oops sorry.. we messed up somewhere...');}},
        data:{
            ajax_action : 'DELETE_BLACKLIST_ALIAS',
            blacklist_id : blacklist_id,
            alias_id : alias_id
        }
    });
}

// Uploads a new image for a given blacklist entry
// blacklist_id : PK of blacklist entry
// files : array of (one) file to upload
function upload_blacklist_image( blacklist_id, files ) {

    // Check file selected or not
    if (files.length > 0) {
        const fd = new FormData();
        fd.append('file',files[0]);
        fd.append('ajax_action', 'UPLOAD_BLACKLIST_IMAGE');
        fd.append('blacklist_id', blacklist_id);
        jQuery.ajax({
            url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
            type: 'POST',
            data: fd,
            cache: false,
            contentType: false,
            processData: false,
            success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_response-' + blacklist_id).html( data ) ;},
            error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Oops sorry.. we messed up somewhere...');}},
        });
    }
    else {
        alert("Please select a file.");
    }
}

//Looks up a booking
//booking_ref : the cloudbeds booking reference ("identifier" in get_reservation request)
function lookup_booking( booking_ref ) {

    jQuery('#ajax_response').html('<div style="margin-left:80px;"><img src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif"></div>');

    jQuery.ajax({                                           // Start Ajax Sending
       url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
       type:'POST',
       success: function (data, textStatus){if( textStatus == 'success') jQuery('#ajax_response').html( data ); },
       error:function (XMLHttpRequest, textStatus, errorThrown){ window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Oops sorry.. we messed up somewhere...');}},
       data:{
           ajax_action : 'LOOKUP_BOOKING',
           booking_ref : booking_ref
       }
    });
}

//Looks up a booking for generate payment link page
//booking_ref : the cloudbeds booking reference ("identifier" in get_reservation request)
function lookup_booking_for_generate_payment_link(booking_ref) {

    jQuery('#ajax_response').html('<div style="margin-left:80px;"><img src="' + wpdev_bk_plugin_url + '/img/ajax-loader.gif"></div>');

    jQuery.ajax({                                           // Start Ajax Sending
        url: wpdev_bk_plugin_url + '/' + wpdev_bk_plugin_filename,
        type: 'POST',
        success: function (data, textStatus) {
            if (textStatus == 'success') jQuery('#ajax_response').html(data);
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            window.status = 'Ajax sending Error status:' + textStatus;
            alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);
            if (XMLHttpRequest.status == 500) {
                alert('Oops sorry.. we messed up somewhere...');
            }
        },
        data: {
            ajax_action: 'LOOKUP_BOOKING_FOR_GENERATE_PAYMENT_LINK',
            booking_ref: booking_ref
        }
    });
}

//Shows the refund dialog.
//txn_id : (cloudbeds) transaction id
function show_refund_dialog( txn_id ) {

	jQuery.ajax({                                           // Start Ajax Sending
		 url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
		 type:'POST',
		 success: function (data, textStatus){if( textStatus == 'success') jQuery('#dialog_ajax_response').html( data ); },
		 error:function (XMLHttpRequest, textStatus, errorThrown){ window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Oops sorry.. we messed up somewhere...');}},
		 data:{
		     ajax_action : 'SHOW_REFUND_DIALOG',
		     txn_id : txn_id
	     }
	});
}

//Submit a refund.
//amount : amount to be refunded
//description : refund note (optional)
function submit_refund( amount, description ) {

	jQuery('#refund_ajax_response').html('<div style="margin-left:80px;"><img src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif"></div>');
	jQuery('#submit_refund_button').addClass("disabled");
	
	jQuery.ajax({                                           // Start Ajax Sending
		 url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
		 type:'POST',
		 success: function (data, textStatus){if( textStatus == 'success') jQuery('#refund_ajax_response').html( data ); },
		 error:function (XMLHttpRequest, textStatus, errorThrown){ window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Oops sorry.. we messed up somewhere...');}},
		 data:{
		     ajax_action : 'SUBMIT_REFUND',
		     amount : amount,
		     description : description
	     }
	});
}

//Shows the gateway response for a refund.
//txn_id : refund transaction id
function show_refund_response( txn_id ) {

	jQuery.ajax({                                           // Start Ajax Sending
		 url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
		 type:'POST',
		 success: function (data, textStatus){if( textStatus == 'success') jQuery('#dialog_ajax_response').html( data ); },
		 error:function (XMLHttpRequest, textStatus, errorThrown){ window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Oops sorry.. we messed up somewhere...');}},
		 data:{
		     ajax_action : 'SHOW_REFUND_RESPONSE',
		     txn_id : txn_id
	     }
	});
}

//generate a booking URL (for users to enter details) given a booking reference
//booking_identifier : cloudbeds booking identifier (the one displayed on the page)
function generate_booking_url(booking_identifier) {

    jQuery.ajax({
        url: wpdev_bk_plugin_url + '/' + wpdev_bk_plugin_filename,
        type: 'POST',
        success: function (data, textStatus) {
            if (textStatus == 'success') {
                // not sure how else to report an error without messing up the main body content
                if(data.indexOf("ERROR TEMPLATE") >= 0) {
                    jQuery('#ajax_error').html(data);
                }
                else {
                    jQuery('#body_content').html(data);
                    jQuery('#ajax_error').html("");
                }
            }
            else {
                jQuery('#ajax_error').html(textStatus + " - Failed to generate booking URL");
            }
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            jQuery('#ajax_error').html(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText)
        },
        data: {
            ajax_action: 'GENERATE_BOOKING_URL',
            booking_identifier: booking_identifier
        }
    });
}

// changes the job status back to submitted
// job_id : PK of job to resubmit
function resubmit_incomplete_job(job_id) {

    jQuery.ajax({
        url: wpdev_bk_plugin_url + '/' + wpdev_bk_plugin_filename,
        type: 'POST',
        success: function (data, textStatus){
            if( textStatus == 'success') {
                jQuery('#ajax_response').html( data );
            }
        },
        error:function (XMLHttpRequest, textStatus, errorThrown){
            window.status = 'Ajax sending Error status:'+ textStatus;
            alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);
            if (XMLHttpRequest.status == 500) {
                alert('Oops sorry.. we messed up somewhere...');
            }
        },
        data: {
            ajax_action: 'RESUBMIT_INCOMPLETE_JOB',
            job_id: job_id
        }
    });
}
