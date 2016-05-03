<?php

/**
 * Encapsulates a cleaner and their past/future room assignments.
 */
class LHCleaner extends XslTransform {
    var $id;     // unique id
    var $firstName;
    var $lastName;
    var $active;  // boolean
    var $bedAssignments = array();  // array of LHCleanerBedAssignment ordered by start date

    // dynamic variables
    var $errorMessages = array(); // html element id -> error message (if we've just attempted an operation)
    var $editingRoomId; // the currently selected (bed) after a POST operation
    var $editingCheckinDate; // the current value in the checkin date field after a POST operation
    var $editingCheckoutDate; // the current value in the checkout date field after a POST operation

    /**
     * Default constructor.
     * $firstName : cleaner first name
     * $lastName : cleaner last name
     * $active : TRUE if cleaner is currently active, FALSE otherwise
     */
    function LHCleaner($id, $firstName, $lastName, $active) {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->active = $active;
    }

    /**
     * Add bed assignment for this cleaner.
     * $roomId : unique id of room to assign to
     * $checkinDate : datetime of checkin
     * $checkoutDate : datetime of checkout
     */
    function addBedAssignment($roomId, $checkinDate, $checkoutDate) {
        LilHotelierDBO::addCleanerBedAssignment( $this->id, $roomId, $checkinDate, $checkoutDate );
        self::loadBedAssignments(); // reload
    }

    /**
     * Loads the bed assignments for this cleaner from the database.
     */
    function loadBedAssignments() {
        $this->bedAssignments = array(); // clear previous data
        $resultset = LilHotelierDBO::getBedAssignmentsForCleaner( $this->id );
        foreach( $resultset as $record ) {
            $this->bedAssignments[] = new LHCleanerBedAssignment(
                $record->room_id, 
                $record->room, 
                $record->bed_name, 
                DateTime::createFromFormat('!Y-m-d', $record->start_date), 
                DateTime::createFromFormat('!Y-m-d', $record->end_date));
        }
        $this->editingRoomId = null;
        $this->editingCheckinDate = null;
        $this->editingCheckoutDate = null;
    }

    /**
     * Adds the error message to display for a particular (UI) component.
     */
    function addErrorMessage( $elementId, $message ) {
        $this->errorMessages[$elementId] = $message;
    }

    /**
     * Returns false iff no errors are defined.
     */
    function hasErrors() {
        return false === empty( $this->errorMessages );
    }

    /**
     * What it says on the tin.
     */
    function clearErrors() {
        $this->errorMessages = array();
    }

    /**
     * Sets the bed that was selected on the last POST.
     */
    function setEditingRoomId( $roomId ) {
        $this->editingRoomId = $roomId;
    }

    /**
     * Sets the checkin date that was entered in the last POST.
     */
    function setEditingCheckinDate( $checkinDate ) {
        $this->editingCheckinDate = $checkinDate;
    }

    /**
     * Sets the checkout date that was entered in the last POST.
     */
    function setEditingCheckoutDate( $checkoutDate ) {
        $this->editingCheckoutDate = $checkoutDate;
    }

    /**
     * Adds this allocation row to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this row will be added
     */
    function addSelfToDocument($domtree, $parentElement) {

        $xmlRoot = $domtree->createElement('cleaner');
        $xmlRoot = $parentElement->appendChild($xmlRoot);
    
        $xmlRoot->appendChild($domtree->createElement('id', $this->id));
        $xmlRoot->appendChild($domtree->createElement('firstname', $this->firstName));
        $xmlRoot->appendChild($domtree->createElement('lastname', $this->lastName));

        // include any errors from the last POST
        if( false === empty( $this->errorMessages )) {
            $errorsRoot = $xmlRoot->appendChild($domtree->createElement('errors'));
            foreach( $this->errorMessages as $elementId => $message ) {
                $errorRoot = $errorsRoot->appendChild($domtree->createElement('error'));
                $errorRoot->appendChild($domtree->createElement('element_id', $elementId));
                $errorRoot->appendChild($domtree->createElement('message', $message));
            }
        }

        if( false === empty( $this->editingRoomId )) {
            $xmlRoot->appendChild($domtree->createElement('editing_room_id', $this->editingRoomId));
        }

        if( false === empty( $this->editingCheckinDate )) {
            $xmlRoot->appendChild($domtree->createElement('editing_checkin_date', $this->editingCheckinDate));
        }

        if( false === empty( $this->editingCheckoutDate )) {
            $xmlRoot->appendChild($domtree->createElement('editing_checkout_date', $this->editingCheckoutDate));
        }

        foreach( $this->bedAssignments as $bed ) {
            $bed->addSelfToDocument($domtree, $xmlRoot);
        }
    }
    
    /** 
      Generates the following xml:
      <view>
        <rooms>
            <room>
                <id>2145</id>
                <number>13</number>
                <bed>Pinkie</bed>
            </room>
            ...
        </rooms>
        <cleaner>
            <id>25</id>
            <firstname>Megan</firstname>
            <lastname>Fox</lastname>
            <assignedbed>
                <from>22.05.2015</from>
                <to>25.05.2015</to>
                <room>
                    <id>2145</id>
                    <number>13</number>
                    <bed>Pinkie</bed>
                </room>
            </assignedbed>
            <assignedbed>
                <from>25.05.2015</from>
                <to>29.05.2015</to>
                <room>
                    <id>2146</id>
                    <number>13</number>
                    <bed>Pokey</bed>
                </room>
            </assignedbed>
            <availablecredits>2</availablecredits>
            <allocateduntil>26.05.2015</allocateduntil>
        </cleaner>
      </view>
     */
    function toXml() {
        /* create a dom document with encoding utf8 */
        $domtree = new DOMDocument('1.0', 'UTF-8');

        // create the root element
        $viewRoot = $domtree->createElement('view');
        $viewRoot = $domtree->appendChild($viewRoot);

        // create the root element for the different beds available on the xml tree
        $roomsRoot = $domtree->createElement('rooms');
        $roomsRoot = $viewRoot->appendChild($roomsRoot);

        foreach( LilHotelierDBO::getAllAssignableCleanerBeds() as $bedAssign ) {
            $bedAssign->addSelfToDocument($domtree, $roomsRoot);
        }

        $this->addSelfToDocument($domtree, $viewRoot);
        return $domtree->saveXML();
    }
    
    /**
     * Returns the filename for the stylesheet to use during transform.
     */
    function getXslFilename() {
        return WPDEV_BK_PLUGIN_DIR. '/include/lh_cleaner.xsl';
    }
}

?>