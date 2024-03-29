<?php

/**
 * Display controller for bedcounts report.
 */
class BedCountsNew extends AbstractBedCountsNew {

    /**
     * Default constructor.
     * $selectionDate : date to display bedcounts for (DateTime) (defaults to now)
     */
    function __construct($selectionDate = null) {
        parent::__construct($selectionDate == null ? new DateTime() : $selectionDate);
    }
    
    /** 
     * Submits a new bedsheets job to run.
     */
    function submitRefreshJob() {
        LilHotelierDBO::insertJobOfType( self::JOB_TYPE,
            array( "selected_date" => $this->selectionDate->format('Y-m-d H:i:s') ) );
        LilHotelierDBO::runProcessor();
    }

    /**
     * Returns the filename for the stylesheet to use during transform.
     */
    function getXslFilename() {
        return HBO_PLUGIN_DIR. '/include/lh_bedcounts_new.xsl';
    }

}

?>