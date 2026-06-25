<?php

/**
 * Display controller for job history page.
 */
class LHJobHistory extends XslTransform {

    var $classnames = array();
    var $statuses = array();
    const JOB_CLASS_PREFIX = 'com.macbackpackers.jobs.';
    const MAX_PAGE_LENGTH = 500;

    /**
     * Default constructor.
     */
    function __construct() {

    }

   /**
    * Reloads the view details.
    */
   function doView() {
       $this->classnames = LilHotelierDBO::getJobHistoryDistinctClassnames();
       $this->statuses = LilHotelierDBO::getJobHistoryDistinctStatuses();
   }

    /**
     * Returns paginated job history as a DataTables-compatible JSON response.
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     * @throws DatabaseException
     */
    function fetch_job_history( $request ) {
        $draw = (int) $request->get_param( 'draw' );
        $start = max( 0, (int) $request->get_param( 'start' ) );
        $length = (int) $request->get_param( 'length' );
        if ( $length <= 0 ) {
            $length = 100;
        }
        $length = min( $length, self::MAX_PAGE_LENGTH );

        $filters = array();
        $jobName = $request->get_param( 'job_name' );
        if ( ! empty( $jobName ) ) {
            $filters['classname'] = $jobName;
        }
        $status = $request->get_param( 'status' );
        if ( ! empty( $status ) ) {
            $filters['status'] = $status;
        }

        $orderColumns = array( 'job_id', 'classname', 'status', 'start_date', 'end_date', 'job_id' );
        $orderColIndex = 0;
        $orderDir = 'desc';
        if ( is_array( $request->get_param( 'order' ) ) && count( $request->get_param( 'order' ) ) > 0 ) {
            $order = $request->get_param( 'order' )[0];
            $orderColIndex = isset( $order['column'] ) ? (int) $order['column'] : 0;
            $orderDir = isset( $order['dir'] ) ? $order['dir'] : 'desc';
        }
        $orderCol = isset( $orderColumns[ $orderColIndex ] ) ? $orderColumns[ $orderColIndex ] : 'job_id';

        $recordsTotal = LilHotelierDBO::getJobHistoryCount( array() );
        $recordsFiltered = LilHotelierDBO::getJobHistoryCount( $filters );
        $rows = LilHotelierDBO::getJobHistoryPage( $start, $length, $filters, $orderCol, $orderDir );

        $jobIds = array_map( function ( $row ) {
            return $row->job_id;
        }, $rows );
        $jobParams = LilHotelierDBO::getJobParametersForJobIds( $jobIds );

        $logDirectory = get_option( 'hbo_log_directory' );
        $logDirectoryUrl = get_option( 'hbo_log_directory_url' );

        $data = array();
        foreach ( $rows as $record ) {
            $shortName = str_replace( self::JOB_CLASS_PREFIX, '', $record->classname );
            $params = isset( $jobParams[ $record->job_id ] ) ? $jobParams[ $record->job_id ] : array();

            $hasLog = file_exists( $logDirectory . '/job-' . $record->job_id . '.log' )
                || file_exists( $logDirectory . '/job-' . $record->job_id . '.gz' );

            $data[] = array(
                'job_id' => (int) $record->job_id,
                'job_name' => $shortName,
                'job_params' => $params,
                'status' => $record->status,
                'can_resubmit' => in_array( $record->status, array( 'failed', 'aborted' ), true ),
                'start_date' => $record->start_date ?? '',
                'end_date' => $record->end_date ?? '',
                'log_file' => $hasLog ? $logDirectoryUrl . $record->job_id : '',
            );
        }

        $response = new WP_REST_Response( array(
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ), 200 );
        $response->header( 'Content-type', 'application/json' );
        return $response;
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
        LilHotelierDBO::runProcessor();
    }

    /**
     * Adds this object to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this object will be added
     */
    function addSelfToDocument($domtree, $parentElement) {

        $parentElement->appendChild($domtree->createElement('homeurl', home_url()));
        $parentElement->appendChild($domtree->createElement('pluginurl', HBO_PLUGIN_URL));
        $parentElement->appendChild($domtree->createElement('log_directory_url', get_option('hbo_log_directory_url')));
        $parentElement->appendChild($domtree->createElement('wpnonce', wp_create_nonce('wp_rest')));

        $jobNamesRoot = $parentElement->appendChild($domtree->createElement('job_names'));
        foreach ( $this->classnames as $classname ) {
            $nameRoot = $jobNamesRoot->appendChild($domtree->createElement('name'));
            $nameRoot->appendChild($domtree->createElement('value', $classname));
            $nameRoot->appendChild($domtree->createElement('label',
                str_replace(self::JOB_CLASS_PREFIX, '', $classname)));
        }

        $statusesRoot = $parentElement->appendChild($domtree->createElement('statuses'));
        foreach ( $this->statuses as $status ) {
            $statusesRoot->appendChild($domtree->createElement('status', $status));
        }
    }

    /**
      Generates the following xml:
        <view>
            <homeurl>...</homeurl>
            <pluginurl>...</pluginurl>
            <log_directory_url>...</log_directory_url>
            <wpnonce>...</wpnonce>
            <job_names>
                <name>
                    <value>com.macbackpackers.jobs.BedCountJob</value>
                    <label>BedCountJob</label>
                </name>
            </job_names>
            <statuses>
                <status>completed</status>
            </statuses>
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
