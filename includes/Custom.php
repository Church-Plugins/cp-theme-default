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
		
		add_filter( 'cp_location_single_label', function() { return 'Campus'; } );
		add_filter( 'cp_location_plural_label', function() { return 'Campuses'; } );

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
}