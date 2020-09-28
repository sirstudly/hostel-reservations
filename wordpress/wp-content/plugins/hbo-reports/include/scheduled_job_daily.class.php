<?php

/**
 * A single scheduled job that runs at the same time everyday.
 */
class ScheduledJobDaily extends ScheduledJob {

    var $jobId;
    var $classname;
    var $timeToRun;
    var $active;
    var $lastRunDate;
    var $params;

    /**
     * Default constructor.
     * $jobId : unique PK of scheduled job
     * $classname : job to run
     * $timeToRun : time to execute job (24 hour clock)
     * $active : is this job active? bool
     * $lastRunDate : datetime this job was run (optional)
     * $repeatTimeMin : number of minutes between runs
     */
    function __construct($jobId, $classname, $timeToRun, $active, $lastRunDate, $params = array()) {
        $this->jobId = $jobId;
        $this->classname = $classname;
        $this->timeToRun = $timeToRun;
        $this->active = $active;
        $this->lastRunDate = $lastRunDate;
        $this->params = $params;
    }

    /**
     * Adds this object to the DOMDocument/XMLElement specified.
     * Generates the following XML:
     * <job>
     *   <id>123</id>
     *   <job-name>Update Widgets</classname>
     *   <repeat-daily-at>23:18:45</repeat-daily-at>
     *   <active>yes</active>
     *   <param><name>param1</name><value>value1</value></param>
     *   <param><name>param2</name><value>value2</value></param>
     * </job>.
     * $domtree : DOM document root
     * $parentElement : DOM element where this object will be added
     */
    function addSelfToDocument($domtree, $parentElement) {
        $jobRoot = $parentElement->appendChild($domtree->createElement('job'));
        $jobRoot->appendChild($domtree->createElement('id', $this->jobId));
        $classnameMap = self::getClassnameMap();
        $jobRoot->appendChild($domtree->createElement('job-name', 
            array_key_exists( $this->classname, $classnameMap ) ? $classnameMap[$this->classname] : $this->classname ));
        $jobRoot->appendChild($domtree->createElement('repeat-daily-at', $this->timeToRun));
        $jobRoot->appendChild($domtree->createElement('active', $this->active ? "yes" : "no" ));
        if( isset( $this->lastRunDate )) {
            $jobRoot->appendChild($domtree->createElement('last_run_date', DateTime::createFromFormat('Y-m-d H:i:s', $this->lastRunDate)->format('D, d M Y H:i:s')));
        }
        foreach( $this->params as $key => $value ) {
            $paramRoot = $jobRoot->appendChild($domtree->createElement('param'));
            $paramRoot->appendChild($domtree->createElement('name', $key));
            $paramRoot->appendChild($domtree->createElement('value', $value));
        }
    }
    
}

?>