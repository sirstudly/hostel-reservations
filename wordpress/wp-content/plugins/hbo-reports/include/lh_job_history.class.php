<?php

/**
 * Display controller for job history page.
 */
class LHJobHistory extends XslTransform {

    var $history = array();  // array() of wp_lh_jobs records
    var $jobParams = array(); // array() keyed by job_id containing job parameters array[key]=value
    const NUM_DAYS_TO_INCLUDE = 99; // number of days in the past to include
    const MAX_NUM_RECORDS = 500;  // max number of records to display

    /**
     * Default constructor.
     */
    function __construct() {
        
    }

   /**
    * Reloads the view details.
    */
   function doView() {
       $this->history = LilHotelierDBO::getJobHistory( self::NUM_DAYS_TO_INCLUDE, self::MAX_NUM_RECORDS );
       $this->jobParams = array(); // clear out old data
       foreach( $this->history as $job ) {
            $this->jobParams[$job->job_id] = LilHotelierDBO::getJobParameters( $job->job_id );
       }
   }

	/**
	 * Changes the job status back to submitted.
	 * $job_id : PK of job
	 * @throws DatabaseException
	 * @throws ValidationException
	 */
   function resubmitIncompleteJob($job_id) {
	   if (empty($job_id)) {
		   throw new ValidationException("Job ID cannot be blank.");
	   }
		LilHotelierDBO::resubmitIncompleteJob($job_id);
   }

    /**
     * Adds this object to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this object will be added
     */
    function addSelfToDocument($domtree, $parentElement) {

        $logDirectory = get_option( 'hbo_log_directory' );
        $logDirectoryUrl = get_option( 'hbo_log_directory_url' );
	    $parentElement->appendChild($domtree->createElement('pluginurl', HBO_PLUGIN_URL));
        foreach( $this->history as $record ) {
            $recordRoot = $parentElement->appendChild($domtree->createElement('record'));
            $recordRoot->appendChild($domtree->createElement('job_id', $record->job_id));
            $recordRoot->appendChild($domtree->createElement('job_name', 
                str_replace("com.macbackpackers.jobs.", "", $record->classname)));
            $recordRoot->appendChild($domtree->createElement('status', $record->status));
            $recordRoot->appendChild($domtree->createElement('start_date', $record->start_date));
            $recordRoot->appendChild($domtree->createElement('end_date', $record->end_date));

            // if there were any parameters, include them...
            if( isset( $this->jobParams[$record->job_id] )) {
                foreach( $this->jobParams[$record->job_id] as $paramKey => $paramVal ) {
                    $paramRoot = $recordRoot->appendChild($domtree->createElement('job_param'));
                    $paramRoot->appendChild($domtree->createElement('name', $paramKey));
                    $paramRoot->appendChild($domtree->createElement('value', $paramVal));
                }
            }

            // only include logfile if it exists
            $jobLogFilename = "job-" . $record->job_id . ".log";
            $jobLogFilenameCompressed = "job-" . $record->job_id . ".gz";
            if( file_exists( $logDirectory . "/" . $jobLogFilename )
                    || file_exists( $logDirectory . "/" . $jobLogFilenameCompressed ) ) {
                $recordRoot->appendChild($domtree->createElement('log_file', $logDirectoryUrl . $record->job_id ));
            }
        }
    }
    
    /** 
      Generates the following xml:
        <view>
            <record>
                <job_id>123</job_id>
                <job_name>com.macbackpackers.jobs.BedCountJob</job_name>
                <status>completed</status>
                <start_date>2016-02-03 02:48:03</start_date>
                <end_date>2016-02-03 02:48:35</end_date>
                <job_param>
                    <name>allocation_scraper_job_id</name>
                    <value>13</value>
                </job_param>
            </record>
            ...
        </view>
     */
    function toXml() {
        // create a dom document with encoding utf8
        $domtree = new DOMDocument('1.0', 'UTF-8');
        $xmlRoot = $domtree->appendChild($domtree->createElement('view'));
        $this->addSelfToDocument($domtree, $xmlRoot);
        $xml = $domtree->saveXML();
        return $xml;
    }
    
    /**
     * Returns the filename for the stylesheet to use during transform.
     */
    function getXslFilename() {
        return HBO_PLUGIN_DIR. '/include/lh_job_history.xsl';
    }

}

?>