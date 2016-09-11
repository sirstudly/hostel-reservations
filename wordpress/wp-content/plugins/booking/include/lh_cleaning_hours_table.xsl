<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<xsl:output method="html" omit-xml-declaration="yes" encoding="UTF-8"/>

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->
<xsl:include href="lh_cleaning_hours_table_row.xsl"/>

<xsl:template match="tasks">

    <table id="cleaning_hours_tbl" class="allocation_view" width="100%" cellspacing="0" cellpadding="3" border="0">
        <thead>
            <th id="hdr_task_name">Task Name</th>
            <th id="hdr_task_description">Description</th>
            <th id="hdr_hours">Hours</th>
            <th id="hdr_cleaner">Cleaner</th>
            <th id="hdr_alloc_from">Start Allocation From</th>
            <th id="hdr_controls"/>
        </thead>
        <tbody>
            <xsl:apply-templates select="task"/>
            <tr>
                <td><input id="task_name" name="task_name" class="regular-text code" type="text" size="255" /></td>
                <td><input id="task_description" name="task_description" class="regular-text code" type="text" /></td>
                <td><input id="hours" name="hours" class="regular-text code" type="text" /></td>
                <td><input id="cleaner" name="sort_order" class="regular-text code" type="text" /></td>
                <td><input id="alloc_from" name="alloc_from" class="regular-text code" type="text" /></td>
                <td style="text-align: center;"><a href="javascript:save_cleaning_hours(jQuery('#task_id').val(), jQuery('#hours').val(), jQuery('#cleaner_id').val(), jQuery('#alloc_from').val() );">Save</a></td>
            </tr>
        </tbody>
    </table>
</xsl:template>

</xsl:stylesheet>