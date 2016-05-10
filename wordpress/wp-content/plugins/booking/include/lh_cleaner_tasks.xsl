<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<xsl:output method="html" omit-xml-declaration="yes" encoding="UTF-8"/>

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->
<xsl:include href="inline_scripts.xsl"/>
<xsl:include href="lh_cleaner_tasks_table.xsl"/>

<xsl:template match="view">

<style media="screen" type="text/css">

#cleaner_task_tbl tbody tr:nth-child(odd) td {
	background-color: #e3e3e3;
}

#cleaner_task_tbl tbody tr td {
    padding-left: 10px;
}

#cleaner_task_tbl tbody tr td input[type='text'] {
    width:90%;
}

#hdr_name {
	width: 20%;
}

#hdr_description {
	width: 25%;
}

#hdr_hours {
	width: 8%;
}

#hdr_active {
	width: 8%;
}

#hdr_daily_tasks {
	width: 8%;
}

#hdr_sort_order {
	width: 8%;
}

#hdr_frequency {
	width: 8%;
}

#hdr_controls {
	width: 15%;
}

</style>

    <div id="report-container" class="wrap bookingpage">
        <h2>Cleaner Tasks</h2>
        <div class="wpdevbk">
    
            <div style="height:1px;clear:both;margin-top:40px;"><xsl:comment/></div>
    
            <div class="visibility_container">
                <xsl:apply-templates select="tasks"/>
            </div>
        </div>

        <xsl:call-template name="write_inline_js"/>
        <xsl:call-template name="write_inline_css"/>
    </div>

</xsl:template>

</xsl:stylesheet>