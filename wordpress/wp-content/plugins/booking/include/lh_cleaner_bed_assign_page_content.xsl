<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<xsl:output method="html" omit-xml-declaration="yes" encoding="UTF-8"/>

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->

<xsl:include href="lh_cleaner.xsl"/>

<xsl:template name="cleaner_page_contents_css">

    <style media="screen" type="text/css">

        .cleaner_bed_assign_tbl {
	        margin-left: 20px;
        }

        .cleaner_bed_assign_tbl_addnew {
	        margin-left: 20px;
        }

        .cleaner_header a.expanded::before {
            content: "▾ ";
        }

        .cleaner_header a.collapsed::before {
            content: "▸ ";
        }

    </style>
</xsl:template>

<xsl:template name="cleaner_page_contents_js">
    <script type="text/javascript">
function show_datepicker() {
            jQuery('input.cleaner-bed-assign-calendar').datepicker(
                {   showOn: 'focus',
                    multiSelect: 0,
                    numberOfMonths: 1,
                    stepMonths: 1,
                    prevText: '&lt;&lt;',
                    nextText: '&gt;&gt;',
                    dateFormat: 'yy-mm-dd',
                    changeMonth: false,
                    changeYear: false,
                    minDate: null, maxDate: '1Y',
                    showStatus: false,
                    multiSeparator: ', ',
                    closeAtTop: false,
                    firstDay: 0, // 0 = sunday
                    gotoCurrent: false,
                    hideIfNoPrevNext:true,
                    useThemeRoller :false,
                    mandatory: true
                }
            );
        };
        jQuery(document).ajaxComplete( show_datepicker );
        jQuery(document).ready( show_datepicker );

        <!-- add toggle scripts to apply to collapse/expand sections -->
        <xsl:apply-templates select="cleaners/cleaner" mode="cleaner_assign_js"/>

    </script>

</xsl:template>

<!-- main template for content body -->
<xsl:template name="cleaner_page_contents">

    <xsl:call-template name="cleaner_page_contents_css"/>
    <xsl:call-template name="cleaner_page_contents_js"/>

    <xsl:apply-templates select="cleaners/cleaner" mode="cleaner_assign_tbl"/>

    <table class="cleaner_bed_assign_tbl_addnew">
        <tbody>
            <tr>
                <td><span style="margin-right:50px;">Add another cleaner:</span>
                    <span style="margin-right:20px;">First Name: <input id="first_name" type="text" name="first_name" value="" size="12" /></span>
                    <span style="margin-right:20px;">Last Name: <input id="last_name" type="text" name="last_name" value="" size="12" /></span>
                    <a href="javascript:add_cleaner();" onclick="this.style.display='none';">Add</a>
                </td>
            </tr>
        </tbody>
    </table>

</xsl:template>

</xsl:stylesheet>

