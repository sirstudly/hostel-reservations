<?php

  add_action('wp_enqueue_scripts', 'wpbootstrap_enqueue_scripts_styles');
  function wpbootstrap_enqueue_scripts_styles() {
      wp_enqueue_style( 'twentyeleven-child-style', get_stylesheet_uri(),
          array( 'twentyeleven_scripts_styles' ), // this is the parent style's handle
          wp_get_theme()->get('Version') // this only works if you have Version in the style header
          );
      wp_enqueue_style('twentyeleven-child-bootstrap', '//stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css');
  }
  
?>
