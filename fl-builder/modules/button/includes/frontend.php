<?php

$button_node_id = "fl-node-$id";

if ( isset( $settings->id ) && ! empty( $settings->id ) ) {
	$button_node_id = $settings->id;
}

$button_classes = 'fl-button ';

if ( 'enable' == $settings->icon_animation ) {
	$button_classes .= 'fl-button-icon-animation ';
}

$button_classes .= $settings->button_style . ' ' . $settings->button_size . ' ' . $settings->button_color . ' ' . $settings->button_text;

// $button_width is no longer used
if ( 'full' === $settings->width || 'is-fullwidth' === $settings->button_width ) {
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
