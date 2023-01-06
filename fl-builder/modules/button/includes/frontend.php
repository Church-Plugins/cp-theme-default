<?php

$button_node_id = "fl-node-$id";

if ( empty( $settings->button_text ) ) {
	$settings->button_text = $settings->text;
}

if ( isset( $settings->id ) && ! empty( $settings->id ) ) {
	$button_node_id = $settings->id;
}

$button_classes = 'fl-button ';

$button_classes .= $settings->button_style . ' ' . $settings->button_size . ' ' . $settings->button_color . ' ' . $settings->button_text;

// $button_width is no longer used
if ( 'full' === $settings->button_width || 'is-fullwidth' === $settings->button_width ) {
	$button_classes .= ' is-fullwidth';
}

$icon_styles = '';

if ( ! empty( $settings->icon_size ) ) {
	$icon_styles .= 'font-size: ' . $settings->icon_size . 'em;';
}

if ( ! empty( $settings->icon_color ) ) {
	$icon_styles .= 'color: ' . FLBuilderColor::hex_or_rgb( $settings->icon_color );
}

if ( ! empty( $icon_styles ) ) {
	$icon_styles = ' style="' . $icon_styles . '"';
}

if ( 'live_watch' == $settings->click_action && function_exists( 'cp_live' ) ) {
	$location_id = false;

	$title = array_map( 'trim', explode( '|', $settings->text ) );
	if ( $use_locations = CP_Live\Admin\Settings::get_advanced( 'cp_locations_enabled', false ) ) {
		$location_id = apply_filters( 'cp_live_video_location_id_default', get_query_var( 'cp_location_id' ) );
	}
	
	if ( $location_id && cp_live()->integrations->cp_locations && cp_live()->integrations->cp_locations::is_location_live( $location_id ) ) {
		$settings->text = array_shift( $title );
	} else if ( ! $location_id && cp_live()->is_live() ) {
		$settings->text = array_shift( $title );
	} else {
		$settings->link = '/' . get_post_type_object( 'cpl_item' )->rewrite['slug'] . '/';
		$settings->text = array_pop( $title );
		$settings->icon = false;
	}
}

?>
<div class="<?php echo $module->get_classname(); ?>">
	<?php if ( isset( $settings->click_action ) && 'lightbox' == $settings->click_action ) : ?>
		<a href="<?php echo 'video' == $settings->lightbox_content_type ? $settings->lightbox_video_link : '#'; ?>" class="<?php echo $button_classes; ?> <?php echo $button_node_id; ?> fl-button-lightbox" role="button">
	<?php else : ?>
		<a href="<?php echo $settings->link; ?>"<?php echo ( isset( $settings->link_download ) && 'yes' === $settings->link_download ) ? ' download' : ''; ?> target="<?php echo $settings->link_target; ?>" class="<?php echo $button_classes; ?>" role="button"<?php echo $module->get_rel(); ?>>
	<?php endif; ?>

		<?php if ( ! empty( $settings->icon ) && ( 'before' == $settings->icon_position || ! isset( $settings->icon_position ) ) ) : ?>
			<i class="fl-button-icon fl-button-icon-before <?php echo $settings->icon; ?>" aria-hidden="true" <?php echo $icon_styles; ?>></i>
		<?php endif; ?>
		
		<?php if ( ! empty( $settings->text ) ) : ?>
			<span class="fl-button-text"><?php echo $settings->text; ?></span>
		<?php endif; ?>

		<?php if ( ! empty( $settings->icon ) && 'after' == $settings->icon_position ) : ?>
			<i class="fl-button-icon fl-button-icon-after <?php echo $settings->icon; ?>" aria-hidden="true" <?php echo $icon_styles; ?>></i>
		<?php endif; ?>
	</a>
</div>
<?php if ( 'lightbox' == $settings->click_action && 'html' == $settings->lightbox_content_type && isset( $settings->lightbox_content_html ) ) : ?>
	<div class="<?php echo $button_node_id; ?> fl-button-lightbox-content mfp-hide">
		<?php echo $settings->lightbox_content_html; ?>
	</div>
<?php endif; ?>
