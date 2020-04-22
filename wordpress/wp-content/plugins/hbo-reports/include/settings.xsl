<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->
<xsl:template match="/view">

    <div id="ajax_respond"><xsl:comment/></div>
    <div class="clear"><xsl:comment/></div>
    <div id="ajax_working"><xsl:comment/></div>
    <div id="poststuff" class="metabox-holder" style="margin-top:0px;">
        <div class="clear" style="height:10px;"><xsl:comment/></div>
        <form name="post_option" action="" method="post" id="post_option" >

            <div style="width:64%; float:left;margin-right:1%;">
                <div class='meta-box'>
                    <div id="bk_general_settings_main" class="postbox"> 
                        <div title="Click to toggle" class="handlediv" onclick="javascript:verify_window_opening(1, 'bk_general_settings_main');"><br/></div>
                        <h3 class='hndle'><span>Main</span></h3> 
                        <div class="inside">
                            <table class="form-table">
                                <tbody>
                                    <tr valign="top">
                                        <th scope="row"><label for="siteicon_url">Site Icon URL:</label></th>
                                        <td><input id="siteicon_url"  name="hbo_siteicon_url" class="regular-text code" type="text" style="width:350px;" size="145" value="{siteicon_url}" /></td>
                                    </tr>  
                                    <tr valign="top">
                                        <th colspan="2" scope="row"><label for="backoffice_urls">URLs of pages to override when displaying on public site:</label><br/>
                                            <span class="description">Create menus/pages for these URLs and they will be replaced with their associated content</span>
                                        </th>
                                    </tr>
                                    <tr valign="top">
                                        <th scope="row"><label for="housekeeping_url">Housekeeping Page</label></th>
                                        <td><input id="housekeeping_url"  name="hbo_housekeeping_url" class="regular-text code" type="text" style="width:350px;" size="145" value="{housekeepingurl}" /></td>
                                    </tr>  
                                    <tr valign="top">
                                        <th scope="row"><label for="redirect_to_url">Redirect Page</label></th>
                                        <td><input id="redirect_to_url"  name="hbo_redirect_to_url" class="regular-text code" type="text" style="width:350px;" size="145" value="{redirect_to_url}" /></td>
                                    </tr>  
                                    <tr valign="top">
                                        <th scope="row"><label for="reports_url">Split Room Reservation Report</label></th>
                                        <td><input id="hbo_split_room_report_url"  name="hbo_split_room_report_url" class="regular-text code" type="text" style="width:350px;" size="145" value="{split_room_report_url}" /></td>
                                    </tr>  
                                    <tr valign="top">
                                        <th scope="row"><label for="unpaid_deposit_report_url">Unpaid Deposit Report</label></th>
                                        <td><input id="unpaid_deposit_report_url"  name="hbo_unpaid_deposit_report_url" class="regular-text code" type="text" style="width:350px;" size="145" value="{unpaid_deposit_report_url}" /></td>
                                    </tr>  
                                    <tr valign="top">
                                        <th scope="row"><label for="group_bookings_report_url">Group Bookings</label></th>
                                        <td><input id="group_bookings_report_url"  name="hbo_group_bookings_report_url" class="regular-text code" type="text" style="width:350px;" size="145" value="{group_bookings_report_url}" /></td>
                                    </tr>  
                                    <tr valign="top">
                                        <th scope="row"><label for="bedcounts_url">Bedcount Report</label></th>
                                        <td><input id="bedcounts_url"  name="hbo_bedcounts_url" class="regular-text code" type="text" style="width:350px;" size="145" value="{bedcounts_url}" /></td>
                                    </tr>  
                                    <tr valign="top">
                                        <th scope="row"><label for="guest_comments_report_url">Guest Comments</label></th>
                                        <td><input id="guest_comments_report_url"  name="hbo_guest_comments_report_url" class="regular-text code" type="text" style="width:350px;" size="145" value="{guest_comments_report_url}" /></td>
                                    </tr>  
                                    <tr valign="top">
                                        <th scope="row"><label for="manual_charge_url">Manual Charges</label></th>
                                        <td><input id="manual_charge_url"  name="hbo_manual_charge_url" class="regular-text code" type="text" style="width:350px;" size="145" value="{manual_charge_url}" /></td>
                                    </tr>  
                                    <tr valign="top">
                                        <th scope="row"><label for="generate_payment_link_url">Generate Payment Link</label></th>
                                        <td><input id="generate_payment_link_url"  name="hbo_generate_payment_link_url" class="regular-text code" type="text" style="width:350px;" size="145" value="{generate_payment_link_url}" /></td>
                                    </tr>  
                                    <tr valign="top">
                                        <th scope="row"><label for="payment_history_url">Booking Payment History Link</label></th>
                                        <td><input id="payment_history_url"  name="hbo_payment_history_url" class="regular-text code" type="text" style="width:350px;" size="145" value="{payment_history_url}" /></td>
                                    </tr>  
                                    <tr valign="top">
                                        <th scope="row"><label for="payment_history_inv_url">Invoice Payment History Link</label></th>
                                        <td><input id="payment_history_inv_url"  name="hbo_payment_history_inv_url" class="regular-text code" type="text" style="width:350px;" size="145" value="{payment_history_inv_url}" /></td>
                                    </tr>  
                                    <tr valign="top">
                                        <th scope="row"><label for="process_refunds_url">Process Refunds</label></th>
                                        <td><input id="process_refunds_url"  name="hbo_process_refunds_url" class="regular-text code" type="text" style="width:350px;" size="145" value="{process_refunds_url}" /></td>
                                    </tr>  
                                    <tr valign="top">
                                        <th scope="row"><label for="report_settings_url">Report Settings</label></th>
                                        <td><input id="report_settings_url"  name="hbo_report_settings_url" class="regular-text code" type="text" style="width:350px;" size="145" value="{report_settings_url}" /></td>
                                    </tr>  
                                    <tr valign="top">
                                        <th scope="row"><label for="view_log_url">View Job Log</label></th>
                                        <td><input id="view_log_url"  name="hbo_view_log_url" class="regular-text code" type="text" style="width:350px;" size="145" value="{view_log_url}" /></td>
                                    </tr>  
                                    <tr valign="top">
                                        <th scope="row"><label for="job_history_url">Job History</label></th>
                                        <td><input id="job_history_url"  name="hbo_job_history_url" class="regular-text code" type="text" style="width:350px;" size="145" value="{job_history_url}" /></td>
                                    </tr>  
                                    <tr valign="top">
                                        <th scope="row"><label for="job_scheduler_url">Job Scheduler</label></th>
                                        <td><input id="job_scheduler_url"  name="hbo_job_scheduler_url" class="regular-text code" type="text" style="width:350px;" size="145" value="{job_scheduler_url}" /></td>
                                    </tr>  
                                    <tr valign="top">
                                        <th scope="row"><label for="log_directory">Log Directory</label></th>
                                        <td><input id="log_directory"  name="hbo_log_directory" class="regular-text code" type="text" style="width:350px;" size="145" value="{log_directory}" /></td>
                                    </tr>  
                                    <tr valign="top">
                                        <th scope="row"><label for="log_directory_url">Log Directory URL</label></th>
                                        <td><input id="log_directory_url"  name="hbo_log_directory_url" class="regular-text code" type="text" style="width:350px;" size="145" value="{log_directory_url}" /></td>
                                    </tr>  
                                    <tr valign="top">
                                        <th scope="row"><label for="run_processor_cmd">Run Processor Command</label></th>
                                        <td><input id="run_processor_cmd"  name="hbo_run_processor" class="regular-text code" type="text" style="width:350px;" size="145" value="{run_processor_cmd}" /></td>
                                    </tr>  
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="clear" style="height:10px;"><xsl:comment/></div>
                <input class="button-primary" style="float:right;" type="submit" value="Save Changes" name="Submit"/>
                <div class="clear" style="height:10px;"><xsl:comment/></div>

            </div>

            <div style="width:35%; float:left;">
                <div class='meta-box'>
                    <div id="bk_general_settings_info" class="gdrgrid postbox"> 
                        <div title="Click to toggle" class="handlediv" onclick="javascript:verify_window_opening(1, 'bk_general_settings_info');"><br/></div>
                        <h3 class='hndle'><span>Information</span></h3>
                        <div class="inside">
                            <style type="text/css">
                                #dashboard_bk {
                                    width:100%;
                                }
                                #dashboard_bk .bk_dashboard_section {
                                    float:left;
                                    margin:0px;
                                    padding:0px;
                                    width:100%;
                                }
                                #dashboard-widgets-wrap #dashboard_bk .bk_dashboard_section {
                                    width:49%;
                                }
                                #dashboard-widgets-wrap #dashboard_bk .bk_right {
                                    float:right
                                }

                                #dashboard_bk .bk_header {
                                    color:#777777;
                                    font-family:Georgia,"Times New Roman","Bitstream Charter",Times,serif;
                                    font-size:16px;
                                    font-style:italic;
                                    line-height:24px;
                                    margin:5px;
                                    padding:0 10px;
                                }

                                #dashboard_bk .bk_table {
                                    background:none repeat scroll 0 0 #FFFBFB;
                                    border-bottom:1px solid #ECECEC;
                                    border-top:1px solid #ECECEC;
                                    margin:6px 0 0 6px;
                                    padding:2px 10px;
                                    width:95%;
                                    -border-radius:4px;
                                    -moz-border-radius:4px;
                                    -webkit-border-radius:4px;
                                    -moz-box-shadow:0 0 2px #C5C3C3;
                                    -webkit-box-shadow:0 0 2px #C5C3C3;
                                    -box-shadow:0 0 2px #C5C3C3;
                                }

                                #dashboard_bk table.bk_table td{
                                    border-top:1px solid #DDDDDD;
                                    line-height:19px;
                                    padding:4px 0px 4px 10px;
                                    font-size:12px;
                                }
                                #dashboard_bk table.bk_table tr.first td{
                                    border:none;

                                }
                                #dashboard_bk table.bk_table tr td.first{
                                    text-align:center;
                                    padding:4px 0px;
                                }
                                #dashboard_bk table.bk_table tr td a {
                                    text-decoration: none;
                                }
                                #dashboard_bk table.bk_table tr td a span{
                                    font-size:18px;
                                    font-family: Georgia,"Times New Roman","Bitstream Charter",Times,serif;
                                }
                                #dashboard_bk table.bk_table td.bk_spec_font a{
                                    font-family: Georgia,"Times New Roman","Bitstream Charter",Times,serif;
                                    font-size:14px;
                                }

                                #dashboard_bk table.bk_table td.bk_spec_font {
                                    font-family: Georgia,"Times New Roman","Bitstream Charter",Times,serif;
                                    font-size:13px;
                                }


                                #dashboard_bk table.bk_table td.pending a{
                                    color:#E66F00;
                                }

                                #dashboard_bk table.bk_table td.new-bookings a{
                                    color:red;
                                }

                                #dashboard_bk table.bk_table td.actual-bookings a{
                                    color:green;
                                }

                                #dashboard-widgets-wrap #dashboard_bk .border_orrange, #dashboard_bk .border_orrange {
                                    border:1px solid #EEAB26;
                                    background: #FFFBCC;
                                    padding:0px;
                                    width:98%;  clear:both;
                                    margin:5px 5px 20px;
                                    border-radius:10px;
                                    -webkit-border-radius:10px;
                                    -moz-border-radius:10px;
                                }
                                #dashboard_bk .bk_dashboard_section h4 {
                                    font-size:13px;
                                    margin:10px 4px;
                                }
                                #bk_errror_loading {
                                        text-align: center;
                                        font-style: italic;
                                        font-size:11px;
                                }
                            </style>
            
                            <div id="dashboard_bk">
                                <div class="bk_dashboard_section" >
                                    <span class="bk_header">Current version:</span>
                                    <table class="bk_table">
                                        <tr class="first">
                                            <td style="width:35%;text-align: right;;" class="">Version:</td>
                                            <td style="text-align: left; font-weight: bold;" class="bk_spec_font">0.2</td>
                                        </tr>
                                    </table>
                                </div>

                            </div>
                            <div style="clear:both;"><xsl:comment/></div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>   

</xsl:template>

</xsl:stylesheet>