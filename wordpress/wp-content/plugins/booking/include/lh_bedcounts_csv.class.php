<?php

/**
 * Controller for displaying the bedcounts report as a CSV.
 */
class BedCountsCSV extends AbstractBedCounts {

    /**
     * Default constructor.
     * $selectionDate : date to display bedcounts for (DateTime) (defaults to now)
     */
    function BedCountsCSV($selectionDate = null) {
        parent::AbstractBedCounts($selectionDate == null ? new DateTime() : $selectionDate);
    }
    
    /**
     * Returns the filename for the stylesheet to use during transform.
     */
    function getXslFilename() {
        return WPDEV_BK_PLUGIN_DIR. '/include/lh_bedcounts_csv.xsl';
    }

    /**
     * Overrides parent method to include configuration flag.
     */
    function addSelfToDocument($domtree, $parentElement) {
        if( get_option('hbo_lilho_username') == 'highstreet') {
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