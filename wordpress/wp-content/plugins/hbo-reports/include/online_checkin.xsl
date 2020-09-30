<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->
<xsl:template match="view">
    <h3>Online Checkin</h3>

    <div class="container-fluid">
        <canvas id="qr_canvas"><xsl:comment/></canvas>
        <div id="ajax_error"><xsl:comment/></div>
        <div id="ajax_response">
            <!-- only javascript responses should appear here -->
        </div>
    </div>

    <script type="text/javascript" src="{pluginurl}/js/qrcode.js"><xsl:comment/></script>
    <script type="text/javascript">
        <xsl:text disable-output-escaping="yes">
        const getElement = (id) => document.getElementById(id);
        function display_qrcode(booking_url) {
            QRCode.toCanvas(getElement('qr_canvas'), booking_url,
                function (error) {
                    if (error) {
                        jQuery('#ajax_error').html(error);
                    }
                    else {
                        console.log('Successfully loaded ' + booking_url);
                        jQuery('#ajax_error').html("");
                    }
                });
        }

        jQuery(document).ready(function() {

            function connect_ws() {
                const ws = new WebSocket('wss://localhost:3030');
                ws.onopen = () => {
                    console.log('Now connected');
                };
                ws.onclose = () => {
                    console.log("Disconnected!");
                    connect_ws(); // keepalive!
                };
                ws.onmessage = (event) => {
                    const payload = JSON.parse(event.data);
                    if(payload.action == 'reset') {
                        display_qrcode("https://bookings.macbackpackers.com/");
                    }
                    else if(payload.booking_ref) {
                        generate_booking_url(payload.booking_ref);
                    }
                };
            }

            connect_ws();
        });
        </xsl:text>
    </script>

</xsl:template>

</xsl:stylesheet>