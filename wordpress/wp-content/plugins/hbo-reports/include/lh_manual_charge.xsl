<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->
<xsl:template match="view">

    <div class="container mb-3">
        <div class="row">
            <div class="col-md-auto mt-2 ml-2"><h2>Process a Manual Charge</h2></div>
        </div>
    </div>

    <div class="container">
        <form name="post_manual_charge" autocomplete="off" action="" method="post" id="post_manual_charge">

            <xsl:choose>
                <xsl:when test="property_manager = 'cloudbeds'">
                    <p>You can do this from Cloudbeds now! Add the credit card onto the Credit Cards tab on the booking if it's not already there. Click AUTHORIZE, then CAPTURE. -RONBOT</p>
                </xsl:when>

                <xsl:otherwise>
            <div style="font-style:italic;">
                <p>This will attempt to charge the card saved against the given booking.<br/>
                   <ul>
                       <li>Agoda/Expedia/Hostelworld - card details taken from their respective website unless "Use card details from LH" is ticked.</li>
                       <li>All other bookings - card details taken from Little Hotelier reservation page.</li>
                   </ul>
                </p>
                <p>Note: Most sites (including LH) only keep card details for about a week after the checkin date for a booking.</p>
                <p>FYI, you cannot charge more than what is currently "outstanding" in Little Hotelier. <br/>
                   Please confirm (or update) the total outstanding in Little Hotelier prior to submitting! <br/>
                   As a safety precaution, only one (successful) charge attempt is allowed against each booking.</p>
            </div>

            <table class="form-table">
                <tbody>
                    <tr valign="top">
                        <th scope="row"><label for="booking_ref">Booking Reference:</label></th>
                        <td><input id="booking_ref" name="booking_ref" class="regular-text code" type="text" autocomplete="false" style="width:200px;" size="75" value="{booking_ref}"/></td>
                        <td><span class="ml-2">e.g. HWL-123-123456789</span></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="charge_amount">Amount to Charge:</label></th>
                        <td><input id="charge_amount" name="charge_amount" class="regular-text code" type="text" autocomplete="false" style="width:200px;" size="75" value="{charge_amount}"/></td>
                        <td><span class="ml-2">in GBP. e.g. 13.20</span></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="override_card_details">Use card details from LH:</label></th>
                        <td><input id="override_card_details" name="override_card_details" type="checkbox"/></td>
                        <td><span class="ml-2">Only applicable for HWL/EXP/AGO bookings</span></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="charge_note">Note:</label></th>
                        <td><textarea name="charge_note" class="regular-text code" style="width: 200px;">Charge attempt as no-show.</textarea></td>
                        <td><xsl:comment/></td>
                    </tr>
                    <tr valign="top">
                        <td colspan="2">
                            <div id="ajax_response"><xsl:comment/></div>
                            <a id="charge_button" class="btn btn-primary mb-2" style="float: right;" onclick="submit_manual_charge(post_manual_charge.booking_ref.value, post_manual_charge.charge_amount.value, post_manual_charge.charge_note.value, post_manual_charge.override_card_details.checked); this.style.visibility='hidden';">Charge Now</a>
                        </td>
                        <td><xsl:comment/></td>
                    </tr>
                </tbody>
            </table>

                </xsl:otherwise>
            </xsl:choose>
        </form>

        <xsl:apply-templates select="transactions" />
    </div>


</xsl:template>

<xsl:template match="transactions">
    <h3>Previous Charges</h3> 
    <xsl:choose>
        <xsl:when test="transaction">
            <table class="table table-striped">
                <thead>
                    <th scope="col">Booking Reference</th>
                    <th scope="col">Transaction Date</th>
                    <th scope="col">Card Number</th>
                    <th scope="col">Payment Amount</th>
                    <th scope="col">Successfully Charged</th>
                    <th scope="col">Details</th>
                    <th scope="col">Job Status</th>
                    <th scope="col">Last Updated</th>
                    <th scope="col">Log File</th>
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
        <td><xsl:if test="log_file"><a><xsl:attribute name="href"><xsl:value-of select="log_file"/></xsl:attribute>job-<xsl:value-of select="job_id"/>.log</a></xsl:if></td>
    </tr>
</xsl:template>

</xsl:stylesheet>