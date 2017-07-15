<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->
<xsl:template match="view">

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

.hint {
    margin-left: 20px;
}

#report-container {
    font-family: sans-serif;
    margin-left: 20px;
    margin-bottom: 20px;
}

#report-container h3 {
    margin: 10px 0;
}

#transaction-report tbody tr:nth-child(odd) td {
	background-color: #e3e3e3;
}

#transaction-report tbody tr td {
	padding-left: 15px;
}

</style>

    <div id="report-container" class="wrap bookingpage wpdevbk">
        <form name="post_manual_charge" autocomplete="off" action="" method="post" id="post_manual_charge">

            <h3>Process a Manual Charge</h3> 

            <div style="font-style:italic;">
                <p>This will attempt to charge the card saved against the given booking. Only Hostelworld bookings are supported.<br/>
                   Note: Hostelworld only keeps card details 7 days after the checkin date for a booking.
                </p>
                <p>FYI, you cannot charge more than what is currently "outstanding" in Little Hotelier. <br/>
                   Please confirm (or update) the total outstanding in Little Hotelier prior to submitting! <br/>
                   As a safety precaution, only one (successful) charge attempt is allowed against each booking.</p>
            </div>

            <table class="form-table">
                <tbody>
                    <tr valign="top">
                        <th scope="row"><label for="booking_ref">Booking Reference:</label></th>
                        <td><input id="booking_ref" name="booking_ref" class="regular-text code" type="text" autocomplete="false" style="width:200px;" size="75" value="{booking_ref}"/></td>
                        <td><span class="hint">e.g. HWL-123-123456789</span></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="charge_amount">Amount to Charge:</label></th>
                        <td><input id="charge_amount" name="charge_amount" class="regular-text code" type="text" autocomplete="false" style="width:200px;" size="75" value="{charge_amount}"/></td>
                        <td><span class="hint">in GBP. e.g. 13.20</span></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="charge_note">Note:</label></th>
                        <td><textarea name="charge_note" class="regular-text code" style="width: 200px;">Charge attempt as no-show.</textarea></td>
                        <td><xsl:comment/></td>
                    </tr>
                    <tr valign="top">
                        <td colspan="2">
                            <div id="ajax_response"><xsl:comment/></div>
                            <a id="charge_button" class="btn btn-primary" style="float: right;" onclick="submit_manual_charge(post_manual_charge.booking_ref.value, post_manual_charge.charge_amount.value, post_manual_charge.charge_note.value); this.style.visibility='hidden';">Charge Now</a>
                        </td>
                        <td><xsl:comment/></td>
                    </tr>
                </tbody>
            </table>
        </form>

        <xsl:apply-templates select="transactions" />
    </div>


</xsl:template>

<xsl:template match="transactions">
    <h3>Previous Charges</h3> 
    <div class="visibility_container" id="transaction_view">
        <xsl:choose>
            <xsl:when test="transaction">
                <table id="transaction-report" class="allocation_view" width="100%" cellspacing="0" cellpadding="3" border="0">
                    <thead>
                        <th>Booking Reference</th>
                        <th>Transaction Date</th>
                        <th>Card Number</th>
                        <th>Payment Amount</th>
                        <th>Successfully Charged</th>
                        <th>Details</th>
                        <th>Job Status</th>
                        <th>Last Updated</th>
                    </thead>
                    <tbody>
                        <xsl:apply-templates select="transaction"/>
                    </tbody>
                </table>
            </xsl:when>
            <xsl:otherwise>
                <div style="margin-left:50px; margin-bottom: 20px; font-style: italic;"><h4>No data available.</h4></div>
            </xsl:otherwise>
        </xsl:choose>
    </div>
</xsl:template>

<xsl:template match="transaction">
    <tr>
        <td>
            <xsl:choose>
                <xsl:when test="data-href">
                    <a target="_blank">
                       <xsl:attribute name="href">https://app.littlehotelier.com<xsl:value-of select="data-href"/>?reservation_filter%5Bbooking_reference_id%5D=<xsl:value-of select="booking-ref"/>&amp;reservation_filter%5Bdate_from%5D=<xsl:value-of select="checkin-date"/>&amp;reservation_filter%5Bdate_to%5D=<xsl:value-of select="checkin-date"/></xsl:attribute>
                       <xsl:value-of select="booking-ref"/>
                    </a>
                </xsl:when>
                <xsl:otherwise><xsl:value-of select="booking-ref"/></xsl:otherwise>
            </xsl:choose>
        </td>
        <td><xsl:value-of select="transaction-date"/></td>
        <td><xsl:value-of select="card-number"/></td>
        <td><xsl:value-of select="payment-amount"/></td>
        <td><xsl:value-of select="success"/></td>
        <td><xsl:value-of select="details"/></td>
        <td><xsl:value-of select="job-status"/></td>
        <td><xsl:value-of select="last-updated-date"/></td>
    </tr>
</xsl:template>

</xsl:stylesheet>