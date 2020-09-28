<?php

/**
 * Display controller for creating/editing job schedules.
 */
class ScheduledJobView extends ScheduledJobViewData {

    /**
     * Default constructor.
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * Returns the filename for the stylesheet to use during transform.
     */
    function getXslFilename() {
        return HBO_PLUGIN_DIR. '/include/scheduled_job_view.xsl';
    }

}

?>