<?php

/**
 * Controller for displaying the bedcounts report as a CSV.
 */
class BedCountsCSV extends AbstractBedCounts {

    /**
     * Default constructor.
     * $selectionDate : date to display bedcounts for (DateTime) (defaults to now)
     */
    function __construct($selectionDate = null) {
        parent::__construct($selectionDate == null ? new DateTime() : $selectionDate);
    }
    
    /**
     * Returns the filename for the stylesheet to use during transform.
     */
    function getXslFilename() {
        return HBO_PLUGIN_DIR. '/include/lh_bedcounts_csv.xsl';
    }

    /**
     * Overrides parent method to include configuration flag.
     */
    function addSelfToDocument($domtree, $parentElement) {
        if( strpos(get_option('hbo_lilho_username'), 'highstreet') === 0 ) {
            $parentElement->appendChild($domtree->createElement('write_zeroes', 'true'));
        }
        parent::addSelfToDocument($domtree, $parentElement);
    }

    /**
     * Converts and returns the generated bedcounts as a CSV.
     * Returns: CSV content
     */
    function toCSV() {
        return $this->toHtml();
    }

}

?>