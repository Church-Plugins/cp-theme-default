<?php

namespace Church\Integrations;

/**
 * Provides the global $arms_directory object
 *
 * @author tanner moushey
 */
class EventsCalendar {

	/**
	 * @var string 
	 */
	public static $_ministry_tax = 'cp_ministry';
	
	/**
	 * @var
	 */
	protected static $_instance;

	/**
	 * Only make one instance of EventsCalendar
	 *
	 * @return EventsCalendar
	 */
	public static function get_instance() {
		if ( ! self::$_instance instanceof EventsCalendar ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Class constructor: Add Hooks and Actions
	 *
	 */
	protected function __construct() {
		$this->actions();
	}

	/**
	 * Actions and Filters
	 *
	 * @return void
	 */
	protected function actions() {
		add_filter( 'tribe_events_pro_inline_placeholders', [ $this, 'inline_placeholders' ] );
		add_action( 'init', [ $this, 'register_ministry_tax' ], 999 );
		
		// filterbar
		add_filter( 'tribe_context_locations', [ $this, 'filter_context_locations' ] );
		add_filter( 'tribe_events_filter_bar_context_to_filter_map', [ $this, 'filter_map' ] );
		add_action( 'tribe_events_filters_create_filters', [ $this, 'create_filter' ] );
	}

	/** Actions **************************************/

	public function inline_placeholders( $placeholders ) {
		$placeholders['{campus}'] = [ $this, 'event_campus' ];
		$placeholders['{ministry}'] = [ $this, 'event_ministry' ];
		$placeholders['{date_flag}'] = [ $this, 'date_flag' ];
		
		return $placeholders;
	}

	/**
	 * Return the campuses for this ministry
	 * 
	 * @param $id
	 *
	 * @return string
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public function event_campus( $id ) {
		if ( ! function_exists( 'cp_locations' ) ) {
			return '';
		}
		
		$tax = cp_locations()->setup->taxonomies->location->taxonomy;
		
		$terms = wp_get_object_terms( $id, $tax, [ 'fields' => 'names' ] );
		
		if ( ! is_array( $terms ) ) {
			return '';
		}
		
		return implode( ', ', $terms );
	}

	/**
	 * Return the categories associated with this event
	 * 
	 * @param $id
	 *
	 * @return string
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public function event_ministry( $id ) {
		$tax = self::$_ministry_tax;
		
		$terms = wp_get_object_terms( $id, $tax, [ 'fields' => 'names' ] );
		
		if ( ! is_array( $terms ) ) {
			return '';
		}
		
		return implode( ', ', $terms );		
	}

	/**
	 * Add a date flag
	 * 
	 * @param $id
	 *
	 * @return string
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public function date_flag( $id ) {
		$month = tribe_get_start_date( $id, false, 'M' );
		$day = tribe_get_start_date( $id, false, 'd' );
		
		if ( ! $month ) {
			return '';
		}
		
		return sprintf( '<div class="date-flag"><div class="h5">%s</div><div class="h3">%s</div></div>', strtoupper( $month ), $day );
	}

	/**
	 * Register the ministry taxonomy
	 * 
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public function register_ministry_tax() {
		$plural_label = 'Ministries';
		$single_label = 'Ministry';
		
		$labels = array(
			'name'                       => $plural_label,
			'singular_name'              => $single_label,
			'search_items'               => sprintf( __( 'Search %s', 'cp-library' ), $plural_label ),
			'popular_items'              => sprintf( __( 'Popular %s', 'cp-library' ), $plural_label ),
			'all_items'                  => sprintf( __( 'All %s', 'cp-library' ), $plural_label ),
			'edit_item'                  => sprintf( __( 'Edit %s', 'cp-library' ), $single_label ),
			'update_item'                => sprintf( __( 'Update %s', 'cp-library' ), $single_label ),
			'add_new_item'               => sprintf( __( 'Add New %s', 'cp-library' ), $single_label ),
			'new_item_name'              => sprintf( __( 'New %s Name', 'cp-library' ), $single_label ),
			'separate_items_with_commas' => sprintf( __( 'Separate %s with commas', 'cp-library' ), strtolower( $plural_label ) ),
			'add_or_remove_items'        => sprintf( __( 'Add or remove %s', 'cp-library' ), strtolower( $plural_label ) ),
			'choose_from_most_used'      => sprintf( __( 'Choose from the most used %s', 'cp-library' ), strtolower( $plural_label ) ),
			'not_found'                  => sprintf( __( 'No %s found.', 'cp-library' ), strtolower( $plural_label ) ),
			'menu_name'                  => $plural_label,
		);

		$args = array(
			'hierarchical'       => true,
			'labels'             => $labels,
			'show_ui'            => true,
			'show_admin_column'  => true,
			'publicly_queryable' => false,
		);
		
		register_taxonomy( self::$_ministry_tax, 'tribe_events', $args );
	}
	
	/**
	 * Filters the Context locations to let the Context know how to fetch the value of the filter from a request.
	 *
	 * Here we add the `time_of_day_custom` as a read-only Context location: we'll not need to write it.
	 *
	 * @param array<string,array> $locations A map of the locations the Context supports and is able to read from and write
	 *
	 * @return array<string,array> The filtered map of Context locations, with the one required from the filter added to it.
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public function filter_context_locations( array $locations ) {
		// Read the filter selected values, if any, from the URL request vars.
		$locations['filterbar_ministries'] = [
			'read' => [
				\Tribe__Context::REQUEST_VAR => [ 'tribe_filterbar_ministries' ]
			]
		];

		// Return the modified $locations.
		return $locations;		
	}
	
	/**
	 * Filters the map of filters available on the front-end to include the custom one.
	 *
	 * @param array<string,string> $map A map relating the filter slugs to their respective classes.
	 *
	 * @return array<string,string> The filtered slug to filter class map.
	 */	
	public function filter_map( array $map ) {
		if ( ! class_exists( 'Tribe__Events__Filterbar__Filter' ) ) {
			// This would not make much sense, but let's be cautious.
			return $map;
		}

		// Add the filter class to our filters map.
		$map['filterbar_ministries'] = EventsCalendar\FilterMinistry::class;

		// Return the modified $map.
		return $map;
	}

	/**
	 * Includes the custom filter class and creates an instance of it.
	 */
	function create_filter() {
		if ( ! class_exists( 'Tribe__Events__Filterbar__Filter' ) ) {
			return;
		}

		new EventsCalendar\FilterMinistry(
			__( 'Ministries', 'cp-theme-default' ),
			'filterbar_ministries'
		);
	}	
}