<?php

/**
 * Display controller for reports page.
 */
class LHGuestCommentsReport extends LHGuestCommentsReportData {

    /**
     * Default constructor.
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * Inserts an allocation scraper job into the jobs table.
     */
    function submitReportJob() {
        LilHotelierDBO::insertAllocationScraperJob();
        LilHotelierDBO::runProcessor();
    }
    
    /**
     * Returns the filename for the stylesheet to use during transform.
     */
    function getXslFilename() {
        return HBO_PLUGIN_DIR. '/include/lh_guest_comments_report.xsl';
    }

}

?>