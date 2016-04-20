<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output method="text" indent="no" omit-xml-declaration="yes" />

<xsl:template match="/view">
Bed Counts,<xsl:value-of select="selectiondate"/>

<xsl:choose>
    <xsl:when test="bedcounts/room">
        <xsl:apply-templates select="bedcounts"/>
    </xsl:when>
    <xsl:otherwise>No data available.</xsl:otherwise>
</xsl:choose>

</xsl:template>

<xsl:template match="bedcounts">

Room,Number of Beds,Room Type,Paid Beds,Staff Beds,No Shows,Empty Beds
<xsl:apply-templates select="room"/>

Totals,<xsl:value-of select="sum(room/capacity)"/>,,<xsl:value-of select="sum(room/num_paid)"/>,<xsl:value-of select="sum(room/num_staff)"/>,<xsl:value-of select="sum(room/num_noshow)"/>,<xsl:value-of select="sum(room/num_empty)"/>
Total Paid,<xsl:value-of select="sum(room/num_paid) + sum(room/num_noshow)"/>
Total Occupied,<xsl:value-of select="sum(room/num_paid) + sum(room/num_noshow) + sum(room/num_staff)"/>

</xsl:template>

<!-- each room; one per line -->
<xsl:template match="room">

<!-- newline after each new floor -->
<xsl:if test="id = 20 or id = 51 or id = 61 or id = 71">
<xsl:text>
</xsl:text>
</xsl:if>

<!-- newline on level 4 only if room 30 isn't present -->
<xsl:if test="id = 41 and 30 != preceding-sibling::room[1]/id">
<xsl:text>
</xsl:text>
</xsl:if>

<xsl:text>"</xsl:text><xsl:value-of select="id"/><xsl:text>",</xsl:text>
<xsl:value-of select="capacity"/><xsl:text>,</xsl:text>
<xsl:choose>
    <xsl:when test="room_type = 'LT_FEMALE'">LongTerm (F)</xsl:when>
    <xsl:when test="room_type = 'LT_MALE'">LongTerm (M)</xsl:when>
    <xsl:when test="room_type = 'DBL'">DOUBLE</xsl:when>
    <xsl:otherwise><xsl:value-of select="room_type"/></xsl:otherwise>
</xsl:choose><xsl:text>,</xsl:text>
<xsl:if test="num_paid != 0 or /view/write_zeroes = 'true'"><xsl:value-of select="num_paid"/></xsl:if><xsl:text>,</xsl:text>
<xsl:if test="num_staff != 0 or /view/write_zeroes = 'true'"><xsl:value-of select="num_staff"/></xsl:if><xsl:text>,</xsl:text>
<xsl:if test="num_noshow != 0 or /view/write_zeroes = 'true'"><xsl:value-of select="num_noshow"/></xsl:if><xsl:text>,</xsl:text>
<xsl:if test="num_empty != 0 or /view/write_zeroes = 'true'"><xsl:value-of select="num_empty"/></xsl:if>

<!-- newline after each record -->
<xsl:text>
</xsl:text>

</xsl:template>

</xsl:stylesheet>
