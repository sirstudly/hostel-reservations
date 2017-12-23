<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->
<xsl:template match="view">

	<div id="primary" class="showcase">

		<div id="content" role="main">

            <div class="usual"> 
                <ul class="idTabs"> 
                    <li><a href="#idTab1" class="selected">All Films</a></li> 
                    <li><a href="#idTab2">Scottish</a></li> 
                    <li><a href="#idTab3">New Releases</a></li> 
                </ul> 
                <div id="idTab1">
                    <div class="filmContent">
                        <xsl:apply-templates select="all"/>
                    </div>
                </div> 
                <div id="idTab2">
                    <div class="filmContent">
                        <xsl:apply-templates select="scottish"/>
                    </div>
                </div> 
                <div id="idTab3">
                    <div class="filmContent">
                        <xsl:apply-templates select="new_releases"/>
                    </div> 
                </div> 
            </div>

		</div> <!-- #content -->

	</div><!-- #primary -->

</xsl:template>

<xsl:template match="all">
    <xsl:apply-templates select="film"/><xsl:comment/>
</xsl:template>

<xsl:template match="scottish">
    <xsl:apply-templates select="film"/><xsl:comment/>
</xsl:template>

<xsl:template match="new_releases">
    <xsl:apply-templates select="film"/><xsl:comment/>
</xsl:template>

<xsl:template match="film">

    <table class="films">
        <tr>
            <td class="filmTitle" rowspan="2"><a target="_blank">
                <xsl:attribute name="href">http://www.imdb.com/title/<xsl:value-of select="imdb_link"/></xsl:attribute>
                <xsl:value-of select="title"/></a></td>
            <td class="filmSubHeading">IMDB Rating</td>
            <td class="imdbRating"><xsl:value-of select="imdb_rating"/></td>
            <td class="filmSubHeading">Year</td>
            <td class="filmYear"><xsl:value-of select="year"/></td>
            <td class="filmSubHeading">Runtime</td>
            <td class="filmRuntime"><xsl:value-of select="duration"/></td>
        </tr>
        <tr>
            <td class="filmSubHeading">Genre</td>
            <td colspan="3"><xsl:value-of select="genre"/></td>
            <td class="filmSubHeading">Director</td>
            <td class="filmDirectors"><xsl:value-of select="directors"/></td>
        </tr>
        <tr>
            <td colspan="8" class="filmSynopsis">
                <xsl:value-of select="synopsis"/>
            </td>
        </tr>
        <tr>
            <td colspan="8" class="filmActors">
                <xsl:value-of select="actors"/>
            </td>
        </tr>
    </table>
    <br/>

</xsl:template>

</xsl:stylesheet>