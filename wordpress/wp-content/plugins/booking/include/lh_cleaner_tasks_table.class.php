<?php

/**
 * Renders the table of cleaning tasks.
 */
class LHCleanerTasksTable extends XslTransform {

    var $tasks = array(); // array of LHCleanerTasksTableRow (indexed by id)
    var $editingTaskId; // id of currently editing task (if any)

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
                $task->active_yn == 'Y',
                $task->show_in_daily_tasks_yn == 'Y',
                $task->sort_order,
                $task->frequency
            );
        }
    }

    /**
     * Adds a new task. Returns the id of the task that was created.
     * $name : name of task
     * $description : description of task
     * $defaultHours : (int) default number of hours for this tak
     * $active : (boolean) true if active, false if not
     * $showInDailyTasks : (boolean) true if task should be shown in the daily tasks page
     * $sortOrder : (int) the order this task appears on the daily tasks page
     * $frequency : (int) the number of times this task appears on the daily tasks page
     */
    function addTask( $name, $description, $defaultHours, $active, $showInDailyTasks, $sortOrder, $frequency ) {
        $taskId = LilHotelierDBO::addCleanerTask( $name, $description, $defaultHours, $active, $showInDailyTasks, $sortOrder, $frequency );
        self::doView();
        return $taskId;
    }

    /**
     * Starts editing an existing task.
     * $id : id of task to edit
     */
    function editTask( $id ) {
        if( isset( $this->tasks[$id] )) {
            $this->editingTaskId = $id;
        }
    }

    /**
     * Cancels the editing of an existing task.
     */
    function cancelEditTask() {
        $this->editingTaskId = null;
    }

    /**
     * Updates an existing task.
     * $id : id of task to edit
     * $name : name of task
     * $description : description of task
     * $defaultHours : (int) default number of hours for this task
     * $active : (boolean) true if active, false if not
     * $showInDailyTasks : (boolean) true if task should be shown in the daily tasks page
     * $sortOrder : (int) the order this task appears on the daily tasks page
     * $frequency : (int) the number of times this task appears on the daily tasks page
     */
    function updateTask( $id, $name, $description, $defaultHours, $active, $showInDailyTasks, $sortOrder, $frequency ) {
        $this->tasks[$id]->updateTask( $name, $description, $defaultHours, $active, $showInDailyTasks, $sortOrder, $frequency );
        $this->editingTaskId = null; // we are done editing
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

        // include the editing task (if any)
        if( $this->editingTaskId ) {
            $editingTaskElem = $domtree->createElement('editing_task_id', $this->editingTaskId);
            $xmlRoot->appendChild($editingTaskElem);
        }

        // element for each task
        foreach( $this->tasks as $task ) {
            $task->addSelfToDocument( $domtree, $xmlRoot );
       }

    }

    /** 
      Generates the following xml:
        <tasks>
            <editing_task_id>7</editing_task_id>
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