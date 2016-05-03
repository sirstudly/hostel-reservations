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

<style media="screen" type="text/css">

#cleaner_task_tbl tbody tr:nth-child(odd) td {
	background-color: #e3e3e3
}

#hdr_name {
	width: 30%;
}

#hdr_description {
	width: 40%;
}

#hdr_hours {
	width: 10%;
}

#hdr_active {
	width: 10%;
}

#hdr_controls {
	width: 10%;
}

</style>

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
                <td><input name="task_name" class="regular-text code" type="text" style="width:97%;" size="255" /></td>
                <td><input name="task_description" class="regular-text code" type="text" style="width:97%;" /></td>
                <td><input name="default_hours" class="regular-text code" type="text" style="width:97%;" /></td>
                <td style="text-align: center;"><input type="checkbox" name="active_checkbox" checked="checked" /></td>
                <td><a href="#">Add Task</a></td>
            </tr>
        </tbody>
    </table>
</xsl:template>

</xsl:stylesheet>