<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->
<xsl:template match="view">

    <xsl:if test="selected_refund">
       <xsl:apply-templates select="selected_refund"/>
    </xsl:if>

    <xsl:if test="refunds">

        <h4>Refund History</h4>

        <div class="font-italic">
            <p>This lists all refunds that have been initiated from the Process Refunds page.
            </p>
        </div>
        <xsl:apply-templates select="refunds" />
        <xsl:if test="not(refunds)">
            <div style="margin: 20px 0 20px 50px; font-style: italic;"><h4>No data available.</h4></div>
        </xsl:if>
        <div id="dialog_ajax_response"><xsl:comment/><!-- ajax response here--></div>
    </xsl:if>
</xsl:template>

<xsl:template match="refunds">
    <xsl:choose>
        <xsl:when test="refund">
            <table id="transaction-report" class="table table-striped">
                <thead class="thead-dark">
                    <th scope="col">Booking</th>
                    <th scope="col">Booking Reference</th>
                    <th scope="col">Refund Amount</th>
                    <th scope="col">Description</th>
                    <th scope="col">Gateway</th>
                    <th scope="col">Details</th>
                    <th scope="col">Processed Date</th>
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
        <td><xsl:if test="refund_status"><a><xsl:attribute name="href">javascript:show_refund_response(<xsl:value-of select="id"/>);</xsl:attribute><xsl:value-of select="refund_status"/><xsl:if test="refund_status_detail">: <xsl:value-of select="refund_status_detail"/></xsl:if></a></xsl:if></td>
        <td><xsl:attribute name="data-order"><xsl:value-of select="last_updated_datetime"/></xsl:attribute><xsl:if test="refund_status"><xsl:value-of select="last_updated_date"/></xsl:if></td>
    </tr>
</xsl:template>

<xsl:template match="selected_refund">

    <div id="response_dialog" class="refund-detail" style="display:none;">
        <xsl:attribute name="title">
            <xsl:if test="gateway = 'Cloudbeds'">Cloudbeds Response (Processed with Stripe)</xsl:if>
            <xsl:if test="gateway = 'Stripe'">Stripe Gateway Response</xsl:if>
            <xsl:if test="gateway = 'Sagepay'">Sagepay Gateway Response</xsl:if>
            <xsl:if test="gateway = 'Unknown'">Response</xsl:if>
        </xsl:attribute>
        <div style="width:100%; clear:both;">
            <textarea readonly="readonly" class="regular-text" style="width: 95%; height: 400px; margin-left: 15px; font-family: monospace;">
                <xsl:value-of select="response"/>
            </textarea>
        </div>
        <div style="width: 100%;">
            <a class="btn btn-primary" style="float: right; margin-right: 20px;" onclick="jQuery('#response_dialog').dialog('close');">Close</a>
        </div>
    </div>

<script type="text/javascript">
    jQuery("#response_dialog").dialog({
        autoOpen: true,
        modal: true,
        close: function(event, ui) {
            jQuery(this).remove();
        },
        width:'60%' });
</script>

</xsl:template>

</xsl:stylesheet>