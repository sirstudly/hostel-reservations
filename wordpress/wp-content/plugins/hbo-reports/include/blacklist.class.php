<?php

/**
 * Display controller for blacklist page.
 */
class Blacklist extends XslTransform {

    var $blacklist;  // array() of blacklist entry
    var $editingId; // set if we're currently editing an entry

    /**
     * Default constructor.
     */
    function __construct() {
        
    }

    /**
     * Updates the view using the current selection date.
     */
    function doView() {
        $this->blacklist = array();
        $this->blacklist = LilHotelierDBO::getInstance()->getBlacklist();
   }

    /**
     * Marks the given ID as currently editing.
     * @param $id blacklist entry id
     * @return void
     */
    function editBlacklistEntry($id) {
        $this->editingId = $id;
    }

    /**
     * Updates details for little hotelier.
     * @throws DatabaseException
     * @throws ValidationException
     */
   function saveBlacklistEntry( $firstname, $lastname, $email, $id = 0 ) {

       if( empty( $firstname )) {
           throw new ValidationException( "First name cannot be blank" );
       }
       if( empty( $lastname )) {
           throw new ValidationException( "Last name cannot be blank" );
       }
       if( FALSE === empty( $email ) && FALSE === strpos($email, "@") ) {
           throw new ValidationException( "This doesn\'t look like a valid email address" );
       }

       LilHotelierDBO::getInstance()->saveBlacklistEntry($id, $firstname, $lastname, $email);
       $this->editingId = null; // unset variable once we've successfully saved
   }

    /**
     * Adds this object to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this object will be added
     */
    function addSelfToDocument($domtree, $parentElement) {

        $parentElement->appendChild($domtree->createElement('property_manager', get_option('hbo_property_manager')));
        if ($this->editingId) {
            $parentElement->appendChild($domtree->createElement('editing_id', $this->editingId));
        }
        $blacklistRoot = $parentElement->appendChild($domtree->createElement('blacklist'));
        if ( $this->blacklist ) {
            foreach ($this->blacklist as $entry) {
                $entryElem = $blacklistRoot->appendChild( $domtree->createElement("entry") );
                $entryElem->appendChild($domtree->createElement("id", $entry->id));
                $entryElem->appendChild($domtree->createElement("first_name", htmlspecialchars($entry->first_name)));
                $entryElem->appendChild($domtree->createElement("last_name", htmlspecialchars($entry->last_name)));
                $entryElem->appendChild($domtree->createElement("email", htmlspecialchars($entry->email)));
                if ($entry->id == $this->editingId) {
                    $entryElem->appendChild($domtree->createElement("editing", "true"));
                }
            }
        }
    }
    
    /** 
      Generates the following xml:
        <view>
            <settings>
                <lilhotelier.url.login>https://app.littlehotelier.com/login</lilhotelier.url.login>
                ...
            </settings>
        </view>
     */
    function toXml() {
        // create a dom document with encoding utf8
        $domtree = new DOMDocument('1.0', 'UTF-8');
        $xmlRoot = $domtree->appendChild($domtree->createElement('view'));
        $this->addSelfToDocument($domtree, $xmlRoot);
        $xml = $domtree->saveXML();
error_log($xml);
        return $xml;
    }
    
    /**
     * Returns the filename for the stylesheet to use during transform.
     */
    function getXslFilename() {
        return HBO_PLUGIN_DIR. '/include/blacklist.xsl';
    }

}
