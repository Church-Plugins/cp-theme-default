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
		add_shortcode( 'cp-paylocity-positions', [ $this, 'paylocity_positions' ] );
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
			'position'     => 'right',
			'button-class' => 'is-transparent is-large is-em',
			'show-map'     => true,
			'relative'     => 'false',
			'include'      => '',
			'default-text' => __( 'Select a Location', 'cp-theme-default'),
		], $atts, 'cp-location-dropdown' );
		
		$include = array_filter( array_map( 'trim', explode( ',', $atts['include'] ) ) );
		$location_id = get_query_var( 'cp_location_id' );
		
		if ( $location = \CP_Locations\Setup\Taxonomies\Location::get_rewrite_location() ) {
			$location_id = $location['ID'];
		}
		
		$url_base = empty( $location_id ) ? trailingslashit( get_home_url() ) : get_permalink( $location_id ); 
		
		do_action( 'cploc_multisite_switch_to_main_site' );
		$locations = \CP_Locations\Models\Location::get_all_locations( true );
		
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
					<div class="dropdown-item">
						<?php foreach ( $locations as $location ) :
							
							if ( ! empty( $include ) && ! in_array( $location->ID, $include ) ) {
								continue;
							}
							
							$link = ( 'false' === $atts['relative'] ) ? get_the_permalink( $location->ID ) : str_replace( $url_base, get_the_permalink( $location->ID ), get_home_url() . $_SERVER['REQUEST_URI'] );
							
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
						
						<?php if ( ! empty( $atts['show-map'] ) && 'false' !== $atts['show-map'] ) : ?>
							<a class="cp-button is-fullwidth is-em is-small" href="<?php echo get_home_url(); ?>/locations"><?php _e( 'View on Map', 'cp-theme-default' ); ?></a>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>		
		
		<?php
		do_action( 'cploc_multisite_restore_current_blog' );
		return ob_get_clean();
	}
	
	public function paylocity_positions( ) {
		$api_key = 'c820b098-d595-489a-9e33-b30120132b5b';
		$base_url = 'https://recruiting.paylocity.com/recruiting/v2/api/feed/jobs/';

		$custom_logo_id = get_theme_mod( 'custom_logo' );
    $image = wp_get_attachment_image_src( $custom_logo_id , 'full' );
		$image = $image[0];
		$error = null;

		$res = @file_get_contents( $base_url . $api_key );

		if( ! $res ) {
			$error = esc_html__( 'Unable to load positions', 'cp-theme-default' );
		}

		try {
			$data = json_decode( $res );
		}
		catch(Exception $err) {
			$error = esc_html__( 'There was a problem loading data', 'cp-theme-default' );
		}

		ob_start(); ?>
		
		<div class="cp-paylocity-positions">
			<div class="cp-paylocity-logo-wrapper">
				<img class="cp-paylocity-logo" src="<?php echo $image ?>">
			</div>

			<div id='cp-paylocity-positions'>
				<?php if( $error ) {
					echo esc_html( $error );
				} ?>
			</div>

			<script>	
				jQuery(($) => {
					const data = <?php echo $res ? json_encode( $data ) : 'null' ?>;
					const content = $('#cp-paylocity-positions')

					if(data) {
						displayList()
					}

					function displayPosition(id) {
						const position = data.jobs.find(job => job.jobId === id)
						content.html('')
						const $item = $(`<div class="cp-paylocity-position-details"></div>`);
						$header = $(`<div class="cp-paylocity-position-header"></div>`)
						$header.append(`<div>
							<h3>${position.title}</h3>
							<div>${position.jobLocation.locationDisplayName} / ${position.hiringDepartment}</div>
						</div>`)
						$applyBtn = $(`<a class="cp-button" target="_blank" href="${position.applyUrl}"><?php esc_html_e( 'Apply', 'cp-theme-default' ) ?></a>`)
						$header.append($applyBtn)
						$item.append($header)
						$item.append(`<p>${position.description}</p>`)
						$backBtn = $(`<button class="cp-button is-text cp-paylocity-back-btn"><span class="material-icons">arrow_back_ios</span><?php esc_html_e( 'View All Jobs', 'cp-theme-default' ) ?></button>`);
						$backBtn.on('click', displayList)
						$item.append($backBtn)
						content.append($item)
					}

					function displayList() {
						content.html('')
						data.jobs.forEach(position => {
							const date = new Date(position.publishedDate)
							const formattedDate = getFormattedDate(date)
							const $item = $(`<div class="cp-paylocity-position">
								<div>
									<h5 class="cp-paylocity-position--title">${position.title}</h5>
									<div>${formattedDate} - ${position.hiringDepartment}</div>
								</div>
								<div class="cp-paylocity-position--location">${position.jobLocation.locationDisplayName}</div>
							</div>`);
							$item.on('click', () => {
								displayPosition(position.jobId)
							})
							content.append($item)
						})
					}

					function getFormattedDate(date) {
							let year = date.getFullYear();
							let month = (1 + date.getMonth()).toString().padStart(2, '0');
							let day = date.getDate().toString().padStart(2, '0');
						
							return month + '/' + day + '/' + year;
					}
				})
			</script>


		</div>

		<?php
		return ob_get_clean();
	}
}