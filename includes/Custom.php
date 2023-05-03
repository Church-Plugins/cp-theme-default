<?php

namespace Church;

use CP_Library\Models\Item;

/**
 * Custom functionality for this church. Should be left empty unless on a project fork.
 *
 * @author tanner moushey
 */
class Custom {

	/**
	 * @var
	 */
	protected static $_instance;

	/**
	 * Only make one instance of Custom
	 *
	 * @return Custom
	 */
	public static function get_instance() {
		if ( ! self::$_instance instanceof Custom ) {
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
		add_filter( 'cp_connect_active_chms', function() {return 'ccb';} );
		add_filter( 'cp_connect_ccb_pull_groups_group', [ $this, 'ccp_group_fields' ], 10, 2 );
		add_action( 'init', [ $this, 'group_parish_taxonomy' ] );
		add_action( 'cp_update_item_after', [ $this, 'save_ccb_item' ], 10, 2 );
		add_filter( 'cp_groups_filter_facets', [ $this, 'parish_facet' ] );

//		add_action( 'tribe_events_single_event_after_the_content', [ $this, 'event_registration' ], 2 );
//		add_filter( 'cp_connect_pco_event_args', [ $this, 'event_args' ] );
//		add_filter( 'cp_connect_process_items', [ $this, 'filter_groups' ], 10, 2 );
//		add_action( 'admin_init', [ $this, 'update_sermon_meta' ] );
	}

	/** Actions **************************************/

	/**
	 * Save CCB fields
	 *
	 * @param $args
	 * @param $group
	 *
	 * @return mixed
	 * @author Tanner Moushey, 5/2/23
	 */
	public function ccp_group_fields( $args, $group ) {
		$additional_fields = array();
		if ( ! empty( (array) $group->user_defined_fields ) ) {
			if ( 'array' == gettype( $group->user_defined_fields->user_defined_field ) ) {
				$additional_fields = $group->user_defined_fields->user_defined_field;
			} elseif ( 'object' == gettype( $group->user_defined_fields->user_defined_field ) ) {
				$additional_fields[] = $group->user_defined_fields->user_defined_field;
			}
		}

		foreach ( $additional_fields as $field ) {

			switch( $field->label ) {
				case 'Life Stage':
					$args['group_life_stage'][] = $field->selection;
					break;
				case 'Gender':
					$args['group_category'][] = $field->selection;
					break;
				case 'Parish':
					$args['cp_group_parish'] = $field->selection;
					break;
			}

		}

		return $args;
	}

	/**
	 * Taxonomies do not save properly on Cron in wp_insert_post, so we need to save it directly
	 *
	 * @param $item
	 * @param $id
	 *
	 * @author Tanner Moushey, 5/2/23
	 */
	public function save_ccb_item( $item, $id ) {
		if ( ! isset( $item['cp_group_parish'] ) ) {
			return;
		}

		wp_set_post_terms( $id, $item['cp_group_parish'], 'cp_group_parish' );
	}

	public function parish_facet( $facets ) {
//		if ( 191 != get_query_var( 'cp_location_id' ) ) {
//			return $facets;
//		}

		$parish = new \stdClass();

		$parish->taxonomy = 'cp_group_parish';
		$parish->single_label = 'Parish';

		$facets[] = $parish;

		return $facets;
	}

	/**
	 * Create parish taxonomy for groups
	 *
	 *
	 * @author Tanner Moushey, 5/2/23
	 */
	public function group_parish_taxonomy() {
		$args = array(
			'label'             => __( 'Parish', 'cp-theme-default' ),
			'public'            => true,
			'show_admin_column' => true,
			'hierarchical'      => false
		);

		register_taxonomy( 'cp_group_parish', 'cp_group', $args );
	}

	public function update_sermon_meta() {
		if ( ! isset( $_GET['update-sermon-meta'] ) ) {
			return;
		}

		$sermons = get_posts( [ 'post_type' => 'cpl_item', 'posts_per_page' => 9999 ] );

		foreach( $sermons as $sermon ) {
			$item = Item::get_instance_from_origin( $sermon->ID );

			if ( $audio = $item->get_meta_value( 'audio_url' ) ) {
				 parse_str( parse_url( $audio )['query'], $results );

				 if ( ! empty( $results['url'] ) ) {
					 $item->update_meta_value( 'audio_url', $results['url'] );
				 }
			}

			if ( $audio = $item->get_meta_value( 'video_url' ) ) {
				parse_str( parse_url( $audio )['query'], $results );

				if ( ! empty( $results['url'] ) ) {
					$item->update_meta_value( 'video_url', $results['url'] );
				}
			}
		}
	}
	/**
	 * Customize event details
	 *
	 * @param $args
	 *
	 * @return mixed
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public function event_args( $args ) {
		// don't use the excerpt
		unset( $args['post_excerpt'] );
		return $args;
	}

	/**
	 * Show registration button if registration is active
	 *
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public function event_registration() {
		if ( ! $registration_url = get_post_meta( get_the_ID(), 'registration_url', true ) ) {
			return;
		}

		printf( '<div><a href="%s" class="cp-button is-large" target="_blank">Register Now</a></div>', $registration_url );
	}

	/**
	 * Remove Unique Groups from Group import
	 *
	 * @param $items
	 * @param $integration
	 *
	 * @return mixed
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public function filter_groups( $items, $integration ) {
		if ( 'groups' != $integration->type ) {
			return $items;
		}

		foreach( $items as $key => $item ) {
			if ( in_array( 'Unique Groups', $item['group_type'] ) ) {
				unset( $items[ $key ] );
			}
		}

		return $items;
	}
}