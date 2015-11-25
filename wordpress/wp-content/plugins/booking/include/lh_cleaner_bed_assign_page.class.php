<?php

/**
 * Controller for viewing/adding cleaner room assignments.
 */
class CleanerBedAssignmentsPage extends CleanerBedAssignmentsPageContent {

    // all cleaners (array of LHCleaner)
    private $cleanersTable = array();

    function CleanerBedAssignmentsPage() {
        // nothing to do
    }

    /**
     * Returns the filename for the stylesheet to use during transform.
     */
    function getXslFilename() {
        return WPDEV_BK_PLUGIN_DIR. '/include/lh_cleaner_bed_assign_page.xsl';
    }
}

?>