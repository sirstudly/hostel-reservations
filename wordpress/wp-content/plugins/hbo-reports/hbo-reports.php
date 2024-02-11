<?php
/*
Plugin Name: Hostel Backoffice
Plugin URI: http://demo.hostelbackoffice.net/
Description: Backoffice reports for Little Hotelier.
Version: 0.2
Author: sir_studly
Author URI: http://www.hostelbackoffice.net
Tested WordPress Versions: 4.6
*/

/*  Copyright 2012 - 2018  http://www.hostelbackoffice.net

    This file is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

    // A J A X /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    if ( isset( $_POST['ajax_action'] )) {
        require_once( dirname(__FILE__) . '/../../../wp-load.php' );
        @header('Content-Type: text/html; charset=' . get_option('blog_charset'));
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //   D e f i n e     S T A T I C              //////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    if (!defined('HBO_BOOTSTRAP_FILE'))       define('HBO_BOOTSTRAP_FILE',  __FILE__ );

    if (!defined('WP_CONTENT_DIR'))      define('WP_CONTENT_DIR', ABSPATH . 'wp-content');                   // Z:\home\test.wpdevelop.com\www/wp-content
    if (!defined('WP_CONTENT_URL'))      define('WP_CONTENT_URL', site_url() . '/wp-content');    // http://test.wpdevelop.com/wp-content
    if (!defined('WP_PLUGIN_DIR'))       define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');               // Z:\home\test.wpdevelop.com\www/wp-content/plugins
    if (!defined('WP_PLUGIN_URL'))       define('WP_PLUGIN_URL', WP_CONTENT_URL . '/plugins');               // http://test.wpdevelop.com/wp-content/plugins
    if (!defined('HBO_PLUGIN_FILENAME'))  define('HBO_PLUGIN_FILENAME',  basename( __FILE__ ) );              // menu-compouser.php
    if (!defined('HBO_PLUGIN_DIRNAME'))   define('HBO_PLUGIN_DIRNAME',  plugin_basename(dirname(__FILE__)) ); // menu-compouser
    if (!defined('HBO_PLUGIN_DIR')) define('HBO_PLUGIN_DIR', WP_PLUGIN_DIR.'/'.HBO_PLUGIN_DIRNAME ); // Z:\home\test.wpdevelop.com\www/wp-content/plugins/menu-compouser
    if (!defined('HBO_PLUGIN_URL')) define('HBO_PLUGIN_URL', WP_PLUGIN_URL.'/'.HBO_PLUGIN_DIRNAME ); // http://test.wpdevelop.com/wp-content/plugins/menu-compouser


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //   L O A D   F I L E S                      //////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    if (file_exists(HBO_PLUGIN_DIR. '/lib/common_functions.php')) {     // S u p p o r t    f u n c t i o n s
        require_once(HBO_PLUGIN_DIR. '/lib/common_functions.php' ); }

    if (file_exists(HBO_PLUGIN_DIR. '/lib/wp_hostel_backoffice.php'))           // B O O T S T R A P
        { require_once(HBO_PLUGIN_DIR. '/lib/wp_hostel_backoffice.php' ); }

    if (file_exists(HBO_PLUGIN_DIR. '/include/xsl_transform.class.php')) 
        { require_once(HBO_PLUGIN_DIR. '/include/xsl_transform.class.php' ); }

    if (file_exists(HBO_PLUGIN_DIR. '/include/database_exception.class.php')) 
        { require_once(HBO_PLUGIN_DIR. '/include/database_exception.class.php' ); }

    if (file_exists(HBO_PLUGIN_DIR. '/include/validation_exception.class.php')) 
        { require_once(HBO_PLUGIN_DIR. '/include/validation_exception.class.php' ); }

    if (file_exists(HBO_PLUGIN_DIR. '/include/processing_exception.class.php')) 
        { require_once(HBO_PLUGIN_DIR. '/include/processing_exception.class.php' ); }

    if (file_exists(HBO_PLUGIN_DIR. '/include/db_transaction.class.php')) 
        { require_once(HBO_PLUGIN_DIR. '/include/db_transaction.class.php' ); }

    if (file_exists(HBO_PLUGIN_DIR. '/include/housekeeping.class.php')) 
        { require_once(HBO_PLUGIN_DIR. '/include/housekeeping.class.php' ); }

    if (file_exists(HBO_PLUGIN_DIR. '/include/lh_split_room_report.class.php')) 
        { require_once(HBO_PLUGIN_DIR. '/include/lh_split_room_report.class.php' ); }

    if (file_exists(HBO_PLUGIN_DIR. '/include/lh_unpaid_deposit_report.class.php')) 
        { require_once(HBO_PLUGIN_DIR. '/include/lh_unpaid_deposit_report.class.php' ); }

    if (file_exists(HBO_PLUGIN_DIR. '/include/lh_group_bookings_report.class.php')) 
        { require_once(HBO_PLUGIN_DIR. '/include/lh_group_bookings_report.class.php' ); }

    if (file_exists(HBO_PLUGIN_DIR. '/include/lh_abstract_bedcounts.class.php')) 
        { require_once(HBO_PLUGIN_DIR. '/include/lh_abstract_bedcounts.class.php' ); }

    if (file_exists(HBO_PLUGIN_DIR. '/include/lh_abstract_bedcounts_new.class.php'))
        { require_once(HBO_PLUGIN_DIR. '/include/lh_abstract_bedcounts_new.class.php' ); }

    if (file_exists(HBO_PLUGIN_DIR. '/include/lh_bedcounts.class.php'))
        { require_once(HBO_PLUGIN_DIR. '/include/lh_bedcounts.class.php' ); }

    if (file_exists(HBO_PLUGIN_DIR. '/include/lh_bedcounts_new.class.php'))
        { require_once(HBO_PLUGIN_DIR. '/include/lh_bedcounts_new.class.php' ); }

    if (file_exists(HBO_PLUGIN_DIR. '/include/lh_bedcounts_csv.class.php'))
        { require_once(HBO_PLUGIN_DIR. '/include/lh_bedcounts_csv.class.php' ); }

    if (file_exists(HBO_PLUGIN_DIR. '/include/lh_bedcounts_csv_new.class.php'))
        { require_once(HBO_PLUGIN_DIR. '/include/lh_bedcounts_csv_new.class.php' ); }

    if (file_exists(HBO_PLUGIN_DIR. '/include/lh_guest_comments_report_data.class.php'))
        { require_once(HBO_PLUGIN_DIR. '/include/lh_guest_comments_report_data.class.php' ); }

    if (file_exists(HBO_PLUGIN_DIR. '/include/lh_guest_comments_report.class.php')) 
        { require_once(HBO_PLUGIN_DIR. '/include/lh_guest_comments_report.class.php' ); }

    if (file_exists(HBO_PLUGIN_DIR. '/include/bottom_bunks_report.class.php'))
        { require_once(HBO_PLUGIN_DIR. '/include/bottom_bunks_report.class.php' ); }

    if (file_exists(HBO_PLUGIN_DIR. '/include/calendar_snapshots.class.php'))
        { require_once(HBO_PLUGIN_DIR. '/include/calendar_snapshots.class.php' ); }

    if (file_exists(HBO_PLUGIN_DIR. '/include/lh_job_history.class.php'))
        { require_once(HBO_PLUGIN_DIR. '/include/lh_job_history.class.php' ); }

    if (file_exists(HBO_PLUGIN_DIR. '/include/lh_report_settings.class.php')) 
        { require_once(HBO_PLUGIN_DIR. '/include/lh_report_settings.class.php' ); }

    if (file_exists(HBO_PLUGIN_DIR. '/include/lh_manual_charge.class.php')) 
        { require_once(HBO_PLUGIN_DIR. '/include/lh_manual_charge.class.php' ); }

    if (file_exists(HBO_PLUGIN_DIR. '/include/generate_payment_link.class.php'))
        { require_once(HBO_PLUGIN_DIR. '/include/generate_payment_link.class.php' ); }

    if (file_exists(HBO_PLUGIN_DIR. '/include/scheduled_job.class.php')) 
        { require_once(HBO_PLUGIN_DIR. '/include/scheduled_job.class.php' ); }

    if (file_exists(HBO_PLUGIN_DIR. '/include/scheduled_job_repeat.class.php')) 
        { require_once(HBO_PLUGIN_DIR. '/include/scheduled_job_repeat.class.php' ); }

    if (file_exists(HBO_PLUGIN_DIR. '/include/scheduled_job_daily.class.php')) 
        { require_once(HBO_PLUGIN_DIR. '/include/scheduled_job_daily.class.php' ); }

    if (file_exists(HBO_PLUGIN_DIR. '/include/scheduled_job_view_data.class.php')) 
        { require_once(HBO_PLUGIN_DIR. '/include/scheduled_job_view_data.class.php' ); }

    if (file_exists(HBO_PLUGIN_DIR. '/include/scheduled_job_view.class.php')) 
        { require_once(HBO_PLUGIN_DIR. '/include/scheduled_job_view.class.php' ); }

    if (file_exists(HBO_PLUGIN_DIR. '/include/blacklist_alias.class.php'))
        { require_once(HBO_PLUGIN_DIR. '/include/blacklist_alias.class.php' ); }

    if (file_exists(HBO_PLUGIN_DIR. '/include/blacklist_entry.class.php'))
        { require_once(HBO_PLUGIN_DIR. '/include/blacklist_entry.class.php' ); }

    if (file_exists(HBO_PLUGIN_DIR. '/include/blacklist_mugshot.class.php'))
        { require_once(HBO_PLUGIN_DIR. '/include/blacklist_mugshot.class.php' ); }

    if (file_exists(HBO_PLUGIN_DIR. '/include/blacklist.class.php'))
        { require_once(HBO_PLUGIN_DIR. '/include/blacklist.class.php' ); }

    if (file_exists(HBO_PLUGIN_DIR. '/include/payment_history.class.php'))
        { require_once(HBO_PLUGIN_DIR. '/include/payment_history.class.php' ); }

    if (file_exists(HBO_PLUGIN_DIR. '/include/payment_history_inv.class.php'))
        { require_once(HBO_PLUGIN_DIR. '/include/payment_history_inv.class.php' ); }

    if (file_exists(HBO_PLUGIN_DIR. '/include/process_refunds.class.php'))
        { require_once(HBO_PLUGIN_DIR. '/include/process_refunds.class.php' ); }

    if (file_exists(HBO_PLUGIN_DIR. '/include/refund_history.class.php'))
        { require_once(HBO_PLUGIN_DIR. '/include/refund_history.class.php' ); }

    if (file_exists(HBO_PLUGIN_DIR. '/include/lil_hotelier_dbo.class.php')) 
        { require_once(HBO_PLUGIN_DIR. '/include/lil_hotelier_dbo.class.php' ); }

    if (file_exists(HBO_PLUGIN_DIR. '/include/settings.class.php')) 
        { require_once(HBO_PLUGIN_DIR. '/include/settings.class.php' ); }

    if (file_exists(HBO_PLUGIN_DIR. '/include/html_headers.class.php')) 
        { require_once(HBO_PLUGIN_DIR. '/include/html_headers.class.php' ); }

    if (file_exists(HBO_PLUGIN_DIR. '/include/help_page.class.php')) 
        { require_once(HBO_PLUGIN_DIR. '/include/help_page.class.php' ); }

    if (file_exists(HBO_PLUGIN_DIR. '/include/page_factory.class.php')) 
        { require_once(HBO_PLUGIN_DIR. '/include/page_factory.class.php' ); }

    if (file_exists(HBO_PLUGIN_DIR. '/include/online_checkin.class.php'))
        { require_once(HBO_PLUGIN_DIR. '/include/online_checkin.class.php' ); }

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //    A J A X     R e s p o n d e r
    if (file_exists(HBO_PLUGIN_DIR. '/lib/ajax_controller.php'))  
        { require_once(HBO_PLUGIN_DIR. '/lib/ajax_controller.php' ); }

    // RUN //
    error_reporting(E_ALL);
    session_start();

    $_SESSION['WP_HOSTELBACKOFFICE'] = new WP_HostelBackoffice();

?>