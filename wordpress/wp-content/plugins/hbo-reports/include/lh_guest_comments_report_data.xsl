<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<xsl:output method="html" omit-xml-declaration="yes" encoding="UTF-8"/>

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->

<xsl:template match="view" name="report_data">

    <table id="guest_comments_rpt"  class="table table-striped">
        <thead class="thead-dark">
            <tr>
                <th scope="col">Guest Name(s)</th>
	            <th scope="col">Booking Reference</th>
	            <th scope="col">Booking Source</th>
	            <th scope="col">Checkin Date</th>
	            <th scope="col">Checkout Date</th>
	            <th scope="col">Number of Guests</th>
	            <th scope="col">Acknowledged
	                <input type="checkbox" name="show_ack" onClick="update_guest_comments_report_view(this.checked);">
	                    <xsl:if test="show_acknowledged = 'true'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
	                </input>
	            </th>
	        </tr>
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

        <td class="text-left"><a target="_blank">
               <xsl:choose>
                 <xsl:when test="../property_manager = 'cloudbeds'">
                   <xsl:attribute name="href">https://hotels.cloudbeds.com<xsl:value-of select="data_href"/></xsl:attribute>
                 </xsl:when>
                 <xsl:otherwise>
                   <xsl:attribute name="href">https://app.littlehotelier.com<xsl:value-of select="data_href"/>?reservation_filter%5Bbooking_reference_id%5D=<xsl:value-of select="booking_reference"/>&amp;reservation_filter%5Bdate_from%5D=<xsl:value-of select="checkin_date_yyyymmdd"/>&amp;reservation_filter%5Bdate_to%5D=<xsl:value-of select="checkin_date_yyyymmdd"/></xsl:attribute>
                 </xsl:otherwise>  
               </xsl:choose>
               <xsl:value-of select="guest_name"/>
            </a>
        </td>
        <td class="text-left"><xsl:value-of select="booking_reference"/></td>
        <td class="text-left"><xsl:value-of select="booking_source"/></td>
        <td class="text-left"><xsl:value-of select="checkin_date"/></td>
        <td class="text-left"><xsl:value-of select="checkout_date"/></td>
        <td class="pl-3"><xsl:value-of select="num_guests"/></td>
        <td class="pl-2">
            <input type="checkbox" name="ack_checkbox">
                <xsl:if test="acknowledged_date"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
                <xsl:attribute name="onClick">if(this.checked) { acknowledge_guest_comment(<xsl:value-of select="reservation_id"/>); this.parentElement.parentElement.style.color = '#aaa'; this.parentElement.parentElement.nextElementSibling.style.color = '#aaa'; } else { unacknowledge_guest_comment(<xsl:value-of select="reservation_id"/>); this.parentElement.parentElement.style.color = null; this.parentElement.parentElement.nextElementSibling.style.color = null; }</xsl:attribute>
            </input>
        </td>
    </tr>
    <tr>
        <!-- grey out if acknowledged -->
        <xsl:if test="acknowledged_date"><xsl:attribute name="style">color: #aaa;</xsl:attribute></xsl:if>
        <td class="text-left" colspan="7"><div class="comment_header">Comments: </div>
            <div class="comment_text"><xsl:value-of select="comments"/></div>
            <xsl:if test="notes">
                <div style="clear: left;" class="comment_header">Notes: </div>
                <div class="comment_text"><xsl:value-of select="notes"/></div>
            </xsl:if>
        </td>
    </tr>
</xsl:template>

</xsl:stylesheet>