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


function cp_register_account_widget_area() {
	register_sidebar( array(
		'name'          => esc_html__( 'Account Sidebar', 'cp-theme-default' ),
		'id'            => 'sidebar-account',
		'description'   => esc_html__( 'Add widgets here.', 'cp-theme-default' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
	) );
}
add_action( 'widgets_init', 'cp_register_account_widget_area' );