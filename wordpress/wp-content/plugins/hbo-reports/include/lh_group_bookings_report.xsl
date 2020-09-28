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
        <span class="bi-book" style="margin: 5px 10px 5px 100px;"/>
        <h2>Bookings with <xsl:value-of select="group_size"/> or More Guests</h2>
    </div>

    <div class="card text-center">
        <div class="card-header pb-0">
            <xsl:call-template name="report_header" />
        </div>
        <div class="card-body">
            <xsl:choose>
                <xsl:when test="record">
                    <xsl:call-template name="report_data"/>
                </xsl:when>
                <xsl:otherwise>
                    <div class="ml-5 mb-2 mt-2 font-italic">
                        <h6>No data available.</h6>
                    </div>
                </xsl:otherwise>
            </xsl:choose>
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


<xsl:template name="report_data">
    <xsl:if test="../property_manager != 'cloudbeds'">
        <p style="padding-left: 20px;"><strong>BOLD</strong> entries have not been viewed before in Little Hotelier.</p>
    </xsl:if>

    <table id="group_bookings_rpt" class="table table-striped table-hover">
        <thead class="thead-dark">
            <tr>
                <th scope="col">Guest Name(s)</th>
                <th scope="col">Booking Reference</th>
                <th scope="col" style="width: 120px;">Booking Source</th>
                <th scope="col">Checkin Date</th>
                <th scope="col">Checkout Date</th>
                <th scope="col">Booked Date</th>
                <th scope="col">Payment<br/>Outstanding</th>
                <th scope="col">Number of<br/>Guests</th>
            </tr>
        </thead>
        <tbody>
            <xsl:apply-templates select="record"/>
        </tbody>
    </table>

<script type="text/javascript">
  jQuery('#group_bookings_rpt').DataTable({
    "paging": false,
    "searching": false,
    "order": [[3, 'asc']]
  });
</script>

</xsl:template>


<xsl:template match="record">
    <tr data-toggle="tooltip" data-html="true">
        <xsl:attribute name="class">
            <xsl:if test="viewed_yn = 'N'">unread</xsl:if>
        </xsl:attribute>
        <xsl:attribute name="title">
            <xsl:value-of select="notes"/>
        </xsl:attribute>
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
        <td class="text-left"><xsl:attribute name="data-order"><xsl:value-of select="checkin_datetime"/></xsl:attribute><xsl:value-of select="checkin_date"/></td>
        <td class="text-left"><xsl:attribute name="data-order"><xsl:value-of select="checkout_datetime"/></xsl:attribute><xsl:value-of select="checkout_date"/></td>
        <td class="text-left"><xsl:attribute name="data-order"><xsl:value-of select="booked_datetime"/></xsl:attribute><xsl:value-of select="booked_date"/></td>
        <td class="text-right"><p class="mr-3"><xsl:value-of select="payment_outstanding"/></p></td>
        <td><xsl:value-of select="num_guests"/></td>
    </tr>
</xsl:template>

</xsl:stylesheet>