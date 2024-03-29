﻿<?xml version="1.0" encoding="utf-8" ?>
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
    <xsl:if test="refund_dialog">
        <xsl:apply-templates select="refund_dialog"/>
    </xsl:if>
    <xsl:if test="not(booking) and not(refund_dialog)">

<style media="screen" type="text/css">

.form_label {
    padding-left: 40px;
    font-size: 12px;
    font-weight: bold;
    font-style: normal;
}

.tooltip_text {
  position: relative;
  display: inline-block;
  border-bottom: 1px dotted black;
  font-size: 10px;
  font-weight: normal;
}

.booking_details {
    font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
    font-size: 13px;
    line-height: 20px;
    margin-bottom: 10px;
    padding-left: 20px;
}

.booking_details label {
    width: 140px;
    display: inline-block;
    font-weight: bold;
}

.matched_booking {
    border-style: solid;
    border-radius: 6px;
    border-width: 1px;
    padding: 10px;
    background-color: #efefef;
}

#ajax_response {
    float: left;
    margin-left: 20px;
    margin-right: 20px;
}

</style>

        <h3>Process Refunds</h3>

        <div class="row offset-sm-1 col-10 mt-3">
            <h4>What is this page for?</h4>
        </div>
        <div class="row offset-sm-1 col-10 mt-3">
            <p>You want to process a partial/full refund for an existing booking and the card details may or may not be present in Cloudbeds.</p>
        </div>
        <div class="row offset-sm-1 col-10 mt-3">
            <h4>How does it work?</h4>
        </div>
        <div class="row offset-sm-1 col-10 mt-3">
            <p>
               Enter the booking reference below and click Submit.
               This will search for the booking and give you any payments made against it so far.
               Refunds need an existing transaction to be credited against.
               It is possible to issue refunds directly in Cloudbeds, however, if the payment was done via
               <a href="https://pay.macbackpackers.com/">https://pay.macbackpackers.com/</a>,
               the refund needs to be issued through here. Also, no automated emails are sent
               if you issue the refund in Cloudbeds directly (see below).
            </p>
            <p>
               Note: You can't refund more than what was originally charged. And you can't refund against
               a card that was never used in a transaction with us. If the guest wants to refund against a different card,
               this will need to be done using the EFTPOS terminal (so card details must be taken over the phone;
               not through email as it's not secure).
            </p>
            <p>Successful refunds will be automatically added to their "Folio" page in Cloudbeds and a
               confirmation email will be sent using the Cloudbeds email template "Refund Processed".
               You can view all processed/pending refunds on the Refund History page.
            </p>

            <form name="post_option" action="" method="post" id="post_option">
                <p>
                    <div><span class="form_label mr-1">Booking Ref:</span>
                        <div class="tooltip_text" data-toggle="tooltip" data-html="true">
                            <xsl:attribute name="title">
                                &lt;img src='<xsl:value-of select="homeurl"/>/wp-content/plugins/hbo-reports/img/cloudbeds-reservation.png'/&gt;
                            </xsl:attribute>
                            Where do I find this?
                        </div>
                        <input id="booking_ref" name="booking_ref" class="regular-text code" type="text" style="margin-left: 25px; width:150px;" size="20" value="{booking_ref}" />
                        <a class="btn btn-primary" style="margin-left: 15px;" onclick="lookup_booking(document.post_option.booking_ref.value);">Submit</a>
                    </div>
                </p>
                <div id="ajax_response"><xsl:comment/><!-- ajax response here--></div>
            </form>
        </div>

        <xsl:call-template name="write_inline_js"/>
        <xsl:call-template name="write_inline_css"/>

    </xsl:if>
</xsl:template>

<xsl:template match="booking">

    <div class="matched_booking">
        <div class="booking_details" style="width: 50%; float: left;">
	        <label>Booking Ref:</label> <xsl:value-of select="identifier"/><br/>
	        <xsl:if test="third_party_identifier != ''"><label>Source Reservation ID:</label> <xsl:value-of select="third_party_identifier"/><br/></xsl:if>
	        <label>Guest Name:</label> <xsl:value-of select="name"/><br/>
	        <label>Email:</label> <xsl:value-of select="email"/><br/>
	        <label>Booking Date:</label> <xsl:value-of select="booking_date_server_time"/><br/>
	        <label>Status:</label> <xsl:value-of select="status"/><br/>
	        <label>Checkin Date:</label> <xsl:value-of select="checkin_date"/><br/>
	        <label>Checkout Date:</label> <xsl:value-of select="checkout_date"/><br/>
	        <label>Number of Guests:</label> <xsl:value-of select="num_guests"/><br/>
        </div>
        <div class="booking_details" style="float: left;">
	        <label>Total Paid:</label> £<xsl:value-of select="amount_paid"/><br/>
	        <label>Grand Total:</label> £<xsl:value-of select="grand_total"/><br/>
	        <label>Balance Due:</label> £<xsl:value-of select="balance_due"/><br/>
        </div>
        <br/>
        <xsl:apply-templates select="transactions"/>
        <div id="dialog_ajax_response"><xsl:comment/><!-- ajax response here--></div>
    </div>

</xsl:template>

<xsl:template match="transactions">
    <xsl:choose>
	    <xsl:when test="transaction">
		    <table id="transaction-report" class="table table-striped">
		        <thead class="thead-dark">
		            <tr>
			            <th scope="col" style="width: 130px;">Date/Time</th>
			            <th scope="col" style="width: 70px;">Amount Paid</th>
			            <th scope="col" style="width: 90px;">Description</th>
			            <th scope="col">Notes</th>
			            <th scope="col" style="width: 100px;"></th>
		            </tr>
		        </thead>
		        <tbody>
		            <xsl:apply-templates select="transaction"/>
		        </tbody>
		    </table>
	    </xsl:when>
	    <xsl:otherwise>
	        <div style="font-style: italic; font-size: 14px; clear: both; padding: 10px 0 10px 100px;">No transactions found.</div>
	    </xsl:otherwise>
    </xsl:choose>
</xsl:template>

<xsl:template match="transaction">
    <tr>
        <td><xsl:value-of select="datetime_transaction"/></td>
        <td><xsl:value-of select="paid"/></td>
        <td><xsl:value-of select="description"/>
            <xsl:if test="string-length(original_description) &gt; 0">
                (<xsl:value-of select="original_description"/>)
            </xsl:if>
        </td>
        <td><xsl:value-of select="notes"/>
            <xsl:if test="is_vcc and is_refundable">
                <p style="font-style: italic;">The original charge was against a virtual CC. Guest must contact BDC to initiate refund (only refund once contacted by BDC).</p>
            </xsl:if>
        </td>
        <td>
            <xsl:if test="is_refundable">
                <a class="btn btn-primary" ><xsl:attribute name="onclick">show_refund_dialog('<xsl:value-of select="id"/>');</xsl:attribute>Refund</a>
            </xsl:if>
            <xsl:if test="not(is_refundable) and not(is_refund)">
                <a class="btn btn-primary disabled">Refund</a>
            </xsl:if>
        </td>
    </tr>
</xsl:template>

<xsl:template match="refund_dialog">
    <div id="refund_dialog" class="refund-detail" style="display:none;" title="Refund Details">
        <form name="post_refund" action="" method="post">
        <div style="width:50%; float:left;">
            <label style="width: 240px; float: left;">Booking Total:</label>
            <div><xsl:value-of select="grand_total"/></div>
        </div>
        <div style="float:left;">
            <label>Payment Gateway: <xsl:value-of select="gateway_name"/></label>
        </div>
        <div style="width:50%; float:left; clear:both;">
            <label style="width: 240px; float: left;">Total Paid:</label>
            <div><xsl:value-of select="amount_paid"/></div>
        </div>
        <xsl:if test="vendor_tx_code">
        <div style="float:left;">
            <label>Vendor Tx Code: <xsl:value-of select="vendor_tx_code"/></label>
        </div>
        </xsl:if>
        <div style="width:100%; clear:both;">
            <label style="width: 240px; float: left;">Amount Charged for this Transaction:<br/><i>(This is the maximum refundable amount)</i></label>
            <div><xsl:value-of select="paid"/></div>
        </div>
        <div style="width:100%; clear:both;">
            <label style="width: 240px; float: left;">Refund Amount</label>
            <div>
                <input id="refund_amount" name="refund_amount" class="regular-text code" type="text" style="width:150px;" size="20"/>
                <span style="padding-left: 20px; font-size: 13px; vertical-align: top;">Hint: 90% of <xsl:value-of select="paid"/> is <b><xsl:value-of select="default_refund"/></b></span>
            </div>
        </div>
        <div style="width:100%; clear:both;">
            <label style="width: 240px; float: left;">Note (optional; for internal use. To be added to booking)</label>
            <textarea id="description" name="description" class="regular-text" style="width: 400px;"/>
        </div>
        <div style="width: 610px;">
            <div id="refund_ajax_response" style="float: left;"><xsl:comment/><!-- ajax response here--></div>
            <a id="submit_refund_button" class="btn btn-primary" style="float: right;" onclick="if (!jQuery(this).hasClass('disabled')) submit_refund(document.post_refund.refund_amount.value, document.post_refund.description.value);">Submit</a>
        </div>
        </form>
    </div>

<script type="text/javascript">
    jQuery("#refund_dialog").dialog({
        autoOpen: true,
        modal: true,
        close: function(event, ui) {
            jQuery(this).remove();
        },
        width:'60%' });
</script>

</xsl:template>

</xsl:stylesheet>