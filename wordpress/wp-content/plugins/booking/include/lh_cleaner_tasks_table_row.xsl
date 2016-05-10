<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<xsl:output method="html" omit-xml-declaration="yes" encoding="UTF-8"/>

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->
<xsl:template match="task">
    <tr id="task{id}">
        <xsl:choose>
            <xsl:when test="id = ../editing_task_id">
                <td><input id="edit_task_name" name="task_name" class="regular-text code" type="text" size="255" value="{name}"/></td>
                <td><input id="edit_task_description" name="task_description" class="regular-text code" type="text" value="{description}" /></td>
                <td><input id="edit_default_hours" name="default_hours" class="regular-text code" type="text" value="{default_hours}"/></td>
                <td style="text-align: center;"><input type="checkbox" id="edit_active_checkbox" name="active_checkbox"><xsl:if test="active = 'true'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if></input></td>
                <td style="text-align: center;"><input type="checkbox" id="edit_daily_tasks_checkbox" name="daily_tasks_checkbox"><xsl:if test="show_in_daily_tasks = 'true'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if></input></td>
                <td><input id="edit_sort_order" name="sort_order" class="regular-text code" type="text" value="{sort_order}"/></td>
                <td><input id="edit_frequency" name="frequency" class="regular-text code" type="text" value="{frequency}"/></td>
                <td style="text-align: center;"><a href="javascript:update_cleaner_task({id}, jQuery('#edit_task_name').val(), jQuery('#edit_task_description').val(), jQuery('#edit_default_hours').val(), jQuery('#edit_active_checkbox').prop('checked'), jQuery('#edit_daily_tasks_checkbox').prop('checked'), jQuery('#edit_sort_order').val(), jQuery('#edit_frequency').val());">OK</a>
                    <a style="margin-left: 25px;" href="javascript:cancel_edit_cleaner_task();">Cancel</a></td>
            </xsl:when>
            <xsl:otherwise>
                <td><xsl:value-of select="name"/></td>
                <td><xsl:value-of select="description"/></td>
                <td><xsl:value-of select="default_hours"/></td>
                <td style="text-align: center;"><input type="checkbox" name="active_checkbox" disabled="disabled"><xsl:if test="active = 'true'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if></input></td>
                <td style="text-align: center;"><input type="checkbox" name="daily_tasks_checkbox" disabled="disabled"><xsl:if test="show_in_daily_tasks = 'true'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if></input></td>
                <td><xsl:value-of select="sort_order"/></td>
                <td><xsl:value-of select="frequency"/></td>
                <td style="text-align: center;"><a href="javascript:edit_cleaner_task({id});">Edit</a></td>
            </xsl:otherwise>
        </xsl:choose>
    </tr>
</xsl:template>

</xsl:stylesheet>