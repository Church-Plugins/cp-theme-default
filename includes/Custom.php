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
		add_filter( 'cp_connect_active_chms', function() {return 'pco';});
		add_action( 'tribe_events_single_event_after_the_content', [ $this, 'event_registration' ], 2 );
		add_filter( 'cp_connect_pco_event_args', [ $this, 'event_args' ] );
		add_filter( 'cp_connect_process_items', [ $this, 'filter_groups' ], 10, 2 );
		add_action( 'admin_init', [ $this, 'update_sermon_meta' ] );
	}

	/** Actions **************************************/

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