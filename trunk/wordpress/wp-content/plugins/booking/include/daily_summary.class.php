<?php

/**
 * Display controller for daily summary page.
 */
class DailySummary extends DailySummaryData {

    /**
     * Default constructor.
     * $selectionDate : date to display summary for (DateTime)
     */
    function DailySummary($selectionDate) {
        parent::DailySummaryData($selectionDate);
    }
    
    /**
     * Returns the filename for the stylesheet to use during transform.
     */
    function getXslFilename() {
        return WPDEV_BK_PLUGIN_DIR. '/include/daily_summary.xsl';
    }

}

?>