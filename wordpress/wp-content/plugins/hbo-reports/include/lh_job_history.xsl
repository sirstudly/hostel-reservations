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

    <div class="container mb-3">
        <div class="row">
            <div class="col-md-auto mt-2 ml-2"><h2>Job History</h2></div>
        </div>
    </div>
    <div id="ajax_response"/>

    <div class="card text-center">
        <div class="card-body">
            <style type="text/css">
                #job_history_table .job-history-filters th {
                    background-color: #f8f9fa;
                    border-top: none;
                    font-weight: normal;
                }
                .job-history-page-jump {
                    display: inline-flex;
                    align-items: center;
                    justify-content: flex-end;
                    margin-top: 8px;
                    gap: 6px;
                    width: 100%;
                }
                .job-history-page-input {
                    width: 4.5em;
                }
            </style>
            <table id="job_history_table" class="table table-striped">
                <thead>
                    <tr class="thead-dark">
                        <th scope="col">Job ID</th>
                        <th scope="col">Job Name</th>
                        <th scope="col">Status</th>
                        <th scope="col">Start Date</th>
                        <th scope="col">End Date</th>
                        <th scope="col">Log File</th>
                    </tr>
                    <tr class="job-history-filters">
                        <th/>
                        <th>
                            <select id="filter_job_name" class="form-control form-control-sm">
                                <option value="">All job types</option>
                                <xsl:for-each select="job_names/name">
                                    <option>
                                        <xsl:attribute name="value"><xsl:value-of select="value"/></xsl:attribute>
                                        <xsl:value-of select="label"/>
                                    </option>
                                </xsl:for-each>
                            </select>
                        </th>
                        <th>
                            <select id="filter_status" class="form-control form-control-sm">
                                <option value="">All statuses</option>
                                <xsl:for-each select="statuses/status">
                                    <option>
                                        <xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
                                        <xsl:value-of select="."/>
                                    </option>
                                </xsl:for-each>
                            </select>
                        </th>
                        <th/>
                        <th/>
                        <th/>
                    </tr>
                </thead>
                <tbody/>
            </table>
            <div id="job_history_page_jump" class="job-history-page-jump" style="display: none;">
                Go to page
                <input type="number" min="1" class="form-control form-control-sm job-history-page-input"/>
                <span class="job-history-page-of"/>
                <button type="button" class="btn btn-sm btn-secondary job-history-page-go">Go</button>
            </div>
        </div>
    </div>

    <script type="text/javascript">
    var job_history_state_key = 'hbo_job_history_state';

    function loadJobHistoryState() {
        try {
            var raw = sessionStorage.getItem(job_history_state_key);
            return raw ? JSON.parse(raw) : null;
        } catch (e) {
            return null;
        }
    }

    function saveJobHistoryState() {
        if (typeof job_history_table === 'undefined') {
            return;
        }
        var info = job_history_table.page.info();
        sessionStorage.setItem(job_history_state_key, JSON.stringify({
            job_name: jQuery('#filter_job_name').val() || '',
            status: jQuery('#filter_status').val() || '',
            page: info.page,
            length: info.length,
            start: info.start
        }));
    }

    function applyJobHistoryState(state) {
        if (!state) {
            return;
        }
        jQuery('#filter_job_name').val(state.job_name || '');
        jQuery('#filter_status').val(state.status || '');
    }

    function restoreJobHistoryTable(state) {
        if (!state || typeof job_history_table === 'undefined') {
            return;
        }
        applyJobHistoryState(state);
        var api = job_history_table;
        var current = api.page.info();
        if (state.length &amp;&amp; state.length !== current.length) {
            api.page.len(state.length);
        }
        api.ajax.reload(function() {
            var targetPage = state.page || 0;
            if (api.page.info().page !== targetPage) {
                api.page(targetPage).draw(false);
            }
            saveJobHistoryState();
        }, false);
    }

    jQuery(window).on('pageshow', function(e) {
        if (e.originalEvent &amp;&amp; e.originalEvent.persisted) {
            restoreJobHistoryTable(loadJobHistoryState());
        }
    });

    jQuery(document).ready(function() {
        var homeurl = '<xsl:value-of select="homeurl"/>';
        var wpnonce = '<xsl:value-of select="wpnonce"/>';
        var pluginurl = '<xsl:value-of select="pluginurl"/>';
        var savedState = loadJobHistoryState();
        applyJobHistoryState(savedState);

        function formatJobParamsTooltip(params) {
            if (!params || Object.keys(params).length === 0) {
                return '';
            }
            var lines = [];
            jQuery.each(params, function(key, val) {
                lines.push(key + ': ' + val);
            });
            return lines.join('&lt;br&gt;');
        }

        job_history_table = new DataTable('#job_history_table', {
            processing: true,
            serverSide: true,
            pageLength: savedState &amp;&amp; savedState.length ? savedState.length : 100,
            displayStart: savedState &amp;&amp; savedState.start ? savedState.start : 0,
            lengthMenu: [[50, 100, 500], [50, 100, 500]],
            searching: false,
            order: [[0, 'desc']],
            layout: {
                topStart: 'pageLength',
                topEnd: null,
                bottomStart: 'info',
                bottomEnd: 'paging'
            },
            ajax: {
                url: homeurl + '/wp-json/hbo-reports/v1/job-history',
                data: function(d) {
                    d.job_name = jQuery('#filter_job_name').val();
                    d.status = jQuery('#filter_status').val();
                },
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', wpnonce);
                }
            },
            columns: [
                { data: 'job_id' },
                {
                    data: 'job_name',
                    className: 'text-left',
                    render: function(data, type, row) {
                        if (type !== 'display') {
                            return data;
                        }
                        var tooltip = formatJobParamsTooltip(row.job_params);
                        if (tooltip) {
                            return jQuery('&lt;a&gt;', {
                                href: 'javascript:void(0)',
                                'data-toggle': 'tooltip',
                                'data-html': true,
                                'data-trigger': 'hover focus click',
                                title: tooltip
                            }).text(data).prop('outerHTML');
                        }
                        return data;
                    }
                },
                {
                    data: 'status',
                    render: function(data, type, row) {
                        if (type !== 'display') {
                            return data;
                        }
                        if (row.can_resubmit) {
                            var link = jQuery('&lt;a&gt;', {
                                href: 'javascript:void(0)',
                                css: { marginLeft: '10px' },
                                onclick: 'resubmit_incomplete_job(' + row.job_id + '); return false;'
                            }).append(jQuery('&lt;img&gt;', {
                                src: pluginurl + '/img/refresh.svg',
                                width: 16
                            }));
                            return data + link.prop('outerHTML');
                        }
                        return data;
                    }
                },
                { data: 'start_date' },
                { data: 'end_date' },
                {
                    data: 'log_file',
                    orderable: false,
                    render: function(data, type, row) {
                        if (type !== 'display' || !data) {
                            return '';
                        }
                        return jQuery('&lt;a&gt;', {
                            href: data,
                            text: 'job-' + row.job_id + '.log'
                        }).prop('outerHTML');
                    }
                }
            ],
            initComplete: function() {
                var api = this.api();
                var jump = jQuery('#job_history_page_jump');
                jump.show();

                function goToPage() {
                    var page = parseInt(jump.find('.job-history-page-input').val(), 10);
                    var info = api.page.info();
                    if ( ! isNaN(page) &amp;&amp; page &gt;= 1 &amp;&amp; page &lt;= info.pages) {
                        api.page(page - 1).draw('page');
                    }
                }

                function updatePageInput() {
                    var info = api.page.info();
                    jump.find('.job-history-page-input').attr('max', info.pages).val(info.page + 1);
                    jump.find('.job-history-page-of').text('of ' + info.pages);
                }

                jump.find('.job-history-page-go').on('click', goToPage);
                jump.find('.job-history-page-input').on('keypress', function(e) {
                    if (e.which === 13) {
                        goToPage();
                    }
                });
                api.on('draw', updatePageInput);
                updatePageInput();

                if (savedState &amp;&amp; typeof savedState.page === 'number' &amp;&amp; api.page.info().page !== savedState.page) {
                    api.page(savedState.page).draw(false);
                }
            },
            drawCallback: function() {
                jQuery('[data-toggle="tooltip"]').tooltip();
                saveJobHistoryState();
            }
        });

        jQuery('#filter_job_name, #filter_status').on('change', function() {
            job_history_table.ajax.reload(function() {
                saveJobHistoryState();
            }, true);
        });
    });
    </script>

    <xsl:call-template name="write_inline_js"/>
    <xsl:call-template name="write_inline_css"/>

</xsl:template>

</xsl:stylesheet>
