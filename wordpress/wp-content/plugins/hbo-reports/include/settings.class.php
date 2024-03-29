<?php

/**
 * Hostel Backoffice Settings controller.
 */
class Settings extends XslTransform {

    /** 
     * Default constructor.
     */
    function __construct() {
    }

    /**
     * Adds this allocation table to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this row will be added
     */
    function addSelfToDocument($domtree, $parentElement) {    
        // create the root element for this class and append it to our parent
        $xmlRoot = $parentElement->appendChild($domtree->createElement('view'));
        $xmlRoot->appendChild($domtree->createElement('siteicon_url', get_option('hbo_siteicon_url')));
        $xmlRoot->appendChild($domtree->createElement('housekeepingurl', get_option('hbo_housekeeping_url')));
        $xmlRoot->appendChild($domtree->createElement('split_room_report_url', get_option('hbo_split_room_report_url')));
        $xmlRoot->appendChild($domtree->createElement('unpaid_deposit_report_url', get_option('hbo_unpaid_deposit_report_url')));
        $xmlRoot->appendChild($domtree->createElement('group_bookings_report_url', get_option('hbo_group_bookings_report_url')));
        $xmlRoot->appendChild($domtree->createElement('guest_comments_report_url', get_option('hbo_guest_comments_report_url')));
        $xmlRoot->appendChild($domtree->createElement('bottom_bunks_report_url', get_option('hbo_bottom_bunks_report_url')));
        $xmlRoot->appendChild($domtree->createElement('calendar_snapshots_url', get_option('hbo_calendar_snapshots_url')));
        $xmlRoot->appendChild($domtree->createElement('bedcounts_url', get_option('hbo_bedcounts_url')));
        $xmlRoot->appendChild($domtree->createElement('manual_charge_url', get_option('hbo_manual_charge_url')));
        $xmlRoot->appendChild($domtree->createElement('generate_payment_link_url', get_option('hbo_generate_payment_link_url')));
        $xmlRoot->appendChild($domtree->createElement('payment_history_url', get_option('hbo_payment_history_url')));
        $xmlRoot->appendChild($domtree->createElement('payment_history_inv_url', get_option('hbo_payment_history_inv_url')));
        $xmlRoot->appendChild($domtree->createElement('process_refunds_url', get_option('hbo_process_refunds_url')));
        $xmlRoot->appendChild($domtree->createElement('refund_history_url', get_option('hbo_refund_history_url')));
        $xmlRoot->appendChild($domtree->createElement('report_settings_url', get_option('hbo_report_settings_url')));
        $xmlRoot->appendChild($domtree->createElement('view_log_url', get_option('hbo_view_log_url')));
        $xmlRoot->appendChild($domtree->createElement('job_history_url', get_option('hbo_job_history_url')));
        $xmlRoot->appendChild($domtree->createElement('job_scheduler_url', get_option('hbo_job_scheduler_url')));
        $xmlRoot->appendChild($domtree->createElement('blacklist_url', get_option('hbo_blacklist_url')));
        $xmlRoot->appendChild($domtree->createElement('online_checkin_url', get_option('hbo_online_checkin_url')));
        $xmlRoot->appendChild($domtree->createElement('redirect_to_url', get_option('hbo_redirect_to_url')));
        $xmlRoot->appendChild($domtree->createElement('log_directory', get_option('hbo_log_directory')));
        $xmlRoot->appendChild($domtree->createElement('log_directory_url', get_option('hbo_log_directory_url')));
        $xmlRoot->appendChild($domtree->createElement('run_processor_cmd', get_option('hbo_run_processor_cmd')));
        $xmlRoot->appendChild($domtree->createElement('reports_help_url', get_option('hbo_reports_help_url')));
    }

    /**
     * Updates the option values for all relevant settings.
     * $optionsArray : array of option name => option values
     */
    function updateOptions($optionsArray) {
        $this->setOptionIfNotEmpty($optionsArray, 'hbo_siteicon_url');
        $this->setOptionIfNotEmpty($optionsArray, 'hbo_housekeeping_url');
        $this->setOptionIfNotEmpty($optionsArray, 'hbo_split_room_report_url');
        $this->setOptionIfNotEmpty($optionsArray, 'hbo_unpaid_deposit_report_url');
        $this->setOptionIfNotEmpty($optionsArray, 'hbo_group_bookings_report_url');
        $this->setOptionIfNotEmpty($optionsArray, 'hbo_guest_comments_report_url');
        $this->setOptionIfNotEmpty($optionsArray, 'hbo_bottom_bunks_report_url');
        $this->setOptionIfNotEmpty($optionsArray, 'hbo_calendar_snapshots_url');
        $this->setOptionIfNotEmpty($optionsArray, 'hbo_bedcounts_url');
        $this->setOptionIfNotEmpty($optionsArray, 'hbo_manual_charge_url');
        $this->setOptionIfNotEmpty($optionsArray, 'hbo_generate_payment_link_url');
        $this->setOptionIfNotEmpty($optionsArray, 'hbo_payment_history_url');
        $this->setOptionIfNotEmpty($optionsArray, 'hbo_payment_history_inv_url');
        $this->setOptionIfNotEmpty($optionsArray, 'hbo_process_refunds_url');
        $this->setOptionIfNotEmpty($optionsArray, 'hbo_refund_history_url');
        $this->setOptionIfNotEmpty($optionsArray, 'hbo_report_settings_url');
        $this->setOptionIfNotEmpty($optionsArray, 'hbo_job_history_url');
        $this->setOptionIfNotEmpty($optionsArray, 'hbo_job_scheduler_url');
        $this->setOptionIfNotEmpty($optionsArray, 'hbo_blacklist_url');
        $this->setOptionIfNotEmpty($optionsArray, 'hbo_online_checkin_url');
        $this->setOptionIfNotEmpty($optionsArray, 'hbo_redirect_to_url');
        $this->setOptionIfNotEmpty($optionsArray, 'hbo_view_log_url');
        $this->setOptionIfNotEmpty($optionsArray, 'hbo_log_directory');
        $this->setOptionIfNotEmpty($optionsArray, 'hbo_log_directory_url');
        $this->setOptionIfNotEmpty($optionsArray, 'hbo_run_processor_cmd');
        $this->setOptionIfNotEmpty($optionsArray, 'hbo_reports_help_url');
    }

    /**
     * Updates the optionName with the associated value in optionsArray
     * if it exists and is not blank.
     * $optionsArray : array of option name => option values
     * $optionName : option name to save
     */
    function setOptionIfNotEmpty($optionsArray, $optionName) {
        if (isset($optionsArray[$optionName]) && $optionsArray[$optionName] != '') {
            update_option($optionName, $optionsArray[$optionName]);
        }
    }

    /** 
     * Writes XML in the following syntax:
     * <view>
     *     <firstdayofweek>0</firstdayofweek>
     *     ...
     * </view>
     */
    function toXml() {
        $domtree = new DOMDocument('1.0', 'UTF-8');
        $this->addSelfToDocument($domtree, $domtree);
        return $domtree->saveXML();
    }

    /**
     * Returns the filename for the stylesheet to use during transform.
     */
    function getXslFilename() {
        return HBO_PLUGIN_DIR. '/include/settings.xsl';
    }
    
}

?>
