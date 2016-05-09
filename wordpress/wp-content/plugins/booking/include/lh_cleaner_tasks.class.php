<?php

/**
 * Admin page for editing lists of cleaning tasks.
 */
class LHCleanerTasks extends XslTransform {

    var $table; // LHCleanerTasksTable

    /**
     * Default constructor.
     */
    function LHCleanerTasks() {
        // nothing to do
    }

    /**
     * Loads all tasks from the database.
     */
    function doView() {
        $this->table = new LHCleanerTasksTable();
        $this->table->doView();
    }

    /**
     * Returns the backing LHCleanerTasksTable.
     */
    function getTasksTable() {
        return $this->table;
    }

    /**
     * Adds a new task. Returns the id of the task that was created.
     * $name : name of task
     * $description : description of task
     * $defaultHours : (int) default number of hours for this tak
     * $active : (boolean) true if active, false if not
     */
    function addTask( $name, $description, $defaultHours, $active ) {
        return $this->table->addTask( $name, $description, $defaultHours, $active );
    }

    /**
     * Starts editing an existing task.
     * $taskId : id of task we are starting to edit
     */
    function editTask( $taskId ) {
        return $this->table->editTask( $taskId );
    }

    /**
     * Cancels editing of an existing task.
     */
    function cancelEditTask() {
        return $this->table->cancelEditTask();
    }

    /**
     * Updates an existing task.
     * $id : id of task to edit
     * $name : name of task
     * $description : description of task
     * $defaultHours : (int) default number of hours for this tak
     * $active : (boolean) true if active, false if not
     */
    function updateTask( $id, $name, $description, $defaultHours, $active ) {
        $this->table->updateTask( $id, $name, $description, $defaultHours, $active );
    }

    /** 
      Generates the following xml:
        <view>
            <tasks>
                <task>
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
        </view>
     */
    function toXml() {
        /* create a dom document with encoding utf8 */
        $domtree = new DOMDocument('1.0', 'UTF-8');
        $xmlRoot = $domtree->appendChild($domtree->createElement('view'));
        $this->table->addSelfToDocument( $domtree, $xmlRoot );
        return $domtree->saveXML();
    }
    
    /**
     * Returns the filename for the stylesheet to use during transform.
     */
    function getXslFilename() {
        return WPDEV_BK_PLUGIN_DIR. '/include/lh_cleaner_tasks.xsl';
    }
}

?>