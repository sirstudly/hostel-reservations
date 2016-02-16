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

#guest_comments_rpt {
    margin: 0 10px 0 10px;
}

#guest_comments_rpt tbody tr:nth-child(4n+1) td {
	background-color: #e3e3e3
}

#guest_comments_rpt tbody tr:nth-child(4n+2) td {
	background-color: #e3e3e3
}

#guest_comments_rpt tbody tr td {
    padding-left: 20px; 
}

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

    <div id="report-container" class="wrap bookingpage">
        <h2>Guest Comments</h2>
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
                        <xsl:call-template name="report_data"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <div style="margin-left:50px; margin-bottom: 20px; font-style: italic;"><h4>No data available.</h4></div>
                    </xsl:otherwise>
                </xsl:choose>
            </div>
        </div>

        <div id="ajax_respond"><xsl:comment/><!-- ajax response here--></div>

        <xsl:call-template name="write_inline_js"/>
        <xsl:call-template name="write_inline_css"/>
    </div>

</xsl:template>


<xsl:template name="report_header">
    <div style="clear:both;height:1px;"><xsl:comment/></div>
    <div class="wpdevbk-filters-section ">

        <div class="control-group" style="float:left;">
            <xsl:if test="last_completed_job">
                <p class="help-block" style="padding-left:5px;font-style: italic; width: 100%">
                    This report was last run on <xsl:value-of select="last_completed_job"/>.<br/>
                    It is automatically run daily at 7:00am.
                </p>
            </xsl:if>
            <xsl:if test="last_job_status = 'failed'">
                <div style="color: red;">The last update of this report failed to run.
                    <xsl:choose>
                        <xsl:when test="check_credentials = 'true'">
                            Has the LittleHotelier password changed recently? If so, update it on the admin page.
                        </xsl:when>
                        <xsl:otherwise>
                            Check the <a><xsl:attribute name="href"><xsl:value-of select="last_job_error_log"/></xsl:attribute>error log</a> for details.
                        </xsl:otherwise>
                    </xsl:choose>
                </div>
            </xsl:if>
        </div>
    
        <div class="btn-group" style="float:right;">
            <div class="inline controls">
                <div class="btn-group">
                    <xsl:choose>
                        <xsl:when test="last_submitted_job">
                            <a class="btn btn-primary disabled" style="float: right; margin-right: 15px;">Update in Progress <span class="icon-refresh icon-white"></span></a>
                        </xsl:when>
                        <xsl:otherwise>
                            <form name="report_form" action="" method="post" id="report_form" class="form-inline">
                                <input type="hidden" name="reload_data" id="reload_data" value="true" />
                                <a class="btn btn-primary" style="float: right; margin-right: 15px;" onclick="javascript:report_form.submit();">Reload Data <span class="icon-refresh icon-white"></span></a>
                            </form>
                        </xsl:otherwise>
                    </xsl:choose>
                </div>
            <p class="help-block" style="float:left;padding-left:5px;padding-right:15px;font-style:italic;">
                <xsl:if test="last_submitted_job">
                    Come back to this page in a few minutes.
                </xsl:if>
            </p>
            </div>
        </div>

        <div style="clear:both;">Acknowledge each request once it's been handled (if applicable) by clicking on the checkbox next to the associated booking.<br/>
        Showing <xsl:value-of select="count(record)"/> unacknowledged records.</div>

    </div>
    <div style="clear:both;height:1px;"><xsl:comment/></div>

</xsl:template>

</xsl:stylesheet>