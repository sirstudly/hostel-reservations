﻿<?xml version="1.0" encoding="utf-8" ?>
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

        <h3>Refund History</h3> 

        <div style="font-style:italic;">
            <p>This lists all refunds that have been initiated from the Process Refunds page.
            </p>
        </div>
        <xsl:apply-templates select="refunds" />
	    <xsl:if test="not(refunds)">
	        <div style="margin: 20px 0 20px 50px; font-style: italic;"><h4>No data available.</h4></div>
	    </xsl:if>
    </div>
</xsl:template>

<xsl:template match="refunds">
    <div class="visibility_container" id="transaction_view">
        <xsl:choose>
            <xsl:when test="refund">
                <table id="transaction-report" class="allocation_view" width="100%" cellspacing="0" cellpadding="3" border="0">
                    <thead>
                        <th>Booking</th>
                        <th>Booking Reference</th>
                        <th>Refund Amount</th>
                        <th>Description</th>
                        <th>Gateway</th>
                        <th>Details</th>
                        <th>Processed Date</th>
                    </thead>
                    <tbody>
                        <xsl:apply-templates select="refund"/>
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

<xsl:template match="refund">
    <tr>
        <td>
            <a target="_blank">
               <xsl:attribute name="href"><xsl:value-of select="data-href"/></xsl:attribute>
               <xsl:value-of select="first_name"/><xsl:text> </xsl:text><xsl:value-of select="last_name"/>
            </a><br/>
            <xsl:value-of select="email"/>
        </td>
        <td><xsl:value-of select="booking_reference"/></td>
        <td><xsl:value-of select="amount"/></td>
        <td><xsl:value-of select="description"/></td>
        <td><xsl:if test="charge_id">Stripe</xsl:if>
            <xsl:if test="auth_vendor_tx_code">Sagepay</xsl:if>
        </td>
        <td><xsl:value-of select="refund_status"/><xsl:if test="refund_status_detail">: <xsl:value-of select="refund_status_detail"/></xsl:if></td>
        <td><xsl:attribute name="data-order"><xsl:value-of select="last_updated_datetime"/></xsl:attribute><xsl:if test="refund_status"><xsl:value-of select="last_updated_date"/></xsl:if></td>
    </tr>
</xsl:template>

</xsl:stylesheet>