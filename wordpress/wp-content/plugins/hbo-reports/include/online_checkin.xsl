﻿<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->
<xsl:template match="view">

    <xsl:if test="reset_view">
        <xsl:apply-templates select="reset_view"/>
    </xsl:if>
    <xsl:if test="booking">
        <xsl:apply-templates select="booking"/>
    </xsl:if>
    <xsl:if test="not(booking) and not(reset_view)">

<style>
:fullscreen {
    background-color: #fff;
}

#fullscreen_btn:fullscreen {
    display: none;
}

#qr_canvas_url {
    font-size: 24px;
}
</style>

    <div class="container">
        <div id="body_content" style="min-height: 200px;"><h5>Please wait... reticulating splines...</h5></div>
        <div id="ajax_error"><xsl:comment/></div>
        <button id="fullscreen_btn" class="btn btn-primary mt-5 mb-2" onclick="open_fullscreen(getElement('body_content')); jQuery(this).hide();">View Fullscreen</button><br/>
    </div>

    <script type="text/javascript" src="{pluginurl}/js/qrcode.js"><xsl:comment/></script>
    <script type="text/javascript">
        const notify_url = '<xsl:value-of select="notifyurl"/>';
        <xsl:text disable-output-escaping="yes">

        function open_fullscreen(elem) {
            if (elem.requestFullscreen) {
                elem.requestFullscreen();
            } else if (elem.mozRequestFullScreen) { /* Firefox */
                elem.mozRequestFullScreen();
            } else if (elem.webkitRequestFullscreen) { /* Chrome, Safari and Opera */
                elem.webkitRequestFullscreen();
            } else if (elem.msRequestFullscreen) { /* IE/Edge */
                elem.msRequestFullscreen();
            }
        }

        // need to show fullscreen button if we exit fullscreen mode
        if (document.addEventListener) {
            document.addEventListener('fullscreenchange', fullscreen_exit_handler, false);
            document.addEventListener('mozfullscreenchange', fullscreen_exit_handler, false);
            document.addEventListener('MSFullscreenChange', fullscreen_exit_handler, false);
            document.addEventListener('webkitfullscreenchange', fullscreen_exit_handler, false);
        }

        function fullscreen_exit_handler() {
            if (!document.webkitIsFullScreen &amp;&amp; !document.mozFullScreen &amp;&amp; document.msFullscreenElement == null) {
                jQuery('#fullscreen_btn').show();
            }
        }

        const getElement = (id) => document.getElementById(id);
        function display_qrcode(booking_url) {
            const qrcode_width = screen.width &lt;= 1024 ? 300 : 400;
            QRCode.toCanvas(getElement('qr_canvas'), booking_url, { width: qrcode_width },
                function (error) {
                    if (error) {
                        jQuery('#ajax_error').html(error);
                    }
                    else {
                        console.log('Successfully loaded ' + booking_url);
                    }
                });
            jQuery("#qr_canvas_url").html('&lt;a href="' + booking_url + '"&gt;' + booking_url + '&lt;/a&gt;');
        }

        jQuery(document).ready(function() {

            function connect_ws() {
                const ws = new WebSocket(notify_url);

                ws.onopen = () => {
                    console.log('Now connected');
                    ws.isAlive = true;

                    ws.interval = setInterval( () => {
                        if(ws.isAlive === false) {
                            console.log("Did not receive echo back. Forcing disconnect.");
                            return ws.close();
                        }
                        ws.isAlive = false;
                        ws.send("ping");
                    }, 30000 );
                };
                ws.onclose = () => {
                    console.log("Disconnected!");
                    clearInterval(ws.interval);
                    connect_ws(); // keepalive!
                };
                ws.onmessage = (event) => {
                    if(event.data == "pong") {
                        console.log("received echo back :)");
                        ws.isAlive = true;
                    }
                    else {
                        const payload = JSON.parse(event.data);
                        if(payload.action == 'reset') {
                            generate_booking_url("reset_view");
                        }
                        else if(payload.booking_ref) {
                            generate_booking_url(payload.booking_ref);
                            // reset after 5 minutes
                            setTimeout( () => { generate_booking_url("reset_view"); }, 300000);
                        }
                    }
                };
            }

            connect_ws();
            generate_booking_url("reset_view");
        });
        </xsl:text>
    </script>

    </xsl:if>
</xsl:template>

<xsl:template match="reset_view">
    <div style="margin-left: 40px; margin-top: calc(3vh);">
        <xsl:if test="error_message">
            <div class="alert alert-danger" role="alert">
                <xsl:value-of select="error_message"/>
            </div>
        </xsl:if>
        <h2>Welcome to <xsl:value-of select="../hostel"/>!</h2>
        <div class="row mt-3">
            <div class="offset-sm-2 col-8" style="font-size: 1.3rem;">
                Please take the time now to update your details with us.
                Everyone in your group needs to do this. Thank you and enjoy your stay!
            </div>
        </div>
        <div class="row mb-4">
            <div class="w-100 text-center">
                <canvas id="qr_canvas"><xsl:comment/></canvas>
                <div id="qr_canvas_url"><xsl:comment/></div>
            </div>
        </div>
        <img style="position:relative;top:-90px;" width="100" src="{../logo}"/>
    </div>
    <script type="text/javascript">
        display_qrcode("https://bookings.macbackpackers.com/");
    </script>
</xsl:template>

<xsl:template match="booking">
    <div style="margin-left: 40px; margin-top: calc(3vh);">
        <h2>Welcome <xsl:value-of select="name"/>!</h2>
        <div class="row">
            <div class="offset-sm-2 col-8" style="font-size: 1.3rem;">
                Here are your booking details. Please take the time now to update your details with us.
                Everyone in your group needs to do this. Thank you and enjoy your stay!
            </div>
        </div>
        <div class="row">
            <div class="col-5 mt-5" style="font-size: 1.1rem;">
                Booking Reference: <xsl:value-of select="identifier"/><br/>
                <xsl:if test="string-length(third_party_identifier) > 0">
                    3rd Party Booking Reference: <xsl:value-of select="third_party_identifier"/><br/>
                </xsl:if>
                Booking Source: <xsl:value-of select="booking_source"/><br/>
                Checkin: <xsl:value-of select="checkin_date"/><br/>
                Checkout: <xsl:value-of select="checkout_date"/><br/>
                Number of Guests: <xsl:value-of select="num_guests"/><br/>
                Grand Total: £<xsl:value-of select="grand_total"/><br/>
                <strong>Balance Due: £<xsl:value-of select="balance_due"/></strong><br/>
            </div>
            <div class="col-7">
                <canvas id="qr_canvas"><xsl:comment/></canvas>
            </div>
        </div>
        <div class="row mb-4 text-center" style="position: relative">
            <div class="w-100" id="qr_canvas_url"><xsl:comment/></div>
            <img style="position: absolute; left: 20px; top: calc(-9vh)" width="100" src="{/view/logo}"/>
        </div>

        <script type="text/javascript">
            display_qrcode('<xsl:value-of select="booking_url"/>');
        </script>
    </div>
</xsl:template>

</xsl:stylesheet>