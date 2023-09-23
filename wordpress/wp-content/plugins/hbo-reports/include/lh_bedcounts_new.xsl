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
    <table border="0" cellpadding="0" cellspacing="0" width="2648"
           style="border-collapse:collapse;table-layout:fixed;width:1977pt">
        <colgroup>
            <col width="71" span="2" style="width:53pt"/>
            <col width="37" style="mso-width-source:userset;mso-width-alt:1194;width:28pt"/>
            <col width="71" span="4" style="width:53pt"/>
            <col width="39" style="mso-width-source:userset;mso-width-alt:1237;width:29pt"/>
            <col width="71" span="4" style="width:53pt"/>
            <col width="39" style="mso-width-source:userset;mso-width-alt:1237;width:29pt"/>
            <col width="71" span="4" style="width:53pt"/>
            <col width="35" style="mso-width-source:userset;mso-width-alt:1109;width:26pt"/>
            <col width="71" span="4" style="width:53pt"/>
            <col width="36" style="mso-width-source:userset;mso-width-alt:1152;width:27pt"/>
            <col width="71" span="4" style="width:53pt"/>
            <col width="39" style="mso-width-source:userset;mso-width-alt:1237;width:29pt"/>
            <col width="71" span="4" style="width:53pt"/>
            <col width="37" style="mso-width-source:userset;mso-width-alt:1194;width:28pt"/>
            <col width="71" span="4" style="width:53pt"/>
            <col width="43" style="mso-width-source:userset;mso-width-alt:1365;width:32pt"/>
            <col width="71" span="3" style="width:53pt"/>
        </colgroup>
        <tbody>
            <tr height="20" style="height:15.0pt">
                <td height="20" width="71" style="height:15.0pt;width:53pt"></td>
                <td width="71" style="width:53pt"></td>
                <td width="37" style="width:28pt"></td>
                <td colspan="7" class="xl105" width="465" style="width:347pt">Week Commencing :
                    <xsl:value-of select="../week_starting"/>
                </td>
                <td width="71" style="width:53pt"></td>
                <td width="71" style="width:53pt"></td>
                <td width="39" style="width:29pt"></td>
                <td width="71" style="width:53pt"></td>
                <td width="71" style="width:53pt"></td>
                <td width="71" style="width:53pt"></td>
                <td width="71" style="width:53pt"></td>
                <td width="35" style="width:26pt"></td>
                <td width="71" style="width:53pt"></td>
                <td width="71" style="width:53pt"></td>
                <td width="71" style="width:53pt"></td>
                <td width="71" style="width:53pt"></td>
                <td width="36" style="width:27pt"></td>
                <td width="71" style="width:53pt"></td>
                <td width="71" style="width:53pt"></td>
                <td width="71" style="width:53pt"></td>
                <td width="71" style="width:53pt"></td>
                <td width="39" style="width:29pt"></td>
                <td width="71" style="width:53pt"></td>
                <td width="71" style="width:53pt"></td>
                <td width="71" style="width:53pt"></td>
                <td width="71" style="width:53pt"></td>
                <td width="37" style="width:28pt"></td>
                <td width="71" style="width:53pt"></td>
                <td width="71" style="width:53pt"></td>
                <td width="71" style="width:53pt"></td>
                <td width="71" style="width:53pt"></td>
                <td width="43" style="width:32pt"></td>
                <td width="71" style="width:53pt"></td>
                <td width="71" style="width:53pt"></td>
                <td width="71" style="width:53pt"></td>
            </tr>
            <tr height="20" style="height:15.0pt">
                <td height="20" colspan="3" style="height:15.0pt;mso-ignore:colspan"></td>
                <td colspan="4" class="xl78">Double bed=2 beds</td>
                <td class="xl78"></td>
                <td colspan="33" style="mso-ignore:colspan"></td>
            </tr>
            <tr height="21" style="height:16.0pt">
                <td height="21" colspan="41" style="height:16.0pt;mso-ignore:colspan"></td>
            </tr>
            <tr height="21" style="height:16.0pt">
                <td height="21" colspan="3" style="height:16.0pt;mso-ignore:colspan"></td>
                <td colspan="4" class="xl88" style="border-right:1.0pt solid black">Monday</td>
                <td class="xl81"></td>
                <td colspan="4" class="xl88" style="border-right:1.0pt solid black">Tuesday</td>
                <td></td>
                <td colspan="4" class="xl95" style="border-right:1.0pt solid black">Wednesday</td>
                <td class="xl81"></td>
                <td colspan="4" class="xl88" style="border-right:1.0pt solid black">Thursday</td>
                <td class="xl81"></td>
                <td colspan="4" class="xl88" style="border-right:1.0pt solid black">Friday</td>
                <td class="xl81"></td>
                <td colspan="4" class="xl88" style="border-right:1.0pt solid black">Saturday</td>
                <td class="xl81"></td>
                <td colspan="4" class="xl88" style="border-right:1.0pt solid black">Sunday</td>
                <td colspan="4" style="mso-ignore:colspan"></td>
            </tr>
            <tr height="20" style="height:15.0pt">
                <td height="20" class="xl79" style="height:15.0pt">Room</td>
                <td class="xl80" style="border-left:none">Total bed</td>
                <td></td>
                <td class="xl73" style="border-top:none">Paid</td>
                <td class="xl67" style="border-top:none;border-left:none">Staff</td>
                <td class="xl67" style="border-top:none;border-left:none">No Show</td>
                <td class="xl74" style="border-top:none;border-left:none">Empty</td>
                <td></td>
                <td class="xl73" style="border-top:none">Paid</td>
                <td class="xl67" style="border-top:none;border-left:none">Staff</td>
                <td class="xl67" style="border-top:none;border-left:none">No Show</td>
                <td class="xl74" style="border-top:none;border-left:none">Empty</td>
                <td></td>
                <td class="xl73" style="border-top:none">Paid</td>
                <td class="xl67" style="border-top:none;border-left:none">Staff</td>
                <td class="xl67" style="border-top:none;border-left:none">No Show</td>
                <td class="xl74" style="border-top:none;border-left:none">Empty</td>
                <td></td>
                <td class="xl73" style="border-top:none">Paid</td>
                <td class="xl67" style="border-top:none;border-left:none">Staff</td>
                <td class="xl67" style="border-top:none;border-left:none">No Show</td>
                <td class="xl74" style="border-top:none;border-left:none">Empty</td>
                <td></td>
                <td class="xl73" style="border-top:none">Paid</td>
                <td class="xl67" style="border-top:none;border-left:none">Staff</td>
                <td class="xl67" style="border-top:none;border-left:none">No Show</td>
                <td class="xl74" style="border-top:none;border-left:none">Empty</td>
                <td></td>
                <td class="xl73" style="border-top:none">Paid</td>
                <td class="xl67" style="border-top:none;border-left:none">Staff</td>
                <td class="xl67" style="border-top:none;border-left:none">No Show</td>
                <td class="xl74" style="border-top:none;border-left:none">Empty</td>
                <td></td>
                <td class="xl73" style="border-top:none">Paid</td>
                <td class="xl67" style="border-top:none;border-left:none">Staff</td>
                <td class="xl67" style="border-top:none;border-left:none">No Show</td>
                <td class="xl74" style="border-top:none;border-left:none">Empty</td>
                <td colspan="4" style="mso-ignore:colspan"></td>
            </tr>
            <xsl:apply-templates select="room|separator"/>
            <xsl:apply-templates select="daily_totals"/>
        </tbody>
    </table>
</xsl:template>

<xsl:template match="room">
    <tr height="20" style="height:15.0pt">
        <td height="20" class="xl69" align="right" style="height:15.0pt;border-top:none"><xsl:value-of select="id"/></td>
        <td class="xl70" align="right" style="border-top:none;border-left:none"><xsl:value-of select="capacity"/></td>
        <xsl:apply-templates select="selected_date"/>
        <td colspan="4" style="mso-ignore:colspan"></td>
    </tr>
</xsl:template>

<!-- blank row to separate rooms -->
<xsl:template match="separator">
    <tr height="20" style="height:15.0pt">
        <td height="20" class="xl71" style="height:15.0pt;border-top:none"><xsl:text>&#160;</xsl:text></td>
        <td class="xl72" style="border-top:none;border-left:none"><xsl:text>&#160;</xsl:text></td>
        <td></td>
        <td class="xl71" style="border-top:none"><xsl:text>&#160;</xsl:text></td>
        <td class="xl66" style="border-top:none;border-left:none"><xsl:text>&#160;</xsl:text></td>
        <td class="xl66" style="border-top:none;border-left:none"><xsl:text>&#160;</xsl:text></td>
        <td class="xl66" style="border-top:none;border-left:none"><xsl:text>&#160;</xsl:text></td>
        <td></td>
        <td class="xl71" style="border-top:none"><xsl:text>&#160;</xsl:text></td>
        <td class="xl66" style="border-top:none;border-left:none"><xsl:text>&#160;</xsl:text></td>
        <td class="xl66" style="border-top:none;border-left:none"><xsl:text>&#160;</xsl:text></td>
        <td class="xl66" style="border-top:none;border-left:none"><xsl:text>&#160;</xsl:text></td>
        <td></td>
        <td class="xl71" style="border-top:none"><xsl:text>&#160;</xsl:text></td>
        <td class="xl66" style="border-top:none;border-left:none"><xsl:text>&#160;</xsl:text></td>
        <td class="xl66" style="border-top:none;border-left:none"><xsl:text>&#160;</xsl:text></td>
        <td class="xl66" style="border-top:none;border-left:none"><xsl:text>&#160;</xsl:text></td>
        <td></td>
        <td class="xl71" style="border-top:none"><xsl:text>&#160;</xsl:text></td>
        <td class="xl66" style="border-top:none;border-left:none"><xsl:text>&#160;</xsl:text></td>
        <td class="xl66" style="border-top:none;border-left:none"><xsl:text>&#160;</xsl:text></td>
        <td class="xl72" style="border-top:none;border-left:none"><xsl:text>&#160;</xsl:text></td>
        <td></td>
        <td class="xl71" style="border-top:none"><xsl:text>&#160;</xsl:text></td>
        <td class="xl66" style="border-top:none;border-left:none"><xsl:text>&#160;</xsl:text></td>
        <td class="xl66" style="border-top:none;border-left:none"><xsl:text>&#160;</xsl:text></td>
        <td class="xl66" style="border-top:none;border-left:none"><xsl:text>&#160;</xsl:text></td>
        <td></td>
        <td class="xl71" style="border-top:none"><xsl:text>&#160;</xsl:text></td>
        <td class="xl66" style="border-top:none;border-left:none"><xsl:text>&#160;</xsl:text></td>
        <td class="xl66" style="border-top:none;border-left:none"><xsl:text>&#160;</xsl:text></td>
        <td class="xl66" style="border-top:none;border-left:none"><xsl:text>&#160;</xsl:text></td>
        <td></td>
        <td class="xl71" style="border-top:none"><xsl:text>&#160;</xsl:text></td>
        <td class="xl66" style="border-top:none;border-left:none"><xsl:text>&#160;</xsl:text></td>
        <td class="xl66" style="border-top:none;border-left:none"><xsl:text>&#160;</xsl:text></td>
        <td class="xl72" style="border-top:none;border-left:none"><xsl:text>&#160;</xsl:text></td>
        <td colspan="4" style="mso-ignore:colspan"></td>
    </tr>
</xsl:template>

<xsl:template match="selected_date">
    <td></td>
    <td class="xl69" style="border-top:none"><xsl:value-of select="num_paid"/></td>
    <td class="xl65" align="right" style="border-top:none;border-left:none"><xsl:value-of select="num_staff"/></td>
    <td class="xl65" style="border-top:none;border-left:none"><xsl:value-of select="num_noshow"/></td>
    <td class="xl70" align="right" style="border-top:none;border-left:none"><xsl:value-of select="num_empty"/></td>
</xsl:template>

<xsl:template match="daily_totals">
    <tr height="20" style="height:15.0pt">
        <td height="20" colspan="3" style="height:15.0pt;mso-ignore:colspan"></td>
        <td class="xl85" style="border-top:none">Paid</td>
        <td class="xl68" style="border-top:none;border-left:none">Staff</td>
        <td class="xl68" style="border-top:none;border-left:none">No Show</td>
        <td class="xl86" style="border-top:none;border-left:none">Empty</td>
        <td></td>
        <td class="xl85" style="border-top:none">Paid</td>
        <td class="xl68" style="border-top:none;border-left:none">Staff</td>
        <td class="xl68" style="border-top:none;border-left:none">No Show</td>
        <td class="xl86" style="border-top:none;border-left:none">Empty</td>
        <td></td>
        <td class="xl85" style="border-top:none">Paid</td>
        <td class="xl68" style="border-top:none;border-left:none">Staff</td>
        <td class="xl68" style="border-top:none;border-left:none">No Show</td>
        <td class="xl86" style="border-top:none;border-left:none">Empty</td>
        <td></td>
        <td class="xl85" style="border-top:none">Paid</td>
        <td class="xl68" style="border-top:none;border-left:none">Staff</td>
        <td class="xl68" style="border-top:none;border-left:none">No Show</td>
        <td class="xl86" style="border-top:none;border-left:none">Empty</td>
        <td></td>
        <td class="xl85" style="border-top:none">Paid</td>
        <td class="xl68" style="border-top:none;border-left:none">Staff</td>
        <td class="xl68" style="border-top:none;border-left:none">No Show</td>
        <td class="xl86" style="border-top:none;border-left:none">Empty</td>
        <td></td>
        <td class="xl85" style="border-top:none">Paid</td>
        <td class="xl68" style="border-top:none;border-left:none">Staff</td>
        <td class="xl68" style="border-top:none;border-left:none">No Show</td>
        <td class="xl86" style="border-top:none;border-left:none">Empty</td>
        <td></td>
        <td class="xl85" style="border-top:none">Paid</td>
        <td class="xl68" style="border-top:none;border-left:none">Staff</td>
        <td class="xl68" style="border-top:none;border-left:none">No Show</td>
        <td class="xl86" style="border-top:none;border-left:none">Empty</td>
        <td></td>
        <td colspan="3" class="xl102" style="border-right:1.0pt solid black">WEEKLY
            TOTALS</td>
    </tr>
    <tr height="20" style="height:15.0pt">
        <td height="20" colspan="3" style="height:15.0pt;mso-ignore:colspan"></td>
        <xsl:apply-templates select="totals_date"/>
        <td colspan="2" class="xl98">Total Paid</td>
        <td class="xl70" align="right" style="border-top:none;border-left:none"><xsl:value-of select="../weekly_totals/total_paid"/></td>
    </tr>
    <tr height="20" style="height:15.0pt">
        <td height="20" colspan="3" style="height:15.0pt;mso-ignore:colspan"></td>
        <xsl:apply-templates select="totals_date/total_paid"/>
        <td colspan="2" class="xl98">Total Occupied</td>
        <td class="xl70" align="right" style="border-top:none;border-left:none"><xsl:value-of select="../weekly_totals/total_occupied"/></td>
    </tr>
    <tr height="21" style="height:16.0pt">
        <td height="21" colspan="3" style="height:16.0pt;mso-ignore:colspan"></td>
        <xsl:apply-templates select="totals_date/total_occupied"/>
        <td colspan="2" class="xl100">Empty</td>
        <td class="xl77" align="right" style="border-top:none;border-left:none"><xsl:value-of select="../weekly_totals/total_empty"/></td>
    </tr>
</xsl:template>

<xsl:template match="totals_date">
    <td class="xl82" align="right"><xsl:value-of select="num_paid"/></td>
    <td class="xl83" align="right" style="border-left:none"><xsl:value-of select="num_staff"/></td>
    <td class="xl83" align="right" style="border-left:none"><xsl:value-of select="num_noshow"/></td>
    <td class="xl84" align="right" style="border-left:none"><xsl:value-of select="num_empty"/></td>
    <td></td>
</xsl:template>

<xsl:template match="total_paid">
    <td colspan="3" class="xl93">Total Paid</td>
    <td class="xl70" align="right" style="border-top:none;border-left:none"><xsl:value-of select="."/></td>
    <td></td>
</xsl:template>

<xsl:template match="total_occupied">
    <td colspan="3" class="xl91">Total Occupied</td>
    <td class="xl77" align="right" style="border-top:none;border-left:none"><xsl:value-of select="."/></td>
    <td></td>
</xsl:template>

</xsl:stylesheet>