<?php

/**
 * Wordpress Plugin bootstrap. 
 * From here, all other classes are defined and bind points made to wordpress.
 */
class WP_HostelBackoffice {

    /**
     * Default constructor.
     * We will essentially need to call this on each request.
     */
    function __construct() {
        // reset timezone on server
        date_default_timezone_set('Europe/London');

        // Install / Uninstall
        register_activation_hook( HBO_BOOTSTRAP_FILE, array(&$this,'activate'));
        register_deactivation_hook( HBO_BOOTSTRAP_FILE, array(&$this,'deactivate'));

        // Create admin menu
        add_action('admin_menu', array(&$this, 'create_admin_menu'));
        add_action('admin_head', array(&$this, 'enqueue_scripts'));

        // top level admin menu option
        add_action('admin_bar_menu', array(&$this, 'add_admin_bar_bookings_menu'), 70);

        // On client-side menu
        add_action('wp_head', array(&$this, 'enqueue_scripts'));
        add_action('wp_head', array(&$this, 'print_js_css' ));

        // Template fallback: this gets called when not on admin page
        // TODO: can we create a template file the user references when creating a new page?
        add_action("template_redirect", array(&$this, 'my_template_redirect'));

        // this handles the "download" action as we need to set the headers before anything is sent
        add_action( 'plugins_loaded', array(&$this, 'download_bedcounts_page_as_csv'));
    }

    /**
     * Called once on install.
     */
    function activate() {
        add_option('hbo_housekeeping_url', 'housekeeping');
        add_option('hbo_split_room_report_url', 'reports/reservations-split-across-rooms');
        add_option('hbo_unpaid_deposit_report_url', 'reports/unpaid-deposit-report');
        add_option('hbo_group_bookings_report_url', 'reports/group-bookings');
        add_option('hbo_bedcounts_url', 'reports/bedcounts');
        add_option('hbo_guest_comments_report_url', 'reports/guest-comments');
        add_option('hbo_manual_charge_url', 'reports/manual-charge');
        add_option('hbo_generate_payment_link_url', 'payments/generate-payment-link');
        add_option('hbo_payment_history_url', 'payments/payment-history');
        add_option('hbo_payment_history_inv_url', 'payments/invoice-payment-history');
        add_option('hbo_process_refunds_url', 'payments/process-refunds');
        add_option('hbo_report_settings_url', 'admin/report-settings');
        add_option('hbo_redirect_to_url', 'redirect-to');
        add_option('hbo_view_log_url', 'view-log');
        add_option('hbo_log_directory', 'logs');
        add_option('hbo_log_directory_url', '/__SITENAME__/view-log/?job_id=');
        add_option('hbo_job_history_url', 'admin/job-history');
        add_option('hbo_job_scheduler_url', 'admin/job-scheduler');
        add_option('hbo_group_booking_size', '6');
        add_option('hbo_include_5_guests_in_6bed_dorm', 'true');
        self::insert_site_pages();
    }

    /**
     * Called once on uninstall.
     */
    function deactivate() {
        delete_option('hbo_siteicon_url');
        delete_option('hbo_housekeeping_url');
        delete_option('hbo_split_room_report_url');
        delete_option('hbo_unpaid_deposit_report_url');
        delete_option('hbo_group_bookings_report_url');
        delete_option('hbo_bedcounts_url');
        delete_option('hbo_guest_comments_report_url');
        delete_option('hbo_report_settings_url');
        delete_option('hbo_redirect_to_url');
        delete_option('hbo_view_log_url');
        delete_option('hbo_log_directory');
        delete_option('hbo_log_directory_url');
        delete_option('hbo_job_history_url');
        delete_option('hbo_job_scheduler_url');
        delete_option('hbo_manual_charge_url');
        delete_option('hbo_generate_payment_link_url');
        delete_option('hbo_payment_history_url');
        delete_option('hbo_payment_history_inv_url');
        delete_option('hbo_run_processor_cmd');
        delete_option('hbo_group_booking_size');
        delete_option('hbo_include_5_guests_in_6bed_dorm');
        self::delete_site_pages();
    }

    /**
     * Create an additional admin menu for this plugin.
     */    
    function create_admin_menu() {
        $title = 'Bookings';

        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // S E T T I N G S
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $pagehook1 = add_menu_page( 'Settings',  $title, 'administrator',
                HBO_BOOTSTRAP_FILE . '-settings', array(&$this, 'content_of_settings_page'),  HBO_PLUGIN_URL . '/img/calendar-16x16.png'  );
        add_action("admin_print_scripts-" . $pagehook1 , array( &$this, 'add_js_css_files'));
    }

    /**
     * Safely enqueues any scripts/css to be run.
     */
    function enqueue_scripts() {
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_style('wp-jquery-ui-dialog');
// warning: mixed content        wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
        if (strpos($_SERVER['REQUEST_URI'], 'wpdev-booking.phpwpdev-booking') !== false) {
            if (defined('WP_ADMIN') && WP_ADMIN === true) { 
                wp_enqueue_script('jquery-ui-dialog'); 
            }
        }
    }

    /**
     * Add hook for printing scripts only when displaying pages for this plugin.
     */
    function add_js_css_files() {
        // Write inline scripts and CSS at HEAD
        add_action('admin_head', array(&$this, 'print_js_css'));
    }

    /**
     * Print     J a v a S c r i p t   &    C S S    scripts for admin and client side.
     */
    function print_js_css() {

        wp_print_scripts('jquery');
        //wp_print_scripts('jquery-ui-core');

        $hh = new HtmlHeaders();
        echo $hh->toHtml();
    }

    /**
     * Write the contents of the Job History page.
     */
    function content_of_job_history_page() {
        $s = new LHJobHistory();
        $s->doView();
        echo $s->toHtml();
    }

    /**
     * Write the contents of the Report Settings page.
     */
    function content_of_report_settings_page() {
        $s = new LHReportSettings();
        $s->doView();
        echo $s->toHtml();
    }

    /**
     * Write the contents of the Settings page.
     */
    function content_of_settings_page() {
        $s = new Settings();
        if (false === empty($_POST)) {
            $s->updateOptions($_POST);
        }
        echo $s->toHtml();
    }

    /**
     * Downloads the bedcounts page as a CSV file
     */
    function download_bedcounts_page_as_csv() {
        if (isset($_POST['download_bedcounts']) && trim($_POST['download_bedcounts']) == 'true' ) {

            // generate headers
            $filename = "bedcounts_".$_POST['selectiondate'].".csv";
            header('Content-Disposition: attachment; filename='.$filename);
            header('Content-type: application/force-download');
            header('Content-Transfer-Encoding: binary');
            header('Pragma: public');

            // generate the CSV file and terminate
            $bc = new BedCountsCSV( DateTime::createFromFormat(
                    '!Y-m-d', $_POST['selectiondate'], new DateTimeZone('UTC')));
            $bc->updateBedcounts();
            echo $bc->toCSV();

            die();      
        }
    }

    /**
     * Display a top-level menu dropdown on the admin menu (when logged in as admin).
     */
    function add_admin_bar_bookings_menu(){
        global $wp_admin_bar;
        
        $link_settings = admin_url('admin.php'). "?page=" . HBO_PLUGIN_DIRNAME . '/'. HBO_PLUGIN_FILENAME . "-settings";

        $wp_admin_bar->add_menu(
                array(
                    'id' => 'booking_settings',
                    'title' => 'Bookings',
                    'href' => $link_settings
                    )
                );
    }

    /**
     * This will override the template for the pages associated with this plugin
     * based on the name of the page.
     * These can be set under the Settings for the plugin.
     */
    function my_template_redirect() {
        $this->do_redirect_for_page(get_option('hbo_housekeeping_url'), 'housekeeping.php');
        $this->do_redirect_for_page(get_option('hbo_split_room_report_url'), 'reservations-split-across-rooms.php');
        $this->do_redirect_for_page(get_option('hbo_unpaid_deposit_report_url'), 'unpaid-deposit-report.php');
        $this->do_redirect_for_page(get_option('hbo_group_bookings_report_url'), 'group-bookings.php');
        $this->do_redirect_for_page(get_option('hbo_bedcounts_url'), 'bedcounts.php');
        $this->do_redirect_for_page(get_option('hbo_guest_comments_report_url'), 'guest-comments.php');
        $this->do_redirect_for_page(get_option('hbo_report_settings_url'), 'report-settings.php');
        $this->do_redirect_for_page(get_option('hbo_redirect_to_url'), 'redirect-link.php');
        $this->do_redirect_for_page(get_option('hbo_job_history_url'), 'job-history.php');
        $this->do_redirect_for_page(get_option('hbo_job_scheduler_url'), 'job-scheduler.php');
        $this->do_redirect_for_page(get_option('hbo_manual_charge_url'), 'manual-charge.php');
        $this->do_redirect_for_page(get_option('hbo_generate_payment_link_url'), 'generate-payment-link.php');
        $this->do_redirect_for_page(get_option('hbo_payment_history_url'), 'payment-history.php');
        $this->do_redirect_for_page(get_option('hbo_payment_history_inv_url'), 'payment-history-inv.php');
        $this->do_redirect_for_page(get_option('hbo_process_refunds_url'), 'process-refunds.php');
        $this->do_redirect_for_page(get_option('hbo_view_log_url'), 'view-log.php');
    }

    /**
     * Redirects to page if the current pagename matches $url.
     * $url : url to redirect if matched
     * $templatefile : name of template file to redirect to
     */
    function do_redirect_for_page($url, $templatefile) {
        global $wp;

        // if we're redirecting using a permalink; e.g. /redirect-to/anotherpage/12345
        $page_path = parse_url( $_SERVER['REQUEST_URI'] );
        $page_path = $page_path['path'];
        $home_path = parse_url( home_url() );

        if( array_key_exists( 'path', $home_path )) {
            $home_path = $home_path['path'];

            // if the page path, e.g. /castlerock/redirect-to/...
            // starts the same as the home path, e.g. /castlerock
            // then remove the first bit
            if( substr($page_path, 0, strlen($home_path)) === $home_path ) {
                $page_path = substr( $page_path, strlen($home_path) );
            }
        }

        // if the first character is a slash, remove it
        if ( substr($page_path, 0, 1) === '/' ) {
            $page_path = substr( $page_path, 1 );
        }

        if ( substr($page_path, 0, strlen($url)) === $url ) {
            $_SESSION['url_path'] = $page_path; // save in session so we can use it later
            $this->do_redirect($this->get_template_path($templatefile));
        }

        elseif ( isset($wp->query_vars["pagename"]) && $wp->query_vars["pagename"] == $url) {
            $this->do_redirect($this->get_template_path($templatefile));
        }
    }

    /**
     * Returns the path to the given template file.
     * $templatefile : name of template file to redirect to
     */
    function get_template_path($templatefile) {
        $plugindir = dirname( __FILE__ ) . '/..';
        
        if (file_exists(TEMPLATEPATH . '/' . $templatefile)) {
            $return_template = TEMPLATEPATH . '/' . $templatefile;
        } else {
            $return_template = $plugindir . '/templates/' . $templatefile;
        }
        return $return_template;
    }

    /**
     * Includes the php file specified and terminates.
     * $url : location of php file to include.
     */
    function do_redirect($url) {
        global $post, $wp_query;
//        if (have_posts()) {
            include($url);
            die();
//        } else {
//            $wp_query->is_404 = true;
//        }
    }

    /**
     * Executes a single SQL statement.
     * $simple_sql : sql statement to execute
     * $throw_ex_on_error : bool (when true, if error occurs, a DatabaseException() is thrown)
     */
    function execute_simple_sql($simple_sql, $throw_ex_on_error = false) {
        global $wpdb;
        if (false === $wpdb->query($simple_sql)) {
            error_log($wpdb->last_error." executing sql: ".$wpdb->last_query);
            if ($throw_ex_on_error) {
                throw new DatabaseException($wpdb->last_error);
            }
        }
    }

    /**
     * Create template placeholder and help pages for all users on the site.
     */
    function insert_site_pages() {
        $pf = new PageFactory();
        $pf->createTemplatePages();
    }

    /**
     * Delete the template placeholder and help pages which were created from insert_site_pages.
     */
    function delete_site_pages() {
        $pf = new PageFactory();
        $pf->deleteTemplatePages();
    }
}

?>
