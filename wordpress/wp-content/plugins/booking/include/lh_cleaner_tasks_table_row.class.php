<?php

/**
 * Renders a row in the table of cleaning tasks.
 */
class LHCleanerTasksTableRow extends XslTransform {

    var $id;
    var $name;
    var $description;
    var $defaultHours; // (int)
    var $active; // (boolean)

    /**
     * Default constructor.
     * $id : id of task (default null)
     * $name : name of task (default null) 
     * $description : description of task (default null)
     * $defaultHours : (int) default number of hours for this task (default null)
     * $active : (boolean) true if active, false if not (default null)
     */
    function LHCleanerTasksTableRow( $id = null, $name = null, $description = null, $defaultHours = null, $active = null ) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->defaultHours = $default_hours;
        $this->active = $active;
    }

    /**
     * Loads data from db.
     * $id : id of cleaner task to load
     */
    function loadFromDB( $id ) {
        $row = LilHotelierDBO::getCleanerTask( $id );
        $this->id = $id;
        $this->name = $row->name;
        $this->description = $row->description;
        $this->defaultHours = $row->default_hours;
        $this->active = $row->active_yn == 'Y';
    }

    /**
     * Edits an existing task.
     * $id : id of task to edit
     * $name : name of task
     * $description : description of task
     * $defaultHours : (int) default number of hours for this task
     * $active : (boolean) true if active, false if not
     */
    function editTask( $name, $description, $defaultHours, $active ) {
        if( false === isset( $this->id )) {
            throw new ValidationException( "ID not set" );
        }
        LilHotelierDBO::editCleanerTask( $this->id, $name, $description, $defaultHours, $active );
        $this->name = $name;
        $this->description = $description;
        $this->defaultHours = $defaultHours;
        $this->active = $active;
    }

    /**
     * Adds this object to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this object will be added
     */
    function addSelfToDocument($domtree, $parentElement) {

        $taskRoot = $domtree->createElement('task');
        $taskRoot = $parentElement->appendChild($taskRoot);

        $taskRoot->appendChild( $domtree->createElement('id', $this->id ) );
        $taskRoot->appendChild( $domtree->createElement('name', $this->name ) );
        $taskRoot->appendChild( $domtree->createElement('description', $this->description ) );
        $taskRoot->appendChild( $domtree->createElement('default_hours', $this->defaultHours ) );
        $taskRoot->appendChild( $domtree->createElement('active', $this->active ) );
    }

    /** 
      Generates the following xml:
        <task>
            <id>5</id>
            <name>5am Lounges</name>
            <description>Clean lounges, bathrooms, etc. from 5am - 7am.</description>
            <default_hours>2</default_hours>
            <active>true</active>
        </task>
     */
    function toXml() {
        /* create a dom document with encoding utf8 */
        $domtree = new DOMDocument('1.0', 'UTF-8');
        $this->addSelfToDocument($domtree, $domtree);
        return $domtree->saveXML();
    }
    
    /**
     * Returns the filename for the stylesheet to use during transform.
     */
    function getXslFilename() {
        return WPDEV_BK_PLUGIN_DIR. '/include/lh_cleaner_tasks_table_row.xsl';
    }
}

?>