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

#job_history_table tbody tr:nth-child(odd) td {
	background-color: #e3e3e3;
}

#job_history_table tbody tr td {
	padding-left: 15px;
}

a.tooltip {
    outline:none; 
}

a.tooltip:hover {
    text-decoration:none;
} 

a.tooltip span {
    z-index:10;display:none; 
    padding:14px 20px;
    margin-top:-30px; 
    margin-left:28px;
    line-height:16px;
}

a.tooltip:hover span {
    display:inline; 
    position:absolute; 
    color:#111;
    border:1px solid #DCA; 
    background:#fffAF0;
}
    
/*CSS3 extras*/
a.tooltip span {
    border-radius:4px;
    box-shadow: 5px 5px 8px #CCC;
}

</style>

    <div id="report-container" class="wrap bookingpage">
        <h2>Job History</h2>
        <div class="wpdevbk">
    
            <div class="visibility_container" id="report_data_view">
                <xsl:choose>
                    <xsl:when test="record">
                        <xsl:call-template name="report_data"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <div style="margin-left:50px; margin-bottom: 20px; font-style: italic;"><h4>No jobs found.</h4></div>
                    </xsl:otherwise>
                </xsl:choose>
            </div>
        </div>

        <xsl:call-template name="write_inline_js"/>
        <xsl:call-template name="write_inline_css"/>
    </div>

</xsl:template>



<xsl:template name="report_data">
    <table id="job_history_table" class="allocation_view" width="100%" cellspacing="0" cellpadding="3" border="0">
        <thead>
            <th>Job ID</th>
            <th>Job Name</th>
            <th>Status</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Log File</th>
        </thead>
        <tbody>
            <xsl:apply-templates select="record"/>
        </tbody>
    </table>

</xsl:template>


<xsl:template match="record">
    <tr>
        <td><xsl:value-of select="job_id"/></td>
        <td>
            <xsl:choose>
                <xsl:when test="job_param">
                    <a href="javascript:void(0);" class="tooltip">
                        <xsl:value-of select="job_name"/>
                        <span><xsl:apply-templates select="job_param"/></span>
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
    <xsl:value-of select="name"/>: <xsl:value-of select="value"/><br/>
</xsl:template>

</xsl:stylesheet>