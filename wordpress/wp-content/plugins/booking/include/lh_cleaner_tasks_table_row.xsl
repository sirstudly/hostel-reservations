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
                <td><input id="task_name" name="task_name" class="regular-text code" type="text" style="width:97%;" size="255" value="{name}"/></td>
                <td><input id="task_description" name="task_description" class="regular-text code" type="text" style="width:97%;" value="{description}" /></td>
                <td><input id="default_hours" name="default_hours" class="regular-text code" type="text" style="width:97%;" value="{default_hours}"/></td>
                <td style="text-align: center;"><input type="checkbox" id="active_checkbox" name="active_checkbox"><xsl:if test="active = 'true'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if></input></td>
                <td><a href="javascript:update_cleaner_task({id}, document.getElementById('task_name').value, document.getElementById('task_description').value, document.getElementById('default_hours').value, document.getElementById('active_checkbox').checked);">OK</a>
                    <a style="margin-left: 25px;" href="javascript:cancel_edit_cleaner_task();">Cancel</a></td>
            </xsl:when>
            <xsl:otherwise>
                <td><xsl:value-of select="name"/></td>
                <td><xsl:value-of select="description"/></td>
                <td><xsl:value-of select="default_hours"/></td>
                <td style="text-align: center;"><input type="checkbox" id="active_checkbox" name="active_checkbox" disabled="disabled"><xsl:if test="active = 'true'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if></input></td>
                <td><a href="javascript:edit_cleaner_task({id});">Edit</a></td>
            </xsl:otherwise>
        </xsl:choose>
    </tr>
</xsl:template>

</xsl:stylesheet>