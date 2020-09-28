<?php

/**
 * Facade for generating pre-defined wordpress pages.
 */
class PageFactory {

    /** 
     * Default constructor.
     */
    function __construct() {
    }

    /**
     * Creates a locked page with the given title and contents.
     * $name : name (slug) of new page
     * $title : title of page
     * $content : full contents of page
     * $parent_post_id : (optional)  parent page post id
     * $menu_order : (optional) order if page is in a menu
     * Returns new post id
     */
    function createReadOnlyPage($name, $title, $content, $parent_post_id = 0, $menu_order = 0) {
        $my_post = array(
          'post_title'    => $title,
          'post_content'  => $content,
          'post_status'   => 'publish',
          'post_author'   => 1,
          'post_type'     => 'page',
          'post_name'     => $name,
          'post_parent'   => $parent_post_id,
          'menu_order'    => $menu_order,
          'comment_status' => 'closed',
          'ping_status'   => 'closed'
        );

        // Insert the page into the database
        $post_id = wp_insert_post( $my_post, true );

        if (is_wp_error($post_id)) {
            error_log($post_id->get_error_message());
            $post_id = 0;
        } else {
            update_post_meta($post_id, '_wp_page_template', 'sidebar-page.php'); // add sidebar to page
        } 
        return $post_id;
    }

    /**
     * Create placeholder pages which will be replaced with their respective template pages.
     */
    function createTemplatePages() {

        if (get_page_by_path('reports') == null) {
            $hp = new HelpPage('help_reports');
            $reports_id = $this->createReadOnlyPage('reports', 'Reports', $hp->toHtml(), 0, 30);
            if ($reports_id > 0) {
                $post_id = $this->createReadOnlyPage('reservations-split-across-rooms', 'Reservations Split Across Rooms', 'template content goes here', $reports_id, 10);
                $post_id = $this->createReadOnlyPage('unpaid-deposit-report', 'Unpaid Deposit Report', 'template content goes here', $reports_id, 20);
                $post_id = $this->createReadOnlyPage('group-bookings', 'Group Bookings', 'template content goes here', $reports_id, 30);
                $post_id = $this->createReadOnlyPage('bedcounts', 'Bed Counts', 'template content goes here', $reports_id, 40);
                $post_id = $this->createReadOnlyPage('guest-comments', 'Guest Comments', 'template content goes here', $reports_id, 50);
                $post_id = $this->createReadOnlyPage('manual-charges', 'Manual Charges', 'template content goes here', $reports_id, 60);
            }
        }

        if (get_page_by_path('housekeeping') == null) {
            $this->createReadOnlyPage('housekeeping', 'Housekeeping', 'template content goes here', 0, 50);
        }

        if (get_page_by_path('admin') == null) {
            $hp = new HelpPage('help_admin');
            $admin_id = $this->createReadOnlyPage('admin', 'Admin', $hp->toHtml(), 0, 80);
            if ($admin_id > 0) {
                $post_id = $this->createReadOnlyPage('report-settings', 'Report Settings', 'template content goes here', $admin_id, 10);
                $post_id = $this->createReadOnlyPage('job-history', 'Job History', 'template content goes here', $admin_id, 20);
                $post_id = $this->createReadOnlyPage('job-scheduler', 'Job Scheduler', 'template content goes here', $admin_id, 30);
            }
        }

        if (get_page_by_path('help') == null) {
            $hp = new HelpPage();
            $help_id = $this->createReadOnlyPage('help', 'Help', $hp->toHtml(), 0, 100);
        }
    }

    /**
     * Forcefully removes all template placeholder pages created as part of createTemplatePages().
     */
    function deleteTemplatePages() {
        
        $tabs = array("reports", "housekeeping", "admin", "help");

        foreach ($tabs as $tab) {
            // find the page id and delete it
            $pg = get_page_by_path($tab);
            if ($pg != null) {
                self::deletePages($pg->ID);
            } 
        }
    }

    /**
     * Deletes a given page by id.
     * $page_id : ID of post/page to delete
     * $include_children : true/false (default true) to delete all child pages from given page
     */
    function deletePages($page_id, $include_children = true) {

        $pg_ids_to_del = array($page_id);

        if ($include_children) {
            // find the descendent pages
            $args = array(
	            'child_of' => $page_id,
	            'post_type' => 'page',
	            'post_status' => 'publish'
            ); 
            foreach (get_pages($args) as $p) {
                $pg_ids_to_del[] = $p->ID;
            }
        }

        // we physically delete them in reverse order so children are removed before their parent
        foreach (array_reverse($pg_ids_to_del) as $pid) {
            wp_delete_post($pid, true);
        }
    }

}

?>
