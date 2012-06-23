<?php

/**
 * Booking Resources Management controller.
 */
class Resources {

    // the current resource id that is being edited
    var $editResourceId = '';

    function Resources() {
    }

    /**
     * Fetches all resources in the following format:
     * <resources>
     *     <editResource>14</editResource>
     *     <resource>
     *         <id>1</id>
     *         <name>8-Bed Dorm</name>
     *         <capacity></capacity>
     *         <path>/1</path>
     *         <level>1</level>
     *         <numberChildren>2</numberChildren>
     *         <type>room</type>
     *     </resource>
     *     <resource>
     *         <id>2</id>
     *         <name>Bed A</name>
     *         <capacity>1</capacity>
     *         <path>/1/2</path>
     *         <level>2</level>
     *         <numberChildren>0</numberChildren>
     *         <type>bed</type>
     *     </resource>
     *     <resource>
     *         <id>3</id>
     *         <name>Bed B</name>
     *         <capacity>1</capacity>
     *         <path>/1/3</path>
     *         <level>2</level>
     *         <numberChildren>0</numberChildren>
     *         <type>bed</type>
     *     </resource>
     *     ...
     * </resources>
     */
    function toXml() {
        $domtree = new DOMDocument('1.0', 'UTF-8');
        
        // create the root element for this allocation row
        $xmlRoot = $domtree->createElement('resources');
        $xmlRoot = $domtree->appendChild($xmlRoot);
    
        // if we are editing, then we create a new element
        if( $this->editResourceId != '' ) {
            $xmlRoot->appendChild($domtree->createElement('editResource', $this->editResourceId));
        }
        
        foreach (ResourceDBO::getAllResources() as $res) {
            $resourceRow = $domtree->createElement('resource');
            $resourceRow->appendChild($domtree->createElement('id', $res->resource_id));
            $resourceRow->appendChild($domtree->createElement('name', $res->name));
            $resourceRow->appendChild($domtree->createElement('path', $res->path));
            $resourceRow->appendChild($domtree->createElement('level', $res->lvl));
            $resourceRow->appendChild($domtree->createElement('numberChildren', $res->number_children));
            $resourceRow->appendChild($domtree->createElement('type', $res->resource_type));
            $xmlRoot->appendChild($resourceRow);
        }
        return $domtree->saveXML();
    }

    function toHtml() {
        // create a DOM document and load the XSL stylesheet
        $xsl = new DomDocument;
        $xsl->load(WPDEV_BK_PLUGIN_DIR. '/include/resources.xsl');
        
        // import the XSL styelsheet into the XSLT process
        $xp = new XsltProcessor();
        $xp->importStylesheet($xsl);
        
        // create a DOM document and load the XML datat
        $xml_doc = new DomDocument;
        $xml_doc->loadXML(Resources::toXml());
        
        // transform the XML into HTML using the XSL file
        if ($html = $xp->transformToXML($xml_doc)) {
            return $html;
        } else {
            trigger_error('XSL transformation failed.', E_USER_ERROR);
        } // if 
        return 'XSL transformation failed.';
    }
}

?>