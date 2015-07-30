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
.booking_diffs_rpt tbody tr:nth-child(odd) td {
	background-color: #e3e3e3
}

td.centered {
    text-align:center;
}

td.cancelled a {
    text-decoration: line-through;
    text-decoration-color: red;
    color: red !important;
}

.booking_diffs_rpt tbody td.mismatched {
    background-color: #f8c7c7 !important;
}

.booking_diffs_rpt tbody td.currency {
    padding-right: 30px;
    text-align: right;
}

.booking_diffs_rpt tfoot tr td {
    font-weight: bold;
	background-color: #d7d7d7;
}

.booking_diffs_rpt .report_title_row {
	background-color: #dfeef7;
}

.booking_diffs_rpt .report_title_cell {
	font-size: 20px; 
    font-family: 'Oswald',sans-serif; 
    color: #222; 
    text-align: left; 
    padding-left: 10px;
}
</style>

    <div id="wpdev-booking-booking-diffs" class="wrap bookingpage">
        <h2>Comparison Between HW/HB and Little Hotelier - <xsl:value-of select="selection_date_long"/></h2>
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
                        <xsl:call-template name="report_data_hw"/>
                        <div style="height:1px;clear:both;margin-top:30px;"><xsl:comment/></div>
                        <xsl:call-template name="report_data_hb"/>
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
            <a class="btn btn-primary" style="float: left; margin-right: 15px;"
                onclick="javascript:report_form.submit();">Apply <span class="icon-refresh icon-white"></span>
            </a>

            <div class="control-group" style="float:left;">
                <label for="selectiondate" class="control-label"><xsl:comment/></label>
                <div class="inline controls">
                    <div class="btn-group">
                        <input style="width:100px;" type="text" class="span2span2 wpdevbk-filters-section-calendar" 
                            value="{selection_date}"  id="selectiondate"  name="selectiondate" />
                        <span class="add-on"><span class="icon-calendar"></span></span>
                    </div>
                <p class="help-block" style="float:left;padding-left:5px;">Arrival Date</p>
                </div>
            </div>
    
            <div class="control-group" style="float:left;">
                <p class="help-block" style="float:left;padding-left:5px;font-style: italic;">
                    <xsl:if test="last_completed_job">
                        This report aggregates data as it appeared on <xsl:value-of select="last_completed_job"/>.
                    </xsl:if>
                </p>
            </div>
    
            <div class="btn-group" style="float:right;">
                <div class="inline controls">
                    <div class="btn-group">
                        <xsl:choose>
                            <xsl:when test="job_in_progress">
                                <a class="btn btn-primary disabled" style="float: right; margin-right: 15px;">Update in Progress <span class="icon-refresh icon-white"></span></a>
                            </xsl:when>
                            <xsl:otherwise>
                                <input type="hidden" name="reload_data" id="reload_data" value="" />
                                <a class="btn btn-primary" style="float: right; margin-right: 15px;" onclick="javascript:document.getElementById('reload_data').value = 'true';report_form.submit();">Reload Data <span class="icon-refresh icon-white"></span></a>
                            </xsl:otherwise>
                        </xsl:choose>
                    </div>
                <p class="help-block" style="float:left;padding-left:5px;padding-right:15px;font-style:italic;">
                    <xsl:if test="job_in_progress">
                        An update is already in progress. <br/>
                        Re-apply the filter on this form in a few minutes.
                    </xsl:if>
                    <xsl:if test="count(job_in_progress) = 0">
                        This job is automatically run daily at 12:10am.
                    </xsl:if>
                </p>
                </div>
            </div>

            <div class="clear"><xsl:comment/></div>
        </form>

    </div>
    <div style="clear:both;height:1px;"><xsl:comment/></div>
</xsl:template>

<!-- the main report body for Hostelworld -->
<xsl:template name="report_data_hw">
    <table id="booking_diffs_rpt_hw" class="allocation_view booking_diffs_rpt" width="100%" cellspacing="0" cellpadding="3" border="0">
        <thead>
            <tr class="report_title_row">
                <th colspan="13" class="report_title_cell">Hostelworld</th>
            </tr>
            <tr>
                <th colspan="6"><a target="_blank"><xsl:attribute name="href"><xsl:value-of select="homeurl"/>/redirect-to/HW_ListBookingsByArrivalDate/<xsl:value-of select="selection_date"/></xsl:attribute>
                    Hostelworld</a>
                </th>
                <th></th>
                <th colspan="6"><a target="_blank"><xsl:attribute name="href">https://emea.littlehotelier.com/extranet/properties/533/reservations?utf8=%E2%9C%93&amp;reservation_filter[guest_last_name]=&amp;reservation_filter[booking_reference_id]=HWL&amp;reservation_filter[date_type]=CheckIn&amp;reservation_filter[date_from_display]=<xsl:value-of select="selection_date_uri"/>&amp;reservation_filter[date_from]=<xsl:value-of select="selection_date"/>&amp;reservation_filter[date_to_display]=<xsl:value-of select="selection_date_uri"/>&amp;reservation_filter[date_to]=<xsl:value-of select="selection_date"/>&amp;reservation_filter[status]=&amp;commit=Search</xsl:attribute>
                    Little Hotelier</a>
                </th>
            </tr>
            <tr>
            <th>Guest Name(s)</th>
            <th>Room Type</th>
            <th>Checkin Date</th>
            <th>Checkout Date</th>
            <th>Number of<br/>Guests</th>
            <th>Payment Outstanding</th>
            <th>HW Booking<br/>Reference</th>
            <th>Room Type</th>
            <th>Checkin Date</th>
            <th>Checkout Date</th>
            <th>Number of<br/>Guests</th>
            <th>Payment Outstanding</th>
            <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            <xsl:if test="record">
                <xsl:apply-templates select="record[booking_source != 'Hostelbookers']"/>
            </xsl:if>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="13">TOTAL: <xsl:value-of select="count(record[booking_source != 'Hostelbookers'])"/> records</td>
            </tr>
        </tfoot>
    </table>
</xsl:template>

<!-- the main report body for Hostelbookers -->
<xsl:template name="report_data_hb">
    <table id="booking_diffs_rpt_hb" class="allocation_view booking_diffs_rpt" width="100%" cellspacing="0" cellpadding="3" border="0">
        <thead>
            <tr class="report_title_row">
                <th colspan="13" class="report_title_cell">Hostelbookers</th>
            </tr>
            <tr>
                <th colspan="6"><a target="_blank"><xsl:attribute name="href">https://admin.hostelbookers.com/backoffice/booking/index.cfm?fuseaction=search&amp;sub=query&amp;page=1&amp;searchType=Arrival&amp;strArrivalDateStart=<xsl:value-of select="selection_date_hb"/>&amp;strArrivalDateEnd=<xsl:value-of select="selection_date_hb"/>&amp;intArrivalStatusID=0&amp;intArrivalSourceID=0&amp;btnSubmit=Search</xsl:attribute>
                    Hostelbookers</a>
                </th>
                <th></th>
                <th colspan="6"><a target="_blank"><xsl:attribute name="href">https://emea.littlehotelier.com/extranet/properties/533/reservations?utf8=%E2%9C%93&amp;reservation_filter[guest_last_name]=&amp;reservation_filter[booking_reference_id]=HBK&amp;reservation_filter[date_type]=CheckIn&amp;reservation_filter[date_from_display]=<xsl:value-of select="selection_date_uri"/>&amp;reservation_filter[date_from]=<xsl:value-of select="selection_date"/>&amp;reservation_filter[date_to_display]=<xsl:value-of select="selection_date_uri"/>&amp;reservation_filter[date_to]=<xsl:value-of select="selection_date"/>&amp;reservation_filter[status]=&amp;commit=Search</xsl:attribute>
                    Little Hotelier</a>
                </th>
            </tr>
            <tr>
            <th>Guest Name(s)</th>
            <th>Room Type</th>
            <th>Checkin Date</th>
            <th>Checkout Date</th>
            <th>Number of<br/>Guests</th>
            <th>Payment Outstanding</th>
            <th>HB Booking<br/>Reference</th>
            <th>Room Type</th>
            <th>Checkin Date</th>
            <th>Checkout Date</th>
            <th>Number of<br/>Guests</th>
            <th>Payment Outstanding</th>
            <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            <xsl:if test="record">
                <xsl:apply-templates select="record[booking_source = 'Hostelbookers']"/>
            </xsl:if>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="13">TOTAL: <xsl:value-of select="count(record[booking_source = 'Hostelbookers'])"/> records</td>
            </tr>
        </tfoot>
    </table>
</xsl:template>

<xsl:template match="record">
    <tr>
        <xsl:if test="matched_room_type = 'Y' and matched_checkin_date = 'Y' and matched_checkout_date = 'Y' and matched_persons = 'Y' and matched_payment_outstanding = 'Y' and lh_status != 'cancelled'"><xsl:attribute name="style">color: #888;</xsl:attribute></xsl:if>
        <td>
            <a target="_blank">
                <xsl:attribute name="href">
                    <xsl:choose>
                        <xsl:when test="booking_source = 'Hostelbookers'">
                            <xsl:text>https://admin.hostelbookers.com/backoffice/booking/?fuseaction=detail&amp;i_id=</xsl:text><xsl:value-of select="substring(booking_reference, 6)"/><xsl:text>&amp;mode=search</xsl:text>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:value-of select="../homeurl"/>/redirect-to/HW_CustID/<xsl:value-of select="booking_reference"/>
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:attribute>
                <xsl:value-of select="guest_name"/>
            </a>
        </td>
        <td><xsl:attribute name="class">centered <xsl:if test="matched_room_type = 'N'">mismatched</xsl:if></xsl:attribute><xsl:value-of select="hw_room_type"/></td>
        <td><xsl:attribute name="class">centered <xsl:if test="matched_checkin_date = 'N'">mismatched</xsl:if></xsl:attribute><xsl:value-of select="hw_checkin_date"/></td>
        <td><xsl:attribute name="class">centered <xsl:if test="matched_checkout_date = 'N'">mismatched</xsl:if></xsl:attribute><xsl:value-of select="hw_checkout_date"/></td>
        <td><xsl:attribute name="class">centered <xsl:if test="matched_persons = 'N'">mismatched</xsl:if></xsl:attribute><xsl:value-of select="hw_persons"/></td>
        <td><xsl:attribute name="class">currency <xsl:if test="matched_payment_outstanding = 'N'">mismatched</xsl:if></xsl:attribute><xsl:value-of select="hw_payment_outstanding"/></td>
        <td>
            <xsl:attribute name="class">centered <xsl:if test="lh_status = 'cancelled'">cancelled</xsl:if></xsl:attribute>
            <xsl:choose>
                <xsl:when test="string-length(lh_data_href) > 0">
                    <a target="_blank"><xsl:attribute name="href">https://emea.littlehotelier.com<xsl:value-of select="lh_data_href"/></xsl:attribute>
                       <xsl:value-of select="booking_reference"/>
                    </a>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="booking_reference"/>
                </xsl:otherwise>
            </xsl:choose>
        </td>
        <td><xsl:attribute name="class">centered <xsl:if test="matched_room_type = 'N'">mismatched</xsl:if></xsl:attribute><xsl:value-of select="lh_room_type"/></td>
        <td><xsl:attribute name="class">centered <xsl:if test="matched_checkin_date = 'N'">mismatched</xsl:if></xsl:attribute><xsl:value-of select="lh_checkin_date"/></td>
        <td><xsl:attribute name="class">centered <xsl:if test="matched_checkout_date = 'N'">mismatched</xsl:if></xsl:attribute><xsl:value-of select="lh_checkout_date"/></td>
        <td><xsl:attribute name="class">centered <xsl:if test="matched_persons = 'N'">mismatched</xsl:if></xsl:attribute><xsl:value-of select="lh_persons"/></td>
        <td><xsl:attribute name="class">currency <xsl:if test="matched_payment_outstanding = 'N'">mismatched</xsl:if></xsl:attribute><xsl:value-of select="lh_payment_outstanding"/></td>
        <td><xsl:value-of select="notes"/></td>
    </tr>
</xsl:template>

</xsl:stylesheet>