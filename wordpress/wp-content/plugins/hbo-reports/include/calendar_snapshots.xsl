<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

    <xsl:output method="html" omit-xml-declaration="yes" encoding="UTF-8"/>

    <!--
    //*****************************************************************************
    // Distributed under the GNU General Public Licence
    //*****************************************************************************
    -->
    <xsl:include href="inline_scripts.xsl"/>

    <xsl:template match="view">

        <xsl:call-template name="write_inline_js"/>
        <xsl:call-template name="write_inline_css"/>
        <style>
            .fc-event-start, .fc-event-end {
                border-radius: 10px;
            }
        </style>

        <div class="d-flex">
            <span style="margin: 5px 10px 50px 60px;"/>
            <h2>Calendar Snapshots</h2>
        </div>

        <div class="d-flex" style="width:60%; padding-bottom: 30px;">
            Daily reports take a snapshot of the Cloudbeds calendar 3 months into the future. Select a date to
            see what the calendar looked like at the time!

            <div style="padding-left: 20px; padding-top: 20px;">
                <select id="allocation_scraper_job_select" name="allocation_scraper_job_select">
                    <xsl:apply-templates select="allocation_scraper_jobs/record"/>
                </select>
            </div>
        </div>

        <div class="card text-center">
            <div class="card-body">
                <div id='calendar'></div>
            </div>
        </div>

        <script src='https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@6.1.10/index.global.min.js'></script>
        <script src="https://unpkg.com/popper.js/dist/umd/popper.min.js"></script>
        <script src="https://unpkg.com/tooltip.js/dist/umd/tooltip.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var calendarEl = document.getElementById('calendar');
                var calendar = new FullCalendar.Calendar(calendarEl, {
                    schedulerLicenseKey: 'CC-Attribution-NonCommercial-NoDerivatives',
                    initialView: 'resourceTimelineYear',
                    slotLabelFormat: [
                        { month: 'long', year: 'numeric' }, // top level of text
                        { weekday: 'short', day: 'numeric' } // lower level of text
                    ],
                    resourceOrder: 'title',
                    resources: {
                        url: '/wp-json/hbo-reports/v1/list-room-beds'
                    },
                    events: {
                        url: '/wp-json/hbo-reports/v1/fetch-all-bookings',
                        extraParams: function() {
                            return {
                                _wpnonce: '<xsl:value-of select="wpnonce"/>',
                                job_id: jQuery("#allocation_scraper_job_select").val()
                            };
                        }
                    },
                    eventDidMount: function(info) {
                        var tooltip = new Tooltip(info.el, {
                            title: info.event.extendedProps.description,
                            html: true,
                            placement: 'top',
                            trigger: 'hover',
                            container: 'body'
                        });
                    }
                });

                jQuery("#allocation_scraper_job_select").on('change', function (e) {
                    calendar.refetchEvents();
                });

                calendar.render();
            });
        </script>

    </xsl:template>

    <xsl:template match="record">
        <option>
            <xsl:attribute name="value">
                <xsl:value-of select='job_id'/>
            </xsl:attribute>
            <xsl:value-of select="completed_date"/>
        </option>
    </xsl:template>
</xsl:stylesheet>