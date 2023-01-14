<?php

class BlacklistEntry {

    var $blacklist_id;
    var $first_name;
    var $last_name;
    var $email;
    var $notes;
    var $aliases = array();

    /**
     * Default constructor.
     */
    function __construct($blacklist_id, $first_name, $last_name, $email, $notes) {
        $this->blacklist_id = $blacklist_id;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->email = $email;
        $this->notes = $notes;
    }

    function add_image($image) {

    }

    function add_alias($alias) {
        $this->aliases[] = new BlacklistAlias($alias->alias_id, $alias->blacklist_id, $alias->first_name, $alias->last_name, $alias->email);
    }

}