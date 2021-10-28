<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->
<xsl:template match="/view">

    <!--  J a v a S c r i p t -->
    <script type="text/javascript">
        var wpdev_bk_plugin_url = '<xsl:value-of select="pluginurl"/>';

        // Check for correct URL based on Location.href URL, required for correct ajax request
        var real_domain = window.location.href;
        var start_url = '';
        var pos1 = real_domain.indexOf('//'); //get http
        if (pos1 <xsl:text disable-output-escaping="yes">&gt;</xsl:text> -1 ) { start_url= real_domain.substr(0, pos1+2); real_domain = real_domain.substr(pos1+2);   }  //set without http
        real_domain = real_domain.substr(0, real_domain.indexOf('/') );    //setdomain
        var pos2 = wpdev_bk_plugin_url.indexOf('//');  //get http
        if (pos2 <xsl:text disable-output-escaping="yes">&gt;</xsl:text> -1 ) wpdev_bk_plugin_url = wpdev_bk_plugin_url.substr(pos2+2);    //set without http
        wpdev_bk_plugin_url = wpdev_bk_plugin_url.substr( wpdev_bk_plugin_url.indexOf('/') );    //setdomain
        wpdev_bk_plugin_url = start_url + real_domain + wpdev_bk_plugin_url;
        ///////////////////////////////////////////////////////////////////////////////////////

        var wpdev_bk_plugin_filename = '<xsl:value-of select="pluginfilename"/>';
    </script>

    <!-- icon on browser tab -->
    <xsl:if test="siteicon != ''">
        <link rel="icon" href="{siteicon}" />
    </xsl:if>
    <script type="text/javascript" src="{pluginurl}/js/common.js?v=20211028"><xsl:comment/></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.js"><xsl:comment/></script>

    <!-- C S S -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.7.1/css/bootstrap-datepicker.min.css" rel="stylesheet"/>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.7.1/js/bootstrap-datepicker.min.js"><xsl:comment/></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.css" />
</xsl:template>

</xsl:stylesheet>