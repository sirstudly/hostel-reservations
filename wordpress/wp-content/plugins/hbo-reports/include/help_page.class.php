<?php

/**
 * Controller class for generating pre-defined help pages.
 */
class HelpPage extends XslTransform {

    var $name;  // name of page view we are currently generating

    /** 
     * Default constructor.
     * $name : name of view (page) to be generated
     *         one of: help, help_reports, help_admin
     */
    function HelpPage($name = 'help') {
        $this->name = $name;
    }

    /**
     * Fetches this page in the following format:
     * <view>
     *     <name>add-edit-booking</name>
     *     <homeurl>http://yourdomain.com/subdomain</homeurl>
     * </view>
     */
    function toXml() {
        $domtree = new DOMDocument('1.0', 'UTF-8');
        $xmlRoot = $domtree->appendChild($domtree->createElement('view'));
        $xmlRoot->appendChild($domtree->createElement('name', $this->name));
        $xmlRoot->appendChild($domtree->createElement('homeurl', home_url()));
        return $domtree->saveXML();
    }

    /**
     * Returns the filename for the stylesheet to use during transform.
     */
    function getXslFilename() {
        return HBO_PLUGIN_DIR. '/include/help_page.xsl';
    }
}

?>
