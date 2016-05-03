<?php

/**
 * Renders the table of cleaning tasks.
 */
class LHCleanerTasksTable extends XslTransform {

    var $tasks = array(); // array of LHCleanerTasksTableRow (indexed by id)

    /**
     * Default constructor.
     */
    function LHCleanerTasksTable() {
        // nothing to do
    }

    /**
     * Loads all tasks from the database.
     */
    function doView() {
        $tasks = LilHotelierDBO::getCleanerTasks();
        foreach( $tasks as $task ) {
            $this->tasks[$task->id] = new LHCleanerTasksTableRow(
                $task->id,
                $task->name,
                $task->description,
                $task->default_hours,
                $task->active_yn == 'Y'
            );
        }
    }

    /**
     * Adds a new task. Returns the id of the task that was created.
     * $name : name of task
     * $description : description of task
     * $defaultHours : (int) default number of hours for this tak
     * $active : (boolean) true if active, false if not
     */
    function addTask( $name, $description, $defaultHours, $active ) {
        $taskId = LilHotelierDBO::addCleanerTask( $name, $description, $defaultHours, $active );
        $this->tasks[$taskId] = new LHCleanerTasksTableRow( $taskId, $name, $description, $defaultHours, $active );
        return $taskId;
    }

    /**
     * Edits an existing task.
     * $id : id of task to edit
     * $name : name of task
     * $description : description of task
     * $defaultHours : (int) default number of hours for this tak
     * $active : (boolean) true if active, false if not
     */
    function editTask( $id, $name, $description, $defaultHours, $active ) {
        $this->tasks[$id]->editTask( $name, $description, $defaultHours, $active );
    }

    /**
     * Adds this object to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this object will be added
     */
    function addSelfToDocument($domtree, $parentElement) {

        // create the root element of the xml tree
        $xmlRoot = $domtree->createElement('tasks');
        $xmlRoot = $parentElement->appendChild($xmlRoot);

        // element for each task
        foreach( $this->tasks as $task ) {
            $task->addSelfToDocument( $domtree, $xmlRoot );
       }

    }

    /** 
      Generates the following xml:
        <tasks>
            <task>
                <id>5</id>
                <name>5am Lounges</name>
                <description>Clean lounges, bathrooms, etc. from 5am - 7am.</description>
                <default_hours>2</default_hours>
                <active>true</active>
            </task>
            <task>
                ...
            </task>
            ...
        </tasks>
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
        return WPDEV_BK_PLUGIN_DIR. '/include/lh_cleaner_tasks_table.xsl';
    }
}

?>