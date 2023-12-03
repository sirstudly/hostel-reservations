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

    <div class="d-flex">
        <span style="margin: 5px 10px 50px 60px;"/>
        <h2>Reservations Split Across Different Rooms</h2>
    </div>

    <div class="card text-center">
        <div class="card-header pb-0">
            <xsl:call-template name="report_header" />
        </div>
        <div class="card-body">
            <xsl:apply-templates select="split_room_report"/>
        </div>
    </div>

    <div class="d-flex mt-4 mb-4">
        <h4>Consecutive Bookings in Different Rooms/Beds</h4>
    </div>
    <div class="card text-center">
        <div class="card-body">
            <xsl:apply-templates select="multiple_booking_report"/>
        </div>
    </div>
    <xsl:call-template name="write_inline_js"/>
    <xsl:call-template name="write_inline_css"/>

</xsl:template>


<xsl:template name="report_header">

    <form name="report_form" action="" method="post" id="report_form" class="form-inline">
    <div class="container mt-1">
        <div class="row">
            <div class="col-9">
                <p class="help-block font-italic text-left">
                    <xsl:if test="last_completed_job">
                        This report was last run on <xsl:value-of select="last_completed_job"/>.
                    </xsl:if>
                    <xsl:if test="last_job_status = 'failed'">
                        <div class="text-left" style="color: red;">The last update of this report failed to run.
                            <xsl:choose>
                                <xsl:when test="check_credentials = 'true'">
                                    Credentials check failed.
                                </xsl:when>
                                <xsl:otherwise>
                                    Check the <a><xsl:attribute name="href"><xsl:value-of select="last_job_error_log"/></xsl:attribute>error log</a> for details.
                                </xsl:otherwise>
                            </xsl:choose>
                        </div>
                    </xsl:if>
                </p>
            </div>
            <div class="col-3">
                <div class="d-flex justify-content-end">
                    <xsl:choose>
                        <xsl:when test="last_submitted_job">
                            <a class="btn btn-primary disabled" href="javascript:void(0)">Update in Progress <span class="bi-arrow-repeat-white ml-1"/></a>
                        </xsl:when>
                        <xsl:otherwise>
                            <input type="hidden" name="reload_data" id="reload_data" value="true" />
                            <a class="btn btn-primary" href="javascript:void(0)" onclick="report_form.submit();">Reload Data <span class="bi-arrow-repeat-white ml-1"/></a>
                        </xsl:otherwise>
                    </xsl:choose>
                </div>

                <p class="help-block">
                    <xsl:if test="job_in_progress">
                        Come back to this page in a few minutes.
                    </xsl:if>
                </p>
            </div>
        </div>
    </div>
    </form>

</xsl:template>


<xsl:template match="split_room_report">

    <xsl:choose>
        <xsl:when test="record">
            <table id="split_room_rpt" class="table table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th scope="col">Guest Name(s)</th>
                        <th scope="col">Booking Reference</th>
                        <th scope="col">Booking Source</th>
                        <th scope="col">Checkin Date</th>
                        <th scope="col">Checkout Date</th>
                        <th scope="col">Booked Date</th>
                        <th scope="col">Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <xsl:apply-templates select="record"/>
                </tbody>
            </table>
        </xsl:when>
        <xsl:otherwise>
            <div class="ml-5 mb-2 mt-2 font-italic">
                <h6>No data available.</h6>
            </div>
        </xsl:otherwise>
    </xsl:choose>

<script type="text/javascript">
  jQuery('#split_room_rpt').DataTable({
    "paging": false,
    "searching": false,
    "order": [[3, 'asc']]
  });
</script>
</xsl:template>

<xsl:template match="multiple_booking_report">

    <xsl:choose>
        <xsl:when test="record">
            <table id="multiple_booking_rpt" class="table table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th scope="col">Guest Name(s)</th>
                        <th scope="col">Booking Reference</th>
                        <th scope="col">Checkin Date</th>
                        <th scope="col">Checkout Date</th>
                        <th scope="col">Booked Date</th>
                        <th scope="col">Room/Bed(s)</th>

                        <th scope="col">Booking Reference</th>
                        <th scope="col">Checkin Date</th>
                        <th scope="col">Checkout Date</th>
                        <th scope="col">Booked Date</th>
                        <th scope="col">Room/Bed(s)</th>
                    </tr>
                </thead>
                <tbody>
                    <xsl:apply-templates select="record"/>
                </tbody>
            </table>
        </xsl:when>
        <xsl:otherwise>
            <div class="ml-5 mb-2 mt-2 font-italic">
                <h6>No data available.</h6>
            </div>
        </xsl:otherwise>
    </xsl:choose>

    <script type="text/javascript">
        jQuery('#multiple_booking_rpt').DataTable({
        "paging": false,
        "searching": false,
        "order": [[2, 'asc']]
        });
    </script>
</xsl:template>


<xsl:template match="split_room_report/record">
    <tr>
        <td class="text-left"><a target="_blank">
               <xsl:choose>
                 <xsl:when test="../../property_manager = 'cloudbeds'">
                   <xsl:attribute name="href">https://hotels.cloudbeds.com<xsl:value-of select="data_href"/></xsl:attribute>
                 </xsl:when>
                 <xsl:otherwise>
                   <xsl:attribute name="href">https://app.littlehotelier.com<xsl:value-of select="data_href"/>?reservation_filter%5Bbooking_reference_id%5D=<xsl:value-of select="booking_reference"/>&amp;reservation_filter%5Bdate_from%5D=<xsl:value-of select="checkin_date_yyyymmdd"/>&amp;reservation_filter%5Bdate_to%5D=<xsl:value-of select="checkin_date_yyyymmdd"/></xsl:attribute>
                 </xsl:otherwise>  
               </xsl:choose>
               <xsl:value-of select="guest_name" />
            </a>
        </td>
        <td class="text-left"><xsl:value-of select="booking_reference"/></td>
        <td class="text-left" style="width: 120px;"><xsl:value-of select="booking_source"/></td>
        <td class="text-left"><xsl:attribute name="data-order"><xsl:value-of select="checkin_datetime"/></xsl:attribute><xsl:value-of select="checkin_date"/></td>
        <td class="text-left"><xsl:attribute name="data-order"><xsl:value-of select="checkout_datetime"/></xsl:attribute><xsl:value-of select="checkout_date"/></td>
        <td class="text-left"><xsl:attribute name="data-order"><xsl:value-of select="booked_datetime"/></xsl:attribute><xsl:value-of select="booked_date"/></td>
        <td class="text-left" style="max-width: 300px;"><xsl:value-of select="notes"/></td>
    </tr>
</xsl:template>

<xsl:template match="multiple_booking_report/record">
    <tr>
        <td class="text-left"><xsl:value-of select="guest_name"/></td>
        <td class="text-left"><a target="_blank">
            <xsl:attribute name="href">https://hotels.cloudbeds.com<xsl:value-of select="data_href_left"/></xsl:attribute>
            <xsl:value-of select="booking_ref_left" /></a>
        </td>
        <td class="text-left"><xsl:attribute name="data-order"><xsl:value-of select="checkin_datetime_left"/></xsl:attribute><xsl:value-of select="checkin_date_left"/></td>
        <td class="text-left"><xsl:attribute name="data-order"><xsl:value-of select="checkout_datetime_left"/></xsl:attribute><xsl:value-of select="checkout_date_left"/></td>
        <td class="text-left"><xsl:attribute name="data-order"><xsl:value-of select="booked_datetime_left"/></xsl:attribute><xsl:value-of select="booked_date_left"/></td>
        <td class="text-left"><xsl:value-of select="room_beds_left"/></td>

        <td class="text-left"><a target="_blank">
            <xsl:attribute name="href">https://hotels.cloudbeds.com<xsl:value-of select="data_href_right"/></xsl:attribute>
            <xsl:value-of select="booking_ref_right" /></a>
        </td>
        <td class="text-left"><xsl:attribute name="data-order"><xsl:value-of select="checkin_datetime_right"/></xsl:attribute><xsl:value-of select="checkin_date_right"/></td>
        <td class="text-left"><xsl:attribute name="data-order"><xsl:value-of select="checkout_datetime_right"/></xsl:attribute><xsl:value-of select="checkout_date_right"/></td>
        <td class="text-left"><xsl:attribute name="data-order"><xsl:value-of select="booked_datetime_right"/></xsl:attribute><xsl:value-of select="booked_date_right"/></td>
        <td class="text-left"><xsl:value-of select="room_beds_right"/></td>
    </tr>
</xsl:template>

</xsl:stylesheet>