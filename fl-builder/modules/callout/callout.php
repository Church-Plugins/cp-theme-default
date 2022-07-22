<?php

/**
 * @class FLCalloutModule
 */
class FLCalloutModule extends FLBuilderModule {

	/**
	 * @method __construct
	 */
	public function __construct() {
		parent::__construct(array(
			'name'          	=> __( 'Card', 'fl-builder' ),
			'description'   	=> __( 'A heading and snippet of text with an optional link, icon and image.', 'fl-builder' ),
			'category'      	=> __( 'Actions', 'fl-builder' ),
			'partial_refresh'	=> true,
			'icon'				=> 'text.svg',
		));
	}

	/**
	 * @method update
	 * @param $settings {object}
	 */
	public function update( $settings ) {
		// Cache the photo data.
		$settings->photo_data = FLBuilderPhoto::get_attachment_data( $settings->photo );

		return $settings;
	}

	/**
	 * @method delete
	 */
	public function delete() {
		// Delete photo module cache.
		if ( 'photo' == $this->settings->image_type && ! empty( $this->settings->photo_src ) ) {
			$module_class = get_class( FLBuilderModel::$modules['photo'] );
			$photo_module = new $module_class();
			$photo_module->settings = new stdClass();
			$photo_module->settings->photo_source = 'library';
			$photo_module->settings->photo_src = $this->settings->photo_src;
			$photo_module->settings->crop = $this->settings->photo_crop;
			$photo_module->delete();
		}
	}

	/**
	 * @method get_classname
	 */
	public function get_classname() {
		$classname = 'fl-callout fl-callout-' . $this->settings->align;

		if ( 'photo' == $this->settings->image_type ) {
			$classname .= ' fl-callout-has-photo fl-callout-photo-' . $this->settings->photo_position;
		} elseif ( 'icon' == $this->settings->image_type ) {
			$classname .= ' fl-callout-has-icon fl-callout-icon-' . $this->settings->icon_position;
		}

		return $classname;
	}

	/**
	 * @method render_title
	 */
	public function render_title() {
		echo '<div class="fl-callout-title-wrap"><div class="fl-callout-title-wrap-inner">';
		echo '<' . $this->settings->title_tag . ' class="fl-callout-title">';

		$this->render_image( 'left-title' );

		echo '<span' . ( empty( $this->settings->link ) ? ' class="fl-callout-title-text"' : '' ) . '>';

		if ( ! empty( $this->settings->link ) ) {
			echo '<a href="' . $this->settings->link . '" target="' . $this->settings->link_target . '" class="fl-callout-title-link fl-callout-title-text">';
		}

		echo $this->settings->title;

		if ( ! empty( $this->settings->link ) ) {
			echo '</a>';
		}

		echo '</span>';

		$this->render_image( 'right-title' );

		echo '</' . $this->settings->title_tag . '>';
		
		if ( $this->settings->subtitle ) {
			echo '<h5 class="fl-callout-subtitle">' . $this->settings->subtitle . '</h5>';
		}
		echo '</div></div>';
	}

	/**
	 * @method render_text
	 */
	public function render_text() {
		global $wp_embed;
		
		if ( empty( $this->settings->text ) ) {
			return;
		}

		echo '<div class="fl-callout-text">' . wpautop( $wp_embed->autoembed( $this->settings->text ) ) . '</div>';
	}

	/**
	 * @method render_link
	 */
	public function render_link() {
		if ( 'link' == $this->settings->cta_type ) {
			echo '<a href="' . $this->settings->link . '" target="' . $this->settings->link_target . '" class="fl-callout-cta-link">' . $this->settings->cta_text . '</a>';
		}
	}

	/**
	 * @method render_button
	 */
	public function render_button() {
		if ( 'button' == $this->settings->cta_type ) {

			$btn_settings = array(
				'align'            => '',
				'button_style'     => $this->settings->button_style,
				'button_size'      => $this->settings->button_size,
				'button_width'     => $this->settings->button_width,
				'button_color'     => $this->settings->button_color,
				'button_text'      => $this->settings->button_text,
				'button_align'     => $this->settings->button_align,
				'icon'             => $this->settings->btn_icon,
				'icon_position'    => $this->settings->btn_icon_position,
				'icon_animation'   => $this->settings->btn_icon_animation,
				'link'             => $this->settings->link,
				'link_nofollow'    => $this->settings->link_nofollow,
				'link_target'      => $this->settings->link_target,
				'padding'          => '',
				'style'            => '',
				'text'             => $this->settings->cta_text,
				'text_color'       => '',
				'text_hover_color' => '',
				'width'            => '',
			);

			echo '<div class="fl-callout-button">';
			FLBuilder::render_module_html( 'button', $btn_settings );
			echo '</div>';
		}
	}

	/**
	 * @method render_image
	 */
	public function render_image( $position ) {
		if ( 'photo' == $this->settings->image_type && $this->settings->photo_position == $position ) {

			if ( empty( $this->settings->photo ) ) {
				return;
			}

			$photo_data = FLBuilderPhoto::get_attachment_data( $this->settings->photo );

			if ( ! $photo_data && isset( $this->settings->photo_data ) ) {
				$photo_data = $this->settings->photo_data;
			} elseif ( ! $photo_data ) {
				$photo_data = -1;
			}

			$photo_settings = array(
				'align'         => $this->settings->align,
				'crop'          => $this->settings->photo_crop,
				'link_target'   => $this->settings->link_target,
				'link_type'     => 'url',
				'link_url'      => $this->settings->link,
				'photo'         => $photo_data,
				'photo_src'     => $this->settings->photo_src,
				'photo_source'  => 'library',
			);

			echo '<div class="fl-callout-photo fl-callout-photo--' . $this->settings->photo_size . '">';
			FLBuilder::render_module_html( 'photo', $photo_settings );
			echo '</div>';
		} elseif ( 'icon' == $this->settings->image_type && $this->settings->icon_position == $position ) {

			$icon_settings = array(
				'bg_color'       => $this->settings->icon_bg_color,
				'bg_hover_color' => $this->settings->icon_bg_hover_color,
				'color'          => $this->settings->icon_color,
				'exclude_wrapper' => true,
				'hover_color'    => $this->settings->icon_hover_color,
				'icon'           => $this->settings->icon,
				'link'           => $this->settings->link,
				'link_target'    => $this->settings->link_target,
				'size'           => $this->settings->icon_size,
				'text'           => '',
				'three_d'        => $this->settings->icon_3d,
			);

			FLBuilder::render_module_html( 'icon', $icon_settings );
		}
	}
}

/**
 * Register the module and its form settings.
 */
FLBuilder::register_module('FLCalloutModule', array(
	'general'       => array(
		'title'         => __( 'General', 'fl-builder' ),
		'sections'      => array(
			'title'         => array(
				'title'         => '',
				'fields'        => array(
					'title'         => array(
						'type'          => 'text',
						'label'         => __( 'Heading', 'fl-builder' ),
						'preview'       => array(
							'type'          => 'text',
							'selector'      => '.fl-callout-title-text',
						),
						'connections'   => array( 'string' ),
					),
				),
			),
			'text'          => array(
				'title'         => __( 'Text', 'fl-builder' ),
				'fields'        => array(
					'text'          => array(
						'type'          => 'editor',
						'label'         => '',
						'media_buttons' => false,
						'wpautop'		=> false,
						'preview'       => array(
							'type'          => 'text',
							'selector'      => '.fl-callout-text',
						),
						'connections'   => array( 'string' ),
					),
				),
			),
		),
	),
	'style'         => array(
		'title'         => __( 'Style', 'fl-builder' ),
		'sections'      => array(
			'overall_structure' => array(
				'title'         => __( 'Structure', 'fl-builder' ),
				'fields'        => array(
					'align'         => array(
						'type'          => 'select',
						'label'         => __( 'Overall Alignment', 'fl-builder' ),
						'default'       => 'left',
						'options'       => array(
							'center'        => __( 'Center', 'fl-builder' ),
							'left'          => __( 'Left', 'fl-builder' ),
							'right'         => __( 'Right', 'fl-builder' ),
						),
						'help'          => __( 'The alignment that will apply to all elements within the callout.', 'fl-builder' ),
						'preview'       => array(
							'type'          => 'none',
						),
					),
					'bg_color' => array(
						'type'       => 'color',
						'label'      => __( 'Background Color', 'fl-builder' ),
						'show_reset' => true,
					),
				),
			),
			'title_structure' => array(
				'title'         => __( 'Heading Structure', 'fl-builder' ),
				'fields'        => array(
					'title_tag'     => array(
						'type'          => 'select',
						'label'         => __( 'Heading Tag', 'fl-builder' ),
						'default'       => 'h3',
						'options'       => array(
							'h1'            => 'h1',
							'h2'            => 'h2',
							'h3'            => 'h3',
							'h4'            => 'h4',
							'h5'            => 'h5',
							'h6'            => 'h6',
						),
					),
					'title_size'    => array(
						'type'          => 'select',
						'label'         => __( 'Heading Size', 'fl-builder' ),
						'default'       => 'default',
						'options'       => array(
							'default'       => __( 'Default', 'fl-builder' ),
							'custom'        => __( 'Custom', 'fl-builder' ),
						),
						'toggle'        => array(
							'custom'        => array(
								'fields'        => array( 'title_custom_size' ),
							),
						),
					),
					'title_custom_size' => array(
						'type'              => 'text',
						'label'             => __( 'Heading Custom Size', 'fl-builder' ),
						'default'           => '24',
						'maxlength'         => '3',
						'size'              => '4',
						'description'       => 'px',
						'sanitize'			=> 'absint',
					),
				),
			),
		),
	),
	'image'         => array(
		'title'         => __( 'Image', 'fl-builder' ),
		'sections'      => array(
			'general'       => array(
				'title'         => '',
				'fields'        => array(
					'image_type'    => array(
						'type'          => 'select',
						'label'         => __( 'Image Type', 'fl-builder' ),
						'default'       => 'photo',
						'options'       => array(
							'none'          => _x( 'None', 'Image type.', 'fl-builder' ),
							'photo'         => __( 'Photo', 'fl-builder' ),
							'icon'          => __( 'Icon', 'fl-builder' ),
						),
						'toggle'        => array(
							'none'          => array(),
							'photo'         => array(
								'sections'      => array( 'photo' ),
							),
							'icon'          => array(
								'sections'      => array( 'icon', 'icon_colors', 'icon_structure' ),
							),
						),
					),
				),
			),
			'photo'         => array(
				'title'         => __( 'Photo', 'fl-builder' ),
				'fields'        => array(
					'photo'         => array(
						'type'          => 'photo',
						'show_remove'   => true,
						'label'         => __( 'Photo', 'fl-builder' ),
						'connections'   => array( 'photo' ),
					),
					'photo_crop'    => array(
						'type'          => 'select',
						'label'         => __( 'Crop', 'fl-builder' ),
						'default'       => '',
						'options'       => array(
							''              => _x( 'None', 'Photo Crop.', 'fl-builder' ),
							'landscape'     => __( 'Landscape', 'fl-builder' ),
							'panorama'      => __( 'Panorama', 'fl-builder' ),
							'portrait'      => __( 'Portrait', 'fl-builder' ),
							'square'        => __( 'Square', 'fl-builder' ),
							'circle'        => __( 'Circle', 'fl-builder' ),
						),
					),
					'photo_position' => array(
						'type'          => 'select',
						'label'         => __( 'Position', 'fl-builder' ),
						'default'       => 'above-title',
						'options'       => array(
							'above-title'   => __( 'Above Heading', 'fl-builder' ),
							'below-title'   => __( 'Below Heading', 'fl-builder' ),
							'left'          => __( 'Left of Text and Heading', 'fl-builder' ),
							'right'         => __( 'Right of Text and Heading', 'fl-builder' ),
						),
						'toggle' => array(
							'above-title' => array(),
							'below-title' => array(),
							'left'        => array(
								'fields' => array( 'photo_size' ),
							),
							'right'       => array(
								'fields' => array( 'photo_size' ),
							),
						),
					),
					'photo_size' => array(
						'type'          => 'select',
						'label'         => __( 'Size', 'fl-builder' ),
						'default'       => 'contain',
						'options'       => array(
							'contain'   => __( 'Contain', 'fl-builder' ),
							'cover'     => __( 'Cover', 'fl-builder' ),
						),
					),
				),
			),
			'icon'          => array(
				'title'         => __( 'Icon', 'fl-builder' ),
				'fields'        => array(
					'icon'          => array(
						'type'          => 'icon',
						'label'         => __( 'Icon', 'fl-builder' ),
					),
					'icon_position' => array(
						'type'          => 'select',
						'label'         => __( 'Position', 'fl-builder' ),
						'default'       => 'left-title',
						'options'       => array(
							'above-title'   => __( 'Above Heading', 'fl-builder' ),
							'below-title'   => __( 'Below Heading', 'fl-builder' ),
							'left-title'    => __( 'Left of Heading', 'fl-builder' ),
							'right-title'   => __( 'Right of Heading', 'fl-builder' ),
							'left'          => __( 'Left of Text and Heading', 'fl-builder' ),
							'right'         => __( 'Right of Text and Heading', 'fl-builder' ),
						),
					),
				),
			),
			'icon_colors'   => array(
				'title'         => __( 'Icon Colors', 'fl-builder' ),
				'fields'        => array(
					'icon_color'    => array(
						'type'          => 'color',
						'label'         => __( 'Color', 'fl-builder' ),
						'show_reset'    => true,
					),
//					'icon_hover_color' => array(
//						'type'          => 'color',
//						'label'         => __( 'Hover Color', 'fl-builder' ),
//						'show_reset'    => true,
//						'preview'       => array(
//							'type'          => 'none',
//						),
//					),
					'icon_bg_color' => array(
						'type'          => 'color',
						'label'         => __( 'Background Color', 'fl-builder' ),
						'show_reset'    => true,
					),
//					'icon_bg_hover_color' => array(
//						'type'          => 'color',
//						'label'         => __( 'Background Hover Color', 'fl-builder' ),
//						'show_reset'    => true,
//						'preview'       => array(
//							'type'          => 'none',
//						),
//					),
//					'icon_3d'       => array(
//						'type'          => 'select',
//						'label'         => __( 'Gradient', 'fl-builder' ),
//						'default'       => '0',
//						'options'       => array(
//							'0'             => __( 'No', 'fl-builder' ),
//							'1'             => __( 'Yes', 'fl-builder' ),
//						),
//					),
				),
			),
			'icon_structure' => array(
				'title'         => __( 'Icon Structure', 'fl-builder' ),
				'fields'        => array(
					'icon_size'     => array(
						'type'          => 'text',
						'label'         => __( 'Size', 'fl-builder' ),
						'default'       => '30',
						'maxlength'     => '3',
						'size'          => '4',
						'description'   => 'px',
					),
				),
			),
		),
	),
	'cta'           => array(
		'title'         => __( 'Call To Action', 'fl-builder' ),
		'sections'      => array(
			'link'          => array(
				'title'         => __( 'Link', 'fl-builder' ),
				'fields'        => array(
					'link'          => array(
						'type'          => 'link',
						'label'         => __( 'Link', 'fl-builder' ),
						'help'          => __( 'The link applies to the entire module. If choosing a call to action type below, this link will also be used for the text or button.', 'fl-builder' ),
						'preview'       => array(
							'type'          => 'none',
						),
						'connections'   => array( 'url' ),
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
			'cta'           => array(
				'title'         => __( 'Call to Action', 'fl-builder' ),
				'fields'        => array(
					'cta_type'      => array(
						'type'          => 'select',
						'label'         => __( 'Type', 'fl-builder' ),
						'default'       => 'none',
						'options'       => array(
							'none'          => _x( 'None', 'Call to action.', 'fl-builder' ),
							'link'          => __( 'Text', 'fl-builder' ),
							'button'        => __( 'Button', 'fl-builder' ),
						),
						'toggle'        => array(
							'none'          => array(),
							'link'          => array(
								'fields'        => array( 'cta_text' ),
							),
							'button'        => array(
								'fields'        => array( 'cta_text', 'btn_icon', 'btn_icon_position', 'btn_icon_animation' ),
								'sections'      => array( 'btn_style', 'btn_colors', 'btn_structure', 'btn_responsive_style' ),
							),
						),
					),
					'cta_text'      => array(
						'type'          => 'text',
						'label'         => __( 'Text', 'fl-builder' ),
						'default'		=> __( 'Read More', 'fl-builder' ),
						'connections'   => array( 'string' ),
						'preview'		=> array(
							'type'			=> 'text',
							'selector'		=> '.fl-callout-cta-link, .fl-button-text',
						),
					),
					'btn_icon'      => array(
						'type'          => 'icon',
						'label'         => __( 'Button Icon', 'fl-builder' ),
						'show_remove'   => true,
					),
					'btn_icon_position' => array(
						'type'          => 'select',
						'label'         => __( 'Button Icon Position', 'fl-builder' ),
						'default'       => 'before',
						'options'       => array(
							'before'        => __( 'Before Text', 'fl-builder' ),
							'after'         => __( 'After Text', 'fl-builder' ),
						),
					),
					'btn_icon_animation' => array(
						'type'          => 'select',
						'label'         => __( 'Icon Visibility', 'fl-builder' ),
						'default'       => 'disable',
						'options'       => array(
							'disable'        => __( 'Always Visible', 'fl-builder' ),
							'enable'         => __( 'Fade In On Hover', 'fl-builder' ),
						),
					),
				),
			),
			'btn_colors'     => array(
				'title'         => __( 'Button Style', 'fl-builder' ),
				'fields'        => Church\Integrations\BB::get_button_fields(),
			),
			'btn_responsive_style' 	=> array(
				'title'         		=> __( 'Responsive Button Style', 'fl-builder' ),
				'fields'        		=> array(
					'btn_mobile_align' => array(
						'type'          => 'select',
						'label'         => __( 'Alignment', 'fl-builder' ),
						'default'       => 'center',
						'options'       => array(
							'center'        => __( 'Center', 'fl-builder' ),
							'left'          => __( 'Left', 'fl-builder' ),
							'right'         => __( 'Right', 'fl-builder' ),
						),
						'preview'       => array(
							'type'          => 'none',
						),
					),
				),
			),
		),
	),
));
