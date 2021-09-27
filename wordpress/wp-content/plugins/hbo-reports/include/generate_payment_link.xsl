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

.form_label {
    padding-left: 40px;
    font-size: 14px;
    font-weight: bold;
    font-style: normal;
}

.invoice_label {
    width: 80px;
}

.tooltip_label {
    position: relative;
    display: inline-block;
    border-bottom: 1px dotted black;
    font-size: 10px;
    font-weight: normal;
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

    <div class="container mb-3">
        <div class="row">
            <div class="col-md-auto mt-2 ml-2 mb-2"><h2>Generate Payment Links</h2></div>
        </div>
    </div>
    <div class="container">
        <div class="row">
            <div class="offset-md-1 col-9">
                <h3>What's this page for?</h3>
                <p> We now have an online payment portal for guests
                    to pay for their booking (e.g. for group bookings, unpaid deposits, etc..) without
                    resorting to calling us or (gasp!) emailing their card details to us!
                </p>
                <h3>How does it work?</h3>
                <p>You have a guest who wants/needs to pay for the remaining balance on their booking.
                   So, you come here and enter the booking reference below and click Find, then Generate Link.
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
            </div>
        </div>
        <form name="post_option" action="" method="post" id="post_option">
            <div class="row">
                <div class="offset-md-1 col-7">
                    <label class="form_label" for="booking_ref">Booking Ref:
                        <div class="tooltip_label pl-2" data-toggle="tooltip" data-html="true" data-placement="auto" data-container="body">
                            <xsl:attribute name="title">&lt;img src="<xsl:value-of select="homeurl"/>/wp-content/plugins/hbo-reports/img/cloudbeds-reservation.png"/&gt;</xsl:attribute>
                            Where do I find this?</div>
                    </label>
                    <input id="booking_ref" name="booking_ref" class="regular-text code" type="text" style="margin-left: 25px; margin-right: 10px; width:150px;" size="20" value="{booking_ref}" />
                    <a class="btn btn-primary" href="javascript:void(0);" onclick="lookup_booking_for_generate_payment_link(document.post_option.booking_ref.value);">Find</a>
                </div>
            </div>
            <div class="row">
                <div class="offset-md-1 col-6">
                    <div id="ajax_response"><xsl:comment/><!-- ajax response here--></div>
                </div>
            </div>
        </form>

        <xsl:if test="payment_history_inv_url">
            <div class="row justify-content-center mt-4">
                <div class="col-md-auto">
                    <h3>OR Generate an Invoice Link</h3>
                </div>
            </div>
            <form name="post_invoice" action="" method="post" id="post_invoice">
	        <div class="row">
	            <div class="offset-md-1 col-9">
                    <p>You may want to request payment from a guest (or ex-guest) but it may not be tied to a booking.
                       The most common example is to request for postage to be paid before sending a lost item.
                    </p>
                    <p>Enter the details below. This will generate a link that you can email the recipient (this page won't do this for you!). 
                       Once they navigate to the link you send them below and provide their card details, they will receive a confirmation 
                       email (if payment is successful) and the reception email account will also be CC'd in. You can also view any pending/completed 
                       payments in the <a href="{payment_history_inv_url}">Invoice Payment History</a> page.
                    </p>
                </div>
            </div>
            <div class="row">
                <div class="offset-md-1 col-7">
                    <label for="invoice_name" class="form_label" style="width:80px">Name:</label>
                    <input id="invoice_name" name="invoice_name" class="regular-text code" type="text" style="margin-left: 25px; width:150px;" size="30" value="{invoice_name}" />
                </div>
            </div>
            <div class="row">
                <div class="offset-md-1 col-7">
                    <label for="invoice_email" class="form_label" style="width:80px">Email:</label>
                    <input id="invoice_email" name="invoice_email" class="regular-text code" type="text" style="margin-left: 25px; width:150px;" size="30" value="{invoice_email}" />
                    <label for="invoice_amount" class="form_label" style="margin-left: 25px;">Amount (£):</label>
                    <input id="invoice_amount" name="invoice_amount" class="regular-text code" type="text" style="margin-left: 25px; width:150px;" size="30" value="{invoice_amount}" />
                </div>
            </div>
            <div class="row mt-1">
                <div class="offset-md-1 col-7">
                    <label for="invoice_description" class="form_label">Description (appears on transaction):</label>
                    <textarea id="invoice_description" name="invoice_description" class="regular-text code" style="margin-left: 40px; width: 500px;" maxlength="{payment_description_max_length}"><xsl:comment/></textarea>
                </div>
            </div>
            <div class="row">
                <div class="offset-md-1 col-7">
                    <label class="form_label">Staff Notes (not sent to recipient):</label>
                    <textarea id="invoice_notes" name="invoice_notes" class="regular-text code" style="margin-left: 40px; margin-right: 10px; width: 500px;"><xsl:comment/></textarea>
                    <a class="btn btn-primary" href="javascript:void(0);" style="vertical-align: top; margin-top: 5px;" onclick="generate_invoice_link(document.post_invoice.invoice_name.value, document.post_invoice.invoice_email.value, document.post_invoice.invoice_amount.value, document.post_invoice.invoice_description.value, document.post_invoice.invoice_notes.value);">Submit</a>
                </div>
            </div>
            <div class="row">
                <div class="offset-md-1 col-6">
                    <div id="ajax_response_inv"><xsl:comment/><!-- ajax response here--></div>
                </div>
            </div>
            </form>
        </xsl:if>
    </div>

    <xsl:call-template name="write_inline_js"/>
    <xsl:call-template name="write_inline_css"/>

    </xsl:if>
</xsl:template>

<xsl:template match="booking">

    <div class="matched_booking mt-2">
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
    <div class="row mt-2">
        <div class="offset-md-1 col-10">
            <label class="form_label">Requested Payment Amount:</label>
        </div>
    </div>
    <xsl:if test="amount_first_night">
        <div class="row">
            <div class="offset-md-2 col-10">
                <input id="payment_1st_night" name="payment_type" type="radio" onclick="document.post_option.payment_amount.value='{amount_first_night}';" value="first_night"/>
                <label class="form_label pl-3" for="payment_1st_night">First Night (£<xsl:value-of select="amount_first_night"/>)</label>
            </div>
        </div>
    </xsl:if>
    <xsl:if test="balance_due">
        <div class="row">
            <div class="offset-md-2 col-10">
                <input id="payment_full" name="payment_type" type="radio" onclick="document.post_option.payment_amount.value='{balance_due}';" value="balance_due"/>
                <label class="form_label pl-3" for="payment_full">Outstanding Balance (£<xsl:value-of select="balance_due"/>)</label>
            </div>
        </div>
    </xsl:if>
    <div class="row">
        <div class="offset-md-2 col-10">
            <input id="payment_custom_amount" name="payment_type" type="radio" value="custom_amount" checked="true"/>
            <label class="form_label pl-3" for="payment_custom_amount">Custom Amount:</label>
            <input id="payment_amount" name="payment_amount" class="regular-text code ml-2 mr-2" type="text" style="width:100px; display:inline;" size="20" onfocus="document.getElementById('payment_custom_amount').checked = true;"/>
            <a class="btn btn-primary" href="javascript:void(0);" onclick="generate_payment_link(document.post_option.booking_ref.value, document.post_option.payment_type.value, document.post_option.payment_amount.value);">Generate Link</a>
        </div>
    </div>
    <div id="payment_url_block" style="margin-top: 20px; margin-left: 100px; display:none;">
        <h4>Help spread the word:</h4>
        <input id="paymentUrl" type="text" class="regular-text all-copy" readonly="readonly" onclick="copyToClipboard(this, document.getElementById('copied_to_clipboard'))" style="width:400px;"/>
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