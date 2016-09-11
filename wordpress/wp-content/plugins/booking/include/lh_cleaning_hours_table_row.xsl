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
                <td>task id/name dropdown</td>
                <td>readonly task description</td>
                <td><input id="edit_hours" name="hours" class="regular-text code" type="text" value="{hours}"/></td>
                <td><input id="alloc_from" name="alloc_from" class="regular-text code" type="text" value="{alloc_from}"/></td>
                <td>cleaner id/name dropdown</td>
                <td style="text-align: center;"><a href="javascript:update_cleaning_hours({id}, jQuery('#edit_task_name').val(), jQuery('#edit_task_description').val(), jQuery('#edit_default_hours').val(), jQuery('#edit_active_checkbox').prop('checked'), jQuery('#edit_daily_tasks_checkbox').prop('checked'), jQuery('#edit_sort_order').val(), jQuery('#edit_frequency').val());">OK</a>
                    <a style="margin-left: 25px;" href="javascript:cancel_edit_cleaning_hours();">Cancel</a></td>
            </xsl:when>
            <xsl:otherwise>
                <td><xsl:value-of select="task_name"/></td>
                <td><xsl:value-of select="task_description"/></td>
                <td><xsl:value-of select="hours"/></td>
                <td><xsl:value-of select="cleaner_name"/></td>
                <td><xsl:value-of select="alloc_from"/></td>
                <td style="text-align: center;"><a href="javascript:edit_cleaning_hours({id});">Edit</a></td>
            </xsl:otherwise>
        </xsl:choose>
    </tr>
</xsl:template>

</xsl:stylesheet>