<?php

class BlacklistEntry {

    var $id;
    var $first_name;
    var $last_name;
    var $email;

    /**
     * Default constructor.
     */
    function __construct($id, $first_name, $last_name, $email) {
        $this->id = $id;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->email = $email;
    }

    function add_image($image) {

    }

}