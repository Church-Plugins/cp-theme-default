<?php

namespace Church;

/**
 * Provides the global $arms_directory object
 *
 * @author tanner moushey
 */
class Init {

	/**
	 * @var
	 */
	protected static $_instance;

	/**
	 * @var \WPackio\Enqueue
	 */
	public $enqueue;

	/**
	 * Only make one instance of Init
	 *
	 * @return Init
	 */
	public static function get_instance() {
		if ( ! self::$_instance instanceof Init ) {
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
		
		Custom::get_instance();
		Shortcodes::get_instance();
		Live::get_instance();
		Integrations\BB::get_instance();
		Integrations\TinyMCE::get_instance();
		Integrations\EventsCalendar::get_instance();
	}

	/**
	 * Actions and Filters
	 *
	 * @return void
	 */
	protected function actions() {
		/** API */
		$this->enqueue = new \WPackio\Enqueue( $this->get_id(), 'dist', $this->get_version(), 'theme', false, 'child' );

		// enqueue our stuff before Astra so that our stylesheet is before inline styles output by Astra
		add_filter( 'astra_enqueue_theme_assets', [ $this, 'enqueue_main_style' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ], 2 );
		
		add_filter( 'astra_customizer_configurations', [ $this, 'astra_customizer' ], 50, 2 );
		add_filter( 'astra_theme_dynamic_css', function( $css ) {
			return $css;
		});
		
		add_filter( 'astra_render_fonts', function() { return []; } );
		add_filter( 'astra_get_option_array', [ $this, 'astra_options'], 10, 3 );
		
		add_filter( 'cpl_topic_object_types', function( $types ) { return [ 'cpl_item', 'cpl_item_type' ]; } );
		add_action( 'astra_render_mobile_header_column', [ $this, 'add_mobile_logo_link_action' ], 5 );
		add_action( 'astra_render_mobile_header_column', [ $this, 'remove_mobile_logo_link_action' ], 15 );
	}

	/** Actions **************************************/

	/**
	 * Add logo link filter 
	 * 
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public function add_mobile_logo_link_action() {
		add_action( 'astra_logo', [ $this, 'mobile_logo_link_action' ] );
	}

	/**
	 * Remove logo link filter
	 * 
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public function remove_mobile_logo_link_action() {
		remove_action( 'astra_logo', [ $this, 'mobile_logo_link_action' ] );
	}

	/**
	 * Filter the logo link on location pages
	 * 
	 * @param $html
	 *
	 * @return array|mixed|string|string[]
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public function mobile_logo_link_action( $html ) {
		if ( ! $location_id = get_query_var( 'cp_location_id' ) ) {
			return $html;
		} 

		return str_replace( home_url( '/' ), get_the_permalink( $location_id ), $html );
	}
	
	/**
	 * @param $options_array
	 * @param $option
	 * @param $default
	 *
	 * @return mixed
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public function astra_options( $options_array, $option, $default ) {
		$options_array['font-size-h1'] = [
			'desktop'      => '3.052',
			'tablet'       => '3.052',
			'mobile'       => '2.25',
			'desktop-unit' => 'em',
			'tablet-unit'  => 'em',
			'mobile-unit'  => 'em',
		];

		$options_array['font-size-h2'] = [
			'desktop'      => '2.441',
			'tablet'       => '2.441',
			'mobile'       => '1.8',
			'desktop-unit' => 'em',
			'tablet-unit'  => 'em',
			'mobile-unit'  => 'em',
		];

		$options_array['font-size-h3'] = [
			'desktop'      => '1.953',
			'tablet'       => '1.953',
			'mobile'       => '1.44',
			'desktop-unit' => 'em',
			'tablet-unit'  => 'em',
			'mobile-unit'  => 'em',
		];

		$options_array['font-size-h4'] = [
			'desktop'      => '1.563',
			'tablet'       => '1.563',
			'mobile'       => '1.563',
			'desktop-unit' => 'em',
			'tablet-unit'  => 'em',
			'mobile-unit'  => 'em',
		];

		$options_array['font-size-h5'] = [
			'desktop'      => '1.25',
			'tablet'       => '1.25',
			'mobile'       => '1.25',
			'desktop-unit' => 'em',
			'tablet-unit'  => 'em',
			'mobile-unit'  => 'em',
		];

		$options_array['font-size-h6'] = [
			'desktop'      => '1',
			'tablet'       => '1',
			'mobile'       => '1',
			'desktop-unit' => 'em',
			'tablet-unit'  => 'em',
			'mobile-unit'  => 'em',
		];

		$options_array['body-font-family'] = "'Futura-PT',futura-pt,sans-serif";
		$options_array['headings-font-family'] = "'Futura-PT',futura-pt,sans-serif";
		
		return $options_array;
	}
	
	/**
	 * Enqueue the main stylesheet
	 * 
	 * @param $return
	 *
	 * @return mixed
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public function enqueue_main_style( $return ) {
		$this->enqueue->enqueue( 'theme', 'styles', [ 'in_footer' => false ] );
		$this->enqueue->enqueue( 'theme', 'scripts', [ 'in_footer' => true ] );

		return $return;
	}

	/**
	 * Update Astra to use blank stylesheet since we are overwriting it all
	 * 
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public function enqueue_scripts() {
		$wp_styles = wp_styles();

		// update astra to use our custom css
		if ( isset( $wp_styles->registered['astra-theme-css'] ) ) {
			$wp_styles->registered['astra-theme-css']->src = get_stylesheet_uri();
		}
		
		$this->enqueue->enqueue( 'theme', 'dynamic', [ 'in_footer' => false ] );

	}
	
	public function astra_customizer( $config, $customizer ) {
		
		foreach( $config as $key => $item ) {
			if ( isset( $item['name'] ) && 'section-typography' === $item['name'] ) {
//				unset( $config[ $key ] );
			}
		}
		return $config;
	}

	/**
	 * Returns the theme name, localized
	 *
	 * @return string the theme name
	 * @since 1.0.0
	 */
	public function get_theme_name() {
		return __( 'cp', 'cp' );
	}

	/**
	 * Provide a unique ID tag for the plugin
	 *
	 * @return string
	 */
	public function get_id() {
		return 'cp';
	}

	/**
	 * The version for this theme
	 * 
	 * @return string
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public function get_version() {
		return '1.0.0';
	}

	/**
	 * Get the API namespace to use
	 *
	 * @return string
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public function get_api_namespace() {
		return $this->get_id() . '/v1';
	}

}