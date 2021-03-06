<?php
$post_spacing = floatval( $settings->post_spacing );
$post_width   = floatval( $settings->post_width );

if ( 'grid' == $settings->layout ) : ?>
.fl-node-<?php echo $id; ?> .fl-post-grid-post {
	margin-bottom: <?php echo $post_spacing; ?>rem;
	width: <?php echo $post_width; ?>rem;
}
.fl-node-<?php echo $id; ?> .fl-post-grid-sizer {
	width: <?php echo $post_width; ?>rem;
}
@media screen and (max-width: <?php echo ( $post_width + $post_spacing ) * 16; ?>px) {
	.fl-node-<?php echo $id; ?> .fl-post-grid,
	.fl-node-<?php echo $id; ?> .fl-post-grid-post,
	.fl-node-<?php echo $id; ?> .fl-post-grid-sizer {
		width: 100% !important;
	}
}
<?php elseif ( 'columns' == $settings->layout ) : ?>

.fl-node-<?php echo $id; ?> .fl-post-grid {
	margin-left: -<?php echo $post_spacing / 2; ?>rem;
	margin-right: -<?php echo $post_spacing / 2; ?>rem;
}
.fl-node-<?php echo $id; ?> .fl-post-column {
	padding-bottom: <?php echo $post_spacing; ?>rem;
	padding-left: <?php echo $post_spacing / 2; ?>rem;
	padding-right: <?php echo $post_spacing / 2; ?>rem;
	width: <?php echo 100 / $settings->post_columns; ?>%;
}
.fl-node-<?php echo $id; ?> .fl-post-column:nth-child(<?php echo $settings->post_columns; ?>n + 1) {
	clear: both;
}
@media screen and (max-width: <?php echo $global_settings->medium_breakpoint; ?>px) {
	.fl-node-<?php echo $id; ?> .fl-post-column {
		width: <?php echo 100 / $settings->post_columns_medium; ?>%;
	}
	.fl-node-<?php echo $id; ?> .fl-post-column:nth-child(<?php echo $settings->post_columns; ?>n + 1) {
		clear: none;
	}
	.fl-node-<?php echo $id; ?> .fl-post-column:nth-child(<?php echo $settings->post_columns_medium; ?>n + 1) {
		clear: both;
	}
}
@media screen and (max-width: <?php echo $global_settings->responsive_breakpoint; ?>px) {
	.fl-node-<?php echo $id; ?> .fl-post-column {
		width: <?php echo 100 / $settings->post_columns_responsive; ?>%;
	}
	.fl-node-<?php echo $id; ?> .fl-post-column:nth-child(<?php echo $settings->post_columns_medium; ?>n + 1) {
		clear: none;
	}
	.fl-node-<?php echo $id; ?> .fl-post-column:nth-child(<?php echo $settings->post_columns_responsive; ?>n + 1) {
		clear: both;
	}
}
<?php endif; ?>
