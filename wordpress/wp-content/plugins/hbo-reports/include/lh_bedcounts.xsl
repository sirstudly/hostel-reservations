<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output method="html" omit-xml-declaration="yes" encoding="UTF-8"/>

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->
<xsl:include href="inline_scripts.xsl"/>

<xsl:template match="/view">

    <div class="container mb-3">
        <div class="row">
            <div class="col-md-auto icon32" style="margin:10px 25px 10px 10px;"><img src="{homeurl}/wp-content/plugins/hbo-reports/img/bunkbed-48x48.png"/></div>
            <div class="col-md-auto mt-2"><h2>Bed Counts - <xsl:value-of select="selectiondate_long"/></h2></div>
        </div>
    </div>

    <div class="card text-center">
        <div class="card-header">
            <xsl:call-template name="show_bedcount_view" />
        </div>
        <div class="card-body">
            <xsl:choose>
                <xsl:when test="bedcounts/room">
                    <xsl:apply-templates select="bedcounts" />
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

<xsl:template name="show_bedcount_view">
    <form name="bedcount_view_form" action="" method="post" id="bedcount_view_form"  class="form-inline">
    <div class="container mt-1">
        <div class="row">
            <div class="col-2">
                <input style="width:100px;" type="text" data-date-format="yyyy-mm-dd"
                    value="{selectiondate}"  id="selectiondate"  name="selectiondate" 
                    onchange="document.getElementById('download_bedcounts').value = '';bedcount_view_form.submit();"/>
                <span class="bi-calendar-day ml-1"></span>
                <label for="selectiondate" style="justify-content: normal; margin-left: 20px;">Selection Date</label>
            </div>
    
            <div class="col-6 text-left">
                <p class="help-block mb-0">
                    <xsl:if test="last_completed_job">
                        This report aggregates data as it appeared on <xsl:value-of select="last_completed_job"/>.<br/>
                    </xsl:if>

                    <xsl:if test="last_job_status = 'failed'">
                        <div style="color: red;">The last update of this report failed to run.
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
    
            <div class="col-4">
                <div class="d-flex justify-content-end">
                    <input type="hidden" name="download_bedcounts" id="download_bedcounts" value="" />
                    <a title="Export this page to CSV format" data-toggle="tooltip" data-placement="top" href="javascript:void(0)" class="btn btn-outline-secondary" onclick="document.getElementById('download_bedcounts').value = 'true';bedcount_view_form.submit();">
                        Export <span class="bi-card-list ml-1"></span></a>
                    <span style="margin: 0 5px;"/>
    
                    <xsl:choose>
                        <xsl:when test="job_in_progress">
                            <a class="btn btn-primary disabled" href="javascript:void(0)">Update in Progress <span class="bi-arrow-repeat-white ml-1"/></a>
                        </xsl:when>
                        <xsl:otherwise>
                            <input type="hidden" name="bedcount_job" id="bedcount_job" value="" />
                            <a class="btn btn-primary" href="javascript:void(0)" onclick="document.getElementById('bedcount_job').value = 'true';bedcount_view_form.submit();">Reload Data <span class="bi-arrow-repeat-white ml-1"/></a>
                        </xsl:otherwise>
                    </xsl:choose>
                </div>

                <p class="help-block">
                    <xsl:if test="job_in_progress">
                        An update is already in progress. <br/>
                        Reload this page in a few minutes.
                    </xsl:if>
                </p>
            </div>
        </div>
    </div>
    </form>
</xsl:template>

<xsl:template match="bedcounts">

    <table class="table table-striped">
        <thead class="thead-dark">
            <tr>
                <th scope="col">Room</th>
                <th scope="col">Number of Beds</th>
                <th scope="col">Room Type</th>
                <th scope="col">Paid Beds</th>
                <th scope="col">Staff Beds</th>
                <th scope="col">No Shows</th>
                <th scope="col">Empty Beds</th>
            </tr>
        </thead>
        <tbody>
            <xsl:apply-templates select="room"/>
        </tbody>
        <tfoot>
            <tr class="table-primary font-weight-bold">
                <td>Total Beds</td>
                <td><xsl:value-of select="sum(room/capacity)"/></td>
                <td><!-- room type --></td>
                <td><xsl:value-of select="sum(room/num_paid)"/></td>
                <td><xsl:value-of select="sum(room/num_staff)"/></td>
                <td><xsl:value-of select="sum(room/num_noshow)"/></td>
                <td><xsl:value-of select="sum(room/num_empty)"/></td>
            </tr>
            <tr class="table-primary font-weight-bold">
                <td>Total Paid</td>
                <td><xsl:value-of select="sum(room/num_paid) + sum(room/num_noshow)"/></td>
                <td colspan="5"></td>
            </tr>
            <tr class="table-primary font-weight-bold">
                <td>Total Occupied</td>
                <td><xsl:value-of select="sum(room/num_paid) + sum(room/num_noshow) + sum(room/num_staff)"/></td>
                <td colspan="5"></td>
            </tr>
        </tfoot>
    </table>

</xsl:template>

<xsl:template match="room">
    <tr>
        <td><xsl:value-of select="id"/></td>
        <td><xsl:value-of select="capacity"/></td>
        <td>
            <xsl:choose>
                <xsl:when test="room_type = 'LT_FEMALE'">LongTerm (F)</xsl:when>
                <xsl:when test="room_type = 'LT_MALE'">LongTerm (M)</xsl:when>
                <xsl:when test="room_type = 'LT_MIXED'">LongTerm</xsl:when>
                <xsl:when test="room_type = 'DBL'">DOUBLE</xsl:when>
                <xsl:otherwise><xsl:value-of select="room_type"/></xsl:otherwise>
            </xsl:choose>
        </td>
        <td><xsl:value-of select="num_paid"/></td>
        <td><xsl:value-of select="num_staff"/></td>
        <td><xsl:value-of select="num_noshow"/></td>
        <td><xsl:value-of select="num_empty"/></td>
    </tr>
</xsl:template>

</xsl:stylesheet>