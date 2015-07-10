<?php

/**
 * Display controller for housekeeping page.
 */
class HouseKeeping extends XslTransform {

    var $selectionDate;  // DateTime of selected date

    var $bedsheetView;  // the view of the current bedsheet counts on $selectionDate
    var $jobInfo; // latest COMPLETED job we will show the report on
    var $isRefreshJobInProgress = false;

    const JOB_TYPE = "com.macbackpackers.jobs.HousekeepingJob";
    
    /**
     * Default constructor.
     */
    function HouseKeeping() {
        $this->selectionDate = new DateTime();
    }

    /**
     * Updates the selection date when performing a view
     * $selectionDate : date to display housekeeping for (DateTime)
     */
    function setSelectionDate( $selectionDate ) {
        $this->selectionDate = $selectionDate;
    }

    /**
     * Updates the view using the current selection date.
     */
    function doView() {
        $this->doViewForDate($this->selectionDate);
    }
    
    /**
     * Updates the view data for the given date.
     */
    function doViewForDate($selectionDate) {
        $this->selectionDate = $selectionDate;

        // show view for this date
        $startDate = clone $selectionDate;

        // find the last completed job
        $this->jobInfo = LilHotelierDBO::getLatestJobOfType( self::JOB_TYPE );

        if( $this->jobInfo ) {
            //$this->bedsheetView = LilHotelierDBO::fetchBedSheetsFrom($startDate, $this->jobInfo->job_id);
            $this->bedsheetView = LilHotelierDBO::fetchBedSheetsFrom(
            //DateTime::createFromFormat('!Y-m-d', '2014-08-10', new DateTimeZone('UTC')), 
            $selectionDate,
            $this->jobInfo->job_id);
        }

        $this->isRefreshJobInProgress = LilHotelierDBO::isExistsIncompleteJobOfType( self::JOB_TYPE );
    }

    /** 
     * Submits a new bedsheets job to run.
     */
    function submitRefreshJob() {
        LilHotelierDBO::insertJobOfType( self::JOB_TYPE,
            array( "selected_date" => $this->selectionDate->format('Y-m-d H:i:s') ) );
    }

    /**
     * Calculates the number of beds to be changed grouped by
     * the following:
     * 'level2' => 'X beds'
     * 'level4' => 'Y beds'
     * 'level5' => 'Z beds'
     * 'level6_7' => 'A beds'
     * 'upstairs' => 'Y+Z+A beds'
     * 'total' => 'X+Y+Z+A beds'
     */
    function calculateBedChangeCountsByRooms() {
        $bedcounts = array();
        if($this->bedsheetView) {
            foreach( $this->bedsheetView as $bed ) {
                $this->update_bedsheets_to_change( $bedcounts, $bed, '/^2.*/', 'level2' );
                $this->update_bedsheets_to_change( $bedcounts, $bed, '/^4.*/', 'level4' );
                $this->update_bedsheets_to_change( $bedcounts, $bed, '/^5.*/', 'level5' );
                $this->update_bedsheets_to_change( $bedcounts, $bed, '/^[67].*/', 'level6_7' );
            }
            $bedcounts['upstairs'] = 
                ( isset( $bedcounts['level4'] ) ? + $bedcounts['level4'] : 0 ) +
                ( isset( $bedcounts['level5'] ) ? + $bedcounts['level5'] : 0 ) +
                ( isset( $bedcounts['level6_7'] ) ? + $bedcounts['level6_7'] : 0 );
            $bedcounts['total'] = $bedcounts['upstairs'] +
                ( isset( $bedcounts['level2'] ) ? $bedcounts['level2'] : 0 );
        }
        return $bedcounts;
    }

    /**
     * Updates the given array with the bedsheets to change.
     * &$bedcounts: the array to update
     * $bed: the current bedsheet record [resultset] we're looking at
     * $roomMatch: the regex matching string for the "room"
     * $arrayKey: the array key to update if a CHANGE or 3 DAY CHANGE appears in $bed
     */
    function update_bedsheets_to_change( &$bedcounts, $bed, $roomMatch, $arrayKey ) {
        if( preg_match( $roomMatch, $bed->room )) {
            if( !isset( $bedcounts[$arrayKey] )) {
                $bedcounts[$arrayKey] = 0;
            }
            if( $bed->bedsheet == 'CHANGE' || $bed->bedsheet == '3 DAY CHANGE' ) {
                if( $bed->room_type == 'TWIN' || $bed->room_type == 'DBL'
                        || $bed->room_type == 'TRIPLE' || $bed->room_type == 'QUAD' ) {
                    $bedcounts[$arrayKey] += $bed->capacity; // increment by capacity of private room
                } else {
                    $bedcounts[$arrayKey]++; // otherwise, just count as dorm bed
                }
            }
        }
    }
    
    /**
     * Adds this object to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this object will be added
     */
    function addSelfToDocument($domtree, $parentElement) {
        $parentElement->appendChild($domtree->createElement('homeurl', home_url()));
        $parentElement->appendChild($domtree->createElement('selectiondate', $this->selectionDate->format('l jS F Y')));

        if( $this->jobInfo ) {
            // job_id, name, status, created_date, last_updated_date
            $jobRoot = $parentElement->appendChild($domtree->createElement('job'));
            $jobRoot->appendChild($domtree->createElement('id', $this->jobInfo->job_id));
            $jobRoot->appendChild($domtree->createElement('name', $this->jobInfo->classname));
            $jobRoot->appendChild($domtree->createElement('status', $this->jobInfo->status));
            $jobRoot->appendChild($domtree->createElement('start_date', 
                DateTime::createFromFormat('Y-m-d H:i:s', $this->jobInfo->start_date)->format('D, d M Y H:i:s')));
            $jobRoot->appendChild($domtree->createElement('end_date', 
                DateTime::createFromFormat('Y-m-d H:i:s', $this->jobInfo->end_date)->format('D, d M Y H:i:s')));
        }

        if( $this->isRefreshJobInProgress ) {
            $parentElement->appendChild($domtree->createElement('job_in_progress', 'true' ));
        }

        if ( $this->bedsheetView ) {
            foreach( $this->bedsheetView as $bed ) {
                $bedRoot = $parentElement->appendChild($domtree->createElement('bed'));
                $bedRoot->appendChild($domtree->createElement('room', $bed->room));
                $bedRoot->appendChild($domtree->createElement('bed_name', htmlspecialchars($bed->bed_name)));
                $bedRoot->appendChild($domtree->createElement('guest_name', htmlspecialchars(html_entity_decode($bed->guest_name, ENT_COMPAT, "UTF-8" ))));
                $bedRoot->appendChild($domtree->createElement('checkin_date', $bed->checkin_date));
                $bedRoot->appendChild($domtree->createElement('checkout_date', $bed->checkout_date));
                $bedRoot->appendChild($domtree->createElement('data_href', $bed->data_href));
                $bedRoot->appendChild($domtree->createElement('bedsheet', $bed->bedsheet));
            }
        }
        
        $bedcounts = $this->calculateBedChangeCountsByRooms();
        if( !empty( $bedcounts )) {
            $totalsRoot = $parentElement->appendChild($domtree->createElement('totals'));
            foreach( $bedcounts as $bedcountKey => $bedcountValue ) {
                $totalsRoot->appendChild($domtree->createElement( $bedcountKey, $bedcountValue ));
            }
        }
    }
    
    /** 
      Generates the following xml:
        <view>
            <homeurl>/yourwebapp/</homeurl>
            <selectiondate>Tuesday 9th June 2015</selectiondate>
            <job>
              <id>5</id>
              <name>calendar</name>
              <status>completed</status>
              <created_date>15.03.2014</created_date>
              <last_updated_date>22.03.2014</last_updated_date>
            </job>
            <job_in_progress>true</job_in_progress>
            <bed>
                <room>12</room>
                <bed_name>01 Pukie</bed_name>
                <guest_name>Evans, Richard</guest_name>
                <checkin_date>2015-05-20 00:00:00</checkin_date>
                <checkout_date>2015-05-24 00:00:00</checkout_date>
                <data_href>/extranet/properties/533/room_closures/165212/edit</data_href>
                <bedsheet>NO CHANGE<bedsheet>
            </bed>
            <bed>
              ...
            </bed>
            <totals>
                <level2>48</level2>
                ...
                <upstairs>78</upstairs>
                <total>128</total>
            </totals>
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
        return WPDEV_BK_PLUGIN_DIR. '/include/housekeeping.xsl';
    }

}

?>