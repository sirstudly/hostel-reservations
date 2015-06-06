<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<xsl:output method="xml" omit-xml-declaration="yes" encoding="UTF-8"/>

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

.btn-primary {
    background-color: #006DCC;
    background-image: -moz-linear-gradient(center top , #08C, #04C);
    background-repeat: repeat-x;
    border-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);
    text-shadow: 0px -1px 0px rgba(0, 0, 0, 0.25);
    color: #FFF;
    display: inline-block;
    padding: 4px 10px;
    font-size: 13px;
    line-height: 18px;
    text-align: center;
    border-width: 1px;
    border-style: solid;
    border-color: #CCC #CCC #BBB;
    -moz-border-top-colors: none;
    -moz-border-right-colors: none;
    -moz-border-bottom-colors: none;
    -moz-border-left-colors: none;
    border-image: none;
    border-radius: 4px;
    box-shadow: 0px 1px 0px rgba(255, 255, 255, 0.2) inset, 0px 1px 2px rgba(0, 0, 0, 0.05);
    cursor: pointer;
    float: left; 
    margin-right: 15px;
}

tr.unread {
    font-weight: bold;
}

</style>

    <div class="wpdevbk center">
        <h3>Bookings with Unpaid Deposits</h3>
    </div>

    <br/>
    <p><span class="aside">This report was last run on <xsl:value-of select="last_completed_job"/>.</span><br/>
       <span class="aside">It is automatically re-run daily at 10:00pm.</span></p>

    <xsl:choose>
        <xsl:when test="last_submitted_job">
            <p><span class="aside">A request has been made to re-run this report on <xsl:value-of select="last_submitted_job"/>. This may take a while so maybe grab a cup of tea and check back later.</span></p>
        </xsl:when>
        <xsl:otherwise>
            <form name="split_room_form" method="post" id="split_room_form" class="form-inline">
                <input type="hidden" name="allocation_scraper_job" id="allocation_scraper_job" value="I can't wait. Run it now!"/>
                <p style="margin-left:20px"><a class="btn-primary" onclick="javascript:split_room_form.submit();">I can't wait. Run it now!</a></p>
                <br/><br/>
            </form>
        </xsl:otherwise>
    </xsl:choose>

    <br/><p style="padding-left: 20px;"><strong>BOLD</strong> entries have not been viewed before in Little Hotelier.</p>

    <xsl:call-template name="write_inline_js"/>

    <table id="split_room_rpt" class="allocation_view" width="100%" cellspacing="0" cellpadding="3" border="0">
        <thead>
            <th>Guest Name(s)</th>
            <th>Booking Reference</th>
            <th>Booking Source</th>
            <th>Checkin Date</th>
            <th>Checkout Date</th>
            <th>Booked Date</th>
            <th>Notes</th>
        </thead>
        <tbody>
            <xsl:apply-templates select="record"/>
        </tbody>
    </table>


</xsl:template>

<xsl:template match="record">
    <tr>
        <xsl:attribute name="class">
            <xsl:if test="viewed_yn = 'N'">unread</xsl:if>
        </xsl:attribute>
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
        <td><xsl:value-of select="booked_date"/></td>
        <td><xsl:value-of select="notes"/></td>
    </tr>
</xsl:template>

</xsl:stylesheet>