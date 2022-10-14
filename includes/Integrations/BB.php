<?php

namespace Church\Integrations;

/**
 * Provides the global $arms_directory object
 *
 * @author tanner moushey
 */
class BB {

	/**
	 * @var
	 */
	protected static $_instance;

	/**
	 * Only make one instance of BB
	 *
	 * @return BB
	 */
	public static function get_instance() {
		if ( ! self::$_instance instanceof BB ) {
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
		add_filter( 'fl_builder_register_settings_form', [ $this, 'settings_form_router' ], 10, 2 );
		
		// Add our skin class to the row element
		add_filter( 'fl_builder_row_custom_class', array( $this, 'add_permissions_class' ), 20, 2 );
		add_filter( 'fl_builder_column_custom_class', array( $this, 'add_permissions_class' ), 20, 2 );
		add_filter( 'fl_builder_module_custom_class', array( $this, 'add_permissions_class' ), 20, 2 );

		// Set default form values
		add_filter( 'fl_builder_settings_form_defaults', array( $this, 'set_defaults' ), 20, 2 );

		// Add color presets from style guide
		add_filter( 'fl_builder_color_presets', array( $this, 'color_presets' ) );

		// Add theme fonts
		add_filter( 'fl_builder_font_families_system', array( $this, 'custom_fonts' ) );

		// Remove Google fonts
		add_filter( 'fl_builder_font_families_google', array( $this, 'no_google_fonts' ) );

		add_filter( 'fl_builder_register_module', array( $this, 'enabled_modules' ), 10, 2 );

		add_filter( 'fl_builder_icon_sets', array( $this, 'custom_icons' ) );

		// Add the button to insert a button into the editor
//		add_action( 'media_buttons', array( $this, 'insert_editor_button' ), 11 );

		// Add modal for editor button settings
//		add_action( 'wp_footer', array( $this, 'add_modal' ) );		

		if ( is_user_logged_in() ) {
			add_filter( 'body_class', [ $this, 'add_role_to_body' ] );
			add_filter( 'admin_body_class', [ $this, 'add_role_to_body' ] );
		}
		
		add_filter( 'fl_builder_loop_taxonomies', [ $this, 'custom_taxonomies' ], 10, 3 );
		
		add_filter( 'fl_builder_loop_query_args', [ $this, 'custom_query_args' ] );		

	}

	/** Actions **************************************/

	/**
	 * Custom query args for Events
	 * 
	 * @param $args
	 *
	 * @return mixed
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public function custom_query_args( $args ) {
		if ( 'tribe_events' == $args['post_type'] ) {
			$args['eventDisplay'] = 'custom';

			if ( empty( $arts['meta_query'] ) ) {
				$args['meta_query'] = [];
			}

			$args['meta_query'][] = [
				'key'     => '_EventStartDate',
				'value'   => current_time( 'Y-m-d H:i' ),
				'compare' => '>=',
			];

			if ( 'date' == $args['orderby'] ) {
				$args['orderby']  = '_EventStartDate';
				$args['meta_key'] = '_EventStartDate';
			}
		}

		return $args;
	}
	
	public function add_role_to_body( $classes ) {
		$current_user = new \WP_User( get_current_user_id() );
		$user_role    = array_shift( $current_user->roles );
		if ( is_admin() ) {
			$classes .= 'role-' . $user_role;
		} else {
			$classes[] = 'role-' . $user_role;
		}
		
		if ( function_exists( 'cp_locations' ) ) {
			$location_class = []; 
			$locations = cp_locations()->setup->permissions::get_user_locations();
			
			if ( empty( $locations ) ) {
				$location_class[] = 'locations-all'; 
			} else {
				$location_class[] = 'locations-restricted'; 
				
				foreach( $locations as $location_id ) {
					$locations[] = "location-$location_id";
				}
			}

			if ( is_admin() ) {
				$classes .= implode( ' ', $location_class );
			} else {
				$classes = array_merge( $classes, $location_class );
			}
		}

		return $classes;
	}

	public function settings_form_router( $form, $id ) {
		switch( $id ) {
			case 'row' :
				$form['tabs']['style']['sections']['general']['fields']['max_content_width']['units'][] = 'rem';
				$form['tabs']['advanced']['sections'] = $this->array_insert( $this->permissions_setting(), 'permissions', $form['tabs']['advanced']['sections'], 'margins' );
				break;
			case 'module_advanced' :
				$form['sections']['margins']['fields']['margin']['units'][] = 'rem';
				break;
			case 'global' :
				$form['tabs']['general']['sections']['modules']['fields']['module_margins']['default'] = '1';
				$form['tabs']['general']['sections']['modules']['fields']['module_margins']['units'][] = 'rem';
				$form['tabs']['general']['sections']['rows']['fields']['row_width']['units'][] = 'rem';
				break;
		}
		
		return $form;
	}

	/**
	 * Insert an item into an array after the specified key
	 * 
	 * @param $item
	 * @param $item_key
	 * @param $array
	 * @param $after_key
	 *
	 * @return array
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	private function array_insert( $item, $item_key, $array, $after_key ) {
		$return_array = [];
		
		foreach( $array as $key => $array_item ) {
			$return_array[ $key ] = $array_item;
			
			if ( $key === $after_key ) {
				$return_array[ $item_key ] = $item;
			}
		}
		
		return $return_array;
	}
	
	/**
	 * Add theme fonts
	 *
	 * @param $system_fonts
	 *
	 * @return array
	 */
	public function custom_fonts( $system_fonts ) {
		return array(
			'Roboto'            => array(
				'fallback' => 'Arial, sans-serif',
				'weights'  => array(
					'300',
					'400',
					'600',
					'700'
				),
			),
			'Montserrat' => array(
				'fallback' => 'Roboto, sans-serif',
				'weights'  => array(
					'300',
					'400',
					'600',
					'700'
				),
			),
		);
	}

	/**
	 * Remove Google fonts
	 *
	 * @param $google_fonts
	 *
	 * @return array
	 */
	public function no_google_fonts( $google_fonts ) {
		return array();
	}

	public function add_modal() {

		if ( \FLBuilderModel::is_builder_enabled() && isset( $_GET['fl_builder'] ) ) {
			include_once( 'editor-button-template.php' );
		}

	}

	public function insert_editor_button() {

		// Make sure we are on a builder page first!
		if ( \FLBuilderModel::is_builder_enabled() ) {
			echo '<button type="button" class="button sv-insert-button">Add Button</button>';
		}
	}

	/**
	 * Add our own custom color presets to BB
	 *
	 * @param $colors
	 *
	 * @return array
	 */
	public function color_presets( $colors ) {
		return array(

			// grayscale
			'FFFFFF',
			'000000',
			'171717',
			'5F5B64',
			'76737A',
			'A2A1A4',
			
			// ui colors
			'108599',
			'1D5A5c',
			'CCD6D6',

			// alert colors
			'51315B',
			'1FC2A4',
			'CE8E2C',
			'F24441',
		);
	}

	public function set_defaults( $defaults, $form_type ) {

		if ( 'row' !== $form_type ) {
			return $defaults;
		}

		// We need to add a default setting for the bg_type since we removed it so we don't get errors
		$defaults->bg_type = '';

		return $defaults;
	}

	/**
	 * Uses the BB hook to add our skin class to the row element
	 *
	 * @param $class
	 * @param $row
	 *
	 * @return mixed
	 */
	public function add_permissions_class( $class, $row ) {
		$class = ( isset( $row->settings->user_roles ) ? $class . ' permissions-role-' . $row->settings->user_roles : $class );
		$class = ( isset( $row->settings->user_location ) ? $class . ' permissions-loc-' . $row->settings->user_location : $class );
		return $class;
	}

	/**
	 * Disable modules that we don't need right now
	 *
	 * @param $enabled
	 * @param $instance
	 *
	 * @return bool
	 * @author Tanner Moushey
	 */
	public function enabled_modules( $enabled, $instance ) {
		if ( in_array( $instance->slug, array( 'post-carousel', 'numbers', 'post-slider', 'testimonials', 'content-slider', 'countdown', 'heading', 'contact-form' ) ) ) {
			return false;
		}

		return $enabled;
	}

	/**
	 * Add custom icons
	 * 
	 * @param $sets
	 *
	 * @return array
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public function custom_icons( $sets ) {

		$sets['feather'] = [
			'name'       => 'Feather',
			'prefix'     => '',
			'path'       => get_stylesheet_directory() . '/assets/fonts/feather/',
			'url'        => get_stylesheet_directory_uri() . '/assets/fonts/feather/',
			'stylesheet' => false,
			'type'       => 'core',
			'icons'      => [],
		];

		$data  = json_decode( file_get_contents( $sets['feather']['path'] . 'selection.json' ) );

		if ( isset( $data->icons ) ) {

			foreach ( $data->icons as $icon ) {
				$sets['feather']['icons'][] = 'feather-' . $icon->properties->name;
			}
		}

		return array_reverse( $sets, true );
	}

	private function permissions_setting() {

		$permissions = array(
			'title'  => 'Permissions',
			'fields' => array(
				'user_roles' => array(
					'type'    => 'select',
					'label'   => __( 'Roles', 'fl-builder' ),
					'default' => '',
					'options' => array(
						''              => __( 'All Users', 'cp-theme-default' ),
						'author'        => __( 'Author', 'cp-theme-default' ),
						'editor'        => __( 'Editor', 'cp-theme-default' ),
						'administrator' => __( 'Administrator', 'cp-theme-default' ),
					),
				),
			),
		);
		
		if ( function_exists( 'cp_locations' ) ) {
			$permissions['fields']['user_location'] = [
				'type'    => 'select',
				'label'   => __( 'Team', 'fl-builder' ),
				'default' => '',
				'options' => [
					''     => __( 'All Teams', 'cp-theme-default' ),
					'core' => __( 'Core Team', 'cp-theme-default' ),
				],
			];
		}

		return $permissions;
	}
	
	/**
	 * Store button fields in one place for easy updates
	 *
	 * @return array
	 * @author Tanner Moushey
	 */
	public static function get_button_fields() {
		return array(
			'button_style' => array(
				'type'    => 'select',
				'label'   => __( 'Type', 'fl-builder' ),
				'default' => 'is-solid',
				'options' => array(
					'is-solid'       => 'Solid',
					'is-transparent' => 'Transparent',
					'is-text'        => 'Text',
				),
			),
			'button_size' => array(
				'type'    => 'select',
				'label'   => __( 'Size', 'fl-builder' ),
				'default' => '',
				'options' => array(
					''          => 'Default',
					'is-large'  => 'Large',
					'is-small'  => 'Small',
				),
			),
			'button_width'        => array(
				'type'    => 'select',
				'label'   => __( 'Width', 'fl-builder' ),
				'default' => 'auto',
				'options' => array(
					'auto'   => _x( 'Auto', 'Width.', 'fl-builder' ),
					'full'   => __( 'Full Width', 'fl-builder' ),
					'custom' => __( 'Custom', 'fl-builder' ),
				),
				'toggle'  => array(
					'auto'   => array(
						'fields' => array( 'align' ),
					),
					'full'   => array(),
					'custom' => array(
						'fields' => array( 'align', 'custom_width' ),
					),
				),
			),
			'custom_width' => array(
				'type'    => 'unit',
				'label'   => __( 'Custom Width', 'fl-builder' ),
				'default' => '15',
				'slider'  => array(
					'em' => array(
						'min'  => 0,
						'max'  => 40,
						'step' => .1,
					),
				),
				'units'   => array(
					'em',
					'vw',
					'%',
				),
				'preview' => array(
					'type'     => 'css',
					'selector' => 'a.fl-button',
					'property' => 'width',
				),
			),
			'button_color' => array(
				'type'    => 'select',
				'label'   => __( 'Button Color', 'fl-builder' ),
				'default' => '',
				'options' => array(
					''               => 'Primary',
					'is-light'        => 'Light',
				),
			),
//			'button_text'  => array(
//				'type'    => 'select',
//				'label'   => __( 'Button Text', 'fl-builder' ),
//				'default' => '',
//				'options' => array(
//					''        => 'Normal',
//					'is-caps' => 'Uppercase',
//				),
//			),
			'button_align'        => array(
				'type'       => 'align',
				'label'      => __( 'Align', 'fl-builder' ),
				'default'    => 'left',
				'responsive' => true,
				'preview'    => array(
					'type'     => 'css',
					'selector' => '.fl-button-wrap',
					'property' => 'text-align',
				),
			),
		);
	}

	/**
	 * Some of our custom taxonomies are not included by default
	 * 
	 * @param $data
	 * @param $taxonomies
	 * @param $post_type
	 *
	 * @return mixed
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public function custom_taxonomies( $data, $taxonomies, $post_type ) {
		
		// add locations to the query
		if ( isset( $taxonomies['cp_location'] ) ) {
			$data['cp_location'] = $taxonomies['cp_location'];
		}
		
		return $data;
	}
}