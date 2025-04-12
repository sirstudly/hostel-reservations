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
     * Updates the view.
     *
     * @param int|null $editingId (optional) PK of blacklist entry currently being edited
     */
    function doView( int $editingId = null ) {
        $this->blacklist = LilHotelierDBO::getInstance()->getBlacklist();
        $this->editingId = $editingId;
    }

    /**
     * Returns all the blacklisted guests.
     * @param WP_REST_Request $request The request object (can get parameters here but not currently used)
     * @return WP_REST_Response
     */
    function getBlacklist( $request ) {
        $data = LilHotelierDBO::getInstance()->getBlacklist();
        foreach ( $data as $entry ) {
            foreach ( $entry->aliases as $alias ) {
                unset ( $alias->blacklist_id );
            }
            foreach ( $entry->mugshots as $mugshot ) {
                unset( $mugshot->blacklist_id );
                $mugshot->url = plugins_url('hbo-reports/upload/' . rawurlencode($mugshot->filename));
            }
        }
        $response = new WP_REST_Response( array_values( $data ), 200 );
        $response->header( 'Content-type', 'application/json' );
        return $response;
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
     * Saves the image to the upload folder.
     *
     * @param $blacklist_id int PK of blacklist entry
     * @param $filename String name of file on remote filesystem
     * @param $tmp_filename String name of file on local filesystem
     *
     * @return void
     * @throws ValidationException on invalid file type
     * @throws DatabaseException
     */
    function uploadBlacklistImage( $blacklist_id, $filename, $tmp_filename ) {

        $savedFileName = "$blacklist_id $filename";
        $targetLocation = "upload/$savedFileName";
        $imageFileType = pathinfo($targetLocation,PATHINFO_EXTENSION);
        $imageFileType = strtolower($imageFileType);

        // Valid extensions
        $valid_extensions = array("jpg","jpeg","png");

        // Check file extension
        if ( in_array( strtolower( $imageFileType ), $valid_extensions ) ) {
            // Upload file
            if ( move_uploaded_file( $tmp_filename, $targetLocation ) ) {
                LilHotelierDBO::getInstance()->saveBlacklistImage( $blacklist_id, $savedFileName );
            }
            else {
                throw new ValidationException("Failed to save file to $targetLocation!");
            }
        }
        else {
            throw new ValidationException("Unsupported file type. Only JPG, JPEG, PNG images are supported.");
        }
    }

    /**
     * Adds this object to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this object will be added
     */
    function addSelfToDocument($domtree, $parentElement) {

        $parentElement->appendChild( $domtree->createElement( 'property_manager', get_option( 'hbo_property_manager' ) ) );
        $blacklistRoot = $parentElement->appendChild( $domtree->createElement( 'blacklist' ) );
        if ( $this->blacklist ) {
            foreach ( $this->blacklist as $entry ) {
                $entryElem = $blacklistRoot->appendChild( $domtree->createElement( "entry" ) );
                if ($this->editingId == $entry->blacklist_id) {
                    $entryElem->setAttribute('editing', 'true');
                }
                $entryElem->appendChild( $domtree->createElement( "blacklist_id", $entry->blacklist_id ) );
                $entryElem->appendChild( $domtree->createElement( "first_name", htmlspecialchars( $entry->first_name ) ) );
                $entryElem->appendChild( $domtree->createElement( "last_name", htmlspecialchars( $entry->last_name ) ) );
                $entryElem->appendChild( $domtree->createElement( "email", htmlspecialchars( $entry->email ) ) );
                if ( isset( $entry->notes ) ) {
                    $entryElem->appendChild( $domtree->createElement( "notes", htmlspecialchars( $entry->notes ) ) );
                    $entryElem->appendChild( $domtree->createElement( "notes_readonly", nl2br( stripslashes( htmlspecialchars( $entry->notes ) ) ) ) );
                }
                foreach ( $entry->aliases as $alias ) {
                    $aliasElem = $entryElem->appendChild( $domtree->createElement( "alias" ) );
                    $aliasElem->appendChild( $domtree->createElement( "alias_id", $alias->alias_id ) );
                    $aliasElem->appendChild( $domtree->createElement( "first_name", htmlspecialchars( $alias->first_name ) ) );
                    $aliasElem->appendChild( $domtree->createElement( "last_name", htmlspecialchars( $alias->last_name ) ) );
                    $aliasElem->appendChild( $domtree->createElement( "email", htmlspecialchars( $alias->email ) ) );
                }
                foreach ( $entry->mugshots as $mugshot ) {
                    $mugshotElem = $entryElem->appendChild( $domtree->createElement( "mugshot" ) );
                    $mugshotElem->appendChild( $domtree->createElement( "mugshot_id", $mugshot->mugshot_id ) );
                    $mugshotElem->appendChild( $domtree->createElement( "filename", $mugshot->filename ) );
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
        $xmlRoot->appendChild($domtree->createElement('pluginurl', HBO_PLUGIN_URL));
        $this->addSelfToDocument($domtree, $xmlRoot);
        $xml = $domtree->saveXML();
        return $xml;
    }
    
    /**
     * Returns the filename for the stylesheet to use during transform.
     */
    function getXslFilename() {
        return HBO_PLUGIN_DIR. '/include/blacklist.xsl';
    }

}
