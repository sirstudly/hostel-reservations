<?php

/**
 * Base class for listing all films.
 */
class ListMovies extends XslTransform {

    // results of stuff
    var $results;   // array() of ResultSet
    var $scottish;  // array() of ResultSet
    var $newReleases; // array() of ResultSet

    /** 
     * Default constructor.
     */
    function __construct() {
        $this->results = array();
        $this->scottish = array();
        $this->newReleases = array();
    }

    /**
     * Queries all films sorted by sort_title and saves
     * resultset in $results
     */
    function fetchFilms() {
        global $wpdb;
        $this->results = $this->fetchFilmsWithWhereClause();
        $this->scottish = $this->fetchFilmsWithWhereClause(
            "JOIN ".$wpdb->prefix."movie_scottish s ON mv.id = s.id "
        );
        $this->newReleases = $this->fetchFilmsWithWhereClause(
            "JOIN ".$wpdb->prefix."movie_new_releases nr ON mv.id = nr.id "
        );
    }

    /**
     * Queries films sorted by sort_title and the given where clause.
     * $whereClause : the WHERE thing to stick in the SQL
     * Returns array() of ResultSet films
     */
    function fetchFilmsWithWhereClause( $whereClause = null ) {
        global $wpdb;
        
        $resultset = $wpdb->get_results(
            "SELECT REPLACE(c00, '&', '&amp;') AS title, 
                    REPLACE(c01, '&', '&amp;') AS synopsis,
                    c05 AS imdb_rating,
                    c09 AS imdb_link,
                    c07 AS year,
                    c14 AS genre,
                    actors,
                    directors,
                    duration
               FROM ".$wpdb->prefix."movieview mv
               ".($whereClause == null ? "" : " ".$whereClause)."
              ORDER BY sort_title");
        
        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }
        return $resultset;
    }

    /**
     * Fetches this page in the following format:
     * <view>
     *   <film>
     *     <title>Bad Boys</title>
     *     <year>1995</year>
     *     ...
     *   </film>
     *   ...
     * </view>
     */
    function toXml() {
        $domtree = new DOMDocument('1.0', 'UTF-8');
        $xmlRoot = $domtree->appendChild($domtree->createElement('view'));
        $allRoot = $xmlRoot->appendChild($domtree->createElement('all'));
        $this->buildFilmXml( $allRoot, $domtree, $this->results );
        $scottishRoot = $xmlRoot->appendChild($domtree->createElement('scottish'));
        $this->buildFilmXml( $scottishRoot, $domtree, $this->scottish );
        $newReleasesRoot = $xmlRoot->appendChild($domtree->createElement('new_releases'));
        $this->buildFilmXml( $newReleasesRoot, $domtree, $this->newReleases );
        return $domtree->saveXML();
    }

    /**
     * Appends the (films) resultset to the xml DOM root specified.
     * $xmlRoot : the xml element to append <film> to
     * $domtree : the initial dom tree
     * $resultset : array() of (film) ResultSet
     */
    function buildFilmXml( $xmlRoot, $domtree, $resultset ) {
        foreach ($resultset as $row) {
            $filmRow = $xmlRoot->appendChild($domtree->createElement('film'));
            $filmRow->appendChild($domtree->createElement('title', $row->title));
            $filmRow->appendChild($domtree->createElement('imdb_rating', $row->imdb_rating));
            $filmRow->appendChild($domtree->createElement('imdb_link', $row->imdb_link));
            $filmRow->appendChild($domtree->createElement('year', $row->year));
            $filmRow->appendChild($domtree->createElement('duration', $row->duration));
            $filmRow->appendChild($domtree->createElement('genre', $row->genre));
            $filmRow->appendChild($domtree->createElement('directors', $row->directors));
            $filmRow->appendChild($domtree->createElement('actors', $row->actors));
            $filmRow->appendChild($domtree->createElement('synopsis', $row->synopsis));
        }
    }

    /**
     * Returns the filename for the stylesheet to use during transform.
     */
    function getXslFilename() {
        return get_stylesheet_directory() . '/list_movies.xsl';
    }

}

?>
