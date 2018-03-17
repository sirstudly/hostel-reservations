<?php

/**
 * A single scheduled job that repeats every X minutes.
 */
class ScheduledJobRepeat extends ScheduledJob {

    var $jobId;
    var $classname;
    var $repeatTimeMin;
    var $active;
    var $params;

    /**
     * Default constructor.
     * $jobId : unique PK of scheduled job
     * $classname : job to run
     * $repeatTimeMin : minutes between jobs
     * $active : is this job active? bool
     * $repeatTimeMin : number of minutes between runs
     */
    function ScheduledJobRepeat($jobId, $classname, $repeatTimeMin, $active, $params = array()) {
        $this->jobId = $jobId;
        $this->classname = $classname;
        $this->repeatTimeMin = $repeatTimeMin;
        $this->active = $active;
        $this->params = $params;
    }

    /**
     * Adds this object to the DOMDocument/XMLElement specified.
     * Generates the following XML:
     * <job>
     *   <id>123</id>
     *   <job-name>Update Widgets</classname>
     *   <repeat-time-min>60</repeat-time-min>
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
        $jobRoot->appendChild($domtree->createElement('repeat-time-min', $this->repeatTimeMin));
        $jobRoot->appendChild($domtree->createElement('active', $this->active ? "yes" : "no" ));
        foreach( $this->params as $key => $value ) {
            $paramRoot = $jobRoot->appendChild($domtree->createElement('param'));
            $paramRoot->appendChild($domtree->createElement('name', $key));
            $paramRoot->appendChild($domtree->createElement('value', $value));
        }
    }
    
}

?>