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
        register_activation_hook( WPDEV_BK_FILE, array(&$this,'activate'));
        register_deactivation_hook( WPDEV_BK_FILE, array(&$this,'deactivate'));

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
        add_option('hbo_date_format' , get_option('date_format'));
        add_option('hbo_bookings_url', 'admin/bookings');
        add_option('hbo_allocations_url', 'admin/allocations');
        add_option('hbo_summary_url', 'admin/summary');
        add_option('hbo_resources_url', 'admin/resources');
        add_option('hbo_editbooking_url', 'edit-booking');
        add_option('hbo_housekeeping_url', 'housekeeping');
        add_option('hbo_cleaner_bed_assign_url', 'housekeeping/cleaner-bed-assign.php');
        add_option('hbo_cleaner_tasks_url', 'admin/cleaner-tasks');
        add_option('hbo_cleaning_hours_url', 'admin/cleaning-hours');
        add_option('hbo_split_room_report_url', 'reports/reservations-split-across-rooms');
        add_option('hbo_unpaid_deposit_report_url', 'reports/unpaid-deposit-report');
        add_option('hbo_group_bookings_report_url', 'reports/group-bookings');
        add_option('hbo_booking_diffs_report_url', 'reports/booking-diffs');
        add_option('hbo_bedcounts_url', 'reports/bedcounts');
        add_option('hbo_guest_comments_report_url', 'reports/guest-comments');
        add_option('hbo_manual_charge_url', 'reports/manual-charge');
        add_option('hbo_report_settings_url', 'admin/report-settings');
        add_option('hbo_redirect_to_url', 'redirect-to');
        add_option('hbo_log_directory', 'logs');
        add_option('hbo_log_directory_url', 'logs');
        add_option('hbo_job_history_url', 'admin/job-history');
        add_option('hbo_run_processor_cmd', '~/java2/run_processor_mini.sh');
        add_option('hbo_group_booking_size', '6');
        add_option('hbo_include_5_guests_in_6bed_dorm', 'true');
        self::build_db_schema();
        self::insert_site_pages();
    }

    /**
     * Called once on uninstall.
     */
    function deactivate() {
        delete_option('hbo_date_format');
        delete_option('hbo_bookings_url');
        delete_option('hbo_allocations_url');
        delete_option('hbo_summary_url');
        delete_option('hbo_resources_url');
        delete_option('hbo_editbooking_url');
        delete_option('hbo_housekeeping_url');
        delete_option('hbo_cleaner_bed_assign_url');
        delete_option('hbo_cleaner_tasks_url');
        delete_option('hbo_cleaning_hours_url');
        delete_option('hbo_split_room_report_url');
        delete_option('hbo_unpaid_deposit_report_url');
        delete_option('hbo_group_bookings_report_url');
        delete_option('hbo_booking_diffs_report_url');
        delete_option('hbo_bedcounts_url');
        delete_option('hbo_guest_comments_report_url');
        delete_option('hbo_report_settings_url');
        delete_option('hbo_redirect_to_url');
        delete_option('hbo_log_directory');
        delete_option('hbo_log_directory_url');
        delete_option('hbo_job_history_url');
        delete_option('hbo_manual_charge_url');
        delete_option('hbo_run_processor_cmd');
        delete_option('hbo_group_booking_size');
        delete_option('hbo_include_5_guests_in_6bed_dorm');

        self::delete_site_pages();
        self::teardown_db_schema(get_option('hbo_delete_db_on_deactivate') == 'On');
        delete_option('hbo_delete_db_on_deactivate');
    }

    /**
     * Create an additional admin menu for this plugin.
     */    
    function create_admin_menu() {
        $title = __('Bookings', 'wpdev-booking');
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // M A I N     B O O K I N G
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $pagehook1 = add_menu_page( __('Bookings', 'wpdev-booking'),  $title, 'administrator',
                WPDEV_BK_FILE . 'wpdev-booking', array(&$this, 'content_of_bookings_page'),  WPDEV_BK_PLUGIN_URL . '/img/calendar-16x16.png'  );
        add_action("admin_print_scripts-" . $pagehook1 , array( &$this, 'add_js_css_files'));
            
        ///////////////// ALLOCATIONS VIEW /////////////////////////////////////////////
        $pagehook6 = add_submenu_page(WPDEV_BK_FILE . 'wpdev-booking',__('Allocations', 'wpdev-booking'), __('Allocations', 'wpdev-booking'), 'administrator',
                WPDEV_BK_FILE .'wpdev-booking-allocations', array(&$this, 'content_of_allocations_page')  );
        add_action("admin_print_scripts-" . $pagehook6 , array( &$this, 'add_js_css_files'));
            
        ///////////////// DAILY SUMMARY /////////////////////////////////////////////
        $pagehook5 = add_submenu_page(WPDEV_BK_FILE . 'wpdev-booking',__('Summary', 'wpdev-booking'), __('Summary', 'wpdev-booking'), 'administrator',
                WPDEV_BK_FILE .'wpdev-booking-summary', array(&$this, 'content_of_summary_page')  );
        add_action("admin_print_scripts-" . $pagehook5 , array( &$this, 'add_js_css_files'));
            
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // A D D     R E S E R V A T I O N
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $pagehook2 = add_submenu_page(WPDEV_BK_FILE . 'wpdev-booking',__('Add Booking', 'wpdev-booking'), __('Add Booking', 'wpdev-booking'), 'administrator',
                WPDEV_BK_FILE .'wpdev-booking-reservation', array(&$this, 'content_of_edit_booking_page')  );
        add_action("admin_print_scripts-" . $pagehook2 , array( &$this, 'add_js_css_files'));
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // A D D     R E S O U R C E S     Management
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $pagehook4 = add_submenu_page(WPDEV_BK_FILE . 'wpdev-booking',__('Resources', 'wpdev-booking'), __('Resources', 'wpdev-booking'), 'administrator',
                WPDEV_BK_FILE .'wpdev-booking-resources', array(&$this, 'content_of_resources_page')  );
        add_action("admin_print_scripts-" . $pagehook4 , array( &$this, 'add_js_css_files'));

        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // S E T T I N G S
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $pagehook3 = add_submenu_page(WPDEV_BK_FILE . 'wpdev-booking',__('Booking settings customizations', 'wpdev-booking'), __('Settings', 'wpdev-booking'), 'administrator',
                WPDEV_BK_FILE .'wpdev-booking-option', array(&$this, 'content_of_settings_page')  );
        add_action("admin_print_scripts-" . $pagehook3 , array( &$this, 'add_js_css_files'));
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        // TEST DATA
        $pagehook7 = add_submenu_page(WPDEV_BK_FILE . 'wpdev-booking',__('Generate Test Data', 'wpdev-booking'), __('Test Data', 'wpdev-booking'), 'administrator',
                WPDEV_BK_FILE .'wpdev-booking-testdata', array(&$this, 'content_of_testdata_page')  );
        add_action("admin_print_scripts-" . $pagehook7, array( &$this, 'add_js_css_files'));

        // UNIT TESTS
        $pagehook8 = add_submenu_page(WPDEV_BK_FILE . 'wpdev-booking',__('Run Unit Tests', 'wpdev-booking'), __('Unit Tests', 'wpdev-booking'), 'administrator',
                WPDEV_BK_FILE .'wpdev-booking-unittests', array(&$this, 'content_of_unittests_page')  );
        add_action("admin_print_scripts-" . $pagehook8, array( &$this, 'add_js_css_files'));
    }

    /**
     * Safely enqueues any scripts/css to be run.
     */
    function enqueue_scripts() {
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_style('wp-jquery-ui-dialog');
        wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
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
        add_action('admin_head', array(&$this, 'print_js_css' ));
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
     * Write contents of Bookings View
     */
    function content_of_bookings_page() {

        // if filter_status is defined, we are on the bookings view
        if (isset($_POST['filter_status']) && trim($_POST['filter_status']) != '') {
            $bv = new BookingView(
                DateTime::createFromFormat('!Y-m-d', $_POST['bookingmindate'], new DateTimeZone('UTC')),
                DateTime::createFromFormat('!Y-m-d', $_POST['bookingmaxdate'], new DateTimeZone('UTC')));
            $bv->status = $_POST['filter_status'];
            $bv->matchName = trim($_POST['filter_name']) == '' ? null : $_POST['filter_name'];
            $bv->dateMatchType = $_POST['filter_datetype'];
//            $bv->resourceId = $_POST['filter_resource_id'];
            $bv->doSearch();
        } 
        // redo search if we've already done one
        else if (isset($_SESSION['BOOKING_VIEW'])) {
            $bv = $_SESSION['BOOKING_VIEW'];
            $bv = new BookingView($bv->minDate, $bv->maxDate, 
                $bv->dateMatchType, $bv->status, $bv->resourceId, $bv->matchName);
            $bv->doSearch();
        } 
        // leave as blank view if it is the first time
        else {
            $bv = new BookingView();
        }
        $_SESSION['BOOKING_VIEW'] = $bv;

error_log($bv->toXml());
        echo $bv->toHtml();
    }

    /**
     * Write contents of Allocations View.
     */
    function content_of_allocations_page() {

        // if allocation date is defined, we are on the allocations view
        if (isset($_POST['allocationmindate']) && trim($_POST['allocationmindate']) != '') {
            $av = new AllocationView(
                DateTime::createFromFormat('!Y-m-d', $_POST['allocationmindate'], new DateTimeZone('UTC')),
                DateTime::createFromFormat('!Y-m-d', $_POST['allocationmaxdate'], new DateTimeZone('UTC')));
        }
        // redo search on allocation dates
        else if (isset($_SESSION['ALLOCATION_VIEW'])) {
            $av = new AllocationView($_SESSION['ALLOCATION_VIEW']->showMinDate, $_SESSION['ALLOCATION_VIEW']->showMaxDate);
        }
        // use default dates if it's the first time 
        else {
            $av = new AllocationView();
        }

        $av->doSearch();
        $_SESSION['ALLOCATION_VIEW'] = $av;
        
error_log($av->toXml());
        echo $av->toHtml();
    }

    /**
     * Write contents of Add/Edit Booking page
     */
    function content_of_edit_booking_page() {
        // always start with a new object
        $_SESSION['ADD_BOOKING_CONTROLLER'] = new AddBooking();

        // if we are editing an existing booking...
        if(isset($_REQUEST['bookingid'])) {
            // TODO: rename "ADD_BOOKING_CONTROLLER" to "EDIT_BOOKING_CONTROLLER", AddBooking to EditBooking
            $_SESSION['ADD_BOOKING_CONTROLLER']->load($_REQUEST['bookingid']);
        } 
            
        echo $_SESSION['ADD_BOOKING_CONTROLLER']->toHtml();
    }

    /**
     * Write the contents of the Daily Summary page.
     */
    function content_of_summary_page() {
        $ds = new DailySummary();
        $ds->doSummaryUpdate();
        echo $ds->toHtml();
    }

    /**
     * Write the contents of the booking resources page.
     */
    function content_of_resources_page(){

        if (isset($_GET['editResourceId'])) {

            $rpp = new ResourcePropertyPage($_GET['editResourceId']);

            // TODO: move to ResourcePropertyPage
            // SAVE button was pressed on the edit resource property page
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $propertyIds = isset($_POST['resource_property']) ? $_POST['resource_property'] : array();
                ResourceDBO::updateResourceProperties($_GET['editResourceId'], $propertyIds);
                $rpp->isSaved = true;
            }
                
error_log($rpp->toXml());
            echo $rpp->toHtml();

        } else {
            $resources = new Resources();
    
            try {
                // TODO: move to Resources page
                // if the user has just submitted an "Add new resource" request
                if ( isset($_POST['resource_name_new']) && $_POST['resource_name_new'] != '') {
                    ResourceDBO::insertResource($_POST['resource_name_new'], $_POST['resource_capacity_new'], 
                        $_POST['resource_parent_new'] == 0 ? null : $_POST['resource_parent_new'],
                        $_POST['resource_type_new']);
                }
        
            } catch (DatabaseException $de) {
                $resources->errorMessage = $de->getMessage();
            }
            echo $resources->toHtml();
        }
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
     * Test generation page.
     */
    function content_of_testdata_page() {
        $td = new GenerateTestData();
        echo $td->toHtml();
    }

    /**
     * Unit Test generation page.
     */
    function content_of_unittests_page() {
        $td = new RunUnitTests();
        echo $td->toHtml();
    }

    /**
     * Write the contents of the cleaner bed assignment page.
     */
    function content_of_cleaner_bed_assign_page() {
        error_log( 'In content_of_cleaner_bed_assign_page');
        if (isset($_SESSION['CLEANER_BED_CONTROLLER'])) {
            $pg = $_SESSION['CLEANER_BED_CONTROLLER'];
error_log( 'content_of_cleaner_bed_assign_page - controller already set');
        }
        else {
            $pg = new CleanerBedAssignmentsPage();
            $_SESSION['CLEANER_BED_CONTROLLER'] = $pg;
error_log( 'content_of_cleaner_bed_assign_page - creating new controller');
        }
        $pg->loadAssignments();
        echo $pg->toHtml();
    }

    /**
     * Write the contents of the cleaner tasks (admin) page.
     */
    function content_of_cleaner_tasks_page() {

        if (isset($_SESSION['CLEANER_TASKS_CONTROLLER'])) {
            $pg = $_SESSION['CLEANER_TASKS_CONTROLLER'];
        }
        else {
            $pg = new LHCleanerTasks();
            $_SESSION['CLEANER_TASKS_CONTROLLER'] = $pg;
        }
        $pg->doView();
        echo $pg->toHtml();
    }

    /**
     * Write the contents of the cleaning hours allocation (admin) page.
     */
    function content_of_cleaning_hours_page() {

        if (isset($_SESSION['CLEANING_HOURS_CONTROLLER'])) {
            $pg = $_SESSION['CLEANING_HOURS_CONTROLLER'];
        }
        else {
            $pg = new LHCleaningHours();
            $_SESSION['CLEANING_HOURS_CONTROLLER'] = $pg;
        }
        $pg->doView();
        echo $pg->toHtml();
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
        
        $title = __('Bookings', 'wpdev-booking');
        $update_title = $title;

        $link_bookings = admin_url('admin.php'). "?page=" . WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME . "wpdev-booking";
        $link_settings = admin_url('admin.php'). "?page=" . WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME . "wpdev-booking-option";

        $wp_admin_bar->add_menu(
                array(
                    'id' => 'booking_options',
                    'title' => $update_title ,
                    'href' => $link_bookings
                    )
                );

        $wp_admin_bar->add_menu(
                array(
                    'parent' => 'booking_options',
                    'title' => __( 'Settings', 'wpdev-booking' ),
                    'href' => $link_settings,
                    'id' => 'booking_settings'
                    )
                );
    }

    /**
     * This will override the template for the pages associated with this plugin
     * based on the name of the page.
     * These can be set under the Settings for the plugin.
     */
    function my_template_redirect() {
        $this->do_redirect_for_page(get_option('hbo_allocations_url'), 'allocations.php');
        $this->do_redirect_for_page(get_option('hbo_bookings_url'), 'bookings.php');
        $this->do_redirect_for_page(get_option('hbo_summary_url'), 'summary.php');
        $this->do_redirect_for_page(get_option('hbo_editbooking_url'), 'edit-booking.php');
        $this->do_redirect_for_page(get_option('hbo_resources_url'), 'resources.php');
        $this->do_redirect_for_page(get_option('hbo_housekeeping_url'), 'housekeeping.php');
        $this->do_redirect_for_page(get_option('hbo_cleaner_bed_assign_url'), 'cleaner-bed-assign.php');
        $this->do_redirect_for_page(get_option('hbo_cleaner_tasks_url'), 'cleaner-tasks.php');
        $this->do_redirect_for_page(get_option('hbo_cleaning_hours_url'), 'cleaning-hours.php');
        $this->do_redirect_for_page(get_option('hbo_split_room_report_url'), 'reservations-split-across-rooms.php');
        $this->do_redirect_for_page(get_option('hbo_unpaid_deposit_report_url'), 'unpaid-deposit-report.php');
        $this->do_redirect_for_page(get_option('hbo_group_bookings_report_url'), 'group-bookings.php');
        $this->do_redirect_for_page(get_option('hbo_booking_diffs_report_url'), 'booking-diffs.php');
        $this->do_redirect_for_page(get_option('hbo_bedcounts_url'), 'bedcounts.php');
        $this->do_redirect_for_page(get_option('hbo_guest_comments_report_url'), 'guest-comments.php');
        $this->do_redirect_for_page(get_option('hbo_report_settings_url'), 'report-settings.php');
        $this->do_redirect_for_page(get_option('hbo_redirect_to_url'), 'redirect-link.php');
        $this->do_redirect_for_page(get_option('hbo_job_history_url'), 'job-history.php');
        $this->do_redirect_for_page(get_option('hbo_manual_charge_url'), 'manual-charge.php');
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
     * Create/update all db objects required for this booking.
     */
    function build_db_schema() {
        global $wpdb;
        $charset_collate = '';
        if (false === empty($wpdb->charset)) {
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        }

        if ( false == $this->does_table_exist('booking') ) { 
            $simple_sql = "CREATE TABLE ".$wpdb->prefix ."booking (
                        booking_id bigint(20) unsigned NOT NULL auto_increment,
                        firstname varchar(50) NOT NULL,
                        lastname varchar(50),
                        referrer varchar(50),
                        deposit_paid decimal(10,2),
                        amount_to_pay decimal(10,2),
                        created_by varchar(20) NOT NULL,
                        created_date datetime NOT NULL,
                        last_updated_by varchar(20) NOT NULL,
                        last_updated_date datetime NOT NULL,
                        PRIMARY KEY (booking_id)
                    ) $charset_collate;";

            self::execute_simple_sql($simple_sql);
        }

        if ( false == $this->does_table_exist('bookingresources') ) { 
            $simple_sql = "CREATE TABLE ".$wpdb->prefix ."bookingresources (
                        resource_id bigint(20) unsigned NOT NULL auto_increment,
                        name varchar(50) NOT NULL,
                        parent_resource_id bigint(20) unsigned,
                        resource_type varchar(10) NOT NULL,
                        room_type varchar(2),
                        created_by varchar(20) NOT NULL,
                        created_date datetime NOT NULL,
                        last_updated_by varchar(20) NOT NULL,
                        last_updated_date datetime NOT NULL,
                        PRIMARY KEY (resource_id),
                        FOREIGN KEY (parent_resource_id) REFERENCES ".$wpdb->prefix ."bookingresources(resource_id)
                    ) $charset_collate;";

            self::execute_simple_sql($simple_sql);
        }

        if ( false == $this->does_table_exist('mv_resources_by_path') ) { 
            $simple_sql = "CREATE TABLE ".$wpdb->prefix ."mv_resources_by_path (
                        resource_id bigint(20) unsigned NOT NULL,
                        name varchar(50) NOT NULL,
                        parent_resource_id bigint(20) unsigned,
                        path varchar(100) NOT NULL,
                        resource_type varchar(10) NOT NULL,
                        room_type varchar(2),
                        level int(10) unsigned NOT NULL,
                        number_children int(10) unsigned NOT NULL,
                        capacity int(10) unsigned NOT NULL,
                        PRIMARY KEY (resource_id)
                    ) $charset_collate;";

            self::execute_simple_sql($simple_sql);
        }
            
        if ( false == $this->does_table_exist('resource_properties') ) { 
            $simple_sql = "CREATE TABLE ".$wpdb->prefix ."resource_properties (
                        property_id bigint(20) unsigned NOT NULL,
                        description varchar(20) NOT NULL,
                        PRIMARY KEY (property_id)
                    ) $charset_collate;";

            self::execute_simple_sql($simple_sql);
                
            $simple_sql = "INSERT INTO ".$wpdb->prefix ."resource_properties (property_id, description)
                            VALUES(%d, %s)";
            $wpdb->query($wpdb->prepare($simple_sql, 1, '4-Bed Dorm'));
            $wpdb->query($wpdb->prepare($simple_sql, 2, '8-Bed Dorm'));
            $wpdb->query($wpdb->prepare($simple_sql, 3, '10-Bed Dorm'));
            $wpdb->query($wpdb->prepare($simple_sql, 4, '12-Bed Dorm'));
            $wpdb->query($wpdb->prepare($simple_sql, 5, '14-Bed Dorm'));
            $wpdb->query($wpdb->prepare($simple_sql, 6, '16-Bed Dorm'));
            $wpdb->query($wpdb->prepare($simple_sql, 7, 'Twin Room'));
            $wpdb->query($wpdb->prepare($simple_sql, 8, 'Double Room'));
            $wpdb->query($wpdb->prepare($simple_sql, 9, 'Triple Room'));
            $wpdb->query($wpdb->prepare($simple_sql, 10, 'Quad Room'));
        }
            
        if ( false == $this->does_table_exist('resource_properties_map') ) { 
            $simple_sql = "CREATE TABLE ".$wpdb->prefix ."resource_properties_map (
                        resource_id bigint(20) unsigned NOT NULL,
                        property_id bigint(20) unsigned NOT NULL,
                        FOREIGN KEY (resource_id) REFERENCES ".$wpdb->prefix ."bookingresources(resource_id),
                        FOREIGN KEY (property_id) REFERENCES ".$wpdb->prefix ."resource_properties(property_id)
                    ) $charset_collate;";

            self::execute_simple_sql($simple_sql);
        }
            
        if ( false == $this->does_table_exist('bookingcomment') ) { 
            $simple_sql = "CREATE TABLE ".$wpdb->prefix ."bookingcomment (
                        comment_id bigint(20) unsigned NOT NULL auto_increment,
                        booking_id bigint(20) unsigned NOT NULL,
                        comment TEXT NOT NULL,
                        comment_type varchar(10) NOT NULL,
                        created_by varchar(20) NOT NULL,
                        created_date datetime NOT NULL,
                        PRIMARY KEY (comment_id),
                        FOREIGN KEY (booking_id) REFERENCES ".$wpdb->prefix ."booking(booking_id)
                    ) $charset_collate;";

            self::execute_simple_sql($simple_sql);
        }
            
        if ( false == $this->does_table_exist('allocation') ) {
            $simple_sql = "CREATE TABLE ".$wpdb->prefix ."allocation (
                        allocation_id bigint(20) unsigned NOT NULL auto_increment,
                        booking_id bigint(20) unsigned NOT NULL,
                        resource_id bigint(20) unsigned NOT NULL,
                        guest_name varchar(50) NOT NULL,
                        gender varchar(1) NOT NULL,
                        req_room_size varchar(3),
                        req_room_type varchar(1),
                        created_by varchar(20) NOT NULL,
                        created_date datetime NOT NULL,
                        last_updated_by varchar(20) NOT NULL,
                        last_updated_date datetime NOT NULL,
                        PRIMARY KEY (allocation_id),
                        FOREIGN KEY (booking_id) REFERENCES ".$wpdb->prefix ."booking(booking_id),
                        FOREIGN KEY (resource_id) REFERENCES ".$wpdb->prefix ."bookingresources(resource_id) 
                    ) $charset_collate;";

            self::execute_simple_sql($simple_sql);
        }

        if ( false == $this->does_table_exist('bookingdates') ) {
            $simple_sql = "CREATE TABLE ".$wpdb->prefix ."bookingdates (
                        allocation_id bigint(20) unsigned NOT NULL,
                        booking_date date NOT NULL,
                        status varchar(10) NOT NULL,
                        checked_out varchar(1) NULL,
                        created_by varchar(20) NOT NULL,
                        created_date datetime NOT NULL,
                        last_updated_by varchar(20) NOT NULL,
                        last_updated_date datetime NOT NULL,
                        FOREIGN KEY (allocation_id) REFERENCES ".$wpdb->prefix ."allocation(allocation_id)
                    ) $charset_collate;";

            self::execute_simple_sql($simple_sql);
        }

        if ( false == $this->does_table_exist('daterange') ) {
            $simple_sql = "CREATE TABLE ".$wpdb->prefix ."daterange (
              `a_date` datetime NOT NULL,
              PRIMARY KEY(`a_date`)
            ) $charset_collate;";

            self::execute_simple_sql($simple_sql);
        }
            
        if( false == $this->does_routine_exist('walk_tree_path')) {
            $simple_sql = "CREATE FUNCTION walk_tree_path(p_resource_id BIGINT(20) UNSIGNED) RETURNS VARCHAR(255)
                -- walks the bookingresources table from the given resource_id down to the root
                -- returns the path walked delimited with /
                BEGIN
                    DECLARE last_id BIGINT(20) UNSIGNED;
                    DECLARE parent_id BIGINT(20) UNSIGNED;
                    DECLARE return_val VARCHAR(255);
                        
                    SET return_val = p_resource_id;
                    SET parent_id = p_resource_id;
                    
                    WHILE parent_id IS NOT NULL DO
                        
                        SELECT parent_resource_id INTO parent_id
                        FROM ".$wpdb->prefix ."bookingresources
                        WHERE resource_id = parent_id;
                    
                        SET return_val = CONCAT(IFNULL(parent_id, ''), '/', return_val);
                    
                    END WHILE;
                        
                    RETURN return_val;
                    
                END;";

            self::execute_simple_sql($simple_sql);
        }
            
        $simple_sql = "CREATE OR REPLACE VIEW ".$wpdb->prefix."v_resources_sub1 AS
                SELECT resource_id, name, parent_resource_id, walk_tree_path(resource_id) AS path, resource_type, room_type
                FROM ".$wpdb->prefix."bookingresources";

        self::execute_simple_sql($simple_sql);

        $simple_sql = "CREATE OR REPLACE VIEW ".$wpdb->prefix."v_resources_by_path AS
                SELECT resource_id, name, parent_resource_id, path, resource_type, room_type,
                        LENGTH(path) - LENGTH(REPLACE(path, '/', '')) AS level,
                        (SELECT COUNT(*) FROM ".$wpdb->prefix."v_resources_sub1 s1 WHERE s1.path LIKE CAST(CONCAT(s.path, '/%%') AS CHAR) AND resource_type = 'bed') AS number_children,
                        (SELECT COUNT(*) FROM ".$wpdb->prefix."v_resources_sub1 s1 WHERE (s1.path LIKE CAST(CONCAT(s.path, '/%%') AS CHAR) OR s1.path = s.path) AND resource_type = 'bed') AS capacity
                    FROM ".$wpdb->prefix."v_resources_sub1 s
                    ORDER BY path";
// FIXME: remove number_children, rename capacity ==> number_beds

        self::execute_simple_sql($simple_sql);

        $simple_sql = "CREATE OR REPLACE VIEW ".$wpdb->prefix."v_req_room_types AS
                -- subquery summing requested room types by room and booking date
                SELECT r.parent_resource_id, bd.booking_date, pr.room_type, 
                       SUM(CASE WHEN a.req_room_type = 'M' THEN 1 ELSE 0 END) AS num_m, -- number of male only room requests
                       SUM(CASE WHEN a.req_room_type = 'F' THEN 1 ELSE 0 END) AS num_f, -- number of female only room requests
                       SUM(CASE WHEN IFNULL(a.req_room_type, 'X') = 'X' AND a.gender = 'M' THEN 1 ELSE 0 END) as num_mx, -- number of mixed req but of all males
                       SUM(CASE WHEN IFNULL(a.req_room_type, 'X') = 'X' AND a.gender = 'F' THEN 1 ELSE 0 END) as num_fx, -- number of mixed req but of all female
                       SUM(CASE WHEN IFNULL(a.req_room_type, 'X') = 'X' AND a.gender = 'X' THEN 1 ELSE 0 END) as num_x -- number of mixed req and of unknown gender (forces mixed room)
                  FROM ".$wpdb->prefix."bookingresources r
                  JOIN ".$wpdb->prefix."bookingresources pr ON r.parent_resource_id = pr.resource_id AND pr.resource_type = 'room'
                  JOIN ".$wpdb->prefix."allocation a ON a.resource_id = r.resource_id
                  JOIN ".$wpdb->prefix."bookingdates bd ON a.allocation_id = bd.allocation_id
                 WHERE bd.status <> 'cancelled'
                 GROUP BY r.parent_resource_id, bd.booking_date, pr.room_type
                 ORDER BY r.parent_resource_id, bd.booking_date";

        self::execute_simple_sql($simple_sql);

        $simple_sql = "CREATE OR REPLACE VIEW ".$wpdb->prefix."v_derived_room_types AS
                -- view used for determining the requested room types by date
                SELECT parent_resource_id, booking_date, room_type, num_m, num_f, num_mx, num_fx, num_x,
                       CASE WHEN num_m > 0 AND num_f = 0 AND num_mx >= 0 AND num_fx = 0  AND num_x = 0 THEN 'M' -- male only
                            WHEN num_m = 0 AND num_f > 0 AND num_mx = 0  AND num_fx = 0  AND num_x = 0 THEN 'F' -- female only
                            WHEN num_m = 0 AND num_f = 0 AND num_mx >= 0 AND num_fx = 0  AND num_x > 0 THEN 'X' -- mixed
                            WHEN num_m = 0 AND num_f = 0 AND num_mx = 0  AND num_fx >= 0 AND num_x > 0 THEN 'X' -- mixed
                            WHEN num_m = 0 AND num_f = 0 AND num_mx > 0  AND num_fx = 0  AND num_x = 0 THEN 'MX' -- male/mixed
                            WHEN num_m = 0 AND num_f = 0 AND num_mx = 0  AND num_fx > 0  AND num_x = 0 THEN 'FX' -- female/mixed
                            WHEN num_m = 0 AND num_f = 0 AND (num_mx + num_fx + num_x) > 0 THEN 'X' -- mixed
                            ELSE 'E' -- error
                        END AS derived_room_type
                  FROM ".$wpdb->prefix."v_req_room_types";

        self::execute_simple_sql($simple_sql);

        self::build_triggers();
    }

    /**
     * Builds the schema specific for little hotelier
     */
    function build_lh_schema() {

        global $wpdb;
        $charset_collate = '';
        if (false === empty($wpdb->charset)) {
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        }

        if ( false == $this->does_table_exist('lh_jobs') ) {
            $simple_sql = "CREATE TABLE ".$wpdb->prefix ."lh_jobs (
              `job_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `classname` varchar(255) NOT NULL,
              `status` varchar(20) NOT NULL,
              `start_date` timestamp NULL DEFAULT NULL,
              `end_date` timestamp NULL DEFAULT NULL,
              `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `last_updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`job_id`)
            ) ENGINE=InnoDB $charset_collate;";

            self::execute_simple_sql($simple_sql);
        }

        if ( false == $this->does_table_exist('lh_job_param') ) {
            $simple_sql = "CREATE TABLE ".$wpdb->prefix ."lh_job_param (
              `job_param_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `job_id` bigint(20) unsigned NOT NULL,
              `name` varchar(255) NOT NULL,
              `value` varchar(255) NOT NULL,
              PRIMARY KEY (`job_param_id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 $charset_collate;";

            self::execute_simple_sql($simple_sql);
        }

        if ( false == $this->does_table_exist('lh_scheduled_jobs') ) {
            $simple_sql = "CREATE TABLE ".$wpdb->prefix ."lh_scheduled_jobs (
              `job_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `classname` varchar(255) NOT NULL,
              `cron_schedule` varchar(255) NOT NULL,
              `active_yn` char(1) DEFAULT 'Y',
              `last_scheduled_date` timestamp NULL DEFAULT NULL,
              `last_updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`job_id`)
            ) ENGINE=InnoDB $charset_collate;";

            self::execute_simple_sql($simple_sql);
        }

        if ( false == $this->does_table_exist('lh_scheduled_job_param') ) {
            $simple_sql = "CREATE TABLE ".$wpdb->prefix ."lh_scheduled_job_param (
              `job_param_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `job_id` bigint(20) unsigned NOT NULL,
              `name` varchar(255) NOT NULL,
              `value` varchar(255) NOT NULL,
              PRIMARY KEY (`job_param_id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 $charset_collate;";

            self::execute_simple_sql($simple_sql);
        }

        if ( false == $this->does_table_exist('lh_calendar') ) {
            $simple_sql = "CREATE TABLE ".$wpdb->prefix ."lh_calendar (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `job_id` bigint(2) unsigned DEFAULT NULL,
              `room_id` int(10) unsigned DEFAULT NULL,
              `room_type_id` int(10) unsigned DEFAULT NULL,
              `room` varchar(50) NOT NULL,
              `bed_name` varchar(50) DEFAULT NULL,
              `reservation_id` bigint(20) unsigned DEFAULT NULL,
              `guest_name` varchar(255) DEFAULT NULL,
              `checkin_date` datetime NOT NULL,
              `checkout_date` datetime NOT NULL,
              `payment_total` decimal(10,2) DEFAULT NULL,
              `payment_outstanding` decimal(10,2) DEFAULT NULL,
              `rate_plan_name` varchar(50) DEFAULT NULL,
              `payment_status` varchar(50) DEFAULT NULL,
              `num_guests` int(10) unsigned DEFAULT NULL,
              `data_href` varchar(255) DEFAULT NULL,
              `lh_status` varchar(50) DEFAULT NULL,
              `booking_reference` varchar(50) DEFAULT NULL,
              `booking_source` varchar(50) DEFAULT NULL,
              `booked_date` timestamp NULL DEFAULT NULL,
              `eta` varchar(50) DEFAULT NULL,
              `notes` text,
              `viewed_yn` char(1) DEFAULT NULL,
              `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              KEY `lh_c_checkin` (`checkin_date`),
              KEY `lh_c_checkout` (`checkout_date`),
              KEY `lh_c_jobid` (`job_id`),
              KEY `lh_c_roomid` (`room_id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 $charset_collate;";

            self::execute_simple_sql($simple_sql);
        }

        // reporting table for reservations split across multiple rooms of the same type
        if ( false == $this->does_table_exist('lh_rpt_split_rooms') ) {
            $simple_sql = "CREATE TABLE ".$wpdb->prefix ."lh_rpt_split_rooms (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `job_id` bigint(20) unsigned NOT NULL,
              `reservation_id` bigint(20) unsigned DEFAULT NULL,
              `guest_name` TEXT,
              `checkin_date` datetime NOT NULL,
              `checkout_date` datetime NOT NULL,
              `data_href` varchar(255) DEFAULT NULL,
              `lh_status` varchar(50) DEFAULT NULL,
              `booking_reference` varchar(50) DEFAULT NULL,
              `booking_source` varchar(50) DEFAULT NULL,
              `booked_date` timestamp NULL DEFAULT NULL,
              `eta` varchar(50) DEFAULT NULL,
              `notes` text,
              `viewed_yn` char(1) DEFAULT NULL,
              `created_date` timestamp NULL DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `job_id_idx` (`job_id`),
              FOREIGN KEY (`job_id`) REFERENCES ".$wpdb->prefix ."lh_jobs(`job_id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 $charset_collate;";

            self::execute_simple_sql($simple_sql);
        }

        // bookings where no deposit had been paid yet
        if ( false == $this->does_table_exist('lh_rpt_unpaid_deposit') ) {
            $simple_sql = "CREATE TABLE ".$wpdb->prefix ."lh_rpt_unpaid_deposit (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `job_id` bigint(20) unsigned NOT NULL,
              `guest_name` TEXT,
              `checkin_date` datetime NOT NULL,
              `checkout_date` datetime NOT NULL,
              `payment_total` decimal(10,2) DEFAULT NULL,
              `data_href` varchar(255) DEFAULT NULL,
              `booking_reference` varchar(50) DEFAULT NULL,
              `booking_source` varchar(50) DEFAULT NULL,
              `booked_date` timestamp NULL DEFAULT NULL,
              `notes` text,
              `viewed_yn` char(1) DEFAULT NULL,
              `created_date` timestamp NULL DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `job_id_idx` (`job_id`),
              FOREIGN KEY (`job_id`) REFERENCES ".$wpdb->prefix ."lh_jobs(`job_id`)
            ) ENGINE=InnoDB $charset_collate;";

            self::execute_simple_sql($simple_sql);
        }

        if ( false == $this->does_table_exist('lh_group_bookings') ) {
            $simple_sql = "CREATE TABLE ".$wpdb->prefix ."lh_group_bookings (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `job_id` bigint(20) unsigned NOT NULL,
              `reservation_id` bigint(20) unsigned DEFAULT NULL,
              `guest_name` TEXT,
              `booking_reference` varchar(50) DEFAULT NULL,
              `booking_source` varchar(50) DEFAULT NULL,
              `checkin_date` datetime NOT NULL,
              `checkout_date` datetime NOT NULL,
              `booked_date` timestamp NULL DEFAULT NULL,
              `payment_outstanding` decimal(10,2) DEFAULT NULL,
              `data_href` varchar(255) DEFAULT NULL,
              `num_guests` int(10) unsigned NOT NULL DEFAULT '0',
              `notes` text,
              `viewed_yn` char(1) DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `job_id_idx` (`job_id`),
              FOREIGN KEY (`job_id`) REFERENCES ".$wpdb->prefix ."lh_jobs(`job_id`)
            ) ENGINE=InnoDB $charset_collate;";

            self::execute_simple_sql($simple_sql);
        }

        // guest comments report
        if ( false == $this->does_table_exist('lh_rpt_guest_comments') ) {
            $simple_sql = "CREATE TABLE ".$wpdb->prefix ."lh_rpt_guest_comments (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `reservation_id` bigint(20) unsigned DEFAULT NULL,
              `comments` text,
              `acknowledged_date` timestamp NULL DEFAULT NULL,
              `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              KEY `lh_rpt_gc_reservation` (`reservation_id`),
              UNIQUE (`reservation_id`)
            ) ENGINE=InnoDB $charset_collate;";

            self::execute_simple_sql($simple_sql);
        }

        if ( false == $this->does_table_exist('lh_rooms') ) {
            $simple_sql = "CREATE TABLE ".$wpdb->prefix ."lh_rooms (
              `id` bigint(20) unsigned NOT NULL,
              `room` int(5) unsigned,
              `bed_name` varchar(50) DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB $charset_collate;";

            self::execute_simple_sql($simple_sql);
        }
    }

    /**
     * Tears down the schema specific for little hotelier
     * $delete_data : bool (true to drop all transactional tables as well, false to keep transactional tables)
     */
    function teardown_lh_schema( $delete_data ) {
        global $wpdb;
        if( $delete_data ) {
            self::execute_simple_sql("DROP TABLE IF EXISTS ".$wpdb->prefix ."lh_rpt_split_rooms");
            self::execute_simple_sql("DROP TABLE IF EXISTS ".$wpdb->prefix ."lh_rpt_unpaid_deposit");
            self::execute_simple_sql("DROP TABLE IF EXISTS ".$wpdb->prefix ."lh_group_bookings");
            self::execute_simple_sql("DROP TABLE IF EXISTS ".$wpdb->prefix ."lh_rpt_guest_comments");
            self::execute_simple_sql("DROP TABLE IF EXISTS ".$wpdb->prefix ."lh_scheduled_job_param");
            self::execute_simple_sql("DROP TABLE IF EXISTS ".$wpdb->prefix ."lh_scheduled_jobs");
            self::execute_simple_sql("DROP TABLE IF EXISTS ".$wpdb->prefix ."lh_calendar");
            self::execute_simple_sql("DROP TABLE IF EXISTS ".$wpdb->prefix ."lh_job_param");
            self::execute_simple_sql("DROP TABLE IF EXISTS ".$wpdb->prefix ."lh_jobs");
            self::execute_simple_sql("DROP TABLE IF EXISTS ".$wpdb->prefix ."lh_rooms");
        }
    }

    /**
     * Create triggers if they don't already exist.
     */
    function build_triggers() {
        global $wpdb;
        if ( false == $this->does_trigger_exist('trg_privates_conflict') ) {
            $simple_sql = "CREATE TRIGGER ".$wpdb->prefix."trg_privates_conflict
                AFTER INSERT ON ".$wpdb->prefix."bookingdates FOR EACH ROW
                BEGIN
                    DECLARE p_resource_id INT;
                    DECLARE p_parent_resource_id INT;
                    DECLARE p_parent_resource_type VARCHAR(10);
                    DECLARE p_distinct_ids INT;

                    -- first, find the resource and parent resource for the date we are inserting
                    SELECT r.resource_id, r.parent_resource_id, parent_res.resource_type 
                      INTO p_resource_id, p_parent_resource_id, p_parent_resource_type
                      FROM ".$wpdb->prefix."mv_resources_by_path r
                      JOIN ".$wpdb->prefix."allocation alloc ON alloc.resource_id = r.resource_id
                      JOIN ".$wpdb->prefix."bookingresources parent_res ON r.parent_resource_id = parent_res.resource_id
                     WHERE alloc.allocation_id = NEW.allocation_id;

                    -- make sure there are no other allocations sharing the same resource for the same day!
                    SELECT COUNT(1) INTO p_distinct_ids
                      FROM ".$wpdb->prefix."bookingdates bd
                      JOIN ".$wpdb->prefix."allocation alloc ON bd.allocation_id = alloc.allocation_id
                     WHERE bd.booking_date = NEW.booking_date
                       AND bd.status <> 'cancelled'
                       AND alloc.resource_id = p_resource_id;

                    IF p_distinct_ids > 1 THEN
                        SELECT 'Reservation conflicts with an existing reservation' INTO p_distinct_ids 
                        FROM SANITY_CHECK_RESERVATION_CONFLICT_FOUND;
                    END IF;

                    -- if we're adding a date onto a private booking, check that we don't overlap with another private booking!
                    IF p_parent_resource_type = 'private' THEN
        
                        SELECT COUNT(DISTINCT a.booking_id) INTO p_distinct_ids
                          FROM ".$wpdb->prefix."bookingdates bd
                          JOIN ".$wpdb->prefix."allocation a ON bd.allocation_id = a.allocation_id
                          JOIN ".$wpdb->prefix."mv_resources_by_path r ON a.resource_id = r.resource_id
                          JOIN ".$wpdb->prefix."bookingresources parent_res ON r.parent_resource_id = parent_res.resource_id
                         WHERE parent_res.resource_type = 'private'
                           AND parent_res.resource_id = p_parent_resource_id
                           AND bd.booking_date = NEW.booking_date
                           AND bd.status <> 'cancelled'
                         GROUP BY parent_res.resource_id, bd.booking_date;

                        IF p_distinct_ids > 1 THEN
                            SELECT 'Reservation for private room conflicts with an existing reservation' INTO p_distinct_ids 
                            FROM SANITY_CHECK_RESERVATION_CONFLICT_FOUND;
                        END IF;

                    END IF;
                END";

            self::execute_simple_sql($simple_sql);
        }

        if ( false == $this->does_trigger_exist('trg_mv_resources_by_path_ins') ) {
            $simple_sql = "CREATE TRIGGER ".$wpdb->prefix."trg_mv_resources_by_path_ins
                -- this will update the materialized view
                -- whenever an insert is made on the underlying table
                AFTER INSERT ON ".$wpdb->prefix."bookingresources FOR EACH ROW 
                BEGIN
                    INSERT INTO ".$wpdb->prefix."mv_resources_by_path
                    SELECT * FROM ".$wpdb->prefix."v_resources_by_path
                        WHERE resource_id = NEW.resource_id;

                    -- update the child count (could be clever by splitting path)
                    UPDATE ".$wpdb->prefix."mv_resources_by_path m
                      JOIN ".$wpdb->prefix."v_resources_by_path v
                        ON m.resource_id = v.resource_id
                       SET m.number_children = v.number_children,
                           m.capacity = v.capacity;
                END";

            self::execute_simple_sql($simple_sql);
        }

        if ( false == $this->does_trigger_exist('trg_mv_resources_by_path_upd') ) {
            $simple_sql = "CREATE TRIGGER ".$wpdb->prefix."trg_mv_resources_by_path_upd
                -- this will update the materialized view
                -- whenever an update is made on the underlying table
                AFTER UPDATE ON ".$wpdb->prefix."bookingresources FOR EACH ROW 
                BEGIN
                    -- currently only updatable field is name
                    IF NEW.name <> OLD.name AND NEW.resource_id = OLD.resource_id THEN
                        UPDATE ".$wpdb->prefix."mv_resources_by_path m
                          JOIN ".$wpdb->prefix."bookingresources br
                            ON m.resource_id = br.resource_id
                           SET m.name = br.name
                         WHERE m.resource_id = NEW.resource_id;
                    END IF;

                    /** DISABLED: no way to update any fields in wp_bookingresources except name
                    DELETE FROM ".$wpdb->prefix."mv_resources_by_path
                     WHERE resource_id = OLD.resource_id;

                    INSERT INTO ".$wpdb->prefix."mv_resources_by_path
                    SELECT * FROM ".$wpdb->prefix."v_resources_by_path
                     WHERE resource_id = NEW.resource_id;

                    -- update the child count (could be clever by splitting path)
                    UPDATE ".$wpdb->prefix."mv_resources_by_path m
                      JOIN ".$wpdb->prefix."v_resources_by_path v
                        ON m.resource_id = v.resource_id
                       SET m.number_children = v.number_children,
                           m.capacity = v.capacity;
                     */
                END";

            self::execute_simple_sql($simple_sql);
        }

        if ( false == $this->does_trigger_exist('trg_mv_resources_by_path_del') ) {
            $simple_sql = "CREATE TRIGGER ".$wpdb->prefix."trg_mv_resources_by_path_del
                -- this will update the materialized view
                -- whenever a delete is made on the underlying table
                AFTER DELETE ON ".$wpdb->prefix."bookingresources FOR EACH ROW 
                BEGIN
                    DELETE FROM ".$wpdb->prefix."mv_resources_by_path
                     WHERE resource_id = OLD.resource_id;

                    -- update the child count (could be clever by splitting path)
                    UPDATE ".$wpdb->prefix."mv_resources_by_path m
                      JOIN ".$wpdb->prefix."v_resources_by_path v
                        ON m.resource_id = v.resource_id
                       SET m.number_children = v.number_children,
                           m.capacity = v.capacity;
                END";

            self::execute_simple_sql($simple_sql);
        }

        // now rebuild our materialized view
        $simple_sql = "TRUNCATE TABLE ".$wpdb->prefix."mv_resources_by_path
            SELECT * FROM ".$wpdb->prefix."v_resources_by_path";
        self::execute_simple_sql($simple_sql);

        $simple_sql = "INSERT INTO ".$wpdb->prefix."mv_resources_by_path
            SELECT * FROM ".$wpdb->prefix."v_resources_by_path";
        self::execute_simple_sql($simple_sql);
    }

    /**
     * Drop all triggers for this plugin.
     */
    function teardown_triggers() {
        global $wpdb;
        self::execute_simple_sql("DROP TRIGGER ".$wpdb->prefix."trg_mv_resources_by_path_del");
        self::execute_simple_sql("DROP TRIGGER ".$wpdb->prefix."trg_mv_resources_by_path_upd");
        self::execute_simple_sql("DROP TRIGGER ".$wpdb->prefix."trg_mv_resources_by_path_ins");
        self::execute_simple_sql("DROP TRIGGER ".$wpdb->prefix."trg_privates_conflict");
    }

    /**
     * Removes db objects created as part of build_db_schema()
     * $delete_data : bool (true to drop all transactional tables as well, false to keep transactional tables)
     */
    function teardown_db_schema($delete_data) {
        global $wpdb;
        self::teardown_triggers();
        self::execute_simple_sql("DROP VIEW ".$wpdb->prefix."v_resources_by_path");
        self::execute_simple_sql("DROP VIEW ".$wpdb->prefix."v_resources_sub1");
        self::execute_simple_sql("DROP VIEW ".$wpdb->prefix."v_derived_room_types");
        self::execute_simple_sql("DROP VIEW ".$wpdb->prefix."v_req_room_types");

        self::execute_simple_sql("DROP FUNCTION walk_tree_path");
        self::execute_simple_sql("DROP TABLE ".$wpdb->prefix ."mv_resources_by_path");
        if ($delete_data) {
            self::execute_simple_sql("DROP TABLE ".$wpdb->prefix ."bookingdates");
            self::execute_simple_sql("DROP TABLE ".$wpdb->prefix ."allocation");
            self::execute_simple_sql("DROP TABLE ".$wpdb->prefix ."resource_properties_map");
            self::execute_simple_sql("DROP TABLE ".$wpdb->prefix ."resource_properties");
            self::execute_simple_sql("DROP TABLE ".$wpdb->prefix ."bookingresources");
            self::execute_simple_sql("DROP TABLE ".$wpdb->prefix ."bookingcomment");
            self::execute_simple_sql("DROP TABLE ".$wpdb->prefix ."booking");
        }
//        self::teardown_lh_schema( $delete_data ); not implemented here
    }

    /**
     * Clears *ALL* transactional data. Used for unit testing only!!
     */
    function delete_transactional_data() {
        global $wpdb;
        self::execute_simple_sql("TRUNCATE TABLE ".$wpdb->prefix ."bookingdates");
        self::execute_simple_sql("TRUNCATE TABLE ".$wpdb->prefix ."allocation");
        self::execute_simple_sql("TRUNCATE TABLE ".$wpdb->prefix ."bookingcomment");
        self::execute_simple_sql("TRUNCATE TABLE ".$wpdb->prefix ."booking");
    }

    /**
     * DROPS all db objects including ALL transactional tables and recreates them.
     * Returns std output log from data load.
     */
    function reset_sample_data() {
        // drop and recreate all db objects
        self::teardown_db_schema(true);
        self::build_db_schema();

        // no disable trigger command in mysql; 
        // for efficiency: drop triggers, load data, recreate triggers
        self::teardown_triggers();
        $output = self::load_sample_data();
        self::build_triggers();
        return $output;
    }

    /**
     * Executes all unit tests
     */
    function run_unit_tests() {
        $ut = new RunUnitTestsContent();
        $ut->runUnitTests();
        return $ut->toHtml();
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
     * Loads sample data for this plugin. Assumes an empty database to start.
     */
    function load_sample_data() {
        $gtd = new GenerateTestDataContent();
        $gtd->reloadTestData();
        return $gtd->toHtml();
    }

    /**
     * Check if table exists.
     * $tablename : name of table to check (with or without wp prefix)
     * Returns true or false.
     */
    function does_table_exist( $tablename ) {
        global $wpdb;
        if (strpos($tablename, $wpdb->prefix) === false) {
            $tablename = $wpdb->prefix . $tablename;   
        }
        return self::does_table_with_exact_name_exist( $tablename );
    }

    /**
     * Check if the table exists.
     * $tablename : exact name of table to check
     * Returns true or false.
     */
    function does_table_with_exact_name_exist( $tablename ) {
        global $wpdb;
        $res = $wpdb->get_results($wpdb->prepare(
                "SELECT COUNT(*) AS count
                   FROM information_schema.tables
                  WHERE table_schema = '". DB_NAME ."'
                    AND table_name = %s", $tablename));
        return $res[0]->count > 0;
    }

    /**
     * Check if trigger exists.
     * $triggerName : name of trigger to check (with or without wp prefix)
     * Returns true or false.
     */
    function does_trigger_exist( $triggerName ) {
        global $wpdb;
        if (strpos($triggerName, $wpdb->prefix) === false) {
            $triggerName = $wpdb->prefix . $triggerName;   
        }

        $res = $wpdb->get_results($wpdb->prepare(
                "SELECT COUNT(*) AS count
                   FROM information_schema.triggers
                  WHERE trigger_schema = '". DB_NAME ."'
                    AND trigger_name = %s", $triggerName));
        return $res[0]->count > 0;
    }

    /**
     * Check if procedure/function exists.
     * $routineName : name of routine to check (with or without wp prefix)
     * Returns true or false.
     */
    function does_routine_exist( $routineName ) {
        global $wpdb;

        $res = $wpdb->get_results($wpdb->prepare(
                "SELECT COUNT(*) AS count
                   FROM information_schema.routines
                  WHERE routine_schema = '". DB_NAME ."'
                    AND routine_name = %s", $routineName));
        return $res[0]->count > 0;
    }

    /**
     * Create template placeholder and help pages for all users on the site.
     */
    function insert_site_pages() {
        $pf = new PageFactory();
        $pf->createTemplatePages();
        $pf->createHelpPages();
    }

    /**
     * Delete the template placeholder and help pages which were created from insert_site_pages.
     */
    function delete_site_pages() {
        $pf = new PageFactory();
        $pf->deleteTemplatePages();
        $pf->deleteHelpPages();
    }
}

?>
