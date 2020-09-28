<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output method="html" omit-xml-declaration="yes" encoding="UTF-8"/>

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->
<xsl:template name="job_schedule_table">

    <xsl:choose>
        <xsl:when test="job">
            <xsl:call-template name="job_schedule_data"/>
        </xsl:when>
        <xsl:otherwise>
            <div style="margin-left:50px; margin-bottom: 20px; font-style: italic;"><h5>No jobs defined.</h5></div>
        </xsl:otherwise>
    </xsl:choose>
    <xsl:comment/>

</xsl:template>

<xsl:template name="job_schedule_data">

    <table id="job_schedules" class="table table-striped">
        <thead class="thead-dark">
            <th scope="col" class="text-center">Enabled</th>
            <th scope="col">Job Type</th>
            <th scope="col">Parameters</th>
            <th scope="col">Frequency</th>
            <th scope="col">Last Run</th>
            <th><!-- Action --></th>
        </thead>
        <tbody>
            <xsl:apply-templates select="job"/>
        </tbody>
    </table>

</xsl:template>

<xsl:template match="job">
    <tr>
        <td class="text-center"><input type="checkbox">
                <xsl:if test="active = 'yes'">
                    <xsl:attribute name="checked">checked</xsl:attribute>
                </xsl:if>
                <xsl:attribute name="onclick">toggle_scheduled_job(<xsl:value-of select="id"/>);</xsl:attribute>
            </input>
        </td>
        <td><xsl:value-of select="job-name"/></td>
        <td><xsl:apply-templates select="param"/></td>
        <td>
             <xsl:choose>
                <xsl:when test="repeat-time-min">Every <xsl:value-of select="repeat-time-min"/> minutes</xsl:when>
                <xsl:otherwise>Everyday at <xsl:value-of select="repeat-daily-at"/></xsl:otherwise>
            </xsl:choose>
        </td>
        <td><xsl:value-of select="last_run_date"/></td>
        <td>
            <xsl:attribute name="id">delete_scheduled_job_<xsl:value-of select="id"/></xsl:attribute>
            <a class="btn btn-primary">
                <xsl:attribute name="onclick">delete_scheduled_job(<xsl:value-of select="id"/>);</xsl:attribute>
                Delete
            </a>
        </td>
    </tr>
</xsl:template>

<xsl:template match="param">
    <xsl:value-of select="name"/> = <xsl:value-of select="value"/><br/>
</xsl:template>

</xsl:stylesheet>