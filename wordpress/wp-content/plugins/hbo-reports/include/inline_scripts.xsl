<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<xsl:template name="write_inline_js">

    <script type="text/javascript">
    jQuery(document).ready( function(){
            jQuery('#selectiondate').datepicker({
			        weekStart: 1,
			        daysOfWeekHighlighted: "6,0",
			        autoclose: true,
			        todayHighlight: true,
			    }
            );
            jQuery('[data-toggle="tooltip"]').tooltip();
       });
    </script>
</xsl:template>

<xsl:template name="write_inline_css">

    <style type="text/css">

.help-block {
    font-style: italic;
    color: #999;
}
    </style>
</xsl:template>

</xsl:stylesheet>