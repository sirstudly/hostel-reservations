<?php

/**
 * Encapsulates a cleaner and their past/future room assignments.
 */
class LHCleanerBedAssignment {
    var $roomId;
    var $room;
    var $bedName;
    var $startDate;
    var $endDate;

    /**
     * Default constructor.
     * $roomId : unique id of room assigned
     * $room : room number
     * $bedName : name of bed
     * $startDate : start date of room assignment
     * $endDate : end date of room assignment (checkout date)
     */
    function LHCleanerBedAssignment($roomId, $room, $bedName, $startDate, $endDate) {
        $this->roomId = $roomId;
        $this->room = $room;
        $this->bedName = $bedName;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * Adds this allocation row to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this row will be added
     */
    function addSelfToDocument($domtree, $parentElement) {

        $xmlRoot = $domtree->createElement('assignedbed');
        $xmlRoot = $parentElement->appendChild($xmlRoot);
    
        $xmlRoot->appendChild($domtree->createElement('from', $this->startDate->format('Y-m-d')));
        $xmlRoot->appendChild($domtree->createElement('to', $this->endDate->format('Y-m-d')));

        $roomRoot = $domtree->createElement('room');
        $roomRoot = $xmlRoot->appendChild($roomRoot);
        $roomRoot->appendChild($domtree->createElement('id', $this->roomId));
        $roomRoot->appendChild($domtree->createElement('number', $this->room));
        $roomRoot->appendChild($domtree->createElement('bed', htmlspecialchars($this->bedName)));
    }
    
    /** 
      Generates the following xml:
        <assignedbed>
            <from>22.05.2015</from>
            <to>25.05.2015</to>
            <room>
                <id>2145</id>
                <number>13</number>
                <bed>Pinkie</bed>
            </room>
        </assignedbed>
     */
    function toXml() {
        /* create a dom document with encoding utf8 */
        $domtree = new DOMDocument('1.0', 'UTF-8');
        $this->addSelfToDocument($domtree, $domtree);
        return $domtree->saveXML();
    }
    
}

?>