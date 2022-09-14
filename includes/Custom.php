<?php

namespace Church;

/**
 * Custom functionality for this church. Should be left empty unless on a project fork.
 *
 * @author tanner moushey
 */
class Custom {

	/**
	 * @var
	 */
	protected static $_instance;

	/**
	 * Only make one instance of Custom
	 *
	 * @return Custom
	 */
	public static function get_instance() {
		if ( ! self::$_instance instanceof Custom ) {
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
		
		add_filter( 'cp_groups_disable_archive', '__return_true' );
		add_filter( 'cp_connect_congregation_map', [ $this, 'congregation_map' ] );
		
		add_filter( 'cp_connect_chms_mp_groups_filter', [ $this, 'mp_groups_filter' ] );
		
		add_filter( 'cp_location_single_label', function() { return 'Campus'; } );
		add_filter( 'cp_location_plural_label', function() { return 'Campuses'; } );
		
		add_filter( 'cp_live_video_location_id_default', function() { return 399; } ); // Wexford is the only livestreaming campus

		add_action( 'tribe_events_single_event_after_the_content', [ $this, 'event_registration' ], 2 );
		add_action( 'cp_group_single_after_content', [ $this, 'group_registration' ] );
		
//		add_action( 'cploc_location_meta_details', [ $this, 'add_social_meta' ], 10 , 2 );
//		add_filter( 'astra_get_option_array', [ $this, 'campus_social' ], 10, 3 );
		
		
		add_filter( 'cp_post_grid_callout_settings', [ $this, 'staff_email_link' ] );
		
		add_filter( 'post_type_link', [ $this, 'local_partner_link' ], 10, 2 );
		
		add_action( 'plugins_loaded', function () {
			if ( ! function_exists( 'cp_locations' ) || ! function_exists( 'cp_library' ) ) {
				return;
			}

			$locations = cp_locations()->setup->permissions::get_user_locations( get_current_user_id() );

			if ( empty( $locations ) ) {
				return;
			}

			add_filter( "cpl_item_type_show_in_menu", '__return_false' );
		}, 5 );

		add_filter( 'cp_location_taxonomy_types', function ( $types ) {
			$types[] = 'tribe_events';

			return $types;
		} );
		


		add_action( 'cmb2_after_form', function ( $post_id, $cmb ) {
			static $cp_added = false;

			// Only add this to the page once (not for every metabox)
			if ( $cp_added || ! function_exists( 'cp_locations' ) ) {
				return;
			}

			$cp_added  = true;
			$locations = cp_locations()->setup->permissions::get_user_locations( get_current_user_id(), true );
			?>
			<script type="text/javascript">
							jQuery(document).ready(function ($) {

								const locations = <?php echo json_encode( $locations ); ?>;
								const $form = $(document.getElementById('post'));
								const $htmlbody = $('html, body');
								const $toValidate = $('[data-validation]');
								const $required = $('.cmb2-id-cpl-speaker, .cmb2-id-cp-location, .cmb2-id-cpl-series, #cp_typechecklist');
							  

								if (!$toValidate.length && !$required.length) {
									return;
								}

								$('.cmb2-id-cp-location input').each(function () {
									var $this = $(this);
									if (locations.length && !locations.includes($this.val())) {
										$this.attr('disabled', 'disabled');
									}

									if (1 === locations.length && locations.includes($this.val())) {
										$this.attr('checked', 'checked');
									}
								});

								function checkValidation (evt) {
									var labels = [];
									var $first_error_row = null;
									var $row = null;

									function add_required ($row) {

										$row.css({'background-color': 'rgb(255, 170, 170)'});
										$first_error_row = $first_error_row ? $first_error_row : $row;
										labels.push($row.find('.cmb-th label').text());
									}

									function remove_required ($row) {
										$row.css({background: ''});
									}

									$required.each(function () {
										var $this = $(this);
										var $row = $this;
										if ($this.find('input:checked, option:selected').length) {
											remove_required($row);
										} else {
											add_required($row);
										}
									});

									$toValidate.each(function () {
										var $this = $(this);
										var val = $this.val();
										$row = $this.parents('.cmb-row');

										if ($this.is('[type="button"]') || $this.is('.cmb2-upload-file-id')) {
											return true;
										}

										if ('required' === $this.data('validation')) {
											if ($row.is('.cmb-type-file-list')) {

												var has_LIs = $row.find('ul.cmb-attach-list li').length > 0;

												if (!has_LIs) {
													add_required($row);
												} else {
													remove_required($row);
												}

											} else {
												if (!val) {
													add_required($row);
												} else {
													remove_required($row);
												}
											}
										}

									});

									if ($first_error_row) {
										evt.preventDefault();
										$htmlbody.animate({
											scrollTop: (
												$first_error_row.offset().top - 200
											)
										}, 1000);
									} else {
										// Feel free to comment this out or remove
									}

								}

								$form.on('submit', checkValidation);
							});
			</script>
			<?php
		}, 10, 2 );

	}

	/** Actions **************************************/

	/**
	 * Return a map of the congregation IDs from Ministry Platform
	 * 
	 * @param $map
	 *
	 * @return array
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public function congregation_map( $map ) {
		return [
			1  => 'location_399', // 'Wexford',
			//			2 => 'Miscellaneous',
			4  => 'location_400', // 'City',
			5  => 'location_401', // 'Sewickley Valley',
			//			6 => 'City East',
			9  => 'global',
			11 => 'location_402', // 'dormont',
			13 => 'location_403', // 'Beaver Valley',
			14 => 'location_404', // 'Robinson',
			//			15 => 'Ministry Hubs',
		];
	}
	
	public function staff_email_link( $settings ) {
		if ( 'cp_staff' != get_post_type() ) {
			return $settings;
		}

		if ( $email = get_post_meta( get_the_ID(), 'email', true ) ) {
			$settings['link'] = 'mailto:' . $email;
		}

		return $settings;
	}
	
	public function local_partner_link( $link, $post ) {
		if ( 'cp_ministries' !== $post->post_type ) {
			return $link;
		}
		
		if ( ! has_term( 'local-partner', 'cp_type', $post ) ) {
			return $link;
		}
		
		if ( ! $action = get_post_meta( $post->ID, 'contact_action', true ) ) {
			return $link;
		}
		
		return $action;
	}

	/**
	 * Add metaboxes for each location social
	 * 
	 * @param $cmb
	 * @param $object
	 *
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public function add_social_meta( $cmb, $object ) {

		$cmb_footer = new_cmb2_box( [
			'id'           => 'location_footer',
			'title'        => $object->single_label . ' ' . __( 'Footer', 'cp-locations' ),
			'object_types' => [ $object->post_type ],
			'context'      => 'normal',
			'priority'     => 'high',
			'show_names'   => true,
		] );

//		$menus = wp_list_pluck( wp_get_nav_menus(), 'name', 'slug' );
//		$menus = array_merge( [ 0 => 'Use Default' ], $menus );

//		$cmb_footer->add_field( [
//			'name'        => __( 'Menus', 'christpres-core' ),
//			'id'          => 'menus_title',
//			'type'        => 'title',
//			'description' => __( 'Leave blank to use the default menu', 'christpres-core' ),
//		] );
//
//		$cmb_footer->add_field( [
//			'name'    => __( 'Discover Menu', 'christpres-core' ),
//			'id'      => 'discover_menu',
//			'type'    => 'select',
//			'options' => $menus,
//		] );
//
//		$cmb_footer->add_field( [
//			'name'    => __( 'Next Steps Menu', 'christpres-core' ),
//			'id'      => 'next_steps_menu',
//			'type'    => 'select',
//			'options' => $menus,
//		] );
//
//		$cmb_footer->add_field( [
//			'name'    => __( 'Ministries Menu', 'christpres-core' ),
//			'id'      => 'ministries_menu',
//			'type'    => 'select',
//			'options' => $menus,
//		] );

		$cmb_footer->add_field( [
			'name'        => __( 'Social', 'christpres-core' ),
			'id'          => 'social_title',
			'type'        => 'title',
			'description' => __( 'Leave blank disable the social icon.', 'christpres-core' ),
		] );

		$cmb_footer->add_field( [
			'name' => __( 'YouTube', 'christpres-core' ),
			'id'   => 'youtube',
			'type' => 'text_url',
		] );

		$cmb_footer->add_field( [
			'name' => __( 'Facebook', 'christpres-core' ),
			'id'   => 'facebook',
			'type' => 'text_url',
		] );

		$cmb_footer->add_field( [
			'name' => __( 'Twitter', 'christpres-core' ),
			'id'   => 'twitter',
			'type' => 'text_url',
		] );

		$cmb_footer->add_field( [
			'name' => __( 'Instagram', 'christpres-core' ),
			'id'   => 'instagram',
			'type' => 'text_url',
		] );

		$cmb_footer->add_field( [
			'name' => __( 'SoundCloud', 'christpres-core' ),
			'id'   => 'soundcloud',
			'type' => 'text_url',
		] );

		$cmb_footer->add_field( [
			'name' => __( 'Podcast', 'christpres-core' ),
			'id'   => 'podcast',
			'type' => 'text_url',
		] );
		
	}

	/**
	 * Customize social for each location
	 * 
	 * @param $options_array
	 * @param $option
	 * @param $default
	 *
	 * @return array
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public function campus_social( $options_array, $option, $default ) {
		$social = [
			'twitter'    => [ 'label' => 'Twitter' ],
			'youtube'    => [ 'label' => 'YouTube' ],
			'instagram'  => [ 'label' => 'Instagram' ],
			'soundcloud' => [ 'label' => 'SoundCloud' ],
			'podcast'    => [ 'label' => 'Podcast' ],
			'facebook'   => [ 'label' => 'Facebook' ]
		];

		foreach ( $options_array['footer-social-icons-1']['items'] as $key => $item ) {
			if ( isset( $social[ $item['id'] ] ) ) {
				unset( $social[ $item['id'] ] );
			}
		}

		// Add our custom social... make sure that all expected social networks are available
		foreach ( $social as $id => $details ) {
			$options_array['footer-social-icons-1']['items'][] = [
				'id'      => $id,
				'icon'    => $id,
				'label'   => $details['label'],
				'enabled' => false,
			];
		}

		// if we are on a campus page, customize the icons
		if ( $location_id = get_query_var( 'cp_location_id' ) ) {
			foreach ( $options_array['footer-social-icons-1']['items'] as $key => $item ) {
				if ( $url = get_post_meta( $location_id, $item['id'], true ) ) {
					$options_array['footer-social-icons-1']['items'][ $key ]['url']     = esc_url( $url );
					$options_array['footer-social-icons-1']['items'][ $key ]['enabled'] = true;
				} else {
					$options_array['footer-social-icons-1']['items'][ $key ]['enabled'] = false;
				}
			}
		}


		return $options_array;
	}
	
	public function mp_groups_filter( $filter ) {
		return "(Groups.End_Date >= getdate() OR Groups.End_Date IS NULL) AND Group_Type NOT IN ('Age or Grade Group', 'Ministry Team', 'Parent Group', 'Staff', 'Other Group') AND Available_Online = 1 AND Group_Is_Full = 0";
	}

	/**
	 * Show registration button if registration is active
	 * 
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public function event_registration() {
		if ( ! get_post_meta( get_the_ID(), 'cp_registration_active', true ) ) {
			return;
		}
		
		$start_date = get_post_meta( get_the_ID(), 'cp_registration_start', true );
		$end_date   = get_post_meta( get_the_ID(), 'cp_registration_end', true );
		
		if ( $start_date && strtotime( $start_date ) > current_time( 'timestamp' ) ) {
			return;
		}
		
		if ( $end_date && strtotime( $end_date ) < current_time( 'timestamp' ) ) {
			return;
		}
		
		printf( '<div><a href="%s" class="cp-button is-large" target="_blank">Register Now</a></div>', 'https://my.northway.org/portal/event_signup.aspx?id=' . get_post_meta( get_the_ID(), '_chms_id', true ) );
	}

	/**
	 * Add button to contact group
	 * 
	 * @param $item
	 *
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public function group_registration( $item ) {
		printf( '<div><a href="%s" class="cp-button is-large" target="_blank">Contact Group</a></div>', 'https://home.northway.org/small-group-details/?id=' . get_post_meta( $item['id'], '_chms_id', true ) );
	}
	
}