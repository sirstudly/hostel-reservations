<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output method="html" omit-xml-declaration="yes" encoding="UTF-8"/>

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->
<xsl:include href="inline_scripts.xsl"/>
<xsl:include href="scheduled_job_view_include.xsl"/>

<xsl:template match="/view">

        <h3>Job Scheduler</h3>

        <div style="margin-left:50px;">
            <p>Job times are approximate and are run on a best-effort basis.</p>
        </div>

        <div id="job_schedule_table">
            <xsl:call-template name="job_schedule_table"/>
        </div>

        <div id="ajax_response" style="margin-left: 50px; float:left; margin-top:20px;"><xsl:comment/></div>
        <div style="height:1px;clear:both; margin-top:10px;"><xsl:comment/></div>
        <form name="scheduled_job_form" autocomplete="off" action="" method="post" id="scheduled_job_form">
            <select id="new_job_select" style="margin-left: 50px; float: left;" name="classname">
                <xsl:apply-templates select="classnamemap/entry"/>
            </select>
            <div style="margin-left: 20px; float:left;">
                <div id="job_param_div"><xsl:comment/></div>
                <div id="job_repeat_div" class="mb-2">
                    <input id="radio_repeat_every" type="radio" name="schedule_type" value="repeat_every"/> Repeat Every
                    <input id="repeat_minutes" name="repeat_minutes" type="text" autocomplete="false" style="width:40px;" maxlength="4" value="{repeat_minutes}" onkeypress="jQuery('#radio_repeat_every').click();" />
                    Minutes<br/>
                </div>
                <div class="mb-2">
                    <input id="radio_daily" type="radio" name="schedule_type" value="daily"/> Everyday at:
                    <input id="daily_at" name="daily_at" type="text" autocomplete="false" style="width:80px;" maxlength="8" value="{daily_at}" onkeypress="jQuery('#radio_daily').click();" />
                    (24 hour clock in hours/minutes e.g. 23:00)
                </div>
            </div>

            <div style="height:1px;clear:both;margin-top:40px;"><xsl:comment/></div>
            <a id="add_job_button" class="btn btn-primary" style="margin-left: 320px; margin-bottom: 15px;" onclick="add_scheduled_job(scheduled_job_form.classname.value, scheduled_job_form.schedule_type.value == 'repeat_every' ? scheduled_job_form.repeat_minutes.value : null, scheduled_job_form.schedule_type.value == 'daily' ? scheduled_job_form.daily_at.value : null); this.style.visibility='hidden';">Add New Job <span class="icon-plus-sign icon-white"></span></a>
            <div id="ajax_loader" style="margin-left: 290px; float:left; display:none;"><xsl:comment/></div>
        </form>

        <script type="text/javascript">
            var jobs = <xsl:value-of select="jobs_json"/>;
            function job_selected(classname) {
                return jobs.find( elem => elem.classname == classname );
            }
            function onchange_job(classname) {
                var job = job_selected(classname);
                if(job &amp;&amp; job.parameters) {
                    var html = '';
                    for(const propname in job.parameters) {
                        html += '&lt;div&gt;&lt;span class="mb-2" style="width: 110px; display:inline-block;"&gt;' + `${propname}` + '&lt;/span&gt;&lt;input type="text" id="params_' + `${propname}` + '" name="' + `${propname}` + '" value="' + `${job.parameters[propname]}` + '"&gt;&lt;/input&gt;&lt;/div&gt;'
                    }
                    jQuery("#job_param_div").html(html);
                }
            }

            // pre-populate parameter form fields
            onchange_job(jQuery("#new_job_select option:selected").val());
            jQuery("#new_job_select").on('change', function() {
                onchange_job(this.value);
            });
        </script>

        <xsl:call-template name="write_inline_js"/>
        <xsl:call-template name="write_inline_css"/>

</xsl:template>

<xsl:template match="entry">
    <option>
        <xsl:attribute name="value"><xsl:value-of select="classname"/></xsl:attribute>
        <xsl:value-of select="selectionname"/>
    </option>
</xsl:template>

</xsl:stylesheet>