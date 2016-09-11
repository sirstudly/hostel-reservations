<?php

/**
 * Renders the table of daily cleaning assignments.
 */
class LHCleaningHoursTable extends XslTransform {

    var $tasks = array(); // array of LHCleaningHoursTableRow (indexed by id)
//    var $editingTaskId; // id of currently editing task (if any)

    /**
     * Default constructor.
     */
    function LHCleaningHoursTable() {
        // nothing to do
    }

    /**
     * Loads all previously saved cleaning assignments from the database.
     */
    function doView() {
//        $tasks = LilHotelierDBO::getCleanerTasks();
//        foreach( $tasks as $task ) {
//            $this->tasks[$task->id] = new LHCleanerTasksTableRow(
//                $task->id,
//                $task->name,
//                $task->description,
//                $task->default_hours,
//                $task->active_yn == 'Y',
//                $task->show_in_daily_tasks_yn == 'Y',
//                $task->sort_order,
//                $task->frequency
//            );
//        }
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
//        if( $this->editingTaskId ) {
//            $editingTaskElem = $domtree->createElement('editing_task_id', $this->editingTaskId);
//            $xmlRoot->appendChild($editingTaskElem);
//        }

        // element for each task
//        foreach( $this->tasks as $task ) {
//            $task->addSelfToDocument( $domtree, $xmlRoot );
//       }

    }

    /** 
      Generates the following xml:
        <tasks>
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
        return WPDEV_BK_PLUGIN_DIR. '/include/lh_cleaning_hours_table.xsl';
    }
}

?>