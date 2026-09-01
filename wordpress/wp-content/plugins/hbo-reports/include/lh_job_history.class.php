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

        list( $orderCol, $orderDir ) = $this->resolveJobHistoryOrder( $request );

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
     * Resolves sort column and direction from a DataTables server-side request.
     * Defaults to job_id descending when no valid order is supplied.
     * @param WP_REST_Request $request
     * @return array [ orderCol, orderDir ]
     */
    private function resolveJobHistoryOrder( $request ) {
        $orderCol = 'job_id';
        $orderDir = 'desc';

        $orderableColumns = array(
            'job_id' => 'job_id',
            'job_name' => 'classname',
            'status' => 'status',
            'start_date' => 'start_date',
            'end_date' => 'end_date',
        );
        $orderColumns = array( 'job_id', 'classname', 'status', 'start_date', 'end_date' );

        $queryParams = $request->get_query_params();
        $orderParam = isset( $queryParams['order'] ) ? $queryParams['order'] : $request->get_param( 'order' );
        $columnsParam = isset( $queryParams['columns'] ) ? $queryParams['columns'] : $request->get_param( 'columns' );

        if ( ! is_array( $orderParam ) || count( $orderParam ) === 0 || ! isset( $orderParam[0] ) || ! is_array( $orderParam[0] ) ) {
            return array( $orderCol, $orderDir );
        }

        $order = $orderParam[0];
        $orderColIndex = isset( $order['column'] ) ? (int) $order['column'] : 0;
        $orderDir = ( isset( $order['dir'] ) && strtolower( $order['dir'] ) === 'asc' ) ? 'asc' : 'desc';

        if ( is_array( $columnsParam ) && isset( $columnsParam[ $orderColIndex ] ) && is_array( $columnsParam[ $orderColIndex ] ) ) {
            $column = $columnsParam[ $orderColIndex ];
            $isOrderable = ! ( isset( $column['orderable'] ) && ( $column['orderable'] === false || $column['orderable'] === 'false' ) );
            $dataField = isset( $column['data'] ) ? $column['data'] : '';
            if ( $isOrderable && isset( $orderableColumns[ $dataField ] ) ) {
                $orderCol = $orderableColumns[ $dataField ];
            }
        } elseif ( isset( $orderColumns[ $orderColIndex ] ) ) {
            $orderCol = $orderColumns[ $orderColIndex ];
        }

        return array( $orderCol, $orderDir );
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
