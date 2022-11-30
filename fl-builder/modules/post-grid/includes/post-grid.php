<?php 
global $fl_alternating_tracker;

$settings = $settings ?? [];
$strip_breaks = false;
$subtitle = false;
$position = $settings->grid_image_position;

if ( ! empty( $fl_alternating_tracker ) ) {
	$position = 'left' === $fl_alternating_tracker ? 'right' : 'left';
	$fl_alternating_tracker = $position;
}

if ( 'left-right' === $position && empty( $fl_alternating_tracker ) ) {
	$position = $fl_alternating_tracker = 'left';
}

if ( 'right-left' === $position && empty( $fl_alternating_tracker ) ) {
	$position = $fl_alternating_tracker = 'right';
}

if ( 'columns' == $settings->layout ) : ?>
<div class="fl-post-column">
<?php endif; ?>

<?php ob_start(); ?>

<?php do_action( 'fl_builder_post_grid_before_meta', $settings, $module ); ?>

<?php if ( 'tribe_events' == get_post_type() ) {
	if ( ! empty( $settings->event_template ) ) {
		echo '[tribe_event_inline id="' . get_the_ID() . '"]' . $settings->event_template . '[/tribe_event_inline]';
	}
} elseif ( 'cp_staff' == get_post_type() ) {
	if ( $settings->show_title && $title = get_post_meta( get_the_ID(), 'title', true ) ) {
		$subtitle = $title;
	}

	if ( $settings->show_bio ) {
		the_content();
	}
} else { 
	$strip_breaks = true; 
	
	if ( $settings->show_author || $settings->show_date || $settings->show_comments_grid ) : ?>
		<div class="fl-post-grid-meta">
			<?php if ( $settings->show_author ) : ?>
				<span class="fl-post-grid-author">
				<?php

				printf(
				/* translators: %s: author name */
					_x( 'By %s', '%s stands for author name.', 'fl-builder' ),
					'<a href="' . get_author_posts_url( get_the_author_meta( 'ID' ) ) . '"><span>' . get_the_author_meta( 'display_name', get_the_author_meta( 'ID' ) ) . '</span></a>'
				);

				?>
				</span>
			<?php endif; ?>
			<?php if ( $settings->show_date ) : ?>
				<?php if ( $settings->show_author ) : ?>
					<span class="fl-sep"><?php echo $settings->info_separator; ?></span>
				<?php endif; ?>
				<span class="fl-post-grid-date">
					<?php FLBuilderLoop::post_date( $settings->date_format ); ?>
				</span>
			<?php endif; ?>
			<?php if ( $settings->show_comments_grid ) : ?>
				<?php if ( $settings->show_author || $settings->show_date ) : ?>
					<span class="fl-sep"><?php echo $settings->info_separator; ?></span>
				<?php endif; ?>
				<span class="fl-post-feed-comments">
					<?php comments_popup_link( '0 <i class="far fa-comment"></i>', '1 <i class="far fa-comment"></i>', '% <i class="far fa-comment"></i>' ); ?>
				</span>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if ( $settings->show_terms && $module->get_post_terms() ) : ?>
		<div class="fl-post-grid-meta-terms">
			<div class="fl-post-grid-terms">
				<span class="fl-terms-label"><?php echo $settings->terms_list_label; ?></span>
				<?php echo $module->get_post_terms(); ?>
			</div>
		</div>
	<?php endif; ?>

	<?php do_action( 'fl_builder_post_grid_after_meta', $settings, $module ); ?>


	<?php do_action( 'fl_builder_post_grid_before_content', $settings, $module ); ?>

	<?php if ( $settings->show_content || $settings->show_more_link ) { ?>
		<div class="fl-post-grid-content">
			<?php if ( $settings->show_content ) : ?>
				<?php $module->render_excerpt(); ?>
			<?php endif; ?>
			<?php if ( $settings->show_more_link ) : ?>
				<a class="fl-post-grid-more" href="<?php the_permalink(); ?>"
				   title="<?php the_title_attribute(); ?>"><?php echo $settings->more_link_text; ?></a>
			<?php endif; ?>
		</div>
		<?php
	} 
	do_action( 'fl_builder_post_grid_after_content', $settings, $module ); 
	?>
<?php } ?>

<?php

$content = trim( ob_get_clean() );

if ( ! empty( $content ) ) {
	$content = '<div class="fl-post-grid-text">' . $content . '</div>';
}

if ( $strip_breaks ) {
	$content = preg_replace( '|(?<!<br />)\s*\n|', "",  $content );
}

$photo_crop = 'landscape';

if ( 'cp_staff' === get_post_type() ) {
	$photo_crop = 'portrait';
}

if ( in_array( $position, [ 'left', 'right' ] ) ) {
	$photo_crop = 'landscape';
}

$callout_settings = apply_filters( 'cp_post_grid_callout_settings', [
	"title"          => get_the_title(),
	"subtitle"       => $subtitle,
	"text"           => $content,
	"align"          => $settings->post_align,
	"bg_color"       => $settings->bg_color,
	"title_tag"      => $settings->posts_title_tag,
	"subtitle_tag"   => $settings->posts_subtitle_tag,
	"image_type"     => "photo",
	"photo"          => get_post_thumbnail_id() ? get_post_thumbnail_id() : $settings->image_fallback,
	"photo_src"      => get_the_post_thumbnail_url() ? get_the_post_thumbnail_url() : $settings->image_fallback_src,
	"photo_crop"     => apply_filters( 'cp_post_grid_photo_crop', $photo_crop, $settings, $module ),
	"photo_position" => $position,
	"photo_size"     => "cover",
	"link"           => get_the_permalink(),
	"link_target"    => "_self",
	"link_nofollow"  => "no",
], $settings, $module );
?>

<<?php echo $module->get_posts_container(); ?> <?php $module->render_post_class(); ?><?php FLPostGridModule::print_schema( ' itemscope itemtype="' . FLPostGridModule::schema_itemtype() . '"' ); ?>>

	<?php FLPostGridModule::schema_meta(); ?>
	<?php FLBuilder::render_module_html( 'callout', $callout_settings ); ?>

</<?php echo $module->get_posts_container(); ?>>

<?php if ( 'columns' == $settings->layout ) : ?>
	</div>
<?php endif; ?>
