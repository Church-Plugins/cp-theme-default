<?php

// Background Color
/*if ( ! empty( $settings->bg_color ) && empty( $settings->bg_hover_color ) ) {
	$settings->bg_hover_color = $settings->bg_color;
}*/


// Alignment
FLBuilderCSS::responsive_rule( array(
	'settings'     => $settings,
	'setting_name' => 'align',
	'selector'     => ".fl-node-$id .fl-button-wrap",
	'prop'         => 'text-align',
) );

// Old Background Gradient Setting
if ( isset( $settings->three_d ) && $settings->three_d ) {
	$settings->style = 'gradient';
}

// Background Gradient
/*if ( ! empty( $settings->bg_color ) ) {
	$bg_grad_start = FLBuilderColor::adjust_brightness( $settings->bg_color, 30, 'lighten' );
}*/
if ( ! empty( $settings->bg_hover_color ) ) {
	$bg_hover_grad_start = FLBuilderColor::adjust_brightness( $settings->bg_hover_color, 30, 'lighten' );
}

// Border Color
/*if ( ! empty( $settings->bg_color ) ) {
	$border_color = FLBuilderColor::adjust_brightness( $settings->bg_color, 12, 'darken' );
}*/
if ( ! empty( $settings->bg_hover_color ) ) {
	$border_hover_color = FLBuilderColor::adjust_brightness( $settings->bg_hover_color, 12, 'darken' );
}

?>
.fl-builder-content .fl-node-<?php echo $id; ?> {
	<?php if ( ! empty( $settings->button_align ) ) : ?>
	text-align: <?php echo $settings->button_align; ?>;
	<?php endif; ?>
}

<?php if ( empty( $settings->text ) ) : ?>
<?php if ( 'after' == $settings->icon_position ) : ?>
.fl-builder-content .fl-node-<?php echo $id; ?> .fl-button i.fl-button-icon-after {
	margin-left: 0;
}
<?php endif; ?>
<?php if ( 'before' == $settings->icon_position ) : ?>
.fl-builder-content .fl-node-<?php echo $id; ?> .fl-button i.fl-button-icon-before {
	margin-right: 0;
}
<?php endif; ?>
<?php endif; ?>

<?php

// Click action - lightbox
if ( isset( $settings->click_action ) && 'lightbox' == $settings->click_action ) :
	if ( 'html' == $settings->lightbox_content_type ) : ?>
	.fl-node-<?php echo $id; ?>.fl-button-lightbox-content {
		background: #fff none repeat scroll 0 0;
		margin: 20px auto;
		max-width: 600px;
		padding: 20px;
		position: relative;
		width: auto;
	}
	.fl-node-<?php echo $id; ?>.fl-button-lightbox-content .mfp-close,
	.fl-node-<?php echo $id; ?>.fl-button-lightbox-content .mfp-close:hover {
		top: -10px!important;
		right: -10px;
	}
	<?php endif; ?>

	<?php if ( 'video' == $settings->lightbox_content_type ) : ?>
	.fl-button-lightbox-wrap .mfp-content {
		background: #fff;
	}
	.fl-button-lightbox-wrap .mfp-iframe-scaler iframe {
		left: 2%;
		height: 94%;
		top: 3%;
		width: 96%;
	}
	.mfp-wrap.fl-button-lightbox-wrap .mfp-close,
	.mfp-wrap.fl-button-lightbox-wrap .mfp-close:hover {
		color: #333!important;
		right: -4px;
		top: -10px!important;
	}
	<?php endif; ?>

<?php endif; ?>
