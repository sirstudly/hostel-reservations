<?php

/**
 * Display controller for blacklist page.
 */
class Blacklist extends XslTransform {

    var $blacklist;  // array() of blacklist entry
    var $editingId; // set if we're currently editing an entry
    var $addAliasForId; // PK of blacklist entry if we're adding a new alias

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
     * Make the intention of adding an alias. Display a blank line under the blacklist entry.
     * @param $id int PK of blacklist entry
     * @return void
     */
    function addAlias($id) {
        $this->addAliasForId = $id;
    }

    /**
     * Save/updates a new or existing blacklist entry.
     * @param $firstname
     * @param $lastname
     * @param $email
     * @param $id int PK of blacklist entry (optional)
     *
     * @return void
     * @throws DatabaseException
     * @throws ValidationException
     */
    function saveBlacklistEntry( $id, $firstname, $lastname, $email, $notes ) {

       if( empty( $firstname )) {
           throw new ValidationException( "First name cannot be blank" );
       }
       if( empty( $lastname )) {
           throw new ValidationException( "Last name cannot be blank" );
       }
       if( FALSE === empty( $email ) && FALSE === strpos($email, "@") ) {
           throw new ValidationException( "This doesn\'t look like a valid email address" );
       }

       LilHotelierDBO::getInstance()->saveBlacklistEntry($id, $firstname, $lastname, $email, $notes);
    }

    /**
     * Saves a new blacklist alias.
     * @param $id int PK of blacklist entry
     * @param $firstname
     * @param $lastname
     * @param $email
     *
     * @return void
     * @throws DatabaseException
     * @throws ValidationException
     */
    function saveBlacklistAlias( $id, $firstname, $lastname, $email ) {

        if( empty( $id )) {
            throw new ValidationException( "ID cannot be blank" );
        }
        if( empty( $firstname )) {
            throw new ValidationException( "First name cannot be blank" );
        }
        if( empty( $lastname )) {
            throw new ValidationException( "Last name cannot be blank" );
        }
        if( FALSE === empty( $email ) && FALSE === strpos($email, "@") ) {
            throw new ValidationException( "This doesn\'t look like a valid email address" );
        }

        LilHotelierDBO::getInstance()->saveBlacklistAlias($id, $firstname, $lastname, $email);
    }

    /**
     * Deletes an existing blacklist alias.
     * @param $alias_id int PK of blacklist alias
     *
     * @return void
     * @throws DatabaseException
     * @throws ValidationException
     */
    function deleteBlacklistAlias( $alias_id ) {

        if( empty( $alias_id )) {
            throw new ValidationException( "Alias ID cannot be blank" );
        }

        LilHotelierDBO::getInstance()->deleteBlacklistAlias($alias_id);
    }

    /**
     * Adds this object to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this object will be added
     */
    function addSelfToDocument($domtree, $parentElement) {

        $parentElement->appendChild($domtree->createElement('property_manager', get_option('hbo_property_manager')));
        if ($this->editingId || $this->addAliasForId) {
            $parentElement->appendChild($domtree->createElement('reload_table_only', 'true'));
        }
        $blacklistRoot = $parentElement->appendChild($domtree->createElement('blacklist'));
        if ( $this->blacklist ) {
            foreach ($this->blacklist as $entry) {
                $entryElem = $blacklistRoot->appendChild($domtree->createElement("entry"));
                $entryElem->appendChild($domtree->createElement("blacklist_id", $entry->blacklist_id));
                if (isset($entry->alias_id)) {
                    $entryElem->appendChild( $domtree->createElement( "alias_id", $entry->alias_id ));
                }
                $entryElem->appendChild($domtree->createElement("first_name", htmlspecialchars($entry->first_name)));
                $entryElem->appendChild($domtree->createElement("last_name", htmlspecialchars($entry->last_name)));
                $entryElem->appendChild($domtree->createElement("email", htmlspecialchars($entry->email)));
                if (isset($entry->notes)) {
                    $entryElem->appendChild( $domtree->createElement( "notes", htmlspecialchars( $entry->notes ) ) );
                    $entryElem->appendChild( $domtree->createElement( "notes_readonly", nl2br( stripslashes( htmlspecialchars( $entry->notes ) ) ) ) );
                }
                if (FALSE === isset($entry->alias_id) && $entry->blacklist_id == $this->editingId) {
                    $entryElem->appendChild($domtree->createElement("editing", "true"));
                }
                if (FALSE === isset($entry->alias_id) && $entry->blacklist_id == $this->addAliasForId) {
                    $entryElem->appendChild($domtree->createElement("add_alias", "true"));
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
