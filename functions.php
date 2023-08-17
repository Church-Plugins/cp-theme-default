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


function child_enqueue_scripts() {
	wp_enqueue_script( 'mp-widgets', 'https://my.northway.org/widgets/dist/MPWidgets.js' );

	wp_register_script( 'mp-auth', get_stylesheet_directory_uri() . '/assets/js/mp-auth.js', array( 'jquery' ) );

	wp_localize_script( 'mp-auth', 'mpAuth', array(
		'loggedInMenu' => array(
			array( 'text' => esc_html__( 'About', 'cp-theme-default' ),   'url' => cp_get_permalink_from_slug( 'about' ) ),
			array( 'text' => esc_html__( 'Contact', 'cp-theme-default' ), 'url' => cp_get_permalink_from_slug( 'contact' ) ),
			array( 'text' => esc_html__( 'Account', 'cp-theme-default' ), 'url' => cp_get_permalink_from_slug( 'account' ) ),
			array( 'text' => esc_html__( 'Log Out', 'cp-theme-default' ), 'url' => add_query_arg( 'action', 'logout', cp_get_permalink_from_slug( 'account' ) ) ),
		),
		'loggedOutMenu' => array(
			array( 'text' => esc_html__( 'About', 'cp-theme-default' ),   'url' => cp_get_permalink_from_slug( 'about' ) ),
			array( 'text' => esc_html__( 'Contact', 'cp-theme-default' ), 'url' => cp_get_permalink_from_slug( 'contact' ) ),
			array( 'text' => esc_html__( 'Account', 'cp-theme-default' ), 'url' => cp_get_permalink_from_slug( 'account' ) ),
			array( 'text' => esc_html__( 'Log In', 'cp-theme-default' ),  'login' => true ),
		)
	) );

	wp_enqueue_script( 'mp-auth' );
}
add_action( 'wp_enqueue_scripts', 'child_enqueue_scripts' );



function cp_auth_menu_shortcode() {
	return '<ul class="mp-auth-menu"></ul>';
}

add_shortcode( 'mp_auth_menu', 'cp_auth_menu_shortcode' );


/**
 * Get a page permalink from slug
 */
function cp_get_permalink_from_slug( $slug ) {
	$page = get_page_by_path( $slug );

	if( $page ) {
		return esc_url( get_permalink( $page->ID ) );
	}

	return '/' . $slug;
}

/**
 * Changes the MPWidgets script tag ID so that it can be targeted by MPWidgets
 */
function child_custom_script_attributes( $tag, $handle, $src ) {
	if ( 'mp-widgets' !== $handle ) {
		return $tag;
	}

	return '<script id="MPWidgets" src="' . esc_url( $src ) . '"></script>';
}
add_action( 'script_loader_tag', 'child_custom_script_attributes', 10, 3 );

/**
 * Ministry Platform widgets receives the event ID from the URL.
 * When visiting an event page, if the event has a _chms_id custom field,
 * the user will be 'redirected' to the same page with a URL parameter added.
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
