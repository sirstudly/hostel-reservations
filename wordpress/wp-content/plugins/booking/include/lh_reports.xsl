<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->
<xsl:include href="inline_scripts.xsl"/>

<xsl:template match="view">

<style media="screen" type="text/css">
#split_room_rpt tbody tr:nth-child(odd) td,#split_room_rpt tbody tr:nth-child(odd) th {
	background-color: #e3e3e3
}

.aside {
    padding-left: 20px;
    font-style: italic;
}
</style>

    <div class="wpdevbk center">
        <h3>Reservations Split Across Different Rooms</h3>
    </div>

    <br/>
    <p><span class="aside">This report was last run on <xsl:value-of select="record[1]/created_date"/></span></p>

    <xsl:call-template name="write_inline_js"/>

    <table id="split_room_rpt" class="allocation_view" width="100%" cellspacing="0" cellpadding="3" border="0">
        <thead>
            <th>Guest Name(s)</th>
            <th>Checkin Date</th>
            <th>Checkout Date</th>
            <th>Notes</th>
        </thead>
        <tbody>
            <xsl:apply-templates select="record"/>
        </tbody>
    </table>


</xsl:template>

<xsl:template match="record">
    <tr>
        <td><a target="splt_rm_rpt"><xsl:attribute name="href">
                   https://emea.littlehotelier.com<xsl:value-of select="data_href"/>
               </xsl:attribute>
               <xsl:value-of select="guest_name"/>
            </a>
        </td>
        <td><xsl:value-of select="checkin_date"/></td>
        <td><xsl:value-of select="checkout_date"/></td>
        <td><xsl:value-of select="notes"/></td>
    </tr>
</xsl:template>

</xsl:stylesheet>