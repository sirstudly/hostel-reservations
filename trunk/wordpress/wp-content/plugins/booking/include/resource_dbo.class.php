<?php

/**
 * Database object for resources table.
 */
class ResourceDBO {

    /**
     * Returns all resources indexed by resource_id. 
     * Each object in the collection returned has the following properties:
     * $resourceId : id of (parent) resource (if not provided, will return all resources)
     * Returns resultset array()
     * resource_id : id of resource
     * name : resource name
     * capacity : capacity of resource
     * lvl : depth of tree (starting at 1)
     * path : tree path by resource id
     * number_children : number of children (0 for leaf nodes)
     * parent_resource_id : resource id of parent (optional)
     * parent_name : name of parent resource (if applicable)
     * resource_type : one of bed, room, group
     * room_type : one of M, F, MX, "" (null)
     */
    static function getAllResources($resourceId = null) {
        global $wpdb;

        // query all our resources (in order)
        $resultset = $wpdb->get_results($wpdb->prepare(
            "SELECT r.resource_id, r.name, r.lvl, r.path, r.number_children, r.parent_resource_id, rp.name AS parent_name, r.resource_type, r.room_type
               FROM ".$wpdb->prefix."v_resources_by_path r
               LEFT OUTER JOIN ".$wpdb->prefix."bookingresources rp ON r.parent_resource_id = rp.resource_id
                    ". ($resourceId == null ? "" : "
                            WHERE ((r.path LIKE '%%/$resourceId' AND r.number_children = 0)
                                OR (r.path LIKE '%%/$resourceId/%%' AND r.number_children = 0))") . "
              ORDER BY r.path"));
        
        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        $result = array();
        foreach ($resultset as $res) {
            $result[$res->resource_id] = $res;
        }
        return $result;
    }
    
    /**
     * Fetches resource recordset by resource_id. 
     * $resourceId : id of resource 
     * Returns resultset with the following properties:
     * resource_id : id of resource
     * name : resource name
     * capacity : capacity of resource
     * lvl : depth of tree (starting at 1)
     * path : tree path by resource id
     * number_children : number of children (0 for leaf nodes)
     * parent_resource_id : resource id of parent (optional)
     * parent_name : name of parent resource (if applicable)
     * resource_type : one of bed, room, group
     * room_type : one of M, F, MX, "" (null)
     * Throws DatabaseException if resourceId does not exist
     */
    static function fetchResourceById($resourceId) {
        global $wpdb;

        // query resource by id
        $resultset = $wpdb->get_results($wpdb->prepare(
            "SELECT r.resource_id, r.name, r.lvl, r.path, r.number_children, r.parent_resource_id, r.resource_type, r.room_type
               FROM ".$wpdb->prefix."v_resources_by_path r
              WHERE r.resource_id = %d
              ORDER BY r.path", $resourceId));
        
        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        foreach ($resultset as $res) {
            return $res;
        }
        throw new DatabaseException("Resource $resourceId not found");
    }
    
    /**
     * Returns all properties for a given resource_id. 
     * Each object in the collection returned has the following properties:
     * $resourceId : id of resource 
     * Returns resultset array() indexed by property id
     * property_id : id of resource property
     * description : description of property
     * selected_yn : 'Y' if property is selected for resource, 'N' otherwise
     */
    static function getPropertiesForResource($resourceId) {
        global $wpdb;

        // query all our resources and properties (in order)
        $resultset = $wpdb->get_results($wpdb->prepare(
            "SELECT rp.property_id, rp.description, CASE WHEN rpm.property_id IS NULL THEN 'N' ELSE 'Y' END AS selected_yn 
               FROM ".$wpdb->prefix."resource_properties rp
               LEFT OUTER JOIN ".$wpdb->prefix."resource_properties_map rpm ON rp.property_id = rpm.property_id AND rpm.resource_id = %d
              ORDER BY rp.property_id", $resourceId));
        
        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        $result = array();
        foreach ($resultset as $res) {
            $result[$res->property_id] = $res;
        }
        return $result;
    }
    
    /**
     * Update the properties for a particular resource to the DB.
     * $resourceId : id of resource to update
     * $propertyArray : array() of property ids applicable for this resource
     */
    static function updateResourceProperties($resourceId, $propertyArray) {
error_log("updateResourceProperties $resourceId , ".var_export($propertyArray, true));

        global $wpdb;
        $wpdb->query($wpdb->prepare(
                "DELETE FROM ".$wpdb->prefix ."resource_properties_map
                  WHERE resource_id = %d", $resourceId));
        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        // iterate and insert
        foreach ($propertyArray as $propertyId) {
            $wpdb->query($wpdb->prepare(
                "INSERT INTO ".$wpdb->prefix ."resource_properties_map (property_id, resource_id)
                 VALUES(%d, %d)", $propertyId, $resourceId));
            if($wpdb->last_error) {
                throw new DatabaseException($wpdb->last_error);
            }
        }
    }
    
    /**
     * Fetches available resource objects by parent resource id.
     * The result will be a nested tree based on their path.
     * $resourceId : id of (parent) resource (if not provided, will return all resources)
     * $allocationCellMap : 2D map of resource id, date [d.m.Y] => array() of AllocationCell to populate for any matched resource
     * Returns array of BookingResource
     */
    static function getBookingResourcesById($resourceId = null, $allocationCellMap = null) {
        
        // resources are path-ordered
        $return_val = array();
        $return_val_map = array();  // map of all resource id => BookingResource in return_val
        foreach (ResourceDBO::getAllResources($resourceId) as $res) {
            $br = new BookingResource($res->resource_id, $res->name, $res->lvl, $res->path, $res->number_children, $res->resource_type);
            if ($br->level == 1) {  // root element
                $return_val[] = $br;
            } else { // child of root
                // if paths are correct, parent_resource_id will always be set
                $return_val_map[$res->parent_resource_id]->addChildResource($br);
            }
            
            if($allocationCellMap != null && isset($allocationCellMap[$br->resourceId]) && $br->type == 'bed') {
                $br->setAllocationCells($allocationCellMap[$br->resourceId]);
            }
            $return_val_map[$res->resource_id] = $br;
        }
        return $return_val;
    }
    
    /**
     * Returns all resources mapped by ID.
     * Returns (array of) id -> resource name
     */
    static function getResourceMap() {
        $result = array();
        foreach (ResourceDBO::getAllResources() as $res) {
            $result[$res->resource_id] = $res->name;
        }
        return $result;
    }
    
    /**
     * Inserts a new resource.
     * $name : name of new resource
     * $capacity : capacity of new resource (optional if parent resource)
     * $parentResourceId : id of parent resource (optional)
     * $resourceType : one of group, room, bed
     * Throws DatabaseException on insert error
     */
    static function insertResource($name, $capacity, $parentResourceId, $resourceType) {
        $dblink = new DbTransaction();

        // easiest way to represent a single unit is to create separate resources 
        // for each individual bed in the room
        try {
            if ($resourceType == 'private' || $resourceType == 'room') {
        
                // first insert the parent record
                $newId = ResourceDBO::insertResourceDb($dblink->mysqli, $name, $parentResourceId, $resourceType);
                
                // insert a record for each 'bed' in the room
                for($i = 0; $i < $capacity; $i++) {
                    ResourceDBO::insertResourceDb($dblink->mysqli, 'Bed-'.($i+1), $newId, 'bed');
                }
            }
            else {
                $newId = ResourceDBO::insertResourceDb($dblink->mysqli, $name, $parentResourceId, $resourceType);
            }

        } catch(Exception $ex) {
            $dblink->mysqli->rollback();
            $dblink->mysqli->close();
            throw $e;
        }
        $dblink->mysqli->commit();
        $dblink->mysqli->close();
    }
    
    /**
     * Inserts a new resource to the db within the current transaction.
     * $mysqli : current database connection
     * $name : name of new resource
     * $parentResourceId : id of parent resource (optional)
     * $resourceType : one of group, room, private, bed
     * Returns ID of inserted record
     * Throws DatabaseException on insert error
     */
    static function insertResourceDb($mysqli, $name, $parentResourceId, $resourceType) {
        global $wpdb;
        $stmt = $mysqli->prepare(
                "INSERT INTO ".$wpdb->prefix ."bookingresources (name, parent_resource_id, resource_type, created_by, created_date, last_updated_by, last_updated_date)
                 VALUES(?, ?, ?, ?, NOW(), ?, NOW())");
        $userLogin = wp_get_current_user()->user_login;
        $stmt->bind_param('sisss', $name, $parentResourceId, $resourceType, $userLogin, $userLogin);
        if(false === $stmt->execute()) {
            throw new DatabaseException("Error occurred inserting into resource :".$mysqli->error);
        }
        $newId = $stmt->insert_id;
        $stmt->close();
        return $newId;
    }
    
    /**
     * Deletes a resource by ID.
     * If the resource has child resources, they will also be deleted.
     * $resourceId : id of resource to delete
     * Throws DatabaseException if there are underlying bookings linked to resource
     */
    static function deleteResource($resourceId) {
        global $wpdb;

        $arr_resource_ids = array();   // all resource ids we need to delete
        $parentResourceIds = array();  // parent resource ids for current iteration
        $parentResourceIds[] = $resourceId;
        
        // walk down the tree from $resourceId and collect those which share the same parent
        while(sizeof($parentResourceIds) > 0) {
            
            array_push($arr_resource_ids, $parentResourceIds);
            $resstr = '';
            foreach ($parentResourceIds as $p_res) {
                $resstr .= "$p_res,";
            }
            $resstr = rtrim($resstr, ',');

            $resultset = $wpdb->get_results($wpdb->prepare(
                    "SELECT resource_id 
                       FROM ".$wpdb->prefix."bookingresources
                      WHERE parent_resource_id IN ($resstr)"));
            $parentResourceIds = array();
            foreach ($resultset as $res) {
                $parentResourceIds[] = $res->resource_id;
            }
        }
        
        // then delete from child to parent because of FK constraints
        $dblink = new DbTransaction();
        while( sizeof($arr_resource_ids) > 0) {
        
            $resstr = '';
            foreach (array_pop($arr_resource_ids) as $p_res) {
                $resstr .= "$p_res,";
            }
            $resstr = rtrim($resstr, ',');

            $stmt = $dblink->mysqli->prepare(
                  "DELETE FROM ".$wpdb->prefix ."bookingresources 
                    WHERE resource_id IN ($resstr)");
            if(false === $stmt->execute()) {
                $errormsg = $dblink->mysqli->error;
                $stmt->close();
                $dblink->mysqli->rollback();
                $dblink->mysqli->close();
                if(false === strpos($errormsg, "foreign key constraint fails")) { 
                    throw new DatabaseException("Error occurred deleting resource : $errormsg");
                } else {
                    throw new DatabaseException("Resource cannot be deleted as there are linked bookings");
                }
            }
            $stmt->close();
        }
        $dblink->mysqli->commit();
        $dblink->mysqli->close();
    }

    /**
     * Edits a resource by ID.
     * $resourceId : id of resource to edit
     * $resourceName : new name of resource
     */
    static function editResource($resourceId, $resourceName) {
        global $wpdb;
        $userLogin = wp_get_current_user()->user_login;
        if( false === $wpdb->query($wpdb->prepare(
                "UPDATE ".$wpdb->prefix ."bookingresources
                    SET name = %s,
                        last_updated_by = %s,
                        last_updated_date = NOW()
                  WHERE resource_id = %d", $resourceName, $userLogin, $resourceId))) {
            error_log($wpdb->last_error." executing sql: ".$wpdb->last_query);
            throw new DatabaseException("Error occurred updating resource :".$wpdb->last_error);
        }
    }
    
    /**
     * Returns all resources for the given bookingId
     * $bookingId : valid booking id
     * Returns array() of String indexed by resourceId
     */
    function fetchResourcesForBookingId($bookingId) {
        // find all "parent" resources (rooms) for this booking
        global $wpdb;

        $resultset = $wpdb->get_results($wpdb->prepare(
            "SELECT DISTINCT p.resource_id, p.name
               FROM ".$wpdb->prefix."booking b
               JOIN ".$wpdb->prefix."allocation a ON b.booking_id = a.booking_id
               JOIN ".$wpdb->prefix."bookingresources r ON a.resource_id = r.resource_id
               JOIN ".$wpdb->prefix."bookingresources p ON r.parent_resource_id = p.resource_id
              WHERE p.resource_type = 'room'
                AND b.booking_id = %d
              ORDER BY p.resource_id", $bookingId));
        
        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        $return_val = array();
        foreach ($resultset as $res) {
            $return_val[$res->resource_id] = $res->name;
        }
        return $return_val;
    }
    
}

?>