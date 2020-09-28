<?php

  add_action('wp_enqueue_scripts', 'wpbootstrap_enqueue_scripts_styles');
  function wpbootstrap_enqueue_scripts_styles() {
      wp_enqueue_style( 'twentyeleven-child-style', get_stylesheet_uri(),
          array( 'twentyeleven_scripts_styles' ), // this is the parent style's handle
          wp_get_theme()->get('Version') // this only works if you have Version in the style header
          );
      wp_enqueue_style('twentyeleven-child-bootstrap', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css');
      wp_enqueue_script('jquery');
      wp_enqueue_script('twentyeleven-child-popper-js', 'https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js');
      wp_enqueue_script('twentyeleven-child-bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js');
  }
  
?>
