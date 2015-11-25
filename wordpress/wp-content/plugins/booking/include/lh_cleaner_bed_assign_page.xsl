<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<xsl:output method="html" omit-xml-declaration="yes" encoding="UTF-8"/>

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->

<xsl:include href="lh_cleaner_bed_assign_page_content.xsl"/>

<xsl:template match="/view">

    <div id="ajax_respond">
        <div id="flip">Move along, nothing to see here.</div>
        <div id="cleaner_page_contents">
            <xsl:call-template name="cleaner_page_contents"/>
        </div>
    </div>

</xsl:template>

</xsl:stylesheet>