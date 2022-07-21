<?php

/**
 * @class FLButtonModule
 */
class FLButtonModule extends FLBuilderModule {

	/**
	 * @method __construct
	 */
	public function __construct() {
		parent::__construct(array(
			'name'          	=> __( 'Button', 'fl-builder' ),
			'description'   	=> __( 'A simple call to action button.', 'fl-builder' ),
			'category'      	=> __( 'Basic', 'fl-builder' ),
			'partial_refresh'	=> true,
			'icon'				=> 'button.svg',
		));
	}

	/**
	 * @method enqueue_scripts
	 */
	public function enqueue_scripts() {
		if ( $this->settings && 'lightbox' == $this->settings->click_action ) {
			$this->add_js( 'jquery-magnificpopup' );
			$this->add_css( 'jquery-magnificpopup' );
		}
	}

	/**
	 * @method update
	 */
	public function update( $settings ) {
		// Remove the old three_d setting.
		if ( isset( $settings->three_d ) ) {
			unset( $settings->three_d );
		}

		return $settings;
	}

	/**
	 * @method get_classname
	 */
	public function get_classname() {
		$classname = 'fl-button-wrap';

		if ( ! empty( $this->settings->width ) ) {
			$classname .= ' fl-button-width-' . $this->settings->width;
		}
		if ( ! empty( $this->settings->align ) ) {
			$classname .= ' fl-button-' . $this->settings->align;
		}
		if ( ! empty( $this->settings->icon ) ) {
			$classname .= ' fl-button-has-icon';
		}

		return $classname;
	}

	/**
	 * Returns button link rel based on settings
	 * @since 1.10.9
	 */
	public function get_rel() {
		$rel = array();
		if ( '_blank' == $this->settings->link_target ) {
			$rel[] = 'noopener';
		}
		if ( isset( $this->settings->link_nofollow ) && 'yes' == $this->settings->link_nofollow ) {
			$rel[] = 'nofollow';
		}
		$rel = implode( ' ', $rel );
		if ( $rel ) {
			$rel = ' rel="' . $rel . '" ';
		}
		return $rel;
	}

}

/**
 * Register the module and its form settings.
 */
FLBuilder::register_module('FLButtonModule', array(
	'general'       => array(
		'title'         => __( 'General', 'fl-builder' ),
		'sections'      => array(
			'general'       => array(
				'title'         => '',
				'fields'        => array(
					'text'          => array(
						'type'          => 'text',
						'label'         => __( 'Text', 'fl-builder' ),
						'default'       => __( 'Click Here', 'fl-builder' ),
						'preview'         => array(
							'type'            => 'text',
							'selector'        => '.fl-button-text',
						),
						'connections'         => array( 'string' ),
					),
					'icon' => array(
						'type'        => 'icon',
						'label'       => __( 'Icon', 'fl-builder' ),
						'show_remove' => true,
						'show'        => array(
							'fields' => array( 'icon_position', 'icon_color', 'icon_size' ),
						),
						'preview'     => array(
							'type' => 'none',
						),
					),
					'icon_position' => array(
						'type'    => 'select',
						'label'   => __( 'Icon Position', 'fl-builder' ),
						'default' => 'before',
						'options' => array(
							'before' => __( 'Before Text', 'fl-builder' ),
							'after'  => __( 'After Text', 'fl-builder' ),
						),
						'preview' => array(
							'type' => 'none',
						),
					),
					'icon_color' => array(
						'type'       => 'color',
						'label'      => __( 'Icon Color', 'fl-builder' ),
						'show_reset' => true,
					),
					'icon_size' => array(
						'type'    => 'unit',
						'label'   => __( 'Icon Size', 'fl-builder' ),
						'default' => '1.25',
						'units'   => [ 'em' ],
					),
					'click_action' => array(
						'type' 			=> 'select',
						'label'         => __( 'Click Action', 'fl-builder' ),
						'default' 		=> 'link',
						'options' 		=> array(
							'link' 			=> __( 'Link', 'fl-builder' ),
							'live_watch'     => __( 'Live / Watch', 'fl-builder' ),
							'lightbox' 		=> __( 'Lightbox', 'fl-builder' ),
						),
						'toggle'  => array(
							'live_watch'		=> array(
								'sections' => array( 'link' ),
							),
							'link'		=> array(
								'sections' => array( 'link' ),
							),
							'lightbox'	=> array(
								'sections' => array( 'lightbox' ),
							),
						),
					),
				),
			),
			'link'          => array(
				'title'         => __( 'Link', 'fl-builder' ),
				'fields'        => array(
					'link'          => array(
						'type'          => 'link',
						'label'         => __( 'Link', 'fl-builder' ),
						'placeholder'   => __( 'http://www.example.com', 'fl-builder' ),
						'preview'       => array(
							'type'          => 'none',
						),
						'connections'         => array( 'url' ),
					),
					'link_target'   => array(
						'type'          => 'select',
						'label'         => __( 'Link Target', 'fl-builder' ),
						'default'       => '_self',
						'options'       => array(
							'_self'         => __( 'Same Window', 'fl-builder' ),
							'_blank'        => __( 'New Window', 'fl-builder' ),
						),
						'preview'       => array(
							'type'          => 'none',
						),
					),
					'link_nofollow'          => array(
						'type'          => 'select',
						'label'         => __( 'Link No Follow', 'fl-builder' ),
						'default'       => 'no',
						'options' 		=> array(
							'yes' 			=> __( 'Yes', 'fl-builder' ),
							'no' 			=> __( 'No', 'fl-builder' ),
						),
						'preview'       => array(
							'type'          => 'none',
						),
					),
				),
			),
			'lightbox'	=> array(
				'title'		=> __( 'Lightbox Content', 'fl-builder' ),
				'fields'        => array(
					'lightbox_content_type' => array(
						'type' 				=> 'select',
						'label' 			=> __( 'Content Type', 'fl-builder' ),
						'default' 			=> 'html',
						'options' 			=> array(
							'html' 				=> __( 'HTML', 'fl-builder' ),
							'video' 			=> __( 'Video', 'fl-builder' ),
						),
						'preview'       	=> array(
							'type'          	=> 'none',
						),
						'toggle' 		=> array(
							'html'			=> array(
								'fields' 		=> array( 'lightbox_content_html' ),
							),
							'video'			=> array(
								'fields' 		=> array( 'lightbox_video_link' ),
							),
						),
					),
					'lightbox_content_html'	=> array(
						'type'          		=> 'code',
						'editor'        		=> 'html',
						'label'         		=> '',
						'rows'          		=> '19',
						'preview'       		=> array(
							'type'          		=> 'none',
						),
						'connections'         => array( 'string' ),
					),
					'lightbox_video_link' => array(
						'type'          => 'text',
						'label'         => __( 'Video Link', 'fl-builder' ),
						'placeholder'   => 'https://vimeo.com/122546221',
						'preview'       => array(
							'type'          => 'none',
						),
						'connections'         => array( 'custom_field' ),
					),
				),
			),
		),
	),
	'style'         => array(
		'title'         => __( 'Style', 'fl-builder' ),
		'sections'      => array(
			'style'        => array(
				'title'         => '',
				'fields'        => Church\Integrations\BB::get_button_fields(),
			),
		),
	),
));
