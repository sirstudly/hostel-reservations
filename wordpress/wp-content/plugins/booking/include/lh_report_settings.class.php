<?php

/**
 * Display controller for reports settings page.
 */
class LHReportSettings extends XslTransform {

    var $reportSettings;  // array() key value pair of settings

    /**
     * Default constructor.
     */
    function LHReportSettings() {
        
    }

    /**
     * Updates the view using the current selection date.
     */
    function doView() {
        $this->reportSettings = array();
        $this->reportSettings['hbo_lilho_username'] = get_option('hbo_lilho_username');
        $this->reportSettings['hbo_lilho_password'] = get_option('hbo_lilho_password');
        $this->reportSettings['hbo_hw_username'] = get_option('hbo_hw_username');
        $this->reportSettings['hbo_hw_password'] = get_option('hbo_hw_password');
        $this->reportSettings['hbo_hb_username'] = get_option('hbo_hb_username');
        $this->reportSettings['hbo_hb_password'] = get_option('hbo_hb_password');
        $this->reportSettings['hbo_group_booking_size'] = get_option('hbo_group_booking_size');
        $this->reportSettings['hbo_include_5_guests_in_6bed_dorm'] = get_option('hbo_include_5_guests_in_6bed_dorm');
   }

   /**
    * Updates details for little hotelier.
    */
   function saveLittleHotelierSettings( $username, $password ) {

       if( empty( $username )) {
           throw new ValidationException( "Username cannot be blank" );
       }
       if( empty( $password )) {
           throw new ValidationException( "Password cannot be blank" );
       }

       // insert the job and process it; verify the status afterwards
       $jobId = LilHotelierDBO::insertUpdateLittleHotelierSettingsJob( $username, $password );
/* DISABLED as we're no longer running on the same server
       LilHotelierDBO::runProcessorAndWait();
       $jobStatus = LilHotelierDBO::getStatusOfJob( $jobId );

       if( $jobStatus != LilHotelierDBO::STATUS_COMPLETED ) {
           error_log( "saveLittleHotelierSettings: Job $jobId is at $jobStatus");
           if( $jobStatus == LilHotelierDBO::STATUS_FAILED ) {
               throw new ProcessingException( "Could not login using given credentials. Changes not saved." );
           }
           throw new ProcessingException( "Failed to update details. Check log for details." );
       }

       // if we get to this point, we have validated the login so save it
       update_option( "hbo_lilho_username", $username );
       update_option( "hbo_lilho_password", $password );
*/
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

/* DISABLED as we're no longer running on the same server
       // insert the job and process it; verify the status afterwards
       $jobId = LilHotelierDBO::insertUpdateHostelworldSettingsJob( $username, $password );
       LilHotelierDBO::runProcessorAndWait();
       $jobStatus = LilHotelierDBO::getStatusOfJob( $jobId );

       if( $jobStatus != LilHotelierDBO::STATUS_COMPLETED ) {
           error_log( "saveHostelworldSettings: Job $jobId is at $jobStatus");
           if( $jobStatus == LilHotelierDBO::STATUS_FAILED ) {
               throw new ProcessingException( "Could not login using given credentials. Changes not saved." );
           }
           throw new ProcessingException( "Failed to update details. Check log for details." );
       }
*/
       // if we get to this point, we have validated the login so save it
       update_option( "hbo_hw_username", $username );
       update_option( "hbo_hw_password", $password );
   }

   /**
    * Updates details for hostelbookers.
    * ** Now deprecated. HB merged with HW. 9/4/2016 **
    */
   function saveHostelbookersSettings( $username, $password ) {

       if( empty( $username )) {
           throw new ValidationException( "Username cannot be blank" );
       }
       if( empty( $password )) {
           throw new ValidationException( "Password cannot be blank" );
       }

       // insert the job and process it; verify the status afterwards
       $jobId = LilHotelierDBO::insertUpdateHostelbookersSettingsJob( $username, $password );
       LilHotelierDBO::runProcessorAndWait();
       $jobStatus = LilHotelierDBO::getStatusOfJob( $jobId );

       if( $jobStatus != LilHotelierDBO::STATUS_COMPLETED ) {
           error_log( "saveHostelbookersSettings: Job $jobId is at $jobStatus");
           if( $jobStatus == LilHotelierDBO::STATUS_FAILED ) {
               throw new ProcessingException( "Could not login using given credentials. Changes not saved." );
           }
           throw new ProcessingException( "Failed to update details. Check log for details." );
       }

       // if we get to this point, we have validated the login so save it
       update_option( "hbo_hb_username", $username );
       update_option( "hbo_hb_password", $password );
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
       else if( intval( $groupBookingSize ) < 6 ) {
           throw new ValidationException( "Group booking size must be greater or equal to 6" );
       }

       update_option( "hbo_group_booking_size", $groupBookingSize );
       update_option( "hbo_include_5_guests_in_6bed_dorm", $include5guestsIn6bedDorms ? 'true' : 'false' );
   }

    /**
     * Adds this object to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this object will be added
     */
    function addSelfToDocument($domtree, $parentElement) {

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
                <lilhotelier.url.login>https://emea.littlehotelier.com/login</lilhotelier.url.login>
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
        return WPDEV_BK_PLUGIN_DIR. '/include/lh_report_settings.xsl';
    }

}

?>