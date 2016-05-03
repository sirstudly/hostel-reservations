<?php

/**
 * Controller for viewing/adding room assignments for a single cleaner.
 */
class CleanerBedAssignmentsTable extends XslTransform {

    function CleanerBedAssignmentsTable() {
        // nothing to do
    }

    /**
     * Initialises all cleaner bed assignments from the DB.
     */
    function loadAssignments() {
        $this->cleanersTable = LilHotelierDBO::getCleanerBedAssignments();
    }

    /**
     * Adds a cleaner to the roster.
     */
    function addCleaner( $firstName, $lastName ) {

        if( empty( $firstName ) || empty( $lastName ) ) {
            throw new ValidationException( "First/Last name cannot be blank" );
        }
        LilHotelierDBO::addCleaner( $firstName, $lastName );
        self::loadAssignments(); // reload
    }

    /**
     * Fetches a cleaner by id.
     * cleanerId : unique id of cleaner
     * Returns cleaner
     * Throws ValidationException if cleaner by $cleanerId not found
     */
    function getCleaner( $cleanerId ) {
        foreach( $this->cleanersTable as $cleaner ) {
            if( $cleaner->id == $cleanerId ) {
                return $cleaner;
            }
        }
        throw new ValidationException( 'Unable to find cleaner ' . $cleanerId );
    }

    /**
     * Assigns a bed to the given cleaner for the given dates.
     * cleanerId : unique id of cleaner to update
     * roomId : unique id of room to assign to
     * checkinDate : datetime of checkin
     * checkoutDate : datetime of checkout
     */
    function addCleanerBedAssignment( $cleanerId, $roomId, $checkinDate, $checkoutDate ) {
        try {
            LilHotelierDBO::addCleanerBedAssignment( $cleanerId, $roomId, $checkinDate, $checkoutDate );
            self::loadAssignments(); // reload
        }
        catch( ValidationException $ex ) {
            $cleaner = self::getCleaner( $cleanerId );
            $cleaner->addErrorMessage( 'add_assignment', $ex->getMessage() );
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
            <cleaners>
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
                <cleaner>
                  ...
                </cleaner>
                ...
            <cleaners>
        </view>
     */
    function toXml() {
        // create a dom document with encoding utf8
        $domtree = new DOMDocument('1.0', 'UTF-8');
    
        // create the root element of the xml tree
        $xmlRoot = $domtree->createElement('view');
        $xmlRoot = $domtree->appendChild($xmlRoot);

        // create the root element for the different beds available on the xml tree
        $roomsRoot = $domtree->createElement('rooms');
        $roomsRoot = $xmlRoot->appendChild($roomsRoot);

        foreach( LilHotelierDBO::getAllAssignableCleanerBeds() as $bedAssign ) {
            $bedAssign->addSelfToDocument($domtree, $roomsRoot);
        }

        // create the cleaners root element of the xml tree
        $cleanerRoot = $domtree->createElement('cleaners');
        $cleanerRoot = $xmlRoot->appendChild($cleanerRoot);

        foreach( $this->cleanersTable as $cleaner ) {
            $cleaner->addSelfToDocument($domtree, $cleanerRoot);
        }

error_log($domtree->saveXML());
        return $domtree->saveXML();
    }
    
    /**
     * Returns the filename for the stylesheet to use during transform.
     */
    function getXslFilename() {
        return WPDEV_BK_PLUGIN_DIR. '/include/lh_cleaner_bed_assign_table.xsl';
    }
}

?>