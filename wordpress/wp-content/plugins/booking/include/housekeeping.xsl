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

td.border_top {
    border-top: 1px solid #DFDFDF;
}

td.border_bottom {
    border-bottom: 1px solid #DFDFDF;
}

</style>

    <div class="wpdevbk wrap">
        <div class="booking-submenu-tab-container">
            <div class="nav-tabs booking-submenu-tab-insidecontainer">
                <form id="housekeeping_form" class="form-inline" method="post" action="" name="housekeeping_form">
                    <div style="text-align: center">
                        <h3 id="selected_date_label"><xsl:value-of select="selectiondate"/></h3>
                    </div>
                    <!-- show the last /completed/ job -->
                    <!-- also, if one is currently pending/submitted - disable the refresh button -->
                    <div class="control-group" style="float: right;">
                        <div class="inline controls">
                            <div class="btn-group">
                                <xsl:choose>
                                    <xsl:when test="job_in_progress">
                                        <a class="btn btn-primary disabled" style="float: right; margin-right: 15px;">Update in Progress <span class="icon-refresh icon-white"></span></a>
                                    </xsl:when>
                                    <xsl:otherwise>
                                        <input type="hidden" name="housekeeping_job" id="housekeeping_job" value="" />
                                        <a class="btn btn-primary" style="float: right; margin-right: 15px;" onclick="javascript:document.getElementById('housekeeping_job').value = 'true';housekeeping_form.submit();">Refresh Now <span class="icon-refresh icon-white"></span></a>
                                    </xsl:otherwise>
                                </xsl:choose>
                            </div>
                            <p class="help-block" style="float:left;padding-left:5px;padding-right:15px;font-style:italic;">
                            <xsl:choose>
                                <xsl:when test="job">
                                    This report was last updated on: <xsl:value-of select="job/end_date"/>
                                </xsl:when>
                                <xsl:otherwise>
                                    This report has never been updated for this date.
                                </xsl:otherwise>
                            </xsl:choose>
                            <br/>It is re-run daily at 6:30am, 9:00am and 10:20am.
                            </p>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!--
        Home URL: <xsl:value-of select="home_url"/><br/>
        <xsl:choose>
            <xsl:when test="job">
                ID: <xsl:value-of select="job/id"/><br/>
                Name: <xsl:value-of select="job/name"/><br/>
                Status: <xsl:value-of select="job/status"/><br/>
                Start Date: <xsl:value-of select="job/start_date"/><br/>
                End Date: <xsl:value-of select="job/end_date"/><br/>
            </xsl:when>
            <xsl:otherwise>
                No job defined.
            </xsl:otherwise>
        </xsl:choose>
        -->

        <table class="allocation_view" width="100%" cellspacing="0" cellpadding="3" border="0">
            <thead>
                <th>20s : <xsl:value-of select="totals/level2"/></th>
                <th>40s : <xsl:value-of select="totals/level4"/></th>
                <th>50s : <xsl:value-of select="totals/level5"/></th>
                <th>60s / 70s : <xsl:value-of select="totals/level6_7"/></th>
            </thead>
        </table>
        <table class="allocation_view" width="100%" cellspacing="0" cellpadding="3" border="0">
            <thead>
                <th style="text-align: left; padding-left: 20%;">Upstairs : <xsl:value-of select="totals/upstairs"/></th>
                <th style="text-align: left; padding-left: 20%;">Total : <xsl:value-of select="totals/total"/></th>
            </thead>
        </table>

        <div style="padding: 10px 0; background-color: #e3e3e3;"><xsl:comment/></div>

        <table class="allocation_view" width="100%" cellspacing="0" cellpadding="3" border="0">
            <thead>
                <th width="50">Room</th>
                <th width="150">Bed</th>
                <th>Bedsheets</th>
            </thead>
            <tbody>
                <xsl:apply-templates select="bed" mode="bedsheet_row"/>
            </tbody>
        </table>
    </div>

    <xsl:call-template name="write_inline_js"/>

</xsl:template>

<xsl:template match="bed" mode="bedsheet_row">

    <xsl:choose>
        <xsl:when test="room = preceding-sibling::node()/room" />
        <xsl:otherwise>
            <tr>
                <td colspan="3" class="border_top border_bottom border_left border_right"><div class="room_demarkation">Room <xsl:value-of select="room"/></div></td>
            </tr>
        </xsl:otherwise>
    </xsl:choose>
    <tr>
        <xsl:attribute name="class">
            <xsl:text>alloc_resource_attrib</xsl:text>
            <xsl:choose>
                <xsl:when test="position() mod 2">odd</xsl:when>
                <xsl:otherwise>even</xsl:otherwise>
            </xsl:choose>
        </xsl:attribute>

        <td class="border_left border_right" valign="top">
            <xsl:value-of select="room"/>
        </td>
        <td class="border_right" valign="top">
            <xsl:value-of select="bed_name"/>
        </td>
        <td>
            <!--
    guest name: <xsl:value-of select="guest_name"/><br/>
    checkin_date: <xsl:value-of select="checkin_date"/><br/>
    checkout_date: <xsl:value-of select="checkout_date"/><br/>
    data href:  <xsl:value-of select="data_href"/><br/>
    Bed Sheet:<br/>
            -->
        <span>
            <xsl:attribute name="class">
                <xsl:text>label </xsl:text>
                <xsl:if test="bedsheet = 'CHANGE'">
                    <xsl:text>label-change </xsl:text>
                </xsl:if>
                <xsl:if test="bedsheet = '3 DAY CHANGE'">
                    <xsl:text>label-3daychange </xsl:text>
                </xsl:if>
                <xsl:if test="bedsheet = 'NO CHANGE'">
                    <xsl:text>label-nochange </xsl:text>
                </xsl:if>
                <xsl:if test="bedsheet = 'EMPTY'">
                    <xsl:text>label-empty </xsl:text>
                </xsl:if>
            </xsl:attribute>
            <xsl:value-of select="bedsheet"/>
        </span>
        <xsl:if test="contains(data_href, 'room_closures') and ( bedsheet = 'CHANGE' or bedsheet = '3 DAY CHANGE' )">
            * Room closure. Please manually check this.
        </xsl:if>
        </td>
    </tr>

</xsl:template>

</xsl:stylesheet>