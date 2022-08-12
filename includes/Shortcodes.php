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
	
	public function location_dropdown_cb() {
		if ( ! class_exists( 'CP_Locations\Models\Location' ) ) {
			return '';
		}

		$location_id = get_query_var( 'cp_location_id' );
		
		if ( $location = \CP_Locations\Setup\Taxonomies\Location::get_rewrite_location() ) {
			$location_id = $location['ID'];
		}
		
		do_action( 'cploc_multisite_switch_to_main_site' );
		$locations = \CP_Locations\Models\Location::get_all_locations( true );
		
		ob_start(); ?>

		<div class="dropdown is-right cp-location-dropdown">
			<div class="dropdown-trigger">
				<a href="#" class="cp-button is-transparent is-large is-em" aria-haspopup="true" aria-controls="dropdown-menu6">
					<?php if ( $location_id ) : ?>
						<i data-feather="map-pin" class="is-small" aria-hidden="true"></i>
						<span class="text-small"><?php echo get_the_title( $location_id ); ?></span>
					<?php else : ?>
						<span class="text-small"><?php _e( 'Select a Location', 'cp-theme-default'); ?></span>
					<?php endif; ?>
					<i data-feather="chevron-down" aria-hidden="true"></i>
				</a>
			</div>
			<div class="dropdown-menu" role="menu">
				<div class="dropdown-content">
					<div class="dropdown-item">
						<?php foreach ( $locations as $location ) :
							try {
								$loc = new \CP_Locations\Controllers\Location( $location->ID, true );
							} catch ( Exception $e ) {
								error_log( $e );
								continue;
							}
						?>
							<a href="<?php echo get_the_permalink( $location->ID ); ?>" class="cp-location-dropdown--item">
								<div class="cp-location-dropdown--item--thumb">
									<?php if ( ! empty( $loc->get_thumbnail()['thumbnail'] ) ) : ?>
										<img alt="location thumbnail" src="<?php echo $loc->get_thumbnail()['thumbnail']; ?>" />
									<?php endif; ?>
								</div>
								
								<div class="cp-location-dropdown--item--content">
									<div class="cp-location-dropdown--item--title"><?php echo get_the_title( $location->ID ); ?></div>
									<div class="cp-location-dropdown--item--desc text-xsmall"><?php echo $loc->pastor; ?></div>
								</div>
							</a>
						<?php endforeach; ?>
						
						<a class="cp-button is-fullwidth is-em is-small" href="<?php echo get_home_url(); ?>/locations"><?php _e( 'View on Map', 'cp-theme-default' ); ?></a>
					</div>
				</div>
			</div>
		</div>		
		
		<?php
		do_action( 'cploc_multisite_restore_current_blog' );
		return ob_get_clean();
	}
	
}