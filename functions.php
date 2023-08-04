<?php
/**
 * Church Plugins - Default Theme Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Church Plugins - Default Theme
 * @since 1.0.0
 */

/**
 * Return base instance of Church functionality
 * 
 * @return cp\Init
 * @since  1.0.0
 *
 */
include_once( 'vendor/autoload.php' );
function cp() {
    return Church\Init::get_instance();
}
cp();
/**
 * Define Constants
 */
define( 'CHILD_THEME_CHURCH_PLUGINS_DEFAULT_THEME_VERSION', '1.0.0' );

/**
 * Enqueue styles
 */
function child_enqueue_styles() {

	wp_enqueue_style( 'church-plugins-default-theme-theme-css', get_stylesheet_directory_uri() . '/style.css', array('astra-theme-css'), CHILD_THEME_CHURCH_PLUGINS_DEFAULT_THEME_VERSION, 'all' );
    wp_enqueue_style( 'leafletcss', get_stylesheet_directory_uri() . '/assets/css/leaflet.css', array(), CHILD_THEME_CHURCH_PLUGINS_DEFAULT_THEME_VERSION, 'all' );

    wp_enqueue_script( 'leafletjs', get_stylesheet_directory_uri() . '/assets/js/leaflet.js', array(), CHILD_THEME_CHURCH_PLUGINS_DEFAULT_THEME_VERSION, 'all' );


}

add_action( 'wp_enqueue_scripts', 'child_enqueue_styles', 15 );


/**
 * Adds the MP Event ID to the URL so that MP Widgets can access the event ID
 */
function cp_add_ministry_platform_event_id() {
	if( is_singular( 'tribe_events' ) ) {
		if( isset( $_GET['id'] ) ) {
			return;
		}

		$event_id = \Tribe__Events__Main::postIdHelper( get_the_ID() );
		$mpp_event_id = get_post_meta( $event_id, '_chms_id', true );

		if( $mpp_event_id ) {
			wp_redirect( add_query_arg( 'id', $mpp_event_id, get_the_permalink() ), 302 );
			exit;
		}
	}
}

add_action( 'template_redirect', 'cp_add_ministry_platform_event_id' );
