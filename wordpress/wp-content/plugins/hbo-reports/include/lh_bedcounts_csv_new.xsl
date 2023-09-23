<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output method="text" indent="no" omit-xml-declaration="yes" />

<xsl:template match="/view">Bed Counts for week commencing,,,"<xsl:value-of select="week_starting"/>"
<xsl:choose>
    <xsl:when test="bedcounts/room">
        <xsl:apply-templates select="bedcounts"/>
    </xsl:when>
    <xsl:otherwise>No data available.</xsl:otherwise>
</xsl:choose>

</xsl:template>

<xsl:template match="bedcounts">
,,,,Monday,,,,,Tuesday,,,,,Wednesday,,,,,Thursday,,,,,Friday,,,,,Saturday,,,,,Sunday
Room,Number of Beds,Room Type,,Paid Beds,Staff Beds,No Shows,Empty Beds,,Paid Beds,Staff Beds,No Shows,Empty Beds,,Paid Beds,Staff Beds,No Shows,Empty Beds,,Paid Beds,Staff Beds,No Shows,Empty Beds,,Paid Beds,Staff Beds,No Shows,Empty Beds,,Paid Beds,Staff Beds,No Shows,Empty Beds,,Paid Beds,Staff Beds,No Shows,Empty Beds
<xsl:apply-templates select="room"/>
Totals,<xsl:value-of select="sum(room[room_type != 'PAID BEDS']/capacity)"/>,,,<xsl:apply-templates select="daily_totals/totals_date"/>
,,,,<xsl:apply-templates select="daily_totals/totals_date/total_paid"/>
,,,,<xsl:apply-templates select="daily_totals/totals_date/total_occupied"/>

WEEKLY TOTALS
Total Paid,<xsl:value-of select="weekly_totals/total_paid"/>
Total Occupied,<xsl:value-of select="weekly_totals/total_occupied"/>
Empty,<xsl:value-of select="weekly_totals/total_empty"/>
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
</xsl:choose><xsl:text>,,</xsl:text>
<xsl:apply-templates select="selected_date"/>

<!-- newline after each record -->
<xsl:text>
</xsl:text>

</xsl:template>

<xsl:template match="selected_date">
    <xsl:if test="num_paid != 0 or /view/write_zeroes = 'true'"><xsl:value-of select="num_paid"/></xsl:if><xsl:text>,</xsl:text>
    <xsl:if test="num_staff != 0 or /view/write_zeroes = 'true'"><xsl:value-of select="num_staff"/></xsl:if><xsl:text>,</xsl:text>
    <xsl:if test="num_noshow != 0 or /view/write_zeroes = 'true'"><xsl:value-of select="num_noshow"/></xsl:if><xsl:text>,</xsl:text>
    <xsl:if test="num_empty != 0 or /view/write_zeroes = 'true'"><xsl:value-of select="num_empty"/></xsl:if><xsl:text>,,</xsl:text>
</xsl:template>

<xsl:template match="totals_date"><xsl:value-of select="num_paid"/>,<xsl:value-of select="num_staff"/>,<xsl:value-of select="num_noshow"/>,<xsl:value-of select="num_empty"/>,,</xsl:template>

<xsl:template match="total_paid">Total Paid,,,<xsl:value-of select="."/>,,</xsl:template>

<xsl:template match="total_occupied">Total Occupied,,,<xsl:value-of select="."/>,,</xsl:template>

</xsl:stylesheet>
