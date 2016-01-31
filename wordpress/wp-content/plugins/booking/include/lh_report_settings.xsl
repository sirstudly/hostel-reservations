<?xml version="1.0" encoding="utf-8" ?>
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

#lilho-container {
    width: 400px;
    margin-bottom: 30px;
}

#hw-container {
    width: 400px;
    margin-bottom: 30px;
}

#hb-container {
    width: 400px;
}

.shadow {
    padding: 10px;
    border: 2px solid #f0f0f0;
    border-bottom: 4px solid #ccc;
    -webkit-border-radius: 5px;
    -moz-border-radius: 5px;
    border-radius: 5px;
}
</style>

<script type="text/javascript">
jQuery(document).ready( function(){

    // allow user to show/hide passwords
    // http://www.experts-exchange.com/articles/19779/Passwords-in-HTML-Forms-Allow-the-Client-to-Show-or-Hide.html
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

    jQuery("#hb_pwcheck").click(function(){
        if (jQuery("#hb_pwcheck").is(":checked"))
        {
            jQuery("#hb_password").clone()
                .attr("type", "text").insertAfter("#hb_password")
                .prev().remove();
        }
        else
        {
            jQuery("#hb_password").clone()
                .attr("type","password").insertAfter("#hb_password")
                .prev().remove();
        }
    });

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
});
</script>
    
    <xsl:apply-templates select="settings" />
</xsl:template>

<xsl:template match="settings">

    <div id="report-container" class="wrap bookingpage wpdevbk">
    <form name="post_option" action="" method="post" id="post_option">

        <h2>Report Settings</h2> 

        <div style="font-style:italic;">Note: It may take up to a minute to verify when saving the settings below.</div>

        <div id="lilho-container" class="shadow">
            <h3>Little Hotelier</h3> 
            <table class="form-table">
                <tbody>
                    <tr valign="top">
                        <th scope="row"><label for="lilho_username">Username:</label></th>
                        <td><input id="lilho_username" name="hbo_lilho_username" class="regular-text code" type="text" autocomplete="false" style="width:200px;" size="75" value="{hbo_lilho_username}"/></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="lilho_password">Password:</label></th>
                        <td><input id="lilho_password" name="hbo_lilho_password" class="regular-text code" type="password" autocomplete="new-password" style="width:200px;" size="75" value="{hbo_lilho_password}" /><br/>
                            <input type="checkbox" id="lh_pwcheck" /> Show Password</td>
                    </tr>
                </tbody>
            </table>

            <div class="btn-container">
                <div style="float: left;" id="ajax_respond_lh"><xsl:comment/><!-- ajax response here--></div>
                <a id="btn_save_lilho" class="btn btn-primary" style="float: right;" onclick="save_little_hotelier_settings(document.post_option.lilho_username.value, document.post_option.lilho_password.value); this.disabled=true;">Validate &amp; Save</a>
            </div>
        </div>

        <div id="hw-container" class="shadow">
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
                <a id="btn_save_hw" class="btn btn-primary" style="float: right;" onclick="save_hostelworld_settings(document.post_option.hw_username.value, document.post_option.hw_password.value); this.disabled=true;">Validate &amp; Save</a>
            </div>
        </div>

        <div id="hb-container" class="shadow">
            <h3>Hostelbookers</h3> 
            <table class="form-table">
                <tbody>
                    <tr valign="top">
                        <th scope="row"><label for="hb_username">Username:</label></th>
                        <td><input id="hb_username" name="hbo_hb_username" class="regular-text code" type="text" autocomplete="false" style="width:200px;" size="75" value="{hbo_hb_username}"/></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="hb_password">Password:</label></th>
                        <td><input id="hb_password" name="hbo_hb_password" class="regular-text code" type="password" autocomplete="new-password" style="width:200px;" size="75" value="{hbo_hb_password}" /><br/>
                            <input type="checkbox" id="hb_pwcheck" /> Show Password</td>
                    </tr>
                </tbody>
            </table>

            <div class="btn-container">
                <div style="float: left;" id="ajax_respond_hb"><xsl:comment/><!-- ajax response here--></div>
                <a id="btn_save_hb" class="btn btn-primary" style="float: right;" onclick="save_hostelbookers_settings(document.post_option.hb_username.value, document.post_option.hb_password.value); this.disabled=true;">Validate &amp; Save</a>
            </div>
        </div>

    </form>
    </div>

</xsl:template>

</xsl:stylesheet>