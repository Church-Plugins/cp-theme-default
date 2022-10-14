<?php

namespace Church\Integrations;

class TinyMCE {

	/**
	 * @var
	 */
	protected static $_instance;

	/**
	 * Only make one instance of TinyMCE
	 *
	 * @return TinyMCE
	 */
	public static function get_instance() {
		if ( ! self::$_instance instanceof TinyMCE ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Class constructor: Add Hooks and Actions
	 *
	 */
	protected function __construct() {
		add_filter( 'mce_buttons', [ $this, 'hooc_mce_buttons_2' ] );
		add_action( 'mce_css', [ $this, 'hook_mce_css' ] );
		add_filter( 'tiny_mce_before_init', [ $this, 'hook_tiny_mce_before_init' ] );
		add_filter( 'wp_default_editor', [ $this, 'filter_default_editor' ], 9999 );

		//add_filter( 'user_can_richedit', [ $this, 'page_can_richedit' ] );	
	}

	/**
	 * Load custom editor CSS style sheet
	 *
	 * @param $mce_css
	 *
	 * @return string
	 */
	public function hook_mce_css( $mce_css ) {
		if ( ! empty( $mce_css ) ) {
			$mce_css .= ',';
		}
		$mce_css .= get_stylesheet_directory_uri() . '/dist/editor-styles.css?breaker=23489,';

//		$mce_css .= '//fonts.googleapis.com/css?family=Open+Sans:400|Open+Sans:300|Open+Sans:600|Open+Sans:700';

		return $mce_css;
	}


	/**
	 * Add a custom style dropdown
	 *
	 * @param $buttons
	 *
	 * @return mixed
	 */
	public function hooc_mce_buttons_2( $buttons ) {

		if ( in_array( 'formatselect', $buttons ) ) {
			unset( $buttons[ array_search( 'formatselect', $buttons ) ] );
		}

		array_unshift( $buttons, 'styleselect' );

		return $buttons;
	}


	public function hook_tiny_mce_before_init( $settings ) {

		// Add formats
		// From http://tinymce.moxiecode.com/examples/example_24.php
		$style_formats = array(
			array(
				'title' => 'Headers',
				'items' => array(
//					array(
//						'title'    => 'Headline',
//						'selector' => 'p,li,h1,h2,h3,h4,h5,h6,h7,span,a',
//						'classes'  => 'headline',
//					),
					array(
						'title' => 'Header 1',
						'block' => 'h1',
					),
					array(
						'title' => 'Header 2',
						'block' => 'h2',
					),
					array(
						'title' => 'Header 3',
						'block' => 'h3',
					),
					array(
						'title' => 'Header 4',
						'block' => 'h4',
					),
					array(
						'title' => 'Header 5',
						'block' => 'h5',
					),
					array(
						'title' => 'Header 6',
						'block' => 'h6',
					),
				),
			),
			array(
				'title' => 'Paragraph',
				'items' => array(
					array(
						'title'   => 'X-Large',
						'block'   => 'p',
						'classes' => 'text-xlarge',
					),
					array(
						'title'   => 'Large',
						'block'   => 'p',
						'classes' => 'text-large',
					),
					array(
						'title'   => 'Small',
						'block'   => 'p',
						'classes' => 'text-small',
					),
					array(
						'title'   => 'X-Small',
						'block'   => 'p',
						'classes' => 'text-xsmall',
					),
				),
			),
		);

		$settings['style_formats'] = json_encode( $style_formats );

		// Add colors

		$settings['textcolor_map'] = '[
			"171717", "Primary",
			"5F5B64", "Secondary",
			"76737A", "Tertiary",
			"A2A1A4", "Disabled",
			"51315B", "Success",
			"1FC2A4", "Info",
			"CE8E2C", "Warn",
			"F24441", "Danger",
			"108599", "Brand Primary",
			"F6F0ED", "Brand Secondary",
		]';
		
		$settings['toolbar2'] = str_replace( 'fontsizeselect,', '', $settings['toolbar2'] );

		return $settings;
	}


	/**
	 * Set the visual editor as default
	 *
	 * @return string
	 */
	public function filter_default_editor() {
		return 'tinymce';
	}


	/**
	 * Maybe Remove rich text editor
	 *
	 * @param $can
	 *
	 * @return bool
	 */
	public function page_can_richedit( $can ) {
		return $can;
	}

}

