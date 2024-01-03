<?php

namespace Church;


use ChurchPlugins\Exception;

/**
 * Provides the global $arms_directory object
 *
 * @author tanner moushey
 */
class Shortcodes {

	/**
	 * @var
	 */
	protected static $_instance;

	/**
	 * Only make one instance of Shortcodes
	 *
	 * @return Shortcodes
	 */
	public static function get_instance() {
		if ( ! self::$_instance instanceof Shortcodes ) {
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
		add_shortcode( 'cp-location-header', [ $this, 'location_header_cb' ] );
		add_shortcode( 'cp-location-dropdown', [ $this, 'location_dropdown_cb' ] );
	}

	/** Actions **************************************/

	public function location_header_cb() {
		if ( ! $location_id = get_query_var( 'cp_location_id' ) ) {
			return '';
		}
		
		return sprintf( '<a href="%s" class="cp-location-header">%s</a>', get_permalink( $location_id ), get_the_title( $location_id ) );
	}
	
	public function location_dropdown_cb( $atts ) {
		if ( ! class_exists( 'CP_Locations\Models\Location' ) ) {
			return '';
		}

		$atts = shortcode_atts( [
			'position'        => 'right',
			'button-class'    => 'is-transparent is-large is-em',
			'show-map'        => true,
			'button-position' => 'bottom',
			'relative'        => 'false',
			'include'         => '',
			'default-text'    => __( 'Select a Location', 'cp-theme-default'),
		], $atts, 'cp-location-dropdown' );
		
		$include     = array_filter( array_map( 'trim', explode( ',', $atts['include'] ) ) );
		$location_id = get_query_var( 'cp_location_id' );
		$relative    = $atts['relative'] !== 'false';

		$show_button = ! empty( $atts['show-map'] ) && 'false' !== $atts['show-map'];
		
		if ( $location = \CP_Locations\Setup\Taxonomies\Location::get_rewrite_location() ) {
			$location_id = $location['ID'];
		}
		
		$url_base = empty( $location_id ) ? trailingslashit( get_home_url() ) : get_permalink( $location_id ); 
		
		do_action( 'cploc_multisite_switch_to_main_site' );
	
		$locations = (
			$relative ?
			array() :
			\CP_Locations\Models\Location::get_all_locations( true )
		);

		if ( $relative ) {
			$page_path = str_replace( $url_base, '', get_home_url() . $_SERVER['REQUEST_URI'] );
			$page_path = trim( $page_path, '/' );

			// get all pages with the same slug
			$pages = get_posts( [
				'post_type'      => 'page',
				'posts_per_page' => 999,
				'name'           => $page_path,
			] );

			foreach( $pages as $page ) {
				if ( $page->ID === get_the_ID() ) {
					continue;
				}

				// build a path string out of page ancestors and make sure pages have the same path
				$ancestors   = array_reverse( get_post_ancestors( $page->ID ) );
				$ancestors   = wp_list_pluck( array_map( 'get_post', $ancestors ), 'post_name' );
				$ancestors[] = $page->post_name;

				if( $page_path !== implode( '/', $ancestors ) ) {
					continue;
				}

				// get all locations the page is assigned to
				$page_locations = get_terms( [
					'taxonomy'   => 'cp_location',
					'hide_empty' => false,
					'object_ids' => [ $page->ID ],
				] );

				foreach( $page_locations as $page_location ) {
					preg_match( '/^location_(\d+)$/', $page_location->slug, $matches );
					$page_id = absint( $matches[1] );

					if( ! $page_id ) {
						continue;
					}

					// check if location is already in the list
					if ( count( array_filter( $locations, fn( $loc ) => $loc->ID === $page_id ) ) ) {
						continue;
					}

					if( $new_location = get_post( $page_id ) ) {
						$locations[] = $new_location;
					}
				}
				
			}
		}

		$button_html = sprintf(
			'<a class="cp-button is-fullwidth is-em is-small" href="%s/locations">
				<span class="material-icons">fmd_good</span>&nbsp;%s
			</a>',
			esc_url( get_home_url() ),
			esc_html__( 'View on Map', 'cp-theme-default' ),
		);
		
		ob_start(); ?>

		<div class="dropdown is-<?php echo $atts['position']; ?> cp-location-dropdown">
			<div class="dropdown-trigger">
				<a href="#" class="cp-button <?php echo $atts['button-class']; ?>" aria-haspopup="true" aria-controls="location-menu">
					<?php if ( $location_id ) : ?>
						<i data-feather="map-pin" class="is-small" aria-hidden="true"></i>
						<span class="text-small"><?php echo get_the_title( $location_id ); ?></span>
					<?php else : ?>
						<span class="text-small"><?php echo $atts['default-text']; ?></span>
					<?php endif; ?>
					<i data-feather="chevron-down" aria-hidden="true"></i>
				</a>
			</div>
			<div class="dropdown-menu" role="menu">
				<div class="dropdown-content">
					<div class="dropdown-item button-position-<?php echo $atts['button-position'] === 'bottom' ? 'bottom' : 'top'; ?>">
						<?php if ( $show_button && 'top' === $atts['button-position'] ) : ?>
							<?php echo $button_html; ?>
						<?php endif; ?>

						<?php foreach ( $locations as $location ) :
							
							if ( ! empty( $include ) && ! in_array( $location->ID, $include ) ) {
								continue;
							}
							
							$link = $relative ? get_the_permalink( $location->ID ) : str_replace( $url_base, get_the_permalink( $location->ID ), get_home_url() . $_SERVER['REQUEST_URI'] );
							
							try {
								$loc = new \CP_Locations\Controllers\Location( $location->ID, true );
							} catch ( Exception $e ) {
								error_log( $e );
								continue;
							}
						?>
							<a href="<?php echo $link; ?>" class="cp-location-dropdown--item">
								<div class="cp-location-dropdown--item--thumb">
									<?php if ( ! empty( $loc->get_thumbnail()['thumbnail'] ) ) : ?>
										<img alt="location thumbnail" src="<?php echo $loc->get_thumbnail()['thumbnail']; ?>" />
									<?php endif; ?>
								</div>
								
								<div class="cp-location-dropdown--item--content">
									<div class="cp-location-dropdown--item--title"><?php echo get_the_title( $location->ID ); ?></div>
									<div class="cp-location-dropdown--item--desc text-xsmall"><?php echo $loc->subtitle; ?></div>
								</div>
							</a>
						<?php endforeach; ?>

						<?php if ( $show_button && 'bottom' === $atts['button-position'] ) : ?>
							<?php echo $button_html; ?>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>		
		
		<?php
		do_action( 'cploc_multisite_restore_current_blog' );
		return ob_get_clean();
	}
	
}
