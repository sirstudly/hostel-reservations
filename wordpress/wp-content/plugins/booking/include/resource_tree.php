<?php

class ResourceTree {

    var $resourceMap; // array() of BookingResource indexed by id
    var $resourceRsMap; // array() of resource recordset indexed by resource id

    function ResourceTree() {
    }

    /**
     * Builds a resource tree with allocations between the given start end dates
     * and optionally matched by resource, status, name.
     * $startDate : include allocations where a booking exists after this date (inclusive)  (DateTime)
     * $endDate : include allocations where a booking exists before this date (inclusive)  (DateTime)
     * $resourceId : only match this resource id (optional)
     * $status : only match this status (optional)
     * $name : match this name against guest name or booking first/last name (* wildcard allowed) (optional)
     */
    function doSearch($startDate, $endDate, $resourceId = null, $status = null, $name = null) {

        // initialize our resourceMap
        $resourceRsMap = ResourceDBO::getAllResources($resourceId);
        foreach ($resourceRsMap as $res) {
            $br = new BookingResource($res->resource_id, $res->name, $res->level, 
                $res->path, $res->number_children, $res->resource_type, $res->room_type);

            // if parent exists, add child to parent... otherwise set it as root
            if ($res->parent_resource_id != '' && isset($this->resourceMap[$res->parent_resource_id])) {
                $this->resourceMap[$res->parent_resource_id]->addChildResource($br);
            } 
            $this->resourceMap[$res->resource_id] = $br;
        }

        // get our allocations
        // returns 2D array() AllocationCell indexed by allocationId, then booking date (d.M.y)
        $allocCellMap = AllocationDBO::getAllocationsByResourceForDateRange($startDate, $endDate, $resourceId, $status, $name);

        // we only get those with booking dates
        // now pad the remainder from startDate to endDate...
        
        // loop thru all resources
        // from startDate to endDate...
        // add allocation cell (either copy from above, or insert empty one)
        //   for those with resourceId = 1
        //   create a new booking resource (bed) - 'unassigned-X' where X is autoset 1...n

        // now loop through our allocations and set them on the correct BookingResource
        foreach( $allocCellMap as $allocId => $row ) {

            $start = clone $startDate;  // we will incrementally move $start until $start = $endDate

            $allocCells = array();
            while ($start <= $endDate) {

                if( isset( $row[$start->format('d.m.Y')]) ) {
                    $allocCells[$start->format('d.m.Y')] = $row[$start->format('d.m.Y')];

error_log( "allocated cell on ".$start->format('d.m.Y').": " . var_export($allocCells[$start->format('d.m.Y')], true));
                }
                else {
                    $allocCells[$start->format('d.m.Y')] = new AllocationCell();
error_log( 'allocated empty cell for: ' . $start->format('d.m.Y'));
                }

                $start->add(new DateInterval('P1D'));  // increment by day
            }
            $this->resourceMap[$resourceId]->setAllocationCells( $allocCells );
        }

        foreach( array_keys($this->bookingResourceMap) as $resourceId ) {
            $this->resourceMap[$resourceId]->updateRenderStateForAllocations();
        }
    }    

}

?>
