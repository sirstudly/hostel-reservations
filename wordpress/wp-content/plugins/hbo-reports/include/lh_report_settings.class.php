<?php

/**
 * Display controller for reports settings page.
 */
class LHReportSettings extends XslTransform {

    var $reportSettings;  // array() key value pair of settings

    /**
     * Default constructor.
     */
    function __construct() {
        
    }

    /**
     * Updates the view using the current selection date.
     */
    function doView() {
        $this->reportSettings = array();
        $this->reportSettings['hbo_lilho_username'] = get_option('hbo_lilho_username');
        $this->reportSettings['hbo_lilho_password'] = htmlspecialchars(stripslashes(get_option('hbo_lilho_password')));
        $this->reportSettings['hbo_lilho_session'] = get_option('hbo_lilho_session');
	    $this->reportSettings['hbo_cloudbeds_username'] = get_option('hbo_cloudbeds_username');
	    $this->reportSettings['hbo_cloudbeds_password'] = htmlspecialchars(stripslashes(get_option('hbo_cloudbeds_password')));
        $this->reportSettings['hbo_hw_username'] = get_option('hbo_hw_username');
        $this->reportSettings['hbo_hw_password'] = htmlspecialchars(stripslashes(get_option('hbo_hw_password')));
	    $this->reportSettings['hbo_bdc_username'] = get_option('hbo_bdc_username');
	    $this->reportSettings['hbo_bdc_password'] = htmlspecialchars(stripslashes(get_option('hbo_bdc_password')));
        $this->reportSettings['hbo_agoda_username'] = get_option('hbo_agoda_username');
        $this->reportSettings['hbo_agoda_password'] = htmlspecialchars(stripslashes(get_option('hbo_agoda_password')));
        $this->reportSettings['hbo_group_booking_size'] = get_option('hbo_group_booking_size');
        $this->reportSettings['hbo_include_5_guests_in_6bed_dorm'] = get_option('hbo_include_5_guests_in_6bed_dorm');
        $this->reportSettings['hbo_guest_email_subject'] = htmlspecialchars(stripslashes(get_option('hbo_guest_email_subject')));
        $this->reportSettings['hbo_guest_email_template'] = esc_textarea(stripslashes(get_option('hbo_guest_email_template')));
   }

   /**
    * Updates details for little hotelier.
    */
   function saveLittleHotelierSettings( $username, $password, $lh_session ) {

       if( empty( $username )) {
           throw new ValidationException( "Username cannot be blank" );
       }
       if( empty( $password )) {
           throw new ValidationException( "Password cannot be blank" );
       }

       // only the session is important
       update_option( "hbo_lilho_username", $username );
       update_option( "hbo_lilho_password", $password );
       update_option( "hbo_lilho_session", $lh_session );

       // insert the job and process it
       LilHotelierDBO::insertUpdateLittleHotelierSettingsJob();
   }

   /**
    * Updates details for Cloudbeds.
    */
   function saveCloudbedsSettings( $reqHeaders ) {

       if( empty( $reqHeaders )) {
           throw new ValidationException( "Headers cannot be blank" );
       }

       $match_ua = array();
       $match_cookie = array();

       if( preg_match( '/Firefox/m', $reqHeaders ) ) {
           if( ! preg_match_all('/^User-Agent: (.*)$/m', $reqHeaders, $match_ua ) ) {
               throw new ValidationException( "Unable to retrieve user agent" );
           }
           if( ! preg_match_all('/^Cookie: (.*)$/m', $reqHeaders, $match_cookie ) ) {
               throw new ValidationException( "Unable to retrieve cookies" );
           }
       }
       elseif( preg_match( '/Chrome/m', $reqHeaders ) ) {
           // don't know why i need to double escape the terminating single quote...
           if( ! preg_match_all("/'user-agent: (.*?)\\\\'/m", $reqHeaders, $match_ua ) ) {
               throw new ValidationException( "Unable to retrieve user agent" );
           }
           if( ! preg_match_all("/'cookie: (.*?)\\\\'/m", $reqHeaders, $match_cookie ) ) {
               throw new ValidationException( "Unable to retrieve cookies" );
           }
       }
       else {
            throw new ValidationException( "Unable to determine browser from header format." );
       }

       update_option( "hbo_cloudbeds_useragent", $match_ua[1][0] );
       update_option( "hbo_cloudbeds_cookies", $match_cookie[1][0] );
   }
   
   /**
    * Creates a Cloudbeds Login job.
    */
   function resetCloudbedsLogin($username, $password) {
	   if( empty( $username )) {
		   throw new ValidationException( "Username cannot be blank" );
	   }
	   if( empty( $password )) {
		   throw new ValidationException( "Password cannot be blank" );
	   }

	   // if we get to this point, we have validated the login so save it
	   update_option( "hbo_cloudbeds_username", $username );
	   update_option( "hbo_cloudbeds_password", $password );
       LilHotelierDBO::insertJobOfType( "com.macbackpackers.jobs.ResetCloudbedsSessionJob" );
   }

   /**
    * After calling resetCloudbedsLogin(), call this with the 2FA code within the timeout period.
    */
   function updateCloudbeds2FACode( $scaCode ) {
       update_option( "hbo_cloudbeds_2facode", $scaCode );
       LilHotelierDBO::runProcessor();
   }

   /**
    * Updates details for hostelworld.
    */
   function saveHostelworldSettings( $username, $password ) {

       if( empty( $username )) {
           throw new ValidationException( "Username cannot be blank" );
       }
       if( empty( $password )) {
           throw new ValidationException( "Password cannot be blank" );
       }

       // if we get to this point, we have validated the login so save it
       update_option( "hbo_hw_username", $username );
       update_option( "hbo_hw_password", $password );
   }

   /**
    * Updates details for BDC.
    */
   function saveBdcSettings( $username, $password ) {

       if( empty( $username )) {
           throw new ValidationException( "Username cannot be blank" );
       }
       if( empty( $password )) {
           throw new ValidationException( "Password cannot be blank" );
       }

       update_option( "hbo_bdc_username", $username );
       update_option( "hbo_bdc_password", $password );
	   delete_option( "hbo_bdc_lasturl" );
   }

	/**
	 * Updates details for Agoda.
	 */
	function saveAgodaSettings( $username, $password ) {

		if( empty( $username )) {
			throw new ValidationException( "Username cannot be blank" );
		}
		if( empty( $password )) {
			throw new ValidationException( "Password cannot be blank" );
		}

		update_option( "hbo_agoda_username", $username );
		update_option( "hbo_agoda_password", $password );
	}

	/**
    * Updates details for the Group Bookings report.
    * $groupBookingSize : number of guests for a booking to be considered a "group" (string)
    * $include5guestsIn6bedDorms : boolean (true to include bookings of 5 guests in 6 bed dorms)
    */
   function saveGroupBookingsReportSettings( $groupBookingSize, $include5guestsIn6bedDorms ) {

       if( empty( $groupBookingSize )) {
           throw new ValidationException( "Group booking size cannot be blank" );
       }
       if( ctype_digit( $groupBookingSize ) === false ) {
           throw new ValidationException( "Group booking size must be a number" );
       }
       else if( intval( $groupBookingSize ) < 5 ) {
           throw new ValidationException( "Group booking size must be greater or equal to 5" );
       }

       update_option( "hbo_group_booking_size", $groupBookingSize );
       update_option( "hbo_include_5_guests_in_6bed_dorm", $include5guestsIn6bedDorms ? 'true' : 'false' );
   }

   /**
    * Updates email template for all guests marked as checked-out.
    * $emailSubject : email subject
    * $emailTemplate : raw (HTML) template of guest email to send (string)
    */
   function saveCheckedOutEmailTemplate( $emailSubject, $emailTemplate ) {

       if( empty( $emailSubject )) {
           throw new ValidationException( "Email subject cannot be blank" );
       }

       if( empty( $emailTemplate )) {
           throw new ValidationException( "Email template cannot be blank" );
       }

       update_option( "hbo_guest_email_subject", rawurldecode($emailSubject) );
       update_option( "hbo_guest_email_template", base64_decode($emailTemplate) );
   }

    /**
     * Sends a test email using the response template.
     *   $firstName : first name of recipient
     *   $lastName : last name of recipient
     *   $recipientEmail : email address of recipient
     */
   function sendTestResponseEmail( $firstName, $lastName, $recipientEmail ) {
       if( empty( $recipientEmail )) {
           throw new ValidationException( "Email address cannot be blank" );
       }

       // insert the job
       $jobId = LilHotelierDBO::insertCreateTestGuestCheckoutEmailJob( $firstName, $lastName, $recipientEmail );
       LilHotelierDBO::insertSendAllUnsentEmailJob();
   }

    /**
     * Adds this object to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this object will be added
     */
    function addSelfToDocument($domtree, $parentElement) {

        $parentElement->appendChild($domtree->createElement('property_manager', get_option('hbo_property_manager')));
        if ( $this->reportSettings ) {
            $settingsRoot = $parentElement->appendChild($domtree->createElement('settings'));
            foreach( $this->reportSettings as $key => $value ) {
                $settingsRoot->appendChild($domtree->createElement($key, $value));
            }
        }
    }
    
    /** 
      Generates the following xml:
        <view>
            <settings>
                <lilhotelier.url.login>https://app.littlehotelier.com/login</lilhotelier.url.login>
                ...
            </settings>
        </view>
     */
    function toXml() {
        // create a dom document with encoding utf8
        $domtree = new DOMDocument('1.0', 'UTF-8');
        $xmlRoot = $domtree->appendChild($domtree->createElement('view'));
        $this->addSelfToDocument($domtree, $xmlRoot);
        $xml = $domtree->saveXML();
        return $xml;
    }
    
    /**
     * Returns the filename for the stylesheet to use during transform.
     */
    function getXslFilename() {
        return HBO_PLUGIN_DIR. '/include/lh_report_settings.xsl';
    }

}

?>