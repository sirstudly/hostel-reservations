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
    <xsl:choose>
        <xsl:when test="blacklist/entry[@editing='true']">
            <xsl:apply-templates select="blacklist/entry[@editing='true']" mode="edit-blacklist-dialog"/>
        </xsl:when>
        <xsl:otherwise>
            <style media="screen" type="text/css">
                .edit-blacklist label {
                    font-weight: bold;
                }
            </style>
            <script type="text/javascript">
                jQuery(document).ready(function(){
                    jQuery( ".edit-blacklist" ).dialog({
                        autoOpen: false,
                        close: function() { location.reload() },
                        modal: true,
                        width:'80%'
                    });
                    jQuery( ".mugshot-carousel" ).dialog({
                        autoOpen: false,
                        modal: true,
                        width:'80%'
                    });
                });

                // saves the currently editing blacklist
                // elem : the parent div for the modal dialog which we're editing
                function do_save_blacklist(elem) {
                    var blacklist_id = elem.data('blacklist-id');
                    var first_name = elem.find('input[name=edit_first_name]').val();
                    var last_name = elem.find('input[name=edit_last_name]').val();
                    var email = elem.find('input[name=edit_email]').val();
                    var notes = elem.find('textarea[name=edit_notes]').val();
                    save_blacklist(blacklist_id, first_name, last_name, email, notes);
                }

                // saves a new alias against the currently editing blacklist
                // elem : the parent div for the modal dialog which we're editing
                function do_save_blacklist_alias(elem) {
                    var blacklist_id = elem.data('blacklist-id');
                    var first_name = elem.find('input[name=alias_first_name]').val();
                    var last_name = elem.find('input[name=alias_last_name]').val();
                    var email = elem.find('input[name=alias_email]').val();
                    save_blacklist_alias(blacklist_id, first_name, last_name, email);
                }

                // uploads an image against the currently editing blacklist
                // elem : the parent div for the modal dialog which we're editing
                function do_upload_blacklist_image(elem) {
                    var blacklist_id = elem.data('blacklist-id');
                    var files = elem.find("input[name='mugshot']")[0].files;
                    upload_blacklist_image(blacklist_id, files);
                }
            </script>

            <div class="container mb-3">
                <div class="row">
                    <div class="col-md-auto"><h2>Blacklist</h2></div>
                </div>
            </div>
            <xsl:apply-templates select="blacklist" />
            <div id="ajax_response"><xsl:comment/><!-- ajax response here--></div>
            <xsl:call-template name="write_inline_js"/>
            <xsl:call-template name="write_inline_css"/>
        </xsl:otherwise>
    </xsl:choose>

</xsl:template>

<xsl:template match="blacklist">
    <form name="post_option" action="" method="post" id="post_option" enctype="multipart/form-data">
        <table id="blacklist_table" class="table table-striped">
            <thead class="thead-dark">
                <th scope="col">First Name</th>
                <th scope="col">Last Name</th>
                <th scope="col">Email</th>
                <th scope="col">Notes</th>
                <th scope="col">Images</th>
                <th scope="col">Actions</th>
            </thead>
            <tbody>
                <xsl:if test="entry">
                    <xsl:apply-templates select="entry" />
                </xsl:if>
                <xsl:call-template name="new_blacklist_entry"/>
            </tbody>
        </table>
    </form>
</xsl:template>

<xsl:template name="new_blacklist_entry">
    <tr>
        <td><input id="new_first_name" name="new_first_name" class="regular-text code col-3 form-control" type="text" style="min-width: 100%" value=""/></td>
        <td><input id="new_last_name" name="new_last_name" class="regular-text code col-3 form-control" type="text" style="min-width: 100%" value=""/></td>
        <td><input id="new_email" name="new_email" class="regular-text code col-3 form-control" type="text" style="min-width: 100%" value=""/></td>
        <td><textarea id="new_notes" name="new_notes" class="regular-text code col-3 form-control" style="min-width: 100%" value=""/></td>
        <td/>
        <td><a id="btn_save_blacklist" class="btn btn-primary" onclick="save_blacklist(0, document.post_option.new_first_name.value, document.post_option.new_last_name.value, document.post_option.new_email.value, document.post_option.new_notes.value); this.disabled=true;">Add</a></td>
    </tr>
</xsl:template>

<xsl:template match="entry">
    <tr>
        <td><xsl:value-of select="first_name"/><xsl:apply-templates select="alias" mode="alias_first_name"/></td>
        <td><xsl:value-of select="last_name"/><xsl:apply-templates select="alias" mode="alias_last_name"/></td>
        <td><xsl:value-of select="email"/><xsl:apply-templates select="alias" mode="alias_email"/></td>
        <td><xsl:value-of select="notes_readonly" disable-output-escaping="yes"/></td>
        <td>
            <xsl:apply-templates select="mugshot"/>
        </td>
        <td>
            <xsl:apply-templates select="." mode="edit-blacklist-dialog"/>
            <a class="btn btn-primary ml-2"><xsl:attribute name="onclick">jQuery('.edit-blacklist[data-blacklist-id=<xsl:value-of select="blacklist_id"/>]').dialog('open');</xsl:attribute>Edit</a>
        </td>
    </tr>
</xsl:template>

<xsl:template match="alias" mode="alias_first_name">
    <br/><xsl:value-of select="first_name"/>
</xsl:template>

<xsl:template match="alias" mode="alias_last_name">
    <br/><xsl:value-of select="last_name"/>
</xsl:template>

<xsl:template match="alias" mode="alias_email">
    <br/><xsl:value-of select="email"/>
</xsl:template>

<xsl:template match="mugshot">
    <a href="javascript:void(0)"><xsl:attribute name="onclick">jQuery('.mugshot-carousel[data-mugshot-id=<xsl:value-of select="mugshot_id"/>]').dialog('open');</xsl:attribute>
        <xsl:apply-templates select="." mode="mugshot-thumbnails"/>
    </a>

    <!-- this is the full-resolution image hidden until clicked -->
    <div class="mugshot-carousel container-fluid">
        <xsl:attribute name="data-mugshot-id"><xsl:value-of select="mugshot_id"/></xsl:attribute>
        <xsl:attribute name="title">Image of <xsl:value-of select="../first_name"/><xsl:text> </xsl:text><xsl:value-of select="../last_name"/></xsl:attribute>
        <img><xsl:attribute name="src"><xsl:value-of select="/view/pluginurl"/>/upload/<xsl:value-of select="filename"/></xsl:attribute></img>
    </div>
</xsl:template>

<xsl:template match="mugshot" mode="mugshot-thumbnails">
    <img width="100" class="pl-2">
        <xsl:attribute name="src"><xsl:value-of select="/view/pluginurl"/>/upload/<xsl:value-of select="filename"/></xsl:attribute>
    </img>
</xsl:template>

<xsl:template match="entry" mode="edit-blacklist-dialog">
    <div class="edit-blacklist container-fluid" title="Edit Blacklist Entry">
        <xsl:attribute name="data-blacklist-id"><xsl:value-of select="blacklist_id"/></xsl:attribute>
        <div class="row">
        <div class="col form-group">
            <div class="row py-2">
                <label class="col">First Name:</label>
                <input name="edit_first_name" class="regular-text code col" type="text"><xsl:attribute name="value"><xsl:value-of select="first_name"/></xsl:attribute></input>
            </div>
            <div class="row py-2">
                <label class="col">Last Name:</label>
                <input name="edit_last_name" class="regular-text code col" type="text"><xsl:attribute name="value"><xsl:value-of select="last_name"/></xsl:attribute></input>
            </div>
            <div class="row py-2">
                <label class="col">Email:</label>
                <input name="edit_email" class="regular-text code col" type="text"><xsl:attribute name="value"><xsl:value-of select="email"/></xsl:attribute></input>
            </div>
            <div class="row py-2">
                <label class="col">Notes:</label>
                <div class="w-100"/>
                <textarea name="edit_notes" class="regular-text code form-control col ml-3"><xsl:value-of select="notes"/></textarea>
            </div>
            <div class="row py-2">
                <div class="col"><xsl:attribute name="id">ajax_response-<xsl:value-of select="blacklist_id"/></xsl:attribute><xsl:comment/><!-- ajax response here--></div>
            </div>
            <div class="row justify-content-end">
                <div class="ml-5 col-2"><a class="btn btn-primary" onclick="do_save_blacklist(jQuery(this).closest('div.edit-blacklist'))">Save</a></div>
            </div>
        </div>
        <div class="col ml-4">
            <div class="row">
                <input type="file" accept="image/*" name="mugshot" />
                <a class="btn btn-primary" onclick="do_upload_blacklist_image(jQuery(this).closest('div.edit-blacklist'))">Upload</a>
            </div>
            <div class="row">
                <xsl:apply-templates select="mugshot" mode="mugshot-thumbnails"/>
            </div>
        </div>
        </div>
        <div class="w-100"/>
        <div class="col mt-2">
            <label>Aliases</label>
        </div>
        <div class="w-100"/>
        <div>
            <table class="table table-striped w-100">
                <thead class="thead-dark">
                    <tr>
                        <th scope="col">First Name</th>
                        <th scope="col">Last Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <xsl:apply-templates select="alias"/>
                    <tr>
                        <td><input name="alias_first_name" class="regular-text code col-3 form-control" type="text" style="min-width: 100%" value=""/></td>
                        <td><input name="alias_last_name" class="regular-text code col-3 form-control" type="text" style="min-width: 100%" value=""/></td>
                        <td><input name="alias_email" class="regular-text code col-3 form-control" type="text" style="min-width: 100%" value="" placeholder="(optional)"/></td>
                        <td><a class="btn btn-primary"><xsl:attribute name="onclick">do_save_blacklist_alias(jQuery(this).closest('div.edit-blacklist')); this.disabled=true;</xsl:attribute>Add Alias</a></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</xsl:template>

<xsl:template match="alias">
    <tr>
        <td><xsl:value-of select="first_name"/></td>
        <td><xsl:value-of select="last_name"/></td>
        <td><xsl:value-of select="email"/></td>
        <td>
            <a class="btn btn-primary"><xsl:attribute name="onclick">delete_blacklist_alias(<xsl:value-of select="../blacklist_id"/>, <xsl:value-of select="alias_id"/>);</xsl:attribute>Delete</a>
        </td>
    </tr>
</xsl:template>

</xsl:stylesheet>