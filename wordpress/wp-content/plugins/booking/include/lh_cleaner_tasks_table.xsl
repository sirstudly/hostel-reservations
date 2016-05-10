<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<xsl:output method="html" omit-xml-declaration="yes" encoding="UTF-8"/>

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->
<xsl:include href="lh_cleaner_tasks_table_row.xsl"/>

<xsl:template match="tasks">

    <table id="cleaner_task_tbl" class="allocation_view" width="100%" cellspacing="0" cellpadding="3" border="0">
        <thead>
            <th id="hdr_name">Task Name</th>
            <th id="hdr_description">Description</th>
            <th id="hdr_hours">Default Hours</th>
            <th id="hdr_active">Active</th>
            <th id="hdr_daily_tasks">Show in Daily Tasks</th>
            <th id="hdr_sort_order">Sort Order</th>
            <th id="hdr_frequency">Frequency</th>
            <th id="hdr_controls"><!-- operation form controls --></th>
        </thead>
        <tbody>
            <xsl:apply-templates select="task"/>
            <tr>
                <td><input id="task_name" name="task_name" class="regular-text code" type="text" size="255" /></td>
                <td><input id="task_description" name="task_description" class="regular-text code" type="text" /></td>
                <td><input id="default_hours" name="default_hours" class="regular-text code" type="text" /></td>
                <td style="text-align: center;"><input type="checkbox" id="active_checkbox" name="active_checkbox" checked="checked" /></td>
                <td style="text-align: center;"><input type="checkbox" id="daily_tasks_checkbox" name="daily_tasks_checkbox" /></td>
                <td><input id="sort_order" name="sort_order" class="regular-text code" type="text" /></td>
                <td><input id="frequency" name="frequency" class="regular-text code" type="text" /></td>
                <td style="text-align: center;"><a href="javascript:add_cleaner_task(jQuery('#task_name').val(), jQuery('#task_description').val(), jQuery('#default_hours').val(), jQuery('#active_checkbox').prop('checked'), jQuery('#daily_tasks_checkbox').prop('checked'), jQuery('#sort_order').val(), jQuery('#frequency').val() );">Add Task</a></td>
            </tr>
        </tbody>
    </table>
</xsl:template>

</xsl:stylesheet>