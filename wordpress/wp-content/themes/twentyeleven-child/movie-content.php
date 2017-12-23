<?php
/**
 * Template Name: Movie Content Template
 * Description: A Page Template that shows what's playing
 *
 * @package WordPress
 * @subpackage Twenty_Eleven
 * @since Twenty Eleven 1.0
 */

// Enqueue showcase script for the slider
wp_enqueue_script( 'twentyeleven-showcase', get_template_directory_uri() . '/js/showcase.js', array( 'jquery' ), '2011-04-28' );

    error_log( "Template dir is '" . get_template_directory() . "'");
    error_log( "Stylesheet dir is '" . get_stylesheet_directory() . "'");

    if (file_exists( get_stylesheet_directory() . '/list_movies.php')) { 
        require_once( get_stylesheet_directory() . '/list_movies.php' ); 
    }

    get_header(); 

    $lm = new ListMovies();
    $lm->fetchFilms();
//    error_log($lm->toXml());
    echo $lm->toHtml();

    get_footer(); 
?>