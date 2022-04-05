<?php
/*
Plugin Name: FacetWP - Event Date
Description: Custom facet type for FacetWP to filter by upcoming vs. past events
Version: 1.0.0
Author: Jameel Moses
Author URI: https://github.com/jameelmoses
*/

defined( 'ABSPATH' ) or exit;

add_filter( 'facetwp_facet_types', function( $types ) {
  include( dirname( __FILE__ ) . '/class-facet.php' );
  $types['event_date'] = new FacetWP_Facet_Event_Date();
  return $types;
});
