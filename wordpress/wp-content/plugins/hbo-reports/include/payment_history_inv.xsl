<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->
<xsl:template match="view">

  <xsl:choose>
    <xsl:when test="invoices/invoice[@selected='true']">
        <xsl:apply-templates select="invoices/invoice[@selected='true']" mode="inv-detail-dialog"/>
    </xsl:when>
    <xsl:when test="reload_table_only = 'true'">
        <xsl:apply-templates select="invoices"/>
        <xsl:if test="not(invoices)">
            <xsl:call-template name="no_records"/>
        </xsl:if>

<script type="text/javascript">
    jQuery( ".inv-detail" ).dialog({
        autoOpen: false,
        modal: true,
        width:'80%' });
</script>
        
    </xsl:when>
    <xsl:otherwise>

<style media="screen" type="text/css">

.form-table {
    border-style: none; 
}

.form-table th label {
    font-size: 12px;
    font-weight: bold;
    font-style: normal;
    line-height: 25px;
}

.form-table td {
    border-top: initial;
}

.hint {
    margin-left: 20px;
}

#report-container {
    font-family: sans-serif;
    margin-left: 20px;
    margin-bottom: 20px;
}

#report-container h3 {
    margin: 10px 0;
}

#invoice-report tbody tr:nth-child(odd) td {
	background-color: #e3e3e3;
}

#invoice-report tbody tr td {
	padding-left: 15px;
}

#transaction-report tbody tr:nth-child(odd) td {
    background-color: #e3e3e3;
}

#transaction-report tbody tr td {
    padding-left: 15px;
}

.inv-detail label {
    font-weight: bold;
}

</style>
<script type="text/javascript">
function initDialogs() {
}

jQuery(document).ready(function(){
    jQuery( ".inv-detail" ).dialog({
        autoOpen: false,
        modal: true,
        width:'80%' });

    // reset all the checkboxes
    jQuery('input[type="checkbox"]').prop('checked', false);
});
</script>

    <div id="report-container" class="wrap bookingpage wpdevbk">

        <h3>Invoice Payment History</h3> 

        <div style="font-style:italic;">
            <p>This lists all invoice payments made to our payment gateway (Sagepay).<br/>
               This might include payments made to us for: 
               <ul>
                   <li>Lost property. Request any shipping costs be paid before posting.</li>
                   <li>...I can't think of anything else right now...</li>
               </ul>
            </p>
        </div>
        <div class="visibility_container" id="invoice_view">
            <xsl:apply-templates select="invoices" />
        </div>
	    <xsl:if test="not(invoices)">
            <xsl:call-template name="no_records"/>
	    </xsl:if>
    </div>

    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template match="invoices">
    <table id="invoice-report" class="allocation_view" width="100%" cellspacing="0" cellpadding="3" border="0">
        <thead>
            <th width="15%">Contact</th>
            <th>Payment Description</th>
            <th width="80px">Total Requested</th>
            <th width="80px">Total Paid</th>
            <th>Notes</th>
            <th width="100px"><xsl:comment/></th>
            <th width="100px">Acknowledge
          <input type="checkbox" name="show_ack_invoices" onClick="update_invoice_payment_view(this.checked);">
              <xsl:if test="/view/show_acknowledged = 'true'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
          </input>
            </th>
        </thead>
        <tbody>
            <xsl:apply-templates select="invoice"/>
        </tbody>
    </table>
</xsl:template>

<xsl:template match="invoice">
    <tr><xsl:if test="acknowledged_date"><xsl:attribute name="style">color: #aaa</xsl:attribute></xsl:if>
        <td><xsl:value-of select="recipient_name"/> (<xsl:value-of select="recipient_email"/>)</td>
        <td><xsl:value-of select="payment_description"/></td>
        <td><xsl:value-of select="payment_requested"/></td>
        <td><xsl:value-of select="total_paid"/><xsl:comment/></td>
        <td><xsl:apply-templates select="notes/note" mode="invoice_form"/></td>
        <td><xsl:apply-templates select="." mode="inv-detail-dialog"/>
            <a href="javascript:void(0);"><xsl:attribute name="onclick">jQuery('#inv-detail-<xsl:value-of select="invoice_id"/>').dialog('open');</xsl:attribute>View Details</a>
        </td>
        <td style="text-align: center;">
            <input type="checkbox" name="ack_checkbox">
                <xsl:if test="acknowledged_date"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
                <xsl:attribute name="onClick">if(this.checked) { acknowledge_invoice_payment(<xsl:value-of select="invoice_id"/>); this.parentElement.parentElement.style.color = '#aaa'; } else { unacknowledge_invoice_payment(<xsl:value-of select="invoice_id"/>); this.parentElement.parentElement.style.color = null; }</xsl:attribute>
            </input>
        </td>
    </tr>
</xsl:template>

<xsl:template match="invoice" mode="inv-detail-dialog">
    <div class="visibility_container inv-detail" title="Invoice/Payment Details">
        <xsl:attribute name="id">inv-detail-<xsl:value-of select="invoice_id"/></xsl:attribute>
        <div style="width:100%; clear:both;">
	        <label style="width: 200px; float: left;">Contact</label>
	        <div><xsl:value-of select="recipient_name"/> (<xsl:value-of select="recipient_email"/>)</div>
        </div>
        <div style="width:100%; clear:both;">
	        <label style="width: 200px; float: left;">Payment Description</label>
	        <div><xsl:value-of select="payment_description"/></div>
        </div>
        <div style="width:100%; clear:both;">
	        <label style="width: 200px; float: left;">Invoiced Amount</label>
	        <div><xsl:value-of select="payment_requested"/></div>
	    </div>
        <div style="width:100%; clear:both;">
	        <label style="width: 200px; float: left;">Total Paid</label>
	        <div><xsl:value-of select="total_paid"/><xsl:comment/></div>
	    </div>
        <xsl:if test="acknowledged_date">
	        <div style="width:100%; clear:both;">
		        <label style="width: 200px; float: left;">Acknowledged On</label>
		        <div><xsl:value-of select="acknowledged_date"/><xsl:comment/></div>
	        </div>
        </xsl:if>
        <div style="margin-bottom: 5px;"><xsl:comment/></div>

        <xsl:if test="transactions">
            <xsl:apply-templates select="transactions" />
        </xsl:if>
        <br/>
        <div class="wpdevbk" style="margin: 5px 0 10px 0;">
            <xsl:apply-templates select="notes"/>
        </div>
    </div>
</xsl:template>

<xsl:template match="transactions">
    <table id="transaction-report" class="allocation_view" width="100%" cellspacing="0" cellpadding="3" border="0">
        <thead>
            <th width="140px">Payee</th>
            <th width="170px">Vendor Tx Code</th>
            <th width="70px">Payment Amount</th>
            <th width="60px">Auth Status</th>
            <th>Auth Detail</th>
            <th width="180px">Card Details</th>
            <th width="120px">Process Date</th>
        </thead>
        <tbody>
            <xsl:apply-templates select="transaction"/>
        </tbody>
    </table>
</xsl:template>

<xsl:template match="transaction">
    <tr>
        <td><xsl:value-of select="first_name"/>&#160;<xsl:value-of select="last_name"/><br/><xsl:value-of select="email"/></td>
        <td><xsl:value-of select="vendor_tx_code"/></td>
        <td><xsl:value-of select="payment_amount"/></td>
        <td><xsl:value-of select="auth_status"/></td>
        <td><xsl:value-of select="auth_status_detail"/></td>
        <td><xsl:if test="card_type and last_4_digits"><xsl:value-of select="card_type"/> **** **** **** <xsl:value-of select="last_4_digits"/></xsl:if></td>
        <td><xsl:value-of select="processed_date"/></td>
    </tr>
</xsl:template>

<xsl:template match="note" mode="invoice_form">
    <xsl:value-of select="note_text"/><xsl:comment/><br/>
</xsl:template>

<xsl:template match="notes">
    <h4>Notes:</h4>
    <xsl:apply-templates select="note" mode="detail_form"/>
    <form name="post_invoice" action="" method="post">
	    <div style="margin-top: 10px; width: 100%">
		    <label style="width: 200px; float: left;"><xsl:comment/></label>
		    <textarea name="invoice_note" class="regular-text" style="width: 400px;"><xsl:attribute name="id">invoice_note-<xsl:value-of select="../invoice_id"/></xsl:attribute><xsl:comment/></textarea>
	    </div>
	    <div style="width: 610px;">
            <div style="float: left;"><xsl:attribute name="id">ajax_response-<xsl:value-of select="../invoice_id"/></xsl:attribute><xsl:comment/><!-- ajax response here--></div>
	        <a class="btn btn-primary" style="float: right;"><xsl:attribute name="onclick">add_invoice_note(<xsl:value-of select="../invoice_id"/>, jQuery('#invoice_note-<xsl:value-of select="../invoice_id"/>').val());jQuery('#invoice_note-<xsl:value-of select="../invoice_id"/>').val("");</xsl:attribute>Add Note</a>
	    </div>
    </form>
</xsl:template>

<xsl:template match="note" mode="detail_form">
    <div style="width: 200px; float: left;">
        <xsl:value-of select="created_date"/>
    </div>
    <div>
        <xsl:value-of select="note_text"/><xsl:comment/>
    </div>
</xsl:template>

<xsl:template name="no_records">
    <div style="margin: 20px 20px 10px 50px; font-style: italic;"><h4>No data available.</h4></div>
    <div style="margin: 10px 20px 20px 50px;"><a href="javascript:update_invoice_payment_view(true);">Show Acknowledged Records</a></div>
</xsl:template>

</xsl:stylesheet>