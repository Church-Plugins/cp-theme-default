<?php

namespace Church;

class Live {

	/**
	 * @var
	 */
	protected static $_instance;

	/**
	 * Only make one instance of Live
	 *
	 * @return Live
	 */
	public static function get_instance() {
		if ( ! self::$_instance instanceof Live ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Add Hooks and Actions
	 */
	public function __construct() {
		if ( function_exists( 'cp_live' ) ) {
			return;
		}
		
		add_filter( 'cron_schedules', [ $this, 'schedules' ] );
		add_action( 'init', [ $this, 'register_event' ] );
		add_action( 'cp_live_check', [ $this, 'check' ] );
		add_action( 'save_post_cploc_location', [ $this, 'flush_cache' ] );
		add_filter( 'body_class', [ $this, 'live_body_class' ] );
		add_shortcode( 'cp-live-video', [ $this, 'live_video_cb' ] );
		add_action( 'cploc_location_meta_details', [ $this, 'location_meta' ], 10, 2 );
	}
	
	/**
	 * Determine if the location is live streaming. Return video id if true
	 * 
	 * @param $location_id
	 *
	 * @return false|mixed video id
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public static function is_location_live( $location_id = false) {
		if ( ! $location_id ) {
			$location_id = apply_filters( 'cp_live_video_location_id_default', get_query_var( 'cp_location_id' ) );
		}
		
		if ( empty( $location_id ) ) {
			return false;
		} 
		
		$sites_live = get_site_option( 'cp_sites_live', [] );
		
		return isset( $sites_live[ $location_id ] ) ? $sites_live[ $location_id ] : false;
	}

	public function live_body_class( $classes ) {
		if ( ! $location_id = get_query_var( 'cp_location_id' ) ) {
			return $classes;
		} 
		
		$classes[] = self::is_location_live( $location_id ) ? 'cp-is-live' : 'cp-not-live';
		
		return $classes;
	}

	public function schedules( $schedules ) {
		$schedules['cp-live-check'] = [
			'interval' => 120,
			'display'  => esc_html__( 'Live Video Check - Every 2 Minutes' ),
		];

		return $schedules;
	}

	public function register_event() {
		// Only run cron job on Sundays
		if ( ! wp_next_scheduled( 'cp_live_check' ) ) {
			wp_schedule_event( time(), 'cp-live-check', 'cp_live_check' );
		}
	}

	/**
	 * Check the sites to see if any of them are live
	 * 
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public function check() {

		$sites_live_video = get_site_option( 'cp_sites_live_video', [] );
		$sites_live       = get_site_option( 'cp_sites_live', [] );
		
		foreach( $this->sites_to_check() as $site_id => $data ) {
			$live_duration = $data['live_duration'] ?? 6;
			
			if ( empty( $live_duration ) ) {
				$live_duration = 6;
			}
			
			$live_duration = $live_duration * HOUR_IN_SECONDS;
			
			if ( isset( $sites_live[ $site_id ] ) && isset( $sites_live[ $site_id ]['started'] ) ) {
				$duration = time() - $sites_live[ $site_id ]['started'];
				
				// keep live if we are within the live duration window
				if ( $duration < $live_duration ) {
					continue;
				} 
			}
			
			$sites_live[ $site_id ] = false;
			
			// return early if the channel is not set or we don't pass the time check
			if ( empty( $data['api_key'] ) || empty( $data['channel'] ) || ! $this->time_check( $data['times'] ) ) {
				continue;
			}
			
			$video_id = $this->get_channel_status( $data['channel'], $data['api_key'] );

			// if we don't have a video, bail early
			if ( ! $video_id ) {
				continue;
			}

			$sites_live[ $site_id ] = [ 'video_id' => $video_id, 'started' => time() ];
			$sites_live_video[ $site_id ] = $video_id;
		}
	
		// live_video doesn't get overwritten with null values, so it will always have the latest video
		update_site_option( 'cp_sites_live', $sites_live );
		update_site_option( 'cp_sites_live_video', $sites_live_video );
	}

	/**
	 * Check the status of a channel, return the video_id if live
	 * 
	 * @param $channel_id
	 * @param $api_key
	 *
	 * @return false
	 * @since  1.0.1
	 *
	 * @author Tanner Moushey
	 */
	protected function get_channel_status( $channel_id, $api_key ) {
		$args = [
			'part'      => 'snippet',
			'type'      => 'video',
			'eventType' => 'live',
			'channelId' => $channel_id,
			'key'       => $api_key,
		];

		$url = 'https://www.googleapis.com/youtube/v3/search';

		$search   = add_query_arg( $args, $url );
		$response = wp_remote_get( $search );

		// if we don't have a valid body, bail early
		if ( ! $body = wp_remote_retrieve_body( $response ) ) {
			return false;
		}

		$body = json_decode( $body );

		// make sure we have items
		if ( empty( $body->items ) ) {
			return false;
		}
		
		return $body->items[0]->id->videoId;
	}

	/**
	 * Get the sites to check for a live feed
	 * 
	 * @return array|mixed
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	protected function sites_to_check() {
		if ( ! function_exists( 'cp_locations' ) ) {
			return [];
		}
		
		if ( $sites = get_site_transient( 'cp_sites_to_check' ) ) {
			return $sites;
		}
		
		$sites = [];
		
		do_action( 'cploc_multisite_switch_to_main_site' );

		$locations = \CP_Locations\Models\Location::get_all_locations(true);
		
		foreach( $locations as $location ) {
			if ( $channel_id = get_post_meta( $location->ID, 'youtube_channel_id', true ) ) {
				$sites[ $location->ID ] = [
					'channel'  => $channel_id,
					'times'    => get_post_meta( $location->ID, 'service_times', true ),
					'duration' => get_post_meta( $location->ID, 'live_video_duration', true ),
					'api_key'  => get_post_meta( $location->ID, 'youtube_api_key', true ),
				];
			}
		}
		
		do_action( 'cploc_multisite_restore_current_blog');
		
		set_site_transient( 'cp_sites_to_check', $sites );
		return $sites;
	}

	/**
	 * Check if the provided times fall within the required window.
	 * 
	 * @param $times
	 *
	 * @since  1.0.0
	 * @return bool
	 *
	 * @author Tanner Moushey
	 */
	protected function time_check( $times ) {
		$day       = strtolower( date( 'l', current_time( 'timestamp' ) ) );
		$timestamp = current_time( 'timestamp' );
		$buffer    = 8 * MINUTE_IN_SECONDS; // start watching 15 minutes before the start time
		$duration  = 12 * MINUTE_IN_SECONDS; // how long we'll keep checking after the service should have started. Allow for the initial 15 min. 
		
		if ( empty( $times ) ) {
			return false;
		}
		
		foreach( $times as $time ) {
			if ( $day !== $time['day'] ) {
				continue;
			}

			$start = strtotime( 'today ' . $time['time'], current_time( 'timestamp' ) ) - $buffer;
			$end   = $start + $duration + $buffer;
			
			// if we fall in the window, continue with the check
			if ( $timestamp > $start && $timestamp < $end ) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Delete transient that gets live check
	 * 
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public function flush_cache() {
		delete_site_transient( 'cp_sites_to_check' );
	}
	
	public function live_video_cb() {
		global $wp_embed;
		$sites_live_video = get_site_option( 'cp_sites_live_video', [] );

		if ( empty( $sites_live_video ) ) {
			return __( 'No live feeds were found', 'cp-theme-default' );
		}

		$output = '<div class="cp-live-container">';
		
		$location_id = apply_filters( 'cp_live_video_location_id_default', get_query_var( 'cp_location_id' ) );
		
		if ( $location_id ) {
			if ( empty( $sites_live_video[ $location_id ] ) ) {
				return __( 'No live feeds were found', 'cp-theme-default' );
			} else {
				$output .= $wp_embed->autoembed( sprintf( 'https://youtube.com/watch?v=%s', urlencode( $sites_live_video[ $location_id ] ) ) );
			}
		} else {
			foreach( $sites_live_video as $location_id => $feed ) {
				$output .= sprintf( '<hr /><div class="cp-live-location ast-row"><div class="cp-live-location--video ast-grid-common-col ast-width-md-6">%s</div><div class="cp-live-location--info ast-width-md-6 ast-grid-common-col">%s</div></div>', 
					$wp_embed->autoembed( sprintf( 'https://youtube.com/watch?v=%s', urlencode( $feed ) ) ),
					sprintf( '<h3><a href="%s">%s</a></h3><p>%s</p>', get_permalink( $location_id ) . '/live/', get_the_title( $location_id ), do_shortcode( "[cp-location-data field=service_times location=$location_id]" ) ),
				);
			} 
		}
		
		$output .= '</div>';
		
		return $output;
	}
	
	/**
	 * Add meta for live stream
	 * 
	 * @param $cmb
	 * @param $object
	 *
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public function location_meta() {
		$cmb = new_cmb2_box( [
			'id' => 'location_live_meta',
			'title' => __( 'Live Video', 'cp-locations' ),
			'object_types' => [ cp_locations()->setup->post_types->locations->post_type ],
			'context' => 'normal',
			'priority' => 'high',
			'show_names' => true,
		] );
		
		$cmb->add_field( [
			'name'        => __( 'YouTube Channel ID', 'cp-theme-default' ),
			'id'          => 'youtube_channel_id',
			'type'        => 'text',
			'description' => __( 'Used to identify if the channel is live.', 'cp-theme-default' ),
		], 5 );		

		$cmb->add_field( [
			'name'        => __( 'YouTube API Key', 'cp-theme-default' ),
			'id'          => 'youtube_api_key',
			'type'        => 'text',
			'description' => __( 'Used to connect to the YouTube API.', 'cp-theme-default' ),
		], 5 );		

		$cmb->add_field( [
			'name'        => __( 'Live Video Duration', 'cp-theme-default' ),
			'id'          => 'live_video_duration',
			'type'        => 'text',
			'description' => __( 'How many hours to show this location as live once the video has started.', 'cp-theme-default' ),
		], 5 );
	}	
}

Live::get_instance();