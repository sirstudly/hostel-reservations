<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<xsl:output method="html" omit-xml-declaration="yes" encoding="UTF-8"/>

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->

<xsl:template match="view">
    <script type="text/javascript">
        <!-- add toggle scripts to apply to collapse/expand sections -->
        <xsl:apply-templates select="cleaner" mode="cleaner_assign_js"/>
    </script>
    <xsl:apply-templates select="cleaner" mode="cleaner_assign_tbl"/>
</xsl:template>

<!-- JS toggle to show/hide sections -->
<xsl:template match="cleaner" mode="cleaner_assign_js">
    jQuery(document).ready(function(){
        jQuery('#href_cleaner_<xsl:value-of select="id"/>_assign_tbl').click(function(){
            jQuery('#cleaner_<xsl:value-of select="id"/>_assign_tbl').slideToggle("slow");

            // toggle the arrow on the link that was clicked
            var $class_attr = jQuery(this).attr("class");
            jQuery(this).attr("class", $class_attr == "collapsed" ? "expanded" : "collapsed");
        });
    });
</xsl:template>

<!-- generate the table of bed assignments for each cleaner -->
<xsl:template match="cleaner" mode="cleaner_assign_tbl">
    <div>
        <xsl:attribute name="id">cleaner_<xsl:value-of select="id"/></xsl:attribute>
        <div style="margin-left: 20px;" class="cleaner_header">
            <a href="javascript:void(0);">
                <xsl:attribute name="class"><xsl:choose><xsl:when test="local-name(..) = 'cleaners'">collapsed</xsl:when><xsl:otherwise>expanded</xsl:otherwise></xsl:choose></xsl:attribute>
                <xsl:attribute name="id">href_cleaner_<xsl:value-of select="id"/>_assign_tbl</xsl:attribute>
                <xsl:value-of select="lastname"/>, <xsl:value-of select="firstname"/>
            </a>
        </div>
        <table class="allocation_view cleaner_bed_assign_tbl" width="100%" cellspacing="0" cellpadding="3" border="0">
            <xsl:attribute name="id">cleaner_<xsl:value-of select="id"/>_assign_tbl</xsl:attribute>
            <!-- by default, hide sections -->
            <xsl:attribute name="style"><xsl:if test="local-name(..) = 'cleaners'">display:none;</xsl:if></xsl:attribute>
            <thead>
                <tr>
                    <th>Room</th>
                    <th>Checkin Date</th>
                    <th>Checkout Date</th>
                </tr>
            </thead>
            <tbody>
                <xsl:apply-templates select="assignedbed"/>
                <tr>
                    <td>
                        <select>
                            <xsl:attribute name="id">assigned_room-<xsl:value-of select="id"/></xsl:attribute>
                            <xsl:apply-templates select="/view/rooms/room" mode="room_select">
                                <xsl:with-param name="editing_room_id" select= "editing_room_id" />
                            </xsl:apply-templates>
                        </select>
                    </td>
                    <td>
                        <input class="cleaner-bed-assign-calendar" type="text" name="checkin_date" size="6">
                            <xsl:attribute name="id">checkindate-<xsl:value-of select="id"/></xsl:attribute>
                            <xsl:attribute name="value"><xsl:value-of select="editing_checkin_date"/></xsl:attribute>
                        </input>
                        <xsl:if test="errors/error/element_id='checkin_date'">
                            <br/><xsl:value-of select="errors/error[element_id='checkin_date']/message"/>
                        </xsl:if>
                    </td>
                    <td>
                        <input class="cleaner-bed-assign-calendar" type="text" name="checkout_date" size="6">
                            <xsl:attribute name="id">checkoutdate-<xsl:value-of select="id"/></xsl:attribute>
                            <xsl:attribute name="value"><xsl:value-of select="editing_checkout_date"/></xsl:attribute>
                        </input>
                        <xsl:if test="errors/error/element_id='checkout_date'">
                            <br/><xsl:value-of select="errors/error[element_id='checkout_date']/message"/>
                        </xsl:if>
                    </td>
                    <td><a onclick="this.style.display='none';">
                            <xsl:attribute name="href">javascript:add_cleaner_bed_assignment(<xsl:value-of select="id"/>,document.getElementById('assigned_room-<xsl:value-of select="id"/>').value,document.getElementById('checkindate-<xsl:value-of select="id"/>').value,document.getElementById('checkoutdate-<xsl:value-of select="id"/>').value);</xsl:attribute>
                        Add</a><br/>
                        <xsl:if test="errors/error/element_id='add_assignment'"><b>(<xsl:value-of select="errors/error[element_id='add_assignment']/message"/>)</b></xsl:if>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</xsl:template>

<!-- gives a dropdown of each of the beds available -->
<xsl:template match="room" mode="room_select">
    <xsl:param name="editing_room_id" />
    <option>
        <xsl:attribute name="value">
            <xsl:value-of select="id"/>
        </xsl:attribute>
        <xsl:if test="$editing_room_id = id">
            <xsl:attribute name="selected">selected</xsl:attribute>
        </xsl:if>
        <xsl:value-of select="number"/> - <xsl:value-of select="bed"/>
    </option>
</xsl:template>

<!-- write out one row (one bed assignment) for a cleaner -->
<xsl:template match="assignedbed">
    <tr>
        <td><xsl:value-of select="room/bed"/></td>
        <td><xsl:value-of select="from"/></td>
        <td><xsl:value-of select="to"/></td>
    </tr>
</xsl:template>

</xsl:stylesheet>