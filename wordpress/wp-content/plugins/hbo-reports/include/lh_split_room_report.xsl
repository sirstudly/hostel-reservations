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
            <div class="col-7">
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
            <div class="col-5">
                <div class="d-flex justify-content-end">
                    <xsl:if test="split_room_report/record/hint_eligible or multiple_booking_report/record/hint_eligible">
                        <xsl:choose>
                            <xsl:when test="hints_in_progress">
                                <a class="btn btn-primary disabled mr-2" href="javascript:void(0)">Recalculating Hints <span class="bi-arrow-repeat-white ml-1"/></a>
                            </xsl:when>
                            <xsl:when test="last_submitted_job">
                                <a class="btn btn-primary disabled mr-2" href="javascript:void(0)">Recalculate Hints</a>
                            </xsl:when>
                            <xsl:otherwise>
                                <a class="btn btn-primary mr-2" href="javascript:void(0)" onclick="document.getElementById('reload_data').name = 'recalc_hints'; report_form.submit();">Recalculate Hints</a>
                            </xsl:otherwise>
                        </xsl:choose>
                    </xsl:if>
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
                        <th scope="col">Hint</th>
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
  var splitRoomTable = jQuery('#split_room_rpt').DataTable({
    "paging": false,
    "searching": false,
    "order": [[3, 'asc']],
    "columnDefs": [{ "targets": 7, "visible": false, "orderable": false }]
  });
  splitRoomTable.rows().every(function() {
    var hint = this.data()[7];
    if (jQuery.trim(hint)) {
      this.child(hint).show();
    }
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
                        <th scope="col">Hint</th>
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
  var multipleBookingTable = jQuery('#multiple_booking_rpt').DataTable({
    "paging": false,
    "searching": false,
    "order": [[2, 'asc']],
    "columnDefs": [{ "targets": 11, "visible": false, "orderable": false }]
  });
  multipleBookingTable.rows().every(function() {
    var hint = this.data()[11];
    if (jQuery.trim(hint)) {
      this.child(hint).show();
    }
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
        <td>
            <xsl:if test="shuffle_status or hint_eligible = 'true'">
                <div class="d-flex text-left">
                    <span class="font-weight-bold mr-2">Hint:</span>
                    <div><xsl:call-template name="shuffle_hint"/></div>
                </div>
            </xsl:if>
        </td>
    </tr>
</xsl:template>

<xsl:template name="shuffle_hint">
    <xsl:choose>
        <xsl:when test="shuffle_status and not(shuffle_hint)">
            <span class="font-italic">Nothing to do</span>
        </xsl:when>
        <xsl:when test="shuffle_status">
            <details>
                <summary>
                    <xsl:choose>
                        <xsl:when test="shuffle_status = 'FOUND' and shuffle_move_count = 1">1 move</xsl:when>
                        <xsl:when test="shuffle_status = 'FOUND'"><xsl:value-of select="shuffle_move_count"/> moves</xsl:when>
                        <xsl:when test="shuffle_status = 'INFEASIBLE' or shuffle_status = 'OVERBOOKED'">No single-bed fit, see options</xsl:when>
                        <xsl:when test="shuffle_status = 'ALREADY_ASSIGNED'">Nothing to do</xsl:when>
                        <xsl:when test="shuffle_status = 'UNKNOWN'">Timed out</xsl:when>
                        <xsl:otherwise><xsl:value-of select="shuffle_status"/></xsl:otherwise>
                    </xsl:choose>
                </summary>
                <div style="white-space: pre-line;"><xsl:value-of select="shuffle_hint"/></div>
            </details>
        </xsl:when>
        <xsl:when test="hint_eligible = 'true'">
            <span class="font-italic">Pending...</span>
        </xsl:when>
    </xsl:choose>
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
        <td>
            <xsl:if test="shuffle_status or hint_eligible = 'true'">
                <div class="d-flex text-left">
                    <span class="font-weight-bold mr-2">Hint:</span>
                    <div><xsl:call-template name="shuffle_hint"/></div>
                </div>
            </xsl:if>
        </td>
    </tr>
</xsl:template>

</xsl:stylesheet>