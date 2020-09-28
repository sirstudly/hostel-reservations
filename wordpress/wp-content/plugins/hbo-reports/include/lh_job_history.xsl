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

    <div class="container mb-3">
        <div class="row">
            <div class="col-md-auto mt-2 ml-2"><h2>Job History</h2></div>
        </div>
    </div>

    <div class="card text-center">
        <div class="card-body">
            <xsl:choose>
                <xsl:when test="record">
                    <xsl:call-template name="report_data"/>
                </xsl:when>
                <xsl:otherwise>
                    <div class="ml-5 mb-2 mt-2 font-italic">
                        <h6>No jobs found.</h6>
                    </div>
                </xsl:otherwise>
            </xsl:choose>
        </div>
    </div>

    <xsl:call-template name="write_inline_js"/>
    <xsl:call-template name="write_inline_css"/>

</xsl:template>

<xsl:template name="report_data">
    <table id="job_history_table" class="table table-striped">
        <thead class="thead-dark">
            <th scope="col">Job ID</th>
            <th scope="col">Job Name</th>
            <th scope="col">Status</th>
            <th scope="col">Start Date</th>
            <th scope="col">End Date</th>
            <th scope="col">Log File</th>
        </thead>
        <tbody>
            <xsl:apply-templates select="record"/>
        </tbody>
    </table>

</xsl:template>


<xsl:template match="record">
    <tr>
        <td><xsl:value-of select="job_id"/></td>
        <td class="text-left">
            <xsl:choose>
                <xsl:when test="job_param">
                    <a href="javascript:void(0)" data-toggle="tooltip" data-html="true" data-trigger="hover focus click">
                        <xsl:attribute name="title"><xsl:apply-templates select="job_param"/></xsl:attribute>
                        <xsl:value-of select="job_name"/>
                    </a>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="job_name"/>
                </xsl:otherwise>
            </xsl:choose>
        </td>
        <td><xsl:value-of select="status"/></td>
        <td><xsl:value-of select="start_date"/></td>
        <td><xsl:value-of select="end_date"/></td>
        <td><xsl:if test="log_file"><a><xsl:attribute name="href"><xsl:value-of select="log_file"/></xsl:attribute>job-<xsl:value-of select="job_id"/>.log</a></xsl:if></td>
    </tr>
</xsl:template>

<xsl:template match="job_param">
    <xsl:value-of select="name"/>: <xsl:value-of select="value"/>&lt;br&gt;
</xsl:template>

</xsl:stylesheet>