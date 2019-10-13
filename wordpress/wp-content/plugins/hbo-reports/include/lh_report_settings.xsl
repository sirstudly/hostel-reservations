﻿<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->
<xsl:template match="/view">

<style media="screen" type="text/css">

.form-table {
    border-style: none; 
}

.form-table th label {
    font-size: 12px;
    font-weight: bold;
    font-style: normal;
    line-height: 25px;
}

.form-table td {
    border-top: initial;
}

#report-container {
    font-family: sans-serif;
    margin-left: 20px;
    margin-bottom: 20px;
}

#report-container h3 {
    margin: 10px 0;
}

.btn-container {
    height: 30px; 
    width: 100%;
}

.lilho-container {
    width: 400px;
    margin-bottom: 30px;
}

.cloudbeds-container {
    width: 400px;
    margin-bottom: 30px;
}

.hw-container {
    width: 400px;
    margin-bottom: 30px;
}

#group-report-container {
    width: 400px;
    margin-bottom: 30px;
}

.shadow {
    padding: 10px;
    border: 2px solid #f0f0f0;
    border-bottom: 4px solid #ccc;
    -webkit-border-radius: 5px;
    -moz-border-radius: 5px;
    border-radius: 5px;
}

.mail_response_select {
    width: 30%;
    margin: 5px 5px;
    float: left;
}

.mail_response_select input[type="checkbox"] {
    margin: 0 10px;
}

#cloudbeds_2facode label {
    font-size: 12px;
    font-weight: bold;
    font-style: normal;
    line-height: 25px;
}

</style>

<script type="text/javascript">
jQuery(document).ready( function(){

    // allow user to show/hide passwords
    // http://www.experts-exchange.com/articles/19779/Passwords-in-HTML-Forms-Allow-the-Client-to-Show-or-Hide.html
    jQuery("#lh_pwcheck").click(function(){
        if (jQuery("#lh_pwcheck").is(":checked"))
        {
            jQuery("#lilho_password").clone()
                .attr("type", "text").insertAfter("#lilho_password")
                .prev().remove();
        }
        else
        {
            jQuery("#lilho_password").clone()
                .attr("type","password").insertAfter("#lilho_password")
                .prev().remove();
        }
    });

    jQuery("#hw_pwcheck").click(function(){
        if (jQuery("#hw_pwcheck").is(":checked"))
        {
            jQuery("#hw_password").clone()
                .attr("type", "text").insertAfter("#hw_password")
                .prev().remove();
        }
        else
        {
            jQuery("#hw_password").clone()
                .attr("type","password").insertAfter("#hw_password")
                .prev().remove();
        }
    });

    jQuery("#agoda_pwcheck").click(function(){
        if (jQuery("#agoda_pwcheck").is(":checked"))
        {
            jQuery("#agoda_password").clone()
                .attr("type", "text").insertAfter("#agoda_password")
                .prev().remove();
        }
        else
        {
            jQuery("#agoda_password").clone()
                .attr("type","password").insertAfter("#agoda_password")
                .prev().remove();
        }
    });
});
</script>
    
    <xsl:apply-templates select="settings" />
</xsl:template>

<xsl:template match="settings">

    <div id="report-container" class="wrap bookingpage wpdevbk">
    <form name="post_option" action="" method="post" id="post_option">

        <h2>Report Settings</h2> 

        <xsl:if test="../property_manager != 'cloudbeds'">
        <div class="shadow lilho-container">
            <h3>Little Hotelier</h3> 
            <table class="form-table">
                <tbody>
                    <tr valign="top">
                        <th scope="row"><label for="lilho_username">Email:</label></th>
                        <td><input id="lilho_username" name="hbo_lilho_username" class="regular-text code" type="text" autocomplete="false" style="width:200px;" size="75" value="{hbo_lilho_username}"/></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="lilho_password">Password:</label></th>
                        <td><input id="lilho_password" name="hbo_lilho_password" class="regular-text code" type="password" autocomplete="new-password" style="width:200px;" size="75" value="{hbo_lilho_password}" /><br/>
                            <input type="checkbox" id="lh_pwcheck" /> Show Password</td>
                    </tr>
                    <tr valign="top">
                        <td scope="row" colspan="2">
                            <div style="margin-top: 10px;">
                            Due to some weird scripting on the login page that I haven't been able to figure out, we'll also need a valid session ID from a logged in browser.<br/>
                            In <strong>Firefox</strong>, press F12, click the Storage tab.<br/>
                            In <strong>Chrome</strong>, press F12, click the Application tab.<br/>
                            Under Cookies, https://app.littlehotelier.com, copy the value with the name <code>_littlehotelier_session</code> and paste it here.
                            </div>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="lilho_session">Session ID:</label></th>
                        <td><input id="lilho_session" name="hbo_lilho_session" class="regular-text code" type="text" autocomplete="false" style="width:200px;" size="75" value="{hbo_lilho_session}" /></td>
                    </tr>
                </tbody>
            </table>

            <div class="btn-container">
                <div style="float: left;" id="ajax_respond_lh"><xsl:comment/><!-- ajax response here--></div>
                <a id="btn_save_lilho" class="btn btn-primary" style="float: right;" onclick="save_little_hotelier_settings(document.post_option.lilho_username.value, document.post_option.lilho_password.value, document.post_option.lilho_session.value); this.disabled=true;">Save</a>
            </div>
        </div>
        </xsl:if>

        <xsl:if test="../property_manager = 'cloudbeds'">
        <div class="shadow cloudbeds-container">
            <h3>Cloudbeds</h3> 
            <table class="form-table">
                <tbody>
                    <tr valign="top">
                        <td scope="row" colspan="2">
                            <p><em>Note: You shouldn't have to update this as I have a special <code>#ronbot</code> user account dedicated for this purpose.</em></p>
                            We'll need to hijack a currently logged-in session that is not currently being used.
                            Start by opening a new "Private Browsing" window and login with the user you want the reports to run under.<br/>
                            Press F12 to open "Developer Tools" in either Chrome/Firefox. In Cloudbeds, click on "My User Profile" (top-right corner).<br/>
                            In the Developer Tools frame, on the Network tab, find the <code>user_have_ccp_view_permission</code> request.<br/>
                            In <strong>Firefox</strong>, right-click the row, Copy, Copy Request Headers.<br/>
                            In <strong>Chrome</strong>, right-click the row, Copy, Copy as cURL (bash).<br/>
                            Now paste the contents here and close the (Cloudbeds) browser window without logging out.
                        </td>
                    </tr>
                    <tr valign="top">
                        <th colspan="2" scope="row"><label for="cloudbeds_req_headers">Request Headers:</label></th>
                    </tr>
                    <tr valign="top">
                        <td colspan="2" scope="row"><textarea id="cloudbeds_req_headers" name="hbo_cloudbeds_req_headers" class="regular-text code" style="width: 97%;"><xsl:comment/></textarea></td>
                    </tr>
                </tbody>
            </table>

            <div class="btn-container">
                <div style="float: left;" id="ajax_respond_cb"><xsl:comment/><!-- ajax response here--></div>
                <a id="btn_save_cloudbeds" class="btn btn-primary" style="float: right;" onclick="save_cloudbeds_settings(document.post_option.cloudbeds_req_headers.value); this.disabled=true;">Save</a>
            </div>
            <h4>Alternatively:</h4>
            <div class="btn-container">
                <a id="btn_reset_cloudbeds" class="btn btn-primary" style="float: right;" onclick="reset_cloudbeds_login(); jQuery(this).hide(); jQuery('#cloudbeds_2facode').show();">Reset Session</a>
                <div id="cloudbeds_2facode" style="display:none;"> 
	                <div style="float:left;">
	                    <label for="cb_2fa_code">2FA Code:</label>
	                </div>
	                <div style="float: right;">
	                    <input id="cb_2fa_code" name="hbo_cloudbeds_2facode" class="regular-text code" type="text" autocomplete="false" style="width:100px; margin-right: 10px; vertical-align: top;" size="10" value=""/>
	                    <a id="btn_save_cb_2facode" class="btn btn-primary" onclick="update_cloudbeds_2facode(document.post_option.hbo_cloudbeds_2facode.value); this.disabled=true;">Confirm</a>
	                </div>
	            </div>
            </div>
        </div>
        </xsl:if>

        <div class="shadow hw-container">
            <h3>Hostelworld</h3> 
            <table class="form-table">
                <tbody>
                    <tr valign="top">
                        <th scope="row"><label for="hw_username">Username:</label></th>
                        <td><input id="hw_username" name="hbo_hw_username" class="regular-text code" type="text" autocomplete="false" style="width:200px;" size="75" value="{hbo_hw_username}"/></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="hw_password">Password:</label></th>
                        <td><input id="hw_password" name="hbo_hw_password" class="regular-text code" type="password" autocomplete="new-password" style="width:200px;" size="75" value="{hbo_hw_password}" /><br/>
                            <input type="checkbox" id="hw_pwcheck" /> Show Password</td>
                    </tr>
                </tbody>
            </table>

            <div class="btn-container">
                <div style="float: left;" id="ajax_respond_hw"><xsl:comment/><!-- ajax response here--></div>
                <a id="btn_save_hw" class="btn btn-primary" style="float: right;" onclick="save_hostelworld_settings(document.post_option.hw_username.value, document.post_option.hw_password.value); this.disabled=true;">Save</a>
            </div>
        </div>

        <div class="shadow hw-container">
            <h3>Agoda</h3> 
            <table class="form-table">
                <tbody>
                    <tr valign="top">
                        <th scope="row"><label for="agoda_username">Username:</label></th>
                        <td><input id="agoda_username" name="hbo_agoda_username" class="regular-text code" type="text" autocomplete="false" style="width:200px;" size="75" value="{hbo_agoda_username}"/></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="agoda_password">Password:</label></th>
                        <td><input id="agoda_password" name="hbo_agoda_password" class="regular-text code" type="password" autocomplete="new-password" style="width:200px;" size="75" value="{hbo_agoda_password}" /><br/>
                            <input type="checkbox" id="agoda_pwcheck" /> Show Password</td>
                    </tr>
                </tbody>
            </table>

            <div class="btn-container">
                <div style="float: left;" id="ajax_respond_agoda"><xsl:comment/><!-- ajax response here--></div>
                <a id="btn_save_agoda" class="btn btn-primary" style="float: right;" onclick="save_agoda_settings(document.post_option.agoda_username.value, document.post_option.agoda_password.value); this.disabled=true;">Save</a>
            </div>
        </div>

        <div id="group-report-container" class="shadow">
            <h3>Group Bookings Report</h3> 
            <table class="form-table">
                <tbody>
                    <tr valign="top">
                        <th scope="row"><label for="group_booking_size">Group Booking Size:</label></th>
                        <td><input id="group_booking_size" name="hbo_group_booking_size" class="regular-text code" type="text" style="width:200px;" size="75" value="{hbo_group_booking_size}"/></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="include_5_guests_in_6bed_dorm">Include Bookings of 5 Guests in a 6 Bed Dorm:</label></th>
                        <td><input type="checkbox" id="include_5_guests_in_6bed_dorm" name="hbo_include_5_guests_in_6bed_dorm">
                                <xsl:if test="hbo_include_5_guests_in_6bed_dorm = 'true'">
                                    <xsl:attribute name="checked">checked</xsl:attribute>
                                </xsl:if>
                            </input>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="btn-container">
                <div style="float: left;" id="ajax_respond_group_bookings_rpt"><xsl:comment/><!-- ajax response here--></div>
                <a id="btn_save_group_rpt_settings" class="btn btn-primary" style="float: right;" onclick="save_group_bookings_report_settings(document.post_option.group_booking_size.value, document.post_option.include_5_guests_in_6bed_dorm.checked); this.disabled=true;">Save</a>
            </div>
        </div>

    <xsl:if test="starts-with(hbo_lilho_username, '__DISABLED__castlerock')">
        <div class="shadow hw-container" style="width: 600px;">
            <h3>Checked-out Guest Response Email (Template)</h3> 
            <p>If present, the following will be replaced in the subject/body: <br/>
               <ul>
                   <li><strong>%%GUEST_FIRSTNAME%%</strong> - guest's first name</li>
                   <li><strong>%%GUEST_LASTNAME%%</strong> - guest's last name</li>
               </ul>
            </p>
            <p>Applies only to the following bookings:<br/>
                <span class="mail_response_select"><input type="checkbox" disabled="disabled" checked="checked"/>Hostelworld</span>
                <span class="mail_response_select"><input type="checkbox" disabled="disabled"/>Booking.com</span>
                <span class="mail_response_select"><input type="checkbox" disabled="disabled"/>Expedia</span>
                <span class="mail_response_select"><input type="checkbox" disabled="disabled"/>Agoda</span>
                <span class="mail_response_select"><input type="checkbox" disabled="disabled"/>Little Hotelier</span>
            </p>
            <table class="form-table" style="width: 100%;">
                <tbody>
                    <tr valign="top">
                        <th scope="row"><label for="guest_email_subject">Subject:</label></th>
                        <td><input id="guest_email_subject" name="hbo_guest_email_subject" class="regular-text code" type="text" autocomplete="false" style="width:400px;" size="75" value="{hbo_guest_email_subject}"/></td>
                    </tr>
                    <tr valign="top">
                        <td colspan="2"><textarea id="guest_email_template" name="hbo_guest_email_template" class="regular-text code" style="width: 97%;"><xsl:value-of select="hbo_guest_email_template"/></textarea></td>
                    </tr>
                </tbody>
            </table>

            <div class="btn-container">
                <div style="float: left;" id="ajax_respond_guest_email_template"><xsl:comment/><!-- ajax response here--></div>
                <div style="float:right;">
                    <a id="btn_save_guest_email_template" class="btn btn-primary" onclick="save_guest_checkout_template(document.post_option.guest_email_subject.value, document.post_option.guest_email_template.value); this.disabled=true;">Save</a>
                    <a class="btn btn-primary" style="margin-left: 10px;" onclick="jQuery('#test_send_email_dialog').dialog({{width:380, height:210}});">Send Test Email...</a>
                </div>
            </div>
        </div>
    </xsl:if>

    <div id="test_send_email_dialog" title="Send a Test Email" style="display:none;">
        <table class="form-table" style="width: 100%;">
            <tbody>
                <tr valign="top">
                    <th scope="row"><label for="test_email_first_name"><div style="width: 100px;">First Name:</div></label></th>
                    <td><input id="test_email_first_name" type="text" autocomplete="false" size="20"/></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="test_email_last_name">Last Name:</label></th>
                    <td><input id="test_email_last_name" type="text" autocomplete="false" size="20"/></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="test_email_recipient">Email:</label></th>
                    <td><input id="test_email_recipient" type="text" autocomplete="false" size="75" style="width:200px;"/></td>
                </tr>
                <tr>
                    <td colspan="2">
                        <div style="float:right;">
                            <a id="btn_send_test_response_email" style="color: #fff; background-color: #006dcc;background-image: -moz-linear-gradient(center top , #08c, #04c);padding: 4px 10px;border-radius: 4px;border-width: 1px; cursor: pointer; text-decoration: none;" onclick="send_test_response_email(jQuery('#test_email_first_name').val(), jQuery('#test_email_last_name').val(), jQuery('#test_email_recipient').val()); jQuery('#test_send_email_dialog').dialog('close');">Send</a>
                            <a style="color: #fff; background-color: #006dcc;background-image: -moz-linear-gradient(center top , #08c, #04c);padding: 4px 10px;border-radius: 4px;border-width: 1px; cursor: pointer; text-decoration: none; margin-left:10px;" onclick="jQuery('#test_send_email_dialog').dialog('close');">Cancel</a>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    </form>
    </div>

</xsl:template>

</xsl:stylesheet>