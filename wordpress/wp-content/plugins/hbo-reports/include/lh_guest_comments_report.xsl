<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<xsl:output method="html" omit-xml-declaration="yes" encoding="UTF-8"/>

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->
<xsl:include href="lh_guest_comments_report_data.xsl"/>
<xsl:include href="inline_scripts.xsl"/>

<xsl:template match="view">

<style media="screen" type="text/css">

.comment_header {
    float: left; 
    margin: 5px 0 0 0; 
    width: 100px;
    font: 12px/1.5 Arial,Helvetica,sans-serif; 
    font-weight: 700; 
    color: rgb(31,74,146);
}

.comment_text {
    float: left; 
    margin: 0 20px 0 20px;
}

</style>
<script type="text/javascript">
jQuery(document).ready(function(){
    // reset all the checkboxes
    jQuery('input[type="checkbox"]').prop('checked', false);
});
</script>

    <div class="container mb-3">
        <div class="row">
            <div class="col-md-auto mt-2 ml-2"><h2>Guest Comments</h2></div>
        </div>
    </div>

    <div class="card text-center">
        <div class="card-header">
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

        <div id="ajax_respond"><xsl:comment/><!-- ajax response here--></div>
        <xsl:call-template name="write_inline_js"/>
        <xsl:call-template name="write_inline_css"/>

</xsl:template>


<xsl:template name="report_header">
    <div class="container mt-1">
        <div class="row">
            <div class="w-100">
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
                <p class="help-block text-left" style="font-style: normal">Acknowledge each request once it's been handled (if applicable) by clicking on the checkbox next to the associated booking.<br/>
                     Showing <xsl:value-of select="count(record)"/> unacknowledged records.
                </p>
            </div>
            <div class="col-3">
                <div class="d-flex justify-content-end">
                    <p class="help-block">
                        <xsl:if test="job_in_progress">
                            Come back to this page in a few minutes.
                        </xsl:if>
                    </p>
                </div>
            </div>
        </div>
    </div>
</xsl:template>

</xsl:stylesheet>