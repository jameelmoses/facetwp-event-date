<?php

class FacetWP_Facet_Event_Date extends FacetWP_Facet {

  /**
   * Give the facet a label
   */
  function __construct() {
    $this->label = __( 'Event Date', 'fwp' );
  }

  /**
   * Pull the facet choices from the facetwp_index DB table
   */
  function load_values( array $params ): array {
    global $wpdb;

    $facet        = $params['facet'];
    $from_clause  = $wpdb->prefix . 'facetwp_index f';
    $where_clause = $params['where_clause'];

    // Facet in "OR" mode
    $where_clause = $this->get_where_clause( $facet );

    $from_clause  = apply_filters( 'facetwp_facet_from', $from_clause, $facet );
    $where_clause = apply_filters( 'facetwp_facet_where', $where_clause, $facet );
    $now          = date( 'Y-m-d' );

    $sql = "
    SELECT (CASE WHEN f.facet_value >= '$now' THEN 'upcoming' ELSE 'past' END) AS type, COUNT(DISTINCT f.post_id) AS counter
    FROM $from_clause
    WHERE f.facet_name = '{$facet['name']}' $where_clause
    GROUP BY type";

    $results = $wpdb->get_results( $sql, ARRAY_A );

    $output = [ [
      'facet_value'         => 'upcoming',
      'facet_display_value' => 'Upcoming Events',
      'counter'             => 0
    ], [
      'facet_value'         => 'past',
      'facet_display_value' => 'Past Events',
      'counter'             => 0
    ] ];

    foreach ( $results as $row ) {
      $row_num = ( 'upcoming' === $row['type'] ) ? 0 : 1;
      $output[ $row_num ]['counter'] = $row['counter'];
    }

    return $output;
  }


  /**
   * Display the facet HTML (here we're just inheriting from Dropdown facets)
   */
  function render( array $params ): string {
    $output          = '';
    $facet           = $params['facet'];
    $values          = (array) $params['values'];
    $selected_values = (array) $params['selected_values'];

    $output .= '<select class="facetwp-dropdown">';

    foreach ( $values as $result ) {
      $selected = in_array( $result['facet_value'], $selected_values ) ? ' selected' : '';

      $display_value = '';

      // Determine whether to show counts
      $display_value .= esc_attr( $result['facet_display_value'] );
      $show_counts = apply_filters( 'facetwp_facet_dropdown_show_counts', true, [ 'facet' => $facet ] );

      if ( $show_counts ) {
        $display_value .= ' (' . $result['counter'] . ')';
      }

      $output .= '<option value="' . esc_attr( $result['facet_value'] ) . '"' . $selected . '>' . $display_value . '</option>';
    }

    $output .= '</select>';
    return $output;
  }


  /**
   * Apply the filtering logic
   */
  function filter_posts( array $params ): array {
    global $wpdb;

    $output          = [];
    $facet           = $params['facet'];
    $selected_values = $params['selected_values'];

    $now     = date( 'Y-m-d' );
    $compare = implode( ',', $selected_values );
    $compare = ( 'upcoming' == $compare ) ? '>=' : '<';

    $sql = $wpdb->prepare( "SELECT DISTINCT post_id
      FROM {$wpdb->prefix}facetwp_index
      WHERE facet_name = %s",
      $facet['name']
    );

    $output = facetwp_sql( $sql . " AND facet_value $compare '$now'", $facet );

    return $output;
  }


  /**
   * Load the necessary front-end script(s) for handling user interactions
   */
  function front_scripts(): void {
    FWP()->display->assets['event-date-front.js'] = plugins_url( '', __FILE__ ) . '/assets/js/front.js';
  }
}
