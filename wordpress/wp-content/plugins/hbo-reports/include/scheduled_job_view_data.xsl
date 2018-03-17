<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output method="html" omit-xml-declaration="yes" encoding="UTF-8"/>

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->
<xsl:include href="scheduled_job_view_include.xsl"/>

<xsl:template match="/view">
    <xsl:call-template name="job_schedule_table"/>
</xsl:template>

</xsl:stylesheet>