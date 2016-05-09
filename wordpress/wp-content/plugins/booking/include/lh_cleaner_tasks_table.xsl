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
            <th id="hdr_controls"><!-- operation form controls --></th>
        </thead>
        <tbody>
            <xsl:apply-templates select="task"/>
            <tr>
                <td><input id="task_name" name="task_name" class="regular-text code" type="text" style="width:97%;" size="255" /></td>
                <td><input id="task_description" name="task_description" class="regular-text code" type="text" style="width:97%;" /></td>
                <td><input id="default_hours" name="default_hours" class="regular-text code" type="text" style="width:97%;" /></td>
                <td style="text-align: center;"><input type="checkbox" id="active_checkbox" name="active_checkbox" checked="checked" /></td>
                <td><a href="javascript:add_cleaner_task(document.getElementById('task_name').value, document.getElementById('task_description').value, document.getElementById('default_hours').value, document.getElementById('active_checkbox').checked);">Add Task</a></td>
            </tr>
        </tbody>
    </table>
</xsl:template>

</xsl:stylesheet>