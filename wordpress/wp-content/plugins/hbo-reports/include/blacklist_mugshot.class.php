<?php

class BlacklistMugshot {

    var $mugshot_id;
    var $blacklist_id;
    var $filename;
    var $url;

    /**
     * Default constructor.
     */
    function __construct($mugshot_id, $blacklist_id, $filename) {
        $this->mugshot_id = $mugshot_id;
        $this->blacklist_id = $blacklist_id;
        $this->filename = $filename;
    }

}