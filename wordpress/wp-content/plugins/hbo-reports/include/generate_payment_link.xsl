<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<xsl:output method="html" omit-xml-declaration="yes" encoding="UTF-8"/>

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->
<xsl:include href="inline_scripts.xsl"/>

<xsl:template match="view">

    <xsl:if test="booking">
        <xsl:apply-templates select="booking"/>
    </xsl:if>
    <xsl:if test="not(booking)">

<style media="screen" type="text/css">
.body-content {
    padding-top: 20px;
    padding-left: 100px;
    width: 800px;
}
.form_label {
    padding-left: 40px;
    font-size: 12px;
    font-weight: bold;
    font-style: normal;
}

.tooltip {
  position: relative;
  display: inline-block;
  border-bottom: 1px dotted black;
  font-size: 10px;
  font-weight: normal;
}

.tooltip .tooltiptext {
  visibility: hidden;
  color: #fff;
  text-align: center;
  border-radius: 6px;
  padding: 5px 0;
  
  /* Position the tooltip */
  position: absolute;
  z-index: 1;
  top: -100px;
  left: 105%;
}

.tooltip:hover .tooltiptext {
  visibility: visible;
}

.matched_booking {
    border-style: solid;
    border-radius: 6px;
    border-width: 1px;
    margin-left: 100px;
    padding: 10px;
    background-color: #e3e3e3;
}

/* allows field to be selectable */ 
.all-copy {
  -webkit-touch-callout:default;
  -webkit-user-select:text;
  -khtml-user-select: text;
  -moz-user-select:text;
  -ms-user-select:text;
  user-select:text;
}

.wpdevbk input[readonly] {
  cursor: text;
}
</style>

<script>
function copyToClipboard() {
  /* Get the text field */
  var copyText = document.getElementById("paymentUrl");

  /* Select the text field */
  copyText.select();

  /* Copy the text inside the text field */
  document.execCommand("copy");

  /* Display the info message */
  document.getElementById("copied_to_clipboard").style.display = "block";
}
</script>

        <div id="report-container" class="wrap bookingpage">
            <h2>Generate Payment Links</h2>
    
            <xsl:call-template name="write_inline_js"/>
            <xsl:call-template name="write_inline_css"/>
    
            <div class="wpdevbk body-content booking_form_div">
                <h3>What's this? A new page? What is it for?</h3>
                <p>
                    We're currently testing an online payment portal for guests
                    to pay for their booking (e.g. for group bookings, unpaid deposits, etc..) without
                    resorting to calling us or (gasp!) emailing their card details to us!
                </p>
                <h3>How does it work?</h3>
                <p>You have a guest who wants/needs to pay for the remaining balance on their booking.
                   So, you come here and enter the booking reference below and click Submit.
                   This will then generate a link that you can send to the guest (via email). They click on
                   the link and it redirects to our (very secure) payment portal where they can put it their
                   card details and the amount they want to pay.
                </p>
                <p>You can also redirect them to <a href="https://pay.macbackpackers.com/">https://pay.macbackpackers.com/</a> and
                   let them enter their details themselves. Last name on booking must match exactly though; accents and everything.
                </p>
                <p>Successful payments will be automatically added to their "Folio" page in Cloudbeds.
                   Multiple guests (in a group) can use the same link to pay separately if they wish.
                </p>
                <p style="border-style: dotted; border-width: 1px; padding: 5px;">Note: This is currently pointing to the test payment gateway, but it's using our real
                   Cloudbeds account so if you're testing, use it against a test booking you've created with <i>your</i>
                   email address as any confirmation emails will be sent there!
                </p>
                <p>Here are some test cards you can use:
                   <div style="padding-left: 50px;">
                       VISA: 4929000005559<br/>
                       Mastercard: 5404000000000043<br/>
                       Expiry: any<br/>
                       Security Code: 123
                   </div>
                </p>
                <form name="post_option" action="" method="post" id="post_option">
                    <p>
                        <div class="form_label">Booking Ref:
                            <div class="tooltip">Where do I find this?
                                <span class="tooltiptext"><img src="{homeurl}/wp-content/plugins/hbo-reports/img/cloudbeds-reservation.png"/></span>
                            </div>                       
                            <input id="booking_ref" name="booking_ref" class="regular-text code" type="text" style="margin-left: 25px; width:150px;" size="20" value="{booking_ref}" />
                            <a class="btn btn-primary" style="margin-left: 15px;" onclick="generate_payment_link(document.post_option.booking_ref.value);">Submit</a>
                       </div>
                    </p>
                    <div style="float: left;" id="ajax_response"><xsl:comment/><!-- ajax response here--></div>
                </form>
            </div>
        </div>
    </xsl:if>
</xsl:template>

<xsl:template match="booking">

    <div class="matched_booking">
        Booking Ref: <xsl:value-of select="identifier"/><br/>
        <xsl:if test="third_party_identifier != ''">Source Reservation ID: <xsl:value-of select="third_party_identifier"/><br/></xsl:if>
        Guest Name: <xsl:value-of select="name"/><br/>
        Email: <xsl:value-of select="email"/><br/>
        Booking Date: <xsl:value-of select="booking_date_server_time"/><br/>
        Status: <xsl:value-of select="status"/><br/>
        Checkin Date: <xsl:value-of select="checkin_date"/><br/>
        Checkout Date: <xsl:value-of select="checkout_date"/><br/>
        Number of Guests: <xsl:value-of select="num_guests"/><br/>
        Grand Total: £<xsl:value-of select="grand_total"/><br/>
        Balance Due: £<xsl:value-of select="balance_due"/><br/>
    </div>
    <div style="margin-top: 20px; margin-left: 100px;">
        <h4>Help spread the word:</h4>
        <input id="paymentUrl" type="text" class="regular-text all-copy" readonly="readonly" onclick="copyToClipboard()" style="width:400px;" value="{payment_url}"/>
        <p id="copied_to_clipboard" style="display: none; color: red;">Copied to clipboard!</p>
    </div>
</xsl:template>

</xsl:stylesheet>