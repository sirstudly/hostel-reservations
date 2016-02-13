<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<xsl:output method="html" omit-xml-declaration="yes" encoding="UTF-8"/>

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->
<xsl:include href="inline_scripts.xsl"/>

<xsl:template match="view">

<style media="screen" type="text/css">

#group_bookings_rpt tbody tr:nth-child(odd) td {
	background-color: #e3e3e3;
}

tr.unread {
    font-weight: bold;
}

.six_bed_dorm {
    color: #882e44;
}

</style>

    <div id="report-container" class="wrap bookingpage">
        <h2>Bookings with 6 or More Guests</h2>
        <div class="wpdevbk">
    
            <div style="margin-top:10px;" class="booking-submenu-tab-container">
                <div class="nav-tabs booking-submenu-tab-insidecontainer">

                    <div id="filter" class="visibility_container active">
                        <xsl:call-template name="report_header"/>
                    </div>

                </div>
            </div>

            <div style="height:1px;clear:both;margin-top:40px;"><xsl:comment/></div>
    
            <div class="visibility_container" id="report_data_view">
                <xsl:choose>
                    <xsl:when test="record">
                        <xsl:call-template name="report_data"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <div style="margin-left:50px; margin-bottom: 20px; font-style: italic;"><h4>No data available.</h4></div>
                    </xsl:otherwise>
                </xsl:choose>
            </div>
        </div>

        <xsl:call-template name="write_inline_js"/>
        <xsl:call-template name="write_inline_css"/>
    </div>

</xsl:template>


<xsl:template name="report_header">
    <div style="clear:both;height:1px;"><xsl:comment/></div>
    <div class="wpdevbk-filters-section ">

        <form  name="report_form" action="" method="post" id="report_form"  class="form-inline">

            <div class="control-group" style="float:left;">
                <p class="help-block" style="float:left;padding-left:5px;font-style: italic; width: 100%;">
                    <xsl:if test="last_completed_job">
                        This report was last run on <xsl:value-of select="last_completed_job"/>.<br/>
                        It is automatically run daily at 10:50pm.<br/>
                    </xsl:if>
                    <div class="six_bed_dorm">Bookings of 5 guests into 6 bed dorms are formatted like this. You'll probably want to email them to upgrade to a larger dorm or to book the entire room.</div>
                    <xsl:if test="last_job_status = 'failed'">
                        <div style="color: red;">The last update of this report failed to run.
                            <xsl:choose>
                                <xsl:when test="check_credentials = 'true'">
                                    Has the LittleHotelier password changed recently? If so, update it on the admin page.
                                </xsl:when>
                                <xsl:otherwise>
                                    Check the log for details.
                                </xsl:otherwise>
                            </xsl:choose>
                        </div>
                    </xsl:if>
                </p>
            </div>
    
            <div class="btn-group" style="float:right;">
                <div class="inline controls">
                    <div class="btn-group">
                        <xsl:choose>
                            <xsl:when test="last_submitted_job">
                                <a class="btn btn-primary disabled" style="float: right; margin-right: 15px;">Update in Progress <span class="icon-refresh icon-white"></span></a>
                            </xsl:when>
                            <xsl:otherwise>
                                <input type="hidden" name="reload_data" id="reload_data" value="true" />
                                <a class="btn btn-primary" style="float: right; margin-right: 15px;" onclick="javascript:report_form.submit();">Reload Data <span class="icon-refresh icon-white"></span></a>
                            </xsl:otherwise>
                        </xsl:choose>
                    </div>
                <p class="help-block" style="float:left;padding-left:5px;padding-right:15px;font-style:italic;">
                    <xsl:if test="last_submitted_job">
                        Come back to this page in a few minutes.
                    </xsl:if>
                </p>
                </div>
            </div>

            <div class="clear"><xsl:comment/></div>
        </form>

    </div>
    <div style="clear:both;height:1px;"><xsl:comment/></div>

</xsl:template>


<xsl:template name="report_data">
    <p style="padding-left: 20px;"><strong>BOLD</strong> entries have not been viewed before in Little Hotelier.</p>

    <table id="group_bookings_rpt" class="allocation_view" width="100%" cellspacing="0" cellpadding="3" border="0">
        <thead>
            <th>Guest Name(s)</th>
            <th>Booking Reference</th>
            <th>Booking Source</th>
            <th>Checkin Date</th>
            <th>Checkout Date</th>
            <th>Payment Outstanding</th>
            <th>Number of Guests</th>
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
            <xsl:if test="num_guests = 5">six_bed_dorm</xsl:if>
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
        <td style="padding-left: 50px;"><xsl:value-of select="payment_outstanding"/></td>
        <td style="padding-left: 50px;"><xsl:value-of select="num_guests"/></td>
        <td><xsl:value-of select="notes"/></td>
    </tr>
</xsl:template>

</xsl:stylesheet>