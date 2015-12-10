<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<xsl:output method="html" omit-xml-declaration="yes" encoding="UTF-8"/>

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->

<xsl:template match="view" name="report_data">

    <table id="guest_comments_rpt" class="allocation_view" width="100%" cellspacing="0" cellpadding="3" border="0">
        <thead>
            <th>Guest Name(s)</th>
            <th>Booking Reference</th>
            <th>Booking Source</th>
            <th>Checkin Date</th>
            <th>Checkout Date</th>
            <th>Number of Guests</th>
            <th>Acknowledged
                <input type="checkbox" name="show_ack" onClick="update_guest_comments_report_view(this.checked);">
                    <xsl:if test="show_acknowledged = 'true'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
                </input>
            </th>
        </thead>
        <tbody>
            <xsl:apply-templates select="record"/>
        </tbody>
    </table>

</xsl:template>


<xsl:template match="record">
    <tr>
        <!-- grey out if acknowledged -->
        <xsl:if test="acknowledged_date"><xsl:attribute name="style">color: #aaa;</xsl:attribute></xsl:if>

        <td><a target="_blank"><xsl:attribute name="href">
                   https://emea.littlehotelier.com<xsl:value-of select="data_href"/>
               </xsl:attribute>
               <xsl:value-of select="guest_name"/>
            </a>
        </td>
        <td><xsl:value-of select="booking_reference"/></td>
        <td><xsl:value-of select="booking_source"/></td>
        <td><xsl:value-of select="checkin_date"/></td>
        <td><xsl:value-of select="checkout_date"/></td>
        <td style="padding-left: 50px;"><xsl:value-of select="num_guests"/></td>
        <td style="text-align: center;">
            <input type="checkbox" name="ack_checkbox">
                <xsl:if test="acknowledged_date"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
                <xsl:attribute name="onClick">if(this.checked) { acknowledge_guest_comment(<xsl:value-of select="reservation_id"/>); this.parentElement.parentElement.style.color = '#aaa'; this.parentElement.parentElement.nextElementSibling.style.color = '#aaa'; } else { unacknowledge_guest_comment(<xsl:value-of select="reservation_id"/>); this.parentElement.parentElement.style.color = null; this.parentElement.parentElement.nextElementSibling.style.color = null; }</xsl:attribute>
            </input>
        </td>
    </tr>
    <tr>
        <!-- grey out if acknowledged -->
        <xsl:if test="acknowledged_date"><xsl:attribute name="style">color: #aaa;</xsl:attribute></xsl:if>
        <td colspan="7"><div class="comment_header">Comments: </div>
            <div class="comment_text"><xsl:value-of select="comments"/></div>
            <xsl:if test="notes">
                <div style="clear: left;" class="comment_header">Notes: </div>
                <div class="comment_text"><xsl:value-of select="notes"/></div>
            </xsl:if>
        </td>
    </tr>
</xsl:template>

</xsl:stylesheet>