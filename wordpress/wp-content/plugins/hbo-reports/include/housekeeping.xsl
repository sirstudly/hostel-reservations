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

<style media="screen" type="text/css">
.room_demarkation {
    padding-left: 20px;
    font-weight: bold;
    font-size: 140%;
}

.badge-empty {
    color: #fff;
    background-color: #B8B8B8;
    font-size: 100%;
}

.badge-nochange {
    color: #fff;
    background-color: #7A7A7A;
    font-size: 100%;
}

.badge-ndaychange {
    color: #fff;
    background-color: #C87F5B;
    font-size: 100%;
}

.badge-change {
    color: #fff;
    background-color: #3A87AD;
    font-size: 100%;
}

.badge-change-out {
    color: #fff;
    background-color: #2E7D32;
    font-size: 100%;
}

.badge-change-inhouse {
    color: #fff;
    background-color: #F9A825;
    font-size: 100%;
}

.badge-change-closure {
    color: #fff;
    background-color: #6A1B9A;
    font-size: 100%;
}

.hk-live-indicator {
    font-size: 0.85rem;
    font-weight: normal;
}
.hk-live-indicator.connected { color: #2E7D32; }
.hk-live-indicator.disconnected { color: #9E9E9E; }
</style>

    <div class="container mb-3">
        <div class="row align-items-center">
            <div class="col-md-auto ml-2"><h3><xsl:value-of select="selectiondate"/></h3></div>
            <div class="col-md-auto">
                <span id="hk_live_status" class="hk-live-indicator disconnected">
                    <xsl:choose>
                        <xsl:when test="mercure_url">Connecting…</xsl:when>
                        <xsl:otherwise>Live updates not configured</xsl:otherwise>
                    </xsl:choose>
                </span>
                <span id="hk_last_updated" class="hk-live-indicator ml-2"></span>
            </div>
        </div>
    </div>

    <div class="card text-center">
        <div class="card-header pb-0">
            <xsl:call-template name="report_header" />
        </div>
        <div class="card-body">
            <xsl:choose>
                <xsl:when test="bed">
                    <xsl:call-template name="report_data"/>
                </xsl:when>
                <xsl:otherwise>
                    <div class="ml-5 mb-2 mt-2 font-italic">
                        <h6>No data available.</h6>
                    </div>
                </xsl:otherwise>
            </xsl:choose>
        </div>
    </div>
    <xsl:call-template name="write_inline_js"/>
    <xsl:call-template name="write_inline_css"/>

    <xsl:if test="mercure_url">
    <script type="text/javascript">
        (function() {
            const mercureUrl = '<xsl:value-of select="mercure_url"/>';
            const statusEl = document.getElementById('hk_live_status');
            const updatedEl = document.getElementById('hk_last_updated');

            function badgeClass(bedsheet) {
                if (!bedsheet) return 'badge badge-empty';
                if (bedsheet.indexOf('DAY CHANGE') !== -1) return 'badge badge-ndaychange';
                if (bedsheet === 'CHANGE (CHECKED OUT)') return 'badge badge-change-out';
                if (bedsheet === 'CHANGE (IN-HOUSE)') return 'badge badge-change-inhouse';
                if (bedsheet === 'CHANGE (ROOM CLOSURE)') return 'badge badge-change-closure';
                if (bedsheet.indexOf('CHANGE') === 0) return 'badge badge-change';
                if (bedsheet === 'NO CHANGE') return 'badge badge-nochange';
                if (bedsheet === 'EMPTY') return 'badge badge-empty';
                return 'badge badge-empty';
            }

            function applySnapshot(payload) {
                if (!payload || !payload.beds) return;
                payload.beds.forEach(function(bed) {
                    const row = document.querySelector('tr[data-room-id="' + CSS.escape(bed.room_id) + '"]');
                    if (!row) return;
                    const badge = row.querySelector('.hk-bedsheet-badge');
                    if (badge) {
                        badge.className = 'hk-bedsheet-badge ' + badgeClass(bed.bedsheet);
                        badge.textContent = bed.bedsheet;
                    }
                });
                if (payload.totals) {
                    const t = payload.totals;
                    function setTotal(id, val) {
                        const el = document.getElementById(id);
                        if (el &amp;&amp; val !== undefined &amp;&amp; val !== null) el.textContent = val;
                    }
                    setTotal('hk_total_level2', t.level2);
                    setTotal('hk_total_level4', t.level4);
                    setTotal('hk_total_level5', t.level5);
                    setTotal('hk_total_level6_7', t.level6_7);
                    setTotal('hk_total_upstairs', t.upstairs);
                    setTotal('hk_total_total', t.total);
                }
                if (updatedEl) {
                    updatedEl.textContent = 'Updated ' + new Date().toLocaleTimeString();
                }
            }

            function connect() {
                const es = new EventSource(mercureUrl);
                es.onopen = function() {
                    if (statusEl) {
                        statusEl.textContent = 'Live';
                        statusEl.className = 'hk-live-indicator connected';
                    }
                };
                es.onerror = function() {
                    if (statusEl) {
                        statusEl.textContent = 'Reconnecting…';
                        statusEl.className = 'hk-live-indicator disconnected';
                    }
                };
                es.onmessage = function(ev) {
                    try {
                        applySnapshot(JSON.parse(ev.data));
                    } catch (e) {
                        console.warn('Housekeeping Mercure parse error', e);
                    }
                };
            }
            connect();
        })();
    </script>
    </xsl:if>

</xsl:template>

<xsl:template name="report_header">

    <form id="housekeeping_form" class="form-inline" method="post" action="" name="housekeeping_form">
    <div class="container mt-1">
        <div class="row">
            <div class="col-9">
                <p class="help-block font-italic text-left">
                    <xsl:choose>
                        <xsl:when test="job">
                            This report was last reconciled on: <xsl:value-of select="job/end_date"/>
                        </xsl:when>
                        <xsl:otherwise>
                            This report has never been reconciled for this date.
                        </xsl:otherwise>
                    </xsl:choose>
                    <xsl:if test="last_job_status = 'failed'">
                        <div class="text-left" style="color: red;">The last update of this report failed to run.
                            <xsl:choose>
                                <xsl:when test="check_credentials = 'true'">
                                    Credentials check failed.
                                </xsl:when>
                                <xsl:otherwise>
                                    Check the <a><xsl:attribute name="href"><xsl:value-of select="last_job_error_log"/></xsl:attribute>error log</a> for details.
                                </xsl:otherwise>
                            </xsl:choose>
                        </div>
                    </xsl:if>
                </p>
            </div>
            <div class="col-3">
                <div class="d-flex justify-content-end">
                    <xsl:choose>
                        <xsl:when test="job_in_progress">
                            <a class="btn btn-primary disabled" href="javascript:void(0)">Update in Progress <span class="bi-arrow-repeat-white ml-1"/></a>
                        </xsl:when>
                        <xsl:otherwise>
                            <input type="hidden" name="housekeeping_job" id="housekeeping_job" value="" />
                            <a class="btn btn-primary" href="javascript:void(0)" onclick="document.getElementById('housekeeping_job').value = 'true';housekeeping_form.submit();">Refresh Now <span class="bi-arrow-repeat-white ml-1"/></a>
                        </xsl:otherwise>
                    </xsl:choose>
                </div>

                <p class="help-block">
                    <xsl:if test="job_in_progress">
                        Come back to this page in a few minutes.
                    </xsl:if>
                </p>
            </div>
        </div>
    </div>
    </form>

    <div class="container-fluid font-weight-bold">
        <xsl:if test="totals/level2">
            <div class="row">
                <div class="col">
                    20s : <span id="hk_total_level2"><xsl:value-of select="totals/level2"/></span>
                </div>
                <div class="col">
                    40s : <span id="hk_total_level4"><xsl:value-of select="totals/level4"/></span>
                </div>
                <div class="col">
                    50s : <span id="hk_total_level5"><xsl:value-of select="totals/level5"/></span>
                </div>
                <div class="col">
                    60s / 70s : <span id="hk_total_level6_7"><xsl:value-of select="totals/level6_7"/></span>
                </div>
            </div>
        </xsl:if>
        <div class="row mt-2 mb-2">
            <xsl:if test="totals/upstairs">
                <div class="col">
                    Upstairs : <span id="hk_total_upstairs"><xsl:value-of select="totals/upstairs" /></span>
                </div>
            </xsl:if>
            <div class="col">
                Total : <span id="hk_total_total"><xsl:value-of select="totals/total" /></span>
            </div>
        </div>
    </div>

</xsl:template>

<xsl:template name="report_data">

    <table  class="table table-borderless table-hover table-sm">
        <thead class="thead-dark">
            <tr>
                <th scope="col" style="width: 50px;">Room</th>
                <th scope="col" style="width: 150px;">Bed</th>
                <th scope="col">Bedsheets</th>
            </tr>
        </thead>
        <tbody>
            <xsl:apply-templates select="bed" mode="bedsheet_row"/>
        </tbody>
    </table>

</xsl:template>

<xsl:template match="bed" mode="bedsheet_row">

    <xsl:choose>
        <xsl:when test="room = preceding-sibling::node()/room" />
        <xsl:otherwise>
            <tr>
                <td colspan="3" class="border_top border_bottom border_left border_right"><div class="room_demarkation">Room <xsl:value-of select="room"/> (<xsl:value-of select="room_type"/>)</div></td>
            </tr>
        </xsl:otherwise>
    </xsl:choose>
    <tr>
        <xsl:attribute name="data-room-id"><xsl:value-of select="room_id"/></xsl:attribute>
        <td class="text-left">
            <xsl:value-of select="room"/>
        </td>
        <td class="text-left">
            <xsl:value-of select="bed_name"/>
        </td>
        <td class="text-left">
            <span>
                <xsl:attribute name="class">
                    <xsl:text>hk-bedsheet-badge badge </xsl:text>
                    <xsl:choose>
                        <xsl:when test="bedsheet = 'CHANGE (CHECKED OUT)'">badge-change-out</xsl:when>
                        <xsl:when test="bedsheet = 'CHANGE (IN-HOUSE)'">badge-change-inhouse</xsl:when>
                        <xsl:when test="bedsheet = 'CHANGE (ROOM CLOSURE)'">badge-change-closure</xsl:when>
                        <xsl:when test="contains(bedsheet, 'DAY CHANGE')">badge-ndaychange</xsl:when>
                        <xsl:when test="starts-with(bedsheet, 'CHANGE')">badge-change</xsl:when>
                        <xsl:when test="bedsheet = 'NO CHANGE'">badge-nochange</xsl:when>
                        <xsl:when test="bedsheet = 'EMPTY'">badge-empty</xsl:when>
                    </xsl:choose>
                </xsl:attribute>
                <xsl:value-of select="bedsheet"/>
            </span>
        </td>
    </tr>

</xsl:template>

</xsl:stylesheet>
