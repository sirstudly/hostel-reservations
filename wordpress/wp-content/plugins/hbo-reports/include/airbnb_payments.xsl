<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<xsl:template match="view">
    <h3>Airbnb Payments</h3>

    <xsl:if test="message">
        <div class="alert alert-success"><xsl:value-of select="message"/></div>
    </xsl:if>
    <xsl:if test="error">
        <div class="alert alert-danger"><xsl:value-of select="error"/></div>
    </xsl:if>

    <div class="card mb-3" style="max-width: 720px;">
        <div class="card-body">
            <h5 class="card-title">Import Airbnb transactions CSV</h5>
            <form id="airbnb_import_form" enctype="multipart/form-data" onsubmit="return false;">
                <div class="form-group">
                    <label for="airbnb_csv_file">File</label>
                    <input type="file" class="form-control-file" id="airbnb_csv_file" name="airbnb_csv_file" accept=".csv,text/csv"/>
                </div>
                <div class="form-group">
                    <label>Post payment amount from (property default):
                        <strong><xsl:value-of select="post_payment_field"/></strong>
                    </label>
                </div>
                <button type="button" class="btn btn-primary" onclick="upload_airbnb_csv()">Import</button>
                <span id="airbnb_import_status" class="ml-2"></span>
            </form>
            <hr/>
            <form method="post">
                <div class="form-group">
                    <label for="hbo_airbnb_post_payment_field">Property post field</label>
                    <select class="form-control" id="hbo_airbnb_post_payment_field" name="hbo_airbnb_post_payment_field" style="max-width:240px;">
                        <option value="amount">
                            <xsl:if test="post_payment_field = 'amount'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
                            Amount
                        </option>
                        <option value="gross_earnings">
                            <xsl:if test="post_payment_field = 'gross_earnings'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
                            Gross earnings
                        </option>
                    </select>
                </div>
                <button type="submit" name="save_airbnb_post_field" value="1" class="btn btn-secondary btn-sm">Save setting</button>
            </form>
        </div>
    </div>

    <div class="mb-2">
        <label>Filter status:
            <select id="airbnb_status_filter" onchange="filter_airbnb_status(this.value)">
                <option value="">All</option>
                <xsl:for-each select="document('')">
                </xsl:for-each>
                <option value="imported"><xsl:if test="/view/filter_status='imported'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>imported</option>
                <option value="processing"><xsl:if test="/view/filter_status='processing'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>processing</option>
                <option value="processed"><xsl:if test="/view/filter_status='processed'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>processed</option>
                <option value="needs_review"><xsl:if test="/view/filter_status='needs_review'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>needs_review</option>
                <option value="errored"><xsl:if test="/view/filter_status='errored'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>errored</option>
                <option value="ignored"><xsl:if test="/view/filter_status='ignored'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>ignored</option>
                <option value="unmatched"><xsl:if test="/view/filter_status='unmatched'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>unmatched</option>
            </select>
        </label>
    </div>

    <xsl:apply-templates select="rows"/>

    <div id="airbnb_process_dialog" title="Process Airbnb payment" style="display:none;">
        <form id="airbnb_process_form">
            <input type="hidden" id="airbnb_process_confirmation" name="confirmation_code"/>
            <p id="airbnb_process_summary"></p>
            <div class="form-group">
                <label for="airbnb_process_action">Action</label>
                <select class="form-control" id="airbnb_process_action" name="action">
                    <option value="auto">Auto reconcile</option>
                    <option value="post_amount">Post Amount</option>
                    <option value="post_gross">Post Gross earnings</option>
                    <option value="void_repost">Void Airbnb/ronbot credits + repost</option>
                    <option value="mark_processed">Mark processed</option>
                    <option value="mark_ignored">Mark ignored</option>
                </select>
            </div>
        </form>
        <div id="airbnb_process_response"></div>
    </div>
</xsl:template>

<xsl:template match="rows">
    <xsl:choose>
        <xsl:when test="row">
            <table id="airbnb-payments-table" class="table table-striped table-sm">
                <thead class="thead-dark">
                    <tr>
                        <th scope="col">Guest</th>
                        <th scope="col">Confirmation</th>
                        <th scope="col">Check-in</th>
                        <th scope="col">Check-out</th>
                        <th scope="col">Booked</th>
                        <th scope="col">Amount</th>
                        <th scope="col">Service fee</th>
                        <th scope="col">Gross</th>
                        <th scope="col">Booking status</th>
                        <th scope="col">Processing</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    <xsl:apply-templates select="row"/>
                </tbody>
            </table>
<script type="text/javascript">
jQuery('#airbnb-payments-table').DataTable({
  "paging": true,
  "pageLength": 50,
  "order": [[2, 'desc']]
});
</script>
        </xsl:when>
        <xsl:otherwise>
            <div style="margin: 20px 0 20px 50px; font-style: italic;"><h4>No data available.</h4></div>
        </xsl:otherwise>
    </xsl:choose>
</xsl:template>

<xsl:template match="row">
    <tr>
        <td>
            <xsl:choose>
                <xsl:when test="cloudbeds_href">
                    <a target="_blank">
                        <xsl:attribute name="href"><xsl:value-of select="cloudbeds_href"/></xsl:attribute>
                        <xsl:value-of select="guest_name"/>
                    </a>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="guest_name"/>
                </xsl:otherwise>
            </xsl:choose>
        </td>
        <td class="text-nowrap"><xsl:value-of select="confirmation_code"/></td>
        <td><xsl:attribute name="data-order"><xsl:value-of select="checkin_datetime"/></xsl:attribute><xsl:value-of select="checkin_date"/></td>
        <td><xsl:attribute name="data-order"><xsl:value-of select="checkout_datetime"/></xsl:attribute><xsl:value-of select="checkout_date"/></td>
        <td><xsl:attribute name="data-order"><xsl:value-of select="booked_datetime"/></xsl:attribute><xsl:value-of select="booked_date"/></td>
        <td><xsl:value-of select="amount"/></td>
        <td><xsl:value-of select="service_fee"/></td>
        <td><xsl:value-of select="gross_earnings"/></td>
        <td><xsl:value-of select="booking_status"/></td>
        <td>
            <xsl:value-of select="processing_status"/>
            <xsl:if test="status_reason">
                <br/><small class="text-muted"><xsl:value-of select="status_reason"/></small>
            </xsl:if>
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-primary"
                onclick="show_airbnb_process_dialog(this)">
                <xsl:attribute name="data-confirmation"><xsl:value-of select="confirmation_code"/></xsl:attribute>
                <xsl:attribute name="data-guest"><xsl:value-of select="guest_name"/></xsl:attribute>
                <xsl:attribute name="data-status"><xsl:value-of select="processing_status"/></xsl:attribute>
                Process
            </button>
        </td>
    </tr>
</xsl:template>

</xsl:stylesheet>
