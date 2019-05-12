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

        <h3>Booking Payment History</h3> 

        <div style="font-style:italic;">
            <p>This lists all booking payments made to our payment gateway (Sagepay) <b>outside</b> of Cloudbeds.<br/>
               This might include payments made to us for a booking after they've booked because: 
               <ul>
                   <li>we were unable to charge the deposit amount with the card they provided at the time of booking</li>
                   <li>it was for a group and we need the full balance paid before arrival</li>
               </ul>
            </p>
        </div>
        <xsl:apply-templates select="payments" />
	    <xsl:if test="not(payments)">
	        <div style="margin: 20px 0 20px 50px; font-style: italic;"><h4>No data available.</h4></div>
	    </xsl:if>
    </div>
</xsl:template>

<xsl:template match="payments">
    <div class="visibility_container" id="transaction_view">
        <xsl:choose>
            <xsl:when test="payment">
                <table id="transaction-report" class="allocation_view" width="100%" cellspacing="0" cellpadding="3" border="0">
                    <thead>
                        <th>Booking</th>
                        <th>Vendor Tx Code</th>
                        <th>Payment Amount</th>
                        <th>Authorisation Status</th>
                        <th>Details</th>
                        <th>Card Details</th>
                        <th>Processed Date</th>
                    </thead>
                    <tbody>
                        <xsl:apply-templates select="payment"/>
                    </tbody>
                </table>

<script type="text/javascript">
   jQuery('#transaction-report').DataTable({
    "paging": false,
    "searching": false,
    "order": [[6, 'desc']]
  });
</script>
            </xsl:when>
            <xsl:otherwise>
                <div style="margin-left:50px; margin-bottom: 20px; font-style: italic;"><h4>No data available.</h4></div>
            </xsl:otherwise>
        </xsl:choose>
    </div>
</xsl:template>

<xsl:template match="payment">
    <tr>
        <td>
            <a target="_blank">
               <xsl:attribute name="href"><xsl:value-of select="data-href"/></xsl:attribute>
               <xsl:value-of select="first_name"/><xsl:text> </xsl:text><xsl:value-of select="last_name"/>
            </a><br/>
            <xsl:value-of select="email"/>
        </td>
        <td><xsl:value-of select="vendor_tx_code"/></td>
        <td><xsl:value-of select="payment_amount"/></td>
        <td><xsl:value-of select="auth_status"/></td>
        <td><xsl:value-of select="auth_status_detail"/></td>
        <td><xsl:if test="card_type and last_4_digits"><xsl:value-of select="card_type"/> **** **** **** <xsl:value-of select="last_4_digits"/></xsl:if></td>
        <td><xsl:attribute name="data-order"><xsl:value-of select="processed_datetime"/></xsl:attribute><xsl:value-of select="processed_date"/></td>
    </tr>
</xsl:template>

</xsl:stylesheet>