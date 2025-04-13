<?php

class BlacklistEntry {

    var $blacklist_id;
    var $first_name;
    var $last_name;
    var $email;
    var $notes;
    var $aliases = array();
    var $mugshots = array();
    var $created_date;
    var $last_updated_date;

    /**
     * Default constructor.
     */
    function __construct($blacklist_id, $first_name, $last_name, $email, $notes, $created_date, $last_updated_date) {
        $this->blacklist_id = $blacklist_id;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->email = $email;
        $this->notes = $notes;
        $this->created_date = $created_date;
        $this->last_updated_date = $last_updated_date;
    }

    function add_alias($alias) {
        $this->aliases[] = new BlacklistAlias($alias->alias_id, $alias->blacklist_id, $alias->first_name, $alias->last_name, $alias->email);
    }

    function add_mugshot($mugshot) {
        $this->mugshots[] = new BlacklistMugshot($mugshot->mugshot_id, $mugshot->blacklist_id, $mugshot->filename);
    }

}