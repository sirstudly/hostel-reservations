<?php

/**
 * A single bed assignment.
 */
class LHBedAssignment {
    var $id;     // unique id
    var $room;
    var $bedName;

    /**
     * Default constructor.
     * $id : room id
     * $room : room (number)
     * $bedName : name of bed
     */
    function LHBedAssignment($id, $room, $bedName) {
        $this->id = $id;
        $this->room = $room;
        $this->bedName = $bedName;
    }

    /**
     * Adds this allocation row to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this row will be added
     */
    function addSelfToDocument($domtree, $parentElement) {

        $xmlRoot = $domtree->createElement('room');
        $xmlRoot = $parentElement->appendChild($xmlRoot);
    
        $xmlRoot->appendChild($domtree->createElement('id', $this->id));
        $xmlRoot->appendChild($domtree->createElement('number', $this->room));
        $xmlRoot->appendChild($domtree->createElement('bed', htmlspecialchars($this->bedName)));
    }
    
    /** 
      Generates the following xml:
        <room>
            <id>2145</id>
            <number>13</number>
            <bed>Pinkie</bed>
        </room>
     */
    function toXml() {
        /* create a dom document with encoding utf8 */
        $domtree = new DOMDocument('1.0', 'UTF-8');
        $this->addSelfToDocument($domtree, $domtree);
        return $domtree->saveXML();
    }
    
}

?>