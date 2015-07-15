<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->
<xsl:include href="inline_scripts.xsl"/>

<xsl:template match="/view">

<style media="screen" type="text/css">
#bedcounts_rpt tbody tr:nth-child(odd) td,#bedcounts_rpt tbody tr:nth-child(odd) th {
	background-color: #e3e3e3;
}

#bedcounts_rpt tbody tr td {
    text-align: center;
}

#bedcounts_rpt tfoot tr td {
    text-align: center;
    font-weight: bold;
}

</style>

    <div id="wpdev-booking-bedcounts" class="wrap bookingpage">
        <div class="icon32" style="margin:10px 25px 10px 10px;"><img src="{homeurl}/wp-content/plugins/booking/img/bunkbed-48x48.png"/><br /></div>
        <h2>Bed Counts - <xsl:value-of select="selectiondate_long"/></h2>
        <div class="wpdevbk">
    
            <div style="margin-top:10px;" class="booking-submenu-tab-container">
                <div class="nav-tabs booking-submenu-tab-insidecontainer">

                    <div id="filter" class="visibility_container active">
                        <xsl:call-template name="show_bedcount_view"/>
                    </div>

                </div>
            </div>

            <div style="height:1px;clear:both;margin-top:40px;"><xsl:comment/></div>
    
            <div class="visibility_container" id="bedcount_view">
                <xsl:choose>
                    <xsl:when test="bedcounts/room">
                        <xsl:apply-templates select="bedcounts"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <div style="margin-left:50px; margin-bottom: 20px; font-style: italic;"><h4>No data available.</h4></div>
                    </xsl:otherwise>
                </xsl:choose>
                <xsl:comment/>
            </div>
        </div>

        <xsl:call-template name="write_inline_js"/>
        <xsl:call-template name="write_inline_css"/>
    </div>

</xsl:template>

<xsl:template name="show_bedcount_view">
    <div style="clear:both;height:1px;"><xsl:comment/></div>
    <div class="wpdevbk-filters-section ">

        <form  name="bedcount_view_form" action="" method="post" id="bedcount_view_form"  class="form-inline">
            <a class="btn btn-primary" style="float: left; margin-right: 15px;"
                onclick="javascript:document.getElementById('download_bedcounts').value = '';bedcount_view_form.submit();">Apply <span class="icon-refresh icon-white"></span>
            </a>

            <div class="control-group" style="float:left;">
                <label for="selectiondate" class="control-label"><xsl:comment/></label>
                <div class="inline controls">
                    <div class="btn-group">
                        <input style="width:100px;" type="text" class="span2span2 wpdevbk-filters-section-calendar" 
                            value="{selectiondate}"  id="selectiondate"  name="selectiondate" />
                        <span class="add-on"><span class="icon-calendar"></span></span>
                    </div>
                <p class="help-block" style="float:left;padding-left:5px;">Selection Date</p>
                </div>
            </div>
    
            <div class="control-group" style="float:left;">
                <p class="help-block" style="float:left;padding-left:5px;font-style: italic;">
                    <xsl:if test="last_completed_job">
                        This report aggregates data as it appeared on <xsl:value-of select="last_completed_job"/>.
                    </xsl:if>
                </p>
            </div>
    
            <div class="btn-group" style="float:right;">
                <div class="inline controls">
                    <div class="btn-group">
                        <xsl:choose>
                            <xsl:when test="job_in_progress">
                                <a class="btn btn-primary disabled" style="float: right; margin-right: 15px;">Update in Progress <span class="icon-refresh icon-white"></span></a>
                            </xsl:when>
                            <xsl:otherwise>
                                <input type="hidden" name="bedcount_job" id="bedcount_job" value="" />
                                <a class="btn btn-primary" style="float: right; margin-right: 15px;" onclick="javascript:document.getElementById('bedcount_job').value = 'true';bedcount_view_form.submit();">Reload Data <span class="icon-refresh icon-white"></span></a>
                            </xsl:otherwise>
                        </xsl:choose>
                        <span style="padding-left:10px;"><input type="hidden" name="download_bedcounts" id="download_bedcounts" value="" /></span>
                        <a data-original-title="Export this page to CSV format"  rel="tooltip" class="tooltip_top btn" onclick="javascript:document.getElementById('download_bedcounts').value = 'true';bedcount_view_form.submit();">
                            Export <span class="icon-list"></span></a>
                    </div>
                <p class="help-block" style="float:left;padding-left:5px;">
                    <xsl:if test="job_in_progress">
                        An update is already in progress. <br/>
                        Re-apply the filter on this form in a few minutes.
                    </xsl:if>
                </p>
                </div>
            </div>

            <div class="clear"><xsl:comment/></div>
        </form>

    </div>
    <div style="clear:both;height:1px;"><xsl:comment/></div>
</xsl:template>

<xsl:template match="bedcounts">

    <table id="bedcounts_rpt" class="allocation_view" width="100%" cellspacing="0" cellpadding="3" border="0">
        <thead>
            <th>Room</th>
            <th>Number of Beds</th>
            <th>Room Type</th>
            <th>Paid Beds</th>
            <th>Staff Beds</th>
            <th>No Shows</th>
            <th>Empty Beds</th>
        </thead>
        <tbody>
            <xsl:apply-templates select="room"/>
        </tbody>
        <tfoot>
            <tr>
                <td><b>Totals</b></td>
                <td><xsl:value-of select="sum(room/capacity)"/></td>
                <td><!-- room type --></td>
                <td><xsl:value-of select="sum(room/num_paid)"/></td>
                <td><xsl:value-of select="sum(room/num_staff)"/></td>
                <td><xsl:value-of select="sum(room/num_noshow)"/></td>
                <td><xsl:value-of select="sum(room/num_empty)"/></td>
            </tr>
            <tr>
                <td>Total Paid</td>
                <td><xsl:value-of select="sum(room/num_paid) + sum(room/num_noshow)"/></td>
            </tr>
            <tr>
                <td>Total Occupied</td>
                <td><xsl:value-of select="sum(room/num_paid) + sum(room/num_noshow) + sum(room/num_staff)"/></td>
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