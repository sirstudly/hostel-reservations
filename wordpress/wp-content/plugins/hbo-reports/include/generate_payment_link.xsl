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
    <xsl:if test="invoice">
        <xsl:apply-templates select="invoice"/>
    </xsl:if>
    <xsl:if test="not(booking) and not(invoice)">

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

.invoice_label {
  display: inline-block;
  width: 80px;
  font-weight: bold;
}
</style>

<script>
// copies the value in inputElem to the clipboard
// and sets infoMsgElem visible
function copyToClipboard(inputElem, infoMsgElem) {
  /* Select the text field */
  inputElem.select();

  /* Copy the text inside the text field */
  document.execCommand("copy");

  /* Display the info message */
  infoMsgElem.style.display = "block";
}
</script>

        <div id="report-container" class="wrap bookingpage">
            <h2>Generate Payment Links</h2>
    
            <xsl:call-template name="write_inline_js"/>
            <xsl:call-template name="write_inline_css"/>
    
            <div class="wpdevbk body-content booking_form_div">
                <h3>What's this? A new page? What is it for?</h3>
                <p>
                    We now have an online payment portal for guests
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
                <form name="post_option" action="" method="post" id="post_option">
                    <p>
                        <div class="form_label">Booking Ref:
                            <div class="tooltip">Where do I find this?
                                <span class="tooltiptext"><img src="{homeurl}/wp-content/plugins/hbo-reports/img/cloudbeds-reservation.png"/></span>
                            </div>                       
                            <input id="booking_ref" name="booking_ref" class="regular-text code" type="text" style="margin-left: 25px; width:150px;" size="20" value="{booking_ref}" />
                            <a class="btn btn-primary" style="margin-left: 15px;" onclick="generate_payment_link(document.post_option.booking_ref.value, document.post_option.deposit_chk.checked);">Submit</a>
                        </div>
                        <div class="form_label">Request Deposit Only (first night):
                            <input id="deposit_chk" name="deposit_chk" type="checkbox" style="margin-left: 25px;"/>
                        </div>
                    </p>
                    <div style="float: left;" id="ajax_response"><xsl:comment/><!-- ajax response here--></div>
                </form>
                <xsl:if test="payment_history_inv_url">
	                <div style="margin-left: 200px; padding: 20px 0 10px 0; clear: both;"><h3>OR Generate an Invoice Link</h3></div>
	                <p>You may want to request payment from a guest (or ex-guest) but it may not be tied to a booking.
	                   The most common example is to request for postage to be paid before sending a lost item.
	                </p>
	                <p>Enter the details below. This will generate a link that you can email the recipient (this page won't do this for you!). 
	                   Once they navigate to the link you send them below and provide their card details, they will receive a confirmation 
	                   email (if payment is successful) and the reception email account will also be CC'd in. You can also view any pending/completed 
	                   payments in the <a href="{payment_history_inv_url}">Invoice Payment History</a> page.
	                </p>
	                <form name="post_invoice" action="" method="post" id="post_invoice">
	                    <p>
	                        <div>
	                            <span class="invoice_label">Name:</span>
	                            <input id="invoice_name" name="invoice_name" class="regular-text code" type="text" style="margin-left: 25px; width:150px;" size="30" value="{invoice_name}" />
	                        </div>
	                        <div>
	                            <span class="invoice_label">Email:</span>
	                            <input id="invoice_email" name="invoice_email" class="regular-text code" type="text" style="margin-left: 25px; width:150px;" size="30" value="{invoice_email}" />
	                            <span style="margin-left: 25px;" class="invoice_label">Amount (£):</span>
	                            <input id="invoice_amount" name="invoice_amount" class="regular-text code" type="text" style="margin-left: 25px; width:150px;" size="30" value="{invoice_amount}" />
	                        </div>
	                        <div style="margin-top: 10px;">
	                            <span class="invoice_label">Description (appears on transaction):</span>
	                            <textarea id="invoice_description" name="invoice_description" class="regular-text code" style="margin-left: 25px; width: 440px;" maxlength="{payment_description_max_length}"><xsl:comment/></textarea>
	                        </div>
	                        <div style="margin-top: 10px;">
	                            <span class="invoice_label">Staff Notes (not sent to recipient):</span>
	                            <textarea id="invoice_notes" name="invoice_notes" class="regular-text code" style="margin-left: 25px; width: 440px;"><xsl:comment/></textarea>
	                            <a class="btn btn-primary" style="margin-left: 10px; vertical-align: bottom; font-weight: bold;" onclick="generate_invoice_link(document.post_invoice.invoice_name.value, document.post_invoice.invoice_email.value, document.post_invoice.invoice_amount.value, document.post_invoice.invoice_description.value, document.post_invoice.invoice_notes.value);">Submit</a>
	                        </div>
	                    </p>
	                    <div style="float: left;" id="ajax_response_inv"><xsl:comment/><!-- ajax response here--></div>
	                </form>
                </xsl:if>
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
        First Night: £<xsl:value-of select="amount_first_night"/><br/>
        Grand Total: £<xsl:value-of select="grand_total"/><br/>
        Balance Due: £<xsl:value-of select="balance_due"/><br/>
    </div>
    <div style="margin-top: 20px; margin-left: 100px;">
        <h4>Help spread the word:</h4>
        <input id="paymentUrl" type="text" class="regular-text all-copy" readonly="readonly" onclick="copyToClipboard(this, document.getElementById('copied_to_clipboard'))" style="width:400px;" value="{payment_url}"/>
        <p id="copied_to_clipboard" style="display: none; color: red;">Copied to clipboard!</p>
    </div>
</xsl:template>

<xsl:template match="invoice">
    <div style="margin-top: 20px; margin-left: 100px;">
        <h4>Help spread the word:</h4>
        <input id="invPaymentUrl" type="text" class="regular-text all-copy" readonly="readonly" onclick="copyToClipboard(this, document.getElementById('inv_copied_to_clipboard'))" style="width:400px;" value="{payment_url}"/>
        <p id="inv_copied_to_clipboard" style="display: none; color: red;">Copied to clipboard!</p>
    </div>
</xsl:template>

</xsl:stylesheet>