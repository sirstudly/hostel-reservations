<?php

/**
 * Abstract controller for bedcounts report.
 */
abstract class AbstractBedCountsNew extends XslTransform {

    const JOB_TYPE = "com.macbackpackers.jobs.BedCountJob";

    protected DateTime $selectionDate;  // DateTime of selected date

    protected $lastCompletedAllocScraperJob; // date/time of last completed allocation scraper job for selectionDate
    protected $bedcountData;  // array() of BedCountEntry
    protected $isRefreshJobInProgress = false;
    protected $lastJob; // the last job of this type that has run

    /**
     * Default constructor.
     * $selectionDate : date to display bedcounts for (DateTime) (defaults to now)
     */
    function __construct($selectionDate = null) {
        $this->selectionDate = $selectionDate == null ? new DateTime() : $selectionDate;
    }
    
    /**
     * Updates the data required for the given date.
     */
    function updateBedcounts() {
        // get last succcessful bedcount job id
        $allocJobRec = LilHotelierDBO::getLastCompletedBedCountJob($this->selectionDate);
        if( $allocJobRec == null ) {
            $this->bedcountData = array();
            $this->lastCompletedAllocScraperJob = null;
        }
        else {
            $lastMonday = $this->getStartOfWeek();
            $nextMonday = clone $lastMonday;
            $nextMonday->add( new DateInterval( 'P7D' ) );
            $this->bedcountData                 = LilHotelierDBO::getBedcountReportWeekly( $lastMonday, $nextMonday );
            $this->lastCompletedAllocScraperJob = empty( $this->bedcountData ) ? null : $this->bedcountData[0]->created_date;
        }
        $this->isRefreshJobInProgress = LilHotelierDBO::isExistsIncompleteJobOfType( self::JOB_TYPE );
        $this->lastJob = LilHotelierDBO::getDetailsOfLastJob( self::JOB_TYPE );
    }

    private function getStartOfWeek(): DateTime {
        $lastMonday = clone $this->selectionDate;
        if ( $this->selectionDate->format( 'D' ) != 'Mon' ) {
            $lastMonday->modify( 'last monday' );
        }
        return $lastMonday;
    }
    
    /**
     * Adds this object to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this object will be added
     */
    function addSelfToDocument($domtree, $parentElement) {
        $parentElement->appendChild($domtree->createElement('homeurl', home_url()));
        $parentElement->appendChild($domtree->createElement('selectiondate', $this->selectionDate->format('Y-m-d')));
        $parentElement->appendChild($domtree->createElement('selectiondate_long', $this->selectionDate->format('D, d M Y')));
        $parentElement->appendChild($domtree->createElement('week_starting', $this->getStartOfWeek()->format('l, d M Y')));

        if( $this->lastCompletedAllocScraperJob ) {
            $parentElement->appendChild($domtree->createElement('last_completed_job', 
                DateTime::createFromFormat('Y-m-d H:i:s', $this->lastCompletedAllocScraperJob)->format('D, d M Y H:i:s')));
        }

        if( $this->isRefreshJobInProgress ) {
            $parentElement->appendChild($domtree->createElement('job_in_progress', 'true' ));
        }

        // did the last job fail to run?
        if( $this->lastJob ) {
            $parentElement->appendChild($domtree->createElement('last_job_id', $this->lastJob['jobId'] ));
            $parentElement->appendChild($domtree->createElement('last_job_status', $this->lastJob['status'] ));
            $parentElement->appendChild($domtree->createElement('check_credentials', $this->lastJob['lastJobFailedDueToCredentials'] ? 'true' : 'false' ));
            $parentElement->appendChild($domtree->createElement('last_job_error_log', 
                get_option('hbo_log_directory_url') . $this->lastJob['jobId'] ));
        }

        $xmlRoot = $parentElement->appendChild($domtree->createElement('bedcounts'));
        $starting_room = null;
        $roomRoot = null;
        $totals = array();
        foreach ( $this->bedcountData as $row ) {
            // if we're starting a new floor, add in a separator
            if ( strpos( get_option( 'hbo_lilho_username' ), 'castlerock' ) === 0 && $starting_room != null
                    && substr( $starting_room, 0, 1 ) != substr( $row->room, 0, 1 ) ) {
                $xmlRoot->appendChild( $domtree->createElement( 'separator' ) );
            }

            // if this is the first room, or if we're looking at a room we haven't looked at previously
            if ( $starting_room == null || $starting_room != $row->room ) {
                $roomRoot = $xmlRoot->appendChild( $domtree->createElement( 'room' ) );
                $roomRoot->appendChild( $domtree->createElement( 'id', htmlspecialchars( $row->room ) ) );
                $roomRoot->appendChild( $domtree->createElement( 'capacity', $row->capacity ) );
                $roomRoot->appendChild( $domtree->createElement( 'room_type', $row->room_type ) );
                $starting_room = $row->room;
            }

            $date_root        = $xmlRoot->appendChild( $domtree->createElement( 'selected_date' ) );
            $date_attr        = $domtree->createAttribute( 'value' );
            $date_attr->value = (new DateTime($row->report_date))->format('Y-m-d');
            $date_root->appendChild( $date_attr );
            $date_root->appendChild( $domtree->createElement( 'num_empty', $row->num_empty ) );
            $date_root->appendChild( $domtree->createElement( 'num_staff', $row->num_staff ) );
            $date_root->appendChild( $domtree->createElement( 'num_paid', $row->num_paid ) );
            $date_root->appendChild( $domtree->createElement( 'num_noshow', $row->num_noshow ) );
            $roomRoot->appendChild( $date_root );

            // accumulate totals by date
            if ( ! isset( $totals[ $date_attr->value ] ) ) {
                $totals[ $date_attr->value ] = [
                    'num_empty'  => 0,
                    'num_staff'  => 0,
                    'num_paid'   => 0,
                    'num_noshow' => 0
                ];
            }
            $totals[ $date_attr->value ]['num_empty']  += intval( $row->num_empty );
            $totals[ $date_attr->value ]['num_staff']  += intval( $row->num_staff );
            $totals[ $date_attr->value ]['num_paid']   += intval( $row->num_paid );
            $totals[ $date_attr->value ]['num_noshow'] += intval( $row->num_noshow );
        }

        $totalsRoot = $xmlRoot->appendChild( $domtree->createElement( 'daily_totals' ) );
        $weeklyTotals = [
            'total_paid' => 0,
            'total_occupied' => 0,
            'total_empty' => 0
        ];
        foreach ( $totals as $key => $value ) {
            $dt_root        = $totalsRoot->appendChild( $domtree->createElement( 'totals_date' ) );
            $dt_attr        = $domtree->createAttribute( 'value' );
            $dt_attr->value = $key;
            $dt_root->appendChild( $dt_attr );
            $dt_root->appendChild( $domtree->createElement( 'num_empty', $value['num_empty'] ) );
            $dt_root->appendChild( $domtree->createElement( 'num_staff', $value['num_staff'] ) );
            $dt_root->appendChild( $domtree->createElement( 'num_paid', $value['num_paid'] ) );
            $dt_root->appendChild( $domtree->createElement( 'num_noshow', $value['num_noshow'] ) );
            $dt_root->appendChild( $domtree->createElement( 'total_paid', $value['num_paid'] + $value['num_noshow'] ) );
            $dt_root->appendChild( $domtree->createElement( 'total_occupied', $value['num_paid'] + $value['num_staff'] ) );
            $weeklyTotals['total_paid']     += $value['num_paid'] + $value['num_noshow'];
            $weeklyTotals['total_occupied'] += $value['num_paid'] + $value['num_staff'];
            $weeklyTotals['total_empty']    += $value['num_empty'];
            $totalsRoot->appendChild( $dt_root );
        }

        $totalsRoot = $xmlRoot->appendChild( $domtree->createElement( 'weekly_totals' ) );
        $totalsRoot->appendChild( $domtree->createElement( 'total_paid', $weeklyTotals['total_paid'] ) );
        $totalsRoot->appendChild( $domtree->createElement( 'total_occupied', $weeklyTotals['total_occupied'] ) );
        $totalsRoot->appendChild( $domtree->createElement( 'total_empty', $weeklyTotals['total_empty'] ) );
    }

    /** 
      Generates the following xml:
        <view>
            <homeurl>/domain</homeurl>
            <selectiondate>2015-05-21</selectiondate>
            <selectiondate_long>Mon, 21 May 2015</selectiondate_long>
            <last_completed_job>Sun, 20 May 2015</last_completed_job>
            <week_starting>Monday 21/05/23</week_starting>
            <bedcounts>
                <room>
                    <id>10</id>
                    <capacity>12</capacity>
                    <room_type>LT_MALE</room_type>
                    <selected_date value="2023-08-17">
                        <num_empty>3</num_empty>
                        <num_staff>8</num_staff>
                        <num_paid>1</num_paid>
                        <num_noshow>0</num_noshow>
                    </selected_date>
                    <selected_date value="2023-08-18">
                        <num_empty>3</num_empty>
                        <num_staff>8</num_staff>
                        <num_paid>1</num_paid>
                        <num_noshow>0</num_noshow>
                    </selected_date>
                    ...
                </room>
                ...
                <daily_totals>
                    <totals_date value="2023-08-17">
                        <num_empty>2</num_empty>
                        <num_staff>44</num_staff>
                        <num_paid>250</num_paid>
                        <num_noshow>1</num_noshow>
                        <total_paid>251</total_paid>
                        <total_occupied>294</total_occupied>
                    </totals_date>
                    <totals_date value="2023-08-18">
                        <num_empty>5</num_empty>
                        <num_staff>49</num_staff>
                        <num_paid>243</num_paid>
                        <num_noshow>2</num_noshow>
                        <total_paid>245</total_paid>
                        <total_occupied>292</total_occupied>
                    </totals_date>
                </daily_totals>
                ...
                <weekly_totals>
                    <total_paid>1247</total_paid>
                    <total_occupied>1472</total_occupied>
                    <total_empty>584</total_empty>
                </weekly_totals>
        </view>
     */
    function toXml() {
        // create a dom document with encoding utf8
        $domtree = new DOMDocument('1.0', 'UTF-8');
        $xmlRoot = $domtree->appendChild($domtree->createElement('view'));
        $this->addSelfToDocument($domtree, $xmlRoot);
        return $domtree->saveXML();
    }

}

?>