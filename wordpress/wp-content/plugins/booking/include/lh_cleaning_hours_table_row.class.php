<?php

/**
 * Renders a row in the table of cleaning assignments.
 */
class LHCleaningHoursTableRow extends XslTransform {

    var $id;
    var $taskId;
    var $taskName;
    var $taskDescription;
    var $hours; // (int)
    var $allocateFrom; // DateTime to start allocating from
    var $cleanerId; // (int)
    var $cleanerName;

    /**
     * Default constructor.
     * $taskId : id of task (default null)
     * $taskName : name of task (default null) 
     * $taskDescription : description of task (default null)
     * $hours : (int) number of hours for this task (default null)ould be shown in the daily tasks page
     * $allocateFrom : (DateTime; default null) the date when we want to start allocating hours from
     * $cleanerId : (int; default null) the id of the cleaner assigned this task
     * $cleanerName : display name of cleaner (default null)
     */
    function LHCleaningHoursTableRow( $taskId = null, $taskName = null, $taskDescription = null, 
            $hours = null, $allocateFrom = null, $cleanerId = null, $cleanerName = null ) {
        $this->taskId = $taskId;
        $this->taskName = $taskName;
        $this->taskDescription = $taskDescription;
        $this->hours = $hours;
        $this->allocateFrom = $allocateFrom;
        $this->cleanerId = $cleanerId;
        $this->cleanerName = $cleanerName;
    }

    /**
     * Loads data from db.
     * $id : id of cleaner task to load
     */
    function loadFromDB( $id ) {
/*
        $row = LilHotelierDBO::getCleanerTask( $id );
        $this->id = $id;
        $this->name = $row->name;
        $this->description = $row->description;
        $this->defaultHours = $row->default_hours;
        $this->active = $row->active_yn == 'Y';
        $this->showInDailyTasks = $row->show_in_daily_tasks_yn == 'Y';
        $this->sortOrder = $row->sort_order;
        $this->frequency = $row->frequency;
*/
    }

    /**
     * Updates an existing task.
     * $name : name of task
     * $description : description of task
     * $defaultHours : (int) default number of hours for this task
     * $active : (boolean) true if active, false if not
     * $showInDailyTasks : (boolean; default false) true if task should be shown in the daily tasks page
     * $sortOrder : (int; default null) the order this task appears on the daily tasks page
     * $frequency : (int; default null) the number of times this task appears on the daily tasks page
     *
    function updateTask( $name, $description, $defaultHours, $active, 
            $showInDailyTasks = false, $sortOrder = null, $frequency = null ) {

        if( false === isset( $this->id )) {
            throw new ValidationException( "ID not set" );
        }
        LilHotelierDBO::updateCleanerTask( $this->id, $name, $description, $defaultHours, $active, $showInDailyTasks, $sortOrder );
        self::loadFromDB( $this->id );
    }
    */

    /**
     * Adds this object to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this object will be added
     */
    function addSelfToDocument($domtree, $parentElement) {

        $taskRoot = $domtree->createElement('task');
        $taskRoot = $parentElement->appendChild($taskRoot);

        $taskRoot->appendChild( $domtree->createElement('task_id', $this->taskId ) );
        $taskRoot->appendChild( $domtree->createElement('task_name', $this->taskName ) );
        $taskRoot->appendChild( $domtree->createElement('task_description', $this->taskDescription ) );
        $taskRoot->appendChild( $domtree->createElement('hours', $this->hours ) );
        $taskRoot->appendChild( $domtree->createElement('allocate_from', $this->allocateFrom ) );
        $taskRoot->appendChild( $domtree->createElement('cleaner_id', $this->cleanerId ) );
        $taskRoot->appendChild( $domtree->createElement('cleaner_name', $this->cleaner_name ) );
    }

    /** 
      Generates the following xml:
        <task>
            <task_id>5</task_id>
            <task_name>5am Lounges</task_name>
            <task_description>Clean lounges, bathrooms, etc. from 5am - 7am.</task_description>
            <hours>2</hours>
            <allocate_from>16-03-24</allocate_from>
            <cleaner_id>2</cleaner_id>
            <cleaner_name>Joe Bloggs</cleaner_name>
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
        return WPDEV_BK_PLUGIN_DIR. '/include/lh_cleaning_hours_table_row.xsl';
    }
}

?>