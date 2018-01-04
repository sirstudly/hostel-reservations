<?php

/**
 * Display controller for reports page.
 */
class LHGuestCommentsReport extends LHGuestCommentsReportData {

    /**
     * Default constructor.
     */
    function LHGuestCommentsReport() {
        parent::LHGuestCommentsReportData();
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
        return WPDEV_BK_PLUGIN_DIR. '/include/lh_guest_comments_report.xsl';
    }

}

?>