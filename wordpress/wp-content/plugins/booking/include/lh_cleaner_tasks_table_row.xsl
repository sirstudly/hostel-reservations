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
        <td><xsl:value-of select="name"/></td>
        <td><xsl:value-of select="description"/></td>
        <td><xsl:value-of select="default_hours"/></td>
        <td><xsl:value-of select="active"/></td>
        <td>Edit</td>
    </tr>
</xsl:template>

</xsl:stylesheet>