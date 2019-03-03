<?php

/**
 * Execute the ajax controller if we are performing an ajax action.
 */    
if ( isset( $_POST['ajax_action'] ) ) {
    $ax = new AjaxController();
    $ax->handle_request($_POST['ajax_action']);
}

    
/**
 * Handler for AJAX requests.
 */
class AjaxController {

    /**
     * Default constructor.
     */    
    function AjaxController() {
        session_start();  // continue current session
    }

    /**
     * Handles the specified action.
     * $action : action constant to be handled
     */
    function handle_request($action) {

        switch ( $action ) :

            case  'ACKNOWLEDGE_GUEST_COMMENT':
                $this->acknowledge_guest_comment();
                break;

            case  'UNACKNOWLEDGE_GUEST_COMMENT':
                $this->unacknowledge_guest_comment();
                break;

            case 'UPDATE_GUEST_COMMENTS_VIEW':
                $this->update_guest_comments_report_view();
                break;

            case 'SAVE_LITTLE_HOTELIER_SETTINGS':
                $this->saveLittleHotelierSettings();
                break;

            case 'SAVE_CLOUDBEDS_SETTINGS':
                $this->saveCloudbedsSettings();
                break;

            case 'SAVE_HOSTELWORLD_SETTINGS':
                $this->saveHostelworldSettings();
                break;

            case 'SAVE_AGODA_SETTINGS':
                $this->saveAgodaSettings();
                break;

            case 'SAVE_GROUP_BOOKINGS_REPORT_SETTINGS':
                $this->saveGroupBookingsReportSettings();
                break;

            case 'SAVE_CHECKOUT_EMAIL_TEMPLATE':
                $this->saveCheckoutEmailTemplate();
                break;

            case 'SEND_TEST_RESPONSE_EMAIL':
                $this->sendTestResponseEmail();
                break;

            case 'SUBMIT_MANUAL_CHARGE_JOB':
                $this->submitManualChargeJob();
                break;

            case 'GENERATE_PAYMENT_LINK':
                $this->generatePaymentLink();
                break;
                
            case 'GENERATE_INVOICE_LINK':
                $this->generateInvoiceLink();
                break;

            case 'ADD_SCHEDULED_JOB':
                $this->addScheduledJob();
                break;

            case 'TOGGLE_SCHEDULED_JOB':
                $this->toggleScheduledJob();
                break;

            case 'DELETE_SCHEDULED_JOB':
                $this->deleteScheduledJob();
                break;

            default:
                error_log("ERROR: Undefined AJAX action  $action");

        endswitch;
        die();
    }

    /**
     * Acknowledges a guest comment.
     * Requires POST variables:
     *   reservation_id : ID of LH reservation
     */
    function acknowledge_guest_comment() {
        $reservationId = $_POST['reservation_id'];
        if(isset($_SESSION['GUEST_COMMENTS_CONTROLLER'])) {
            $commentPage = $_SESSION['GUEST_COMMENTS_CONTROLLER'];
            $commentPage->acknowledgeComment( $reservationId );
        }
    }

    /**
     * Unacknowledges a guest comment.
     * Requires POST variables:
     *   reservation_id : ID of LH reservation
     */
    function unacknowledge_guest_comment() {
        $reservationId = $_POST['reservation_id'];
        if(isset($_SESSION['GUEST_COMMENTS_CONTROLLER'])) {
            $commentPage = $_SESSION['GUEST_COMMENTS_CONTROLLER'];
            $commentPage->unacknowledgeComment( $reservationId );
        }
    }

    /**
     * Updates the guest comments report table.
     * Requires POST variables:
     *   include_acknowledged : true to include acknowledged comments
     */
    function update_guest_comments_report_view() {
        $includeAcknowledged = $_POST['include_acknowledged'];
        $commentPage = new LHGuestCommentsReportData();
        $commentPage->doView( $includeAcknowledged == 'true' );

        ?> 
        <script type="text/javascript">
            document.getElementById('guest_comments_rpt').innerHTML = <?php echo json_encode($commentPage->toHtml()); ?>;
        </script>
        <?php
    }

    /**
     * Updates the username, password for little hotelier.
     * Requires POST variables:
     *   username : LH username
     *   password : LH password
     */
    function saveLittleHotelierSettings() {
        try {
            $settingsPage = new LHReportSettings();
            $settingsPage->saveLittleHotelierSettings( 
                $_POST['username'], $_POST['password'], $_POST['lh_session'] );

            ?> 
            <script type="text/javascript">
                jQuery("#ajax_respond_lh")
                     .html('Settings saved successfully.')
                     .css({ 'color': 'green' });
                jQuery("#btn_save_lilho").prop( "disabled", false );
            </script>
            <?php
        }
        catch( Exception $e ) {
            ?> 
            <script type="text/javascript">
                jQuery("#ajax_respond_lh")
                     .html('<?php echo $e->getMessage(); ?>')
                     .css({ 'color': 'red' });
                jQuery("#btn_save_lilho").prop( "disabled", false );
            </script>
            <?php
        }
    }

    /**
     * Updates the username, password for Cloudbeds.
     * Requires POST variables:
     *   username : username
     *   password : password
     */
    function saveCloudbedsSettings() {
        try {
            $settingsPage = new LHReportSettings();
            $settingsPage->saveCloudbedsSettings( $_POST['req_headers'] );

            ?> 
            <script type="text/javascript">
                jQuery("#ajax_respond_cb")
                     .html('Settings saved successfully.')
                     .css({ 'color': 'green' });
                jQuery("#btn_save_cloudbeds").prop( "disabled", false );
            </script>
            <?php
        }
        catch( Exception $e ) {
            ?> 
            <script type="text/javascript">
                jQuery("#ajax_respond_cb")
                     .html('<?php echo $e->getMessage(); ?>')
                     .css({ 'color': 'red' });
                jQuery("#btn_save_cloudbeds").prop( "disabled", false );
            </script>
            <?php
        }
    }

    /**
     * Updates the username, password for hostelworld.
     * Requires POST variables:
     *   username : HW username
     *   password : HW password
     */
    function saveHostelworldSettings() {
        try {
            $settingsPage = new LHReportSettings();
            $settingsPage->saveHostelworldSettings( 
                $_POST['username'], $_POST['password'] );

            ?> 
            <script type="text/javascript">
                jQuery("#ajax_respond_hw")
                     .html('Settings saved successfully.')
                     .css({ 'color': 'green' });
                jQuery("#btn_save_hw").prop( "disabled", false );
            </script>
            <?php
        }
        catch( Exception $e ) {
            ?> 
            <script type="text/javascript">
                jQuery("#ajax_respond_hw")
                     .html('<?php echo $e->getMessage(); ?>')
                     .css({ 'color': 'red' });
                jQuery("#btn_save_hw").prop( "disabled", false );
            </script>
            <?php
        }
    }

    /**
     * Updates the username, password for Agoda.
     * Requires POST variables:
     *   username : agoda username
     *   password : agoda password
     */
    function saveAgodaSettings() {
        try {
            $settingsPage = new LHReportSettings();
            $settingsPage->saveAgodaSettings( 
                $_POST['username'], $_POST['password'] );

            ?> 
            <script type="text/javascript">
                jQuery("#ajax_respond_agoda")
                     .html('Settings saved successfully.')
                     .css({ 'color': 'green' });
                jQuery("#btn_save_agoda").prop( "disabled", false );
            </script>
            <?php
        }
        catch( Exception $e ) {
            ?> 
            <script type="text/javascript">
                jQuery("#ajax_respond_agoda")
                     .html('<?php echo $e->getMessage(); ?>')
                     .css({ 'color': 'red' });
                jQuery("#btn_save_agoda").prop( "disabled", false );
            </script>
            <?php
        }
    }

    /**
     * Updates the settings for the group bookings report.
     * Requires POST variables:
     *   group_booking_size : group size in report
     *   include_5_guests_in_6bed_dorm : checkbox value to include 5 guests in 6 bed dorm
     */
    function saveGroupBookingsReportSettings() {
        try {
            $settingsPage = new LHReportSettings();
            $settingsPage->saveGroupBookingsReportSettings( 
                $_POST['group_booking_size'], $_POST['include_5_guests_in_6bed_dorm'] == 'true' );
            ?> 
            <script type="text/javascript">
                jQuery("#ajax_respond_group_bookings_rpt")
                     .html('Settings saved successfully.')
                     .css({ 'color': 'green' });
                jQuery("#btn_save_group_rpt_settings").prop( "disabled", false );
            </script>
            <?php
        }
        catch( Exception $e ) {
            ?> 
            <script type="text/javascript">
                jQuery("#ajax_respond_group_bookings_rpt")
                     .html('<?php echo $e->getMessage(); ?>')
                     .css({ 'color': 'red' });
                jQuery("#btn_save_group_rpt_settings").prop( "disabled", false );
            </script>
            <?php
        }
    }

    /**
     * Updates the email template for guests marked as checked-out.
     * Requires POST variables:
     *   email_template : raw (HTML) of email template
     */
    function saveCheckoutEmailTemplate() {
        try {
            $settingsPage = new LHReportSettings();
            $settingsPage->saveCheckedOutEmailTemplate( $_POST['email_subject'], $_POST['email_template'] );
            ?> 
            <script type="text/javascript">
                jQuery("#ajax_respond_guest_email_template")
                     .html('Email template saved successfully.')
                     .css({ 'color': 'green' });
                jQuery("#btn_save_guest_email_template").prop( "disabled", false );
            </script>
            <?php
        }
        catch( Exception $e ) {
            ?> 
            <script type="text/javascript">
                jQuery("#ajax_respond_guest_email_template")
                     .html('<?php echo $e->getMessage(); ?>')
                     .css({ 'color': 'red' });
                jQuery("#btn_save_guest_email_template").prop( "disabled", false );
            </script>
            <?php
        }
    }

    /**
     * Sends a test email using the response template.
     * Requires POST variables:
     *   first_name : first name of recipient
     *   last_name : last name of recipient
     *   recipient_email : email address of recipient
     */
    function sendTestResponseEmail() {
        try {
            $settingsPage = new LHReportSettings();
            $settingsPage->sendTestResponseEmail( $_POST['first_name'], $_POST['last_name'], $_POST['recipient_email'] );
            ?> 
            <script type="text/javascript">
                jQuery("#ajax_respond_guest_email_template")
                     .html('Email job queued.')
                     .css({ 'color': 'green' });
            </script>
            <?php
        }
        catch( Exception $e ) {
            ?> 
            <script type="text/javascript">
                jQuery("#ajax_respond_guest_email_template")
                     .html('<?php echo $e->getMessage(); ?>')
                     .css({ 'color': 'red' });
            </script>
            <?php
        }
    }

    /**
     * Creates a new manual charge job.
     * Requires POST variables:
     *   booking_ref : booking reference e.g. HWL-551-12345789
     *   charge_amount : amount to charge e.g. 13.44
     *   charge_note : message to append to LH notes
     *   override_card_details : true to use LH card details
     */
    function submitManualChargeJob() {
        try {
            $chargePage = new LHManualCharge();
            $chargePage->submitManualChargeJob( 
                $_POST['booking_ref'], $_POST['charge_amount'], $_POST['charge_note'], $_POST['override_card_details'] );

            ?> 
            <script type="text/javascript">
                // blank out all fields to submit anew
                jQuery("#booking_ref").val('');
                jQuery("#charge_amount").val('');
                jQuery("#charge_note").val('');
                jQuery("#override_card_details").prop('checked', false);;
                // reload the page anyways to refresh the table
                window.location.reload(true); 
            </script>
            <?php
        }
        catch( Exception $e ) {
            ?> 
            <script type="text/javascript">
                jQuery("#ajax_response")
                     .html('<?php echo $e->getMessage(); ?>')
                     .css({ 'color': 'red' });
                jQuery("#charge_button").css("visibility", "visible");
            </script>
            <?php
        }
    }

    /**
     * Looks up an existing booking and generates a new payment link.
     * Requires POST variables:
     *   booking_ref : cloudbeds "identifier"
     */
    function generatePaymentLink() {
        try {
            $paymentLinkPage = new GeneratePaymentLinkController();
            $paymentLinkPage->generatePaymentLink($_POST['booking_ref']);
            ?>
            <script type="text/javascript">
                document.getElementById('ajax_response').innerHTML = <?php echo json_encode($paymentLinkPage->toHtml()); ?>;
                jQuery("#ajax_response").css({ 'color': 'black' });
            </script>
            <?php
        }
        catch( Exception $e ) {
            ?> 
            <script type="text/javascript">
                jQuery("#ajax_response")
                     .html('<?php echo $e->getMessage(); ?>')
                     .css({ 'color': 'red' });
            </script>
            <?php
        }
    }

    /**
     * Inserts a record in the wp_sagepay_invoice table and generates a new payment link.
     * Requires POST variables:
     *   invoice_name : email recipient
     *   invoice_email : email address
     *   invoice_amount : default amount to be charged
     *   invoice_description : payment description
     *   invoice_notes : internal notes
     */
    function generateInvoiceLink() {
        try {
            $paymentLinkPage = new GeneratePaymentLinkController();
            $paymentLinkPage->generateInvoiceLink(
                $_POST['invoice_name'],
                $_POST['invoice_email'],
                $_POST['invoice_amount'],
                $_POST['invoice_description'],
                $_POST['invoice_notes']);
            ?>
            <script type="text/javascript">
                document.getElementById('ajax_response_inv').innerHTML = <?php echo json_encode($paymentLinkPage->toHtml()); ?>;
                jQuery("#ajax_response_inv").css({ 'color': 'black' });
            </script>
            <?php
        }
        catch( Exception $e ) {
            ?> 
            <script type="text/javascript">
                jQuery("#ajax_response_inv")
                     .html('<?php echo $e->getMessage(); ?>')
                     .css({ 'color': 'red' });
            </script>
            <?php
        }
    }

    /**
     * Adds a new scheduled job.
     * Requires POST variables:
     *   classname : fully qualified name of class to run
     *   repeat_every_min : number of minutes in between jobs (choose one of these)
     *   daily_at : run daily at this time in 24 hour clock (choose one of these)
     */
    function addScheduledJob() {
        try {
            $jobView = new ScheduledJobViewData();
            $jobView->addScheduledJob( 
                $_POST['classname'], $_POST['repeat_every_min'], $_POST['daily_at'] );
            $jobView->doView();

            ?>
            <script type="text/javascript">
                jQuery("#job_schedule_table")
                     .html('<?php echo preg_replace('#\R+#', '', $jobView->toHtml()); ?>');
                jQuery("#add_job_button").css("visibility", "visible");
            </script>
            <?php
        }
        catch( Exception $e ) {
            ?> 
            <script type="text/javascript">
                jQuery("#ajax_response")
                     .html('<?php echo $e->getMessage(); ?>')
                     .css({ 'color': 'red' });
                jQuery("#add_job_button").css("visibility", "visible");
            </script>
            <?php
        }
    }

    /**
     * Enable/Disable scheduled job.
     * Requires POST variables:
     *   scheduled_job_id : ID of scheduled job to update
     */
    function toggleScheduledJob() {
        $jobView = new ScheduledJobViewData();
        $jobView->toggleScheduledJob($_POST['scheduled_job_id']);
    }

    /**
     * Deletes a scheduled job.
     * Requires POST variables:
     *   scheduled_job_id : ID of scheduled job to update
     */
    function deleteScheduledJob() {
        try {
            $jobView = new ScheduledJobViewData();
            $jobView->deleteScheduledJob($_POST['scheduled_job_id']);
            $jobView->doView();

            ?>
            <script type="text/javascript">
                jQuery("#job_schedule_table")
                     .html('<?php echo preg_replace('#\R+#', '', $jobView->toHtml()); ?>');
            </script>
            <?php
        }
        catch( Exception $e ) {
            ?> 
            <script type="text/javascript">
                jQuery("#delete_scheduled_job_" + <?php echo $_POST['scheduled_job_id']; ?> )
                     .html('<?php echo $e->getMessage(); ?>')
                     .css({ 'color': 'red' });
            </script>
            <?php
        }
    }
}

?>
