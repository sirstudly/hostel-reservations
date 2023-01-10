<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<xsl:output method="html" omit-xml-declaration="yes" encoding="UTF-8"/>

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->
<xsl:include href="inline_scripts.xsl"/>

<xsl:template match="/view">
    <xsl:if test="not(reload_table_only)">
        <div class="container mb-3">
            <div class="row">
                <div class="col-md-auto"><h2>Blacklist</h2></div>
            </div>
        </div>
        <xsl:apply-templates select="blacklist" />
        <div id="ajax_response"><xsl:comment/><!-- ajax response here--></div>
        <xsl:call-template name="write_inline_js"/>
        <xsl:call-template name="write_inline_css"/>
    </xsl:if>

    <!-- if we're editing, just generate the table -->
    <xsl:if test="reload_table_only">
        <xsl:apply-templates select="blacklist" />
    </xsl:if>
</xsl:template>

<xsl:template match="blacklist">
    <form name="post_option" action="" method="post" id="post_option">
        <table id="blacklist_table" class="table table-striped">
            <thead class="thead-dark">
                <th scope="col">First Name</th>
                <th scope="col">Last Name</th>
                <th scope="col">Email</th>
                <th scope="col">Actions</th>
            </thead>
            <tbody>
                <xsl:if test="entry">
                    <xsl:apply-templates select="entry" />
                </xsl:if>
                <xsl:if test="not(entry/editing)">
                    <xsl:call-template name="new_blacklist_entry"/>
                </xsl:if>
            </tbody>
        </table>
    </form>
</xsl:template>

<xsl:template name="new_blacklist_entry">
    <tr>
        <td><input id="new_first_name" name="new_first_name" class="regular-text code col-3 form-control" type="text" value=""/></td>
        <td><input id="new_last_name" name="new_last_name" class="regular-text code col-3 form-control" type="text" value=""/></td>
        <td><input id="new_email" name="new_email" class="regular-text code col-3 form-control" type="text" value=""/></td>
        <td><a id="btn_save_blacklist" class="btn btn-primary" onclick="save_blacklist(0, document.post_option.new_first_name.value, document.post_option.new_last_name.value, document.post_option.new_email.value); this.disabled=true;">Add</a></td>
    </tr>
</xsl:template>

<xsl:template match="entry">
    <xsl:choose>
        <xsl:when test="editing = 'true'">
            <tr>
                <td><input id="edit_first_name" name="edit_first_name" class="regular-text code col-3 form-control" type="text"><xsl:attribute name="value"><xsl:value-of select="first_name"/></xsl:attribute></input></td>
                <td><input id="edit_last_name" name="edit_last_name" class="regular-text code col-3 form-control" type="text"><xsl:attribute name="value"><xsl:value-of select="last_name"/></xsl:attribute></input></td>
                <td><input id="edit_email" name="edit_email" class="regular-text code col-3 form-control" type="text"><xsl:attribute name="value"><xsl:value-of select="email"/></xsl:attribute></input></td>
                <td><a class="btn btn-primary"><xsl:attribute name="onclick">save_blacklist(<xsl:value-of select="blacklist_id"/>, document.post_option.edit_first_name.value, document.post_option.edit_last_name.value, document.post_option.edit_email.value); this.disabled=true;</xsl:attribute>Save</a>
                    <a class="btn btn-primary ml-2" onclick="location.reload()">Cancel</a>
                </td>
            </tr>
        </xsl:when>
        <xsl:otherwise>
            <tr>
                <td><div><xsl:if test="alias_id"><xsl:attribute name="class">ml-3</xsl:attribute></xsl:if><xsl:value-of select="first_name"/></div></td>
                <td><div><xsl:if test="alias_id"><xsl:attribute name="class">ml-3</xsl:attribute></xsl:if><xsl:value-of select="last_name"/></div></td>
                <td><div><xsl:if test="alias_id"><xsl:attribute name="class">ml-3</xsl:attribute></xsl:if><xsl:value-of select="email"/></div></td>
                <td>
                    <xsl:choose>
                        <xsl:when test="alias_id">
                            <a class="btn btn-primary"><xsl:attribute name="onclick">delete_blacklist_alias(<xsl:value-of select="alias_id"/>);</xsl:attribute>Delete Alias</a>
                        </xsl:when>
                        <xsl:otherwise>
                            <a class="btn btn-primary"><xsl:attribute name="onclick">edit_blacklist(<xsl:value-of select="blacklist_id"/>);</xsl:attribute>Edit</a> [Add Image]
                            <a class="btn btn-primary ml-2"><xsl:attribute name="onclick">add_blacklist_alias(<xsl:value-of select="blacklist_id"/>);</xsl:attribute>Add Alias</a>
                        </xsl:otherwise>
                    </xsl:choose>
                </td>
            </tr>
            <xsl:if test="add_alias = 'true'">
                <tr>
                    <td><input id="alias_first_name" name="alias_first_name" class="regular-text code col-3 form-control" type="text" value=""/></td>
                    <td><input id="alias_last_name" name="alias_last_name" class="regular-text code col-3 form-control" type="text" value=""/></td>
                    <td><input id="alias_email" name="alias_email" class="regular-text code col-3 form-control" type="text" value=""/></td>
                    <td><a class="btn btn-primary"><xsl:attribute name="onclick">save_blacklist_alias(<xsl:value-of select="blacklist_id"/>, document.post_option.alias_first_name.value, document.post_option.alias_last_name.value, document.post_option.alias_email.value); this.disabled=true;</xsl:attribute>Save Alias</a>
                        <a class="btn btn-primary ml-2" onclick="location.reload()">Cancel</a>
                    </td>
                </tr>
            </xsl:if>
        </xsl:otherwise>
    </xsl:choose>
</xsl:template>

</xsl:stylesheet>