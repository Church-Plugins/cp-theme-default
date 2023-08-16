<?php
/**
 * The sidebar containing the main widget area.
 *
 * @package ChurchPlugins
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$astra_sidebar = 'sidebar-account';

echo '<div ';
	echo astra_attr(
		'sidebar',
		array(
			'id'    => 'secondary',
			'class' => join( ' ', astra_get_secondary_class() ),
		)
	);
	echo '>';
	?>

	<div class="sidebar-main" <?php echo apply_filters( 'astra_sidebar_data_attrs', '', $astra_sidebar ); ?>>
		<?php astra_sidebars_before(); ?>

		<?php

		if ( is_active_sidebar( $astra_sidebar ) ) :
				dynamic_sidebar( $astra_sidebar );
		endif;

		astra_sidebars_after();
		?>

	</div><!-- .sidebar-main -->
</div><!-- #secondary -->
