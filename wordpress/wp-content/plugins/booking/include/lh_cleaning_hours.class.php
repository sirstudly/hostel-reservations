<?php

/**
 * Admin page for assigning allocations for cleaning tasks.
 */
class LHCleaningHours extends XslTransform {

    var $table; // LHCleaningHoursTable

    /**
     * Default constructor.
     */
    function LHCleaningHours() {
        // nothing to do
    }

    /**
     * Loads the cleaning hours table.
     */
    function doView() {
        $this->table = new LHCleaningHoursTable();
        $this->table->doView();
    }

    /**
     * Returns the backing LHCleaningHoursTable.
     */
    function getCleaningHoursTable() {
        return $this->table;
    }

    /** 
      Generates the following xml:
        <view>
            ...
        </view>
     */
    function toXml() {
        /* create a dom document with encoding utf8 */
        $domtree = new DOMDocument('1.0', 'UTF-8');
        $xmlRoot = $domtree->appendChild($domtree->createElement('view'));
        $this->table->addSelfToDocument( $domtree, $xmlRoot );
        return $domtree->saveXML();
    }
    
    /**
     * Returns the filename for the stylesheet to use during transform.
     */
    function getXslFilename() {
        return WPDEV_BK_PLUGIN_DIR. '/include/lh_cleaning_hours.xsl';
    }
}

?>