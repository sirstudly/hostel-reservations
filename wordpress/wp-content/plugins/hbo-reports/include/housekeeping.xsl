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
.room_demarkation {
    padding-left: 20px;
    font-weight: bold;
    font-size: 140%;
}

.badge-empty {
    color: #fff;
    background-color: #B8B8B8;
    font-size: 100%;
}

.badge-nochange {
    color: #fff;
    background-color: #7A7A7A;
    font-size: 100%;
}

.badge-ndaychange {
    color: #fff;
    background-color: #C87F5B;
    font-size: 100%;
}

.badge-change {
    color: #fff;
    background-color: #3A87AD;
    font-size: 100%;
}
</style>

    <div class="container mb-3">
        <div class="row">
            <div class="col-md-auto ml-2"><h3><xsl:value-of select="selectiondate"/></h3></div>
        </div>
    </div>

    <div class="card text-center">
        <div class="card-header pb-0">
            <xsl:call-template name="report_header" />
        </div>
        <div class="card-body">
            <xsl:choose>
                <xsl:when test="bed">
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

    <form id="housekeeping_form" class="form-inline" method="post" action="" name="housekeeping_form">
    <div class="container mt-1">
        <div class="row">
            <div class="col-9">
                <p class="help-block font-italic text-left">
                    <xsl:choose>
                        <xsl:when test="job">
                            This report was last updated on: <xsl:value-of select="job/end_date"/>
                        </xsl:when>
                        <xsl:otherwise>
                            This report has never been updated for this date.
                        </xsl:otherwise>
                    </xsl:choose>
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
                        <xsl:when test="job_in_progress">
                            <a class="btn btn-primary disabled" href="javascript:void(0)">Update in Progress <span class="bi-arrow-repeat-white ml-1"/></a>
                        </xsl:when>
                        <xsl:otherwise>
                            <input type="hidden" name="housekeeping_job" id="housekeeping_job" value="" />
                            <a class="btn btn-primary" href="javascript:void(0)" onclick="document.getElementById('housekeeping_job').value = 'true';housekeeping_form.submit();">Refresh Now <span class="bi-arrow-repeat-white ml-1"/></a>
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

    <div class="container-fluid font-weight-bold">
        <xsl:if test="totals/level2">
            <div class="row">
                <div class="col">
                    20s : <xsl:value-of select="totals/level2"/>
                </div>
                <div class="col">
                    40s : <xsl:value-of select="totals/level4"/>
                </div>
                <div class="col">
                    50s : <xsl:value-of select="totals/level5"/>
                </div>
                <div class="col">
                    60s / 70s : <xsl:value-of select="totals/level6_7"/>
                </div>
            </div>
        </xsl:if>
        <div class="row mt-2 mb-2">
            <xsl:if test="totals/upstairs">
                <div class="col">
                    Upstairs : <xsl:value-of select="totals/upstairs" />
                </div>
            </xsl:if>
            <div class="col">
                Total : <xsl:value-of select="totals/total" />
            </div>
        </div>
    </div>

</xsl:template>

<xsl:template name="report_data">

    <table  class="table table-borderless table-hover table-sm">
        <thead class="thead-dark">
            <tr>
                <th scope="col" style="width: 50px;">Room</th>
                <th scope="col" style="width: 150px;">Bed</th>
                <th scope="col">Bedsheets</th>
            </tr>
        </thead>
        <tbody>
            <xsl:apply-templates select="bed" mode="bedsheet_row"/>
        </tbody>
    </table>

</xsl:template>

<xsl:template match="bed" mode="bedsheet_row">

    <xsl:choose>
        <xsl:when test="room = preceding-sibling::node()/room" />
        <xsl:otherwise>
            <tr>
                <td colspan="3" class="border_top border_bottom border_left border_right"><div class="room_demarkation">Room <xsl:value-of select="room"/> (<xsl:value-of select="room_type"/>)</div></td>
            </tr>
        </xsl:otherwise>
    </xsl:choose>
    <tr>
        <td class="text-left">
            <xsl:value-of select="room"/>
        </td>
        <td class="text-left">
            <xsl:value-of select="bed_name"/>
        </td>
        <td class="text-left">
            <span>
                <xsl:attribute name="class">
                    <xsl:text>badge </xsl:text>
                    <xsl:if test="bedsheet = 'CHANGE'">
                        <xsl:text>badge-change</xsl:text>
                    </xsl:if>
                    <xsl:if test="contains(bedsheet, 'DAY CHANGE')">
                        <xsl:text>badge-ndaychange</xsl:text>
                    </xsl:if>
                    <xsl:if test="bedsheet = 'NO CHANGE'">
                        <xsl:text>badge-nochange</xsl:text>
                    </xsl:if>
                    <xsl:if test="bedsheet = 'EMPTY'">
                        <xsl:text>badge-empty</xsl:text>
                    </xsl:if>
                </xsl:attribute>
                <xsl:value-of select="bedsheet"/>
            </span>
            <xsl:if test="contains(data_href, 'room_closures') and contains(bedsheet, 'CHANGE')">
                * Room closure. Please manually check this.
            </xsl:if>
        </td>
    </tr>

</xsl:template>

</xsl:stylesheet>