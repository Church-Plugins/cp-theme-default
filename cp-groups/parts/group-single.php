<?php
use ChurchPlugins\Helpers;
use CP_Groups\Templates;
use CP_Groups\Admin\Settings;

try {
	$item = new \CP_Groups\Controllers\Group( get_the_ID() );
	$item = $item->get_api_data();
} catch ( \ChurchPlugins\Exception $e ) {
	error_log( $e );

	return;
}

$parishes = get_the_terms( get_the_ID(), 'cp_group_parish' );
?>
<div class="cp-group-single">

	<div class="cp-group-single--thumb">
		<?php if ( $item['thumb'] ) : ?>
			<img alt="<?php esc_attr( $item['title'] ); ?>" src="<?php echo esc_url( $item['thumb'] ); ?>">
		<?php endif; ?>
	</div>

	<div class="cp-group-single--details">

		<?php if ( ! empty( $item['locations'] ) ) : ?>
			<div class="cp-group-single--locations">
				<?php foreach( $item['locations'] as $id => $location ) : ?>
					<a class="cp-button is-xsmall is-transparent" href="<?php echo Templates::get_facet_link( 'location_' . $id, 'cp_location' ); ?>"><?php echo $location['title']; ?></a>
				<?php endforeach; ?>

				<?php if ( ! empty( $parishes ) ) : ?>
					<span class="text-small">|&nbsp;</span><a class="cp-button is-xsmall is-transparent" href="<?php echo Templates::get_facet_link( $parishes[0]->slug, 'cp_group_parish' ); ?>"><?php echo esc_html( $parishes[0]->name ); ?></a>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $item['types'] ) || ! empty( $item['categories'] ) || ! empty( $item['lifeStages'] ) ) : // for mobile ?>
			<div class="cp-group-single--categories">
				<?php if ( ! empty( $item['categories'] ) ) : ?>
					<div class="cp-group-single--category">
						<?php foreach( $item['categories'] as $slug => $label ) : ?>
							<a class="cp-button is-xsmall" href="<?php echo Templates::get_facet_link( $slug, 'cp_group_category' ); ?>"><?php echo $label; ?></a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $item['types'] ) ) : ?>
					<div class="cp-group-single--type">
						<?php foreach( $item['types'] as $slug => $label ) : ?>
							<a class="cp-button is-xsmall is-transparent" href="<?php echo Templates::get_facet_link( $slug, 'cp_group_type' ); ?>"><?php echo $label; ?></a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $item['lifeStages'] ) ) : ?>
					<div class="cp-group-single--life-stage">
						<?php foreach( $item['lifeStages'] as $slug => $label ) : ?>
							<a class="cp-button is-xsmall is-transparent" href="<?php echo Templates::get_facet_link( $slug, 'cp_group_life_stage' ); ?>"><?php echo $label; ?></a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<h3 class="cp-group-single--title"><?php echo $item['title']; ?></h3>

		<?php if ( $item['leader'] ) : ?>
			<h6 class="cp-group-single--leader"><?php echo $item['leader']; ?></h6>
		<?php endif; ?>

		<div class="cp-group-single--meta">
			<?php if ( ! empty( $item['startTime'] ) ) : ?>
				<div class="cp-group--item--meta--start-time"><?php echo Helpers::get_icon( 'date' ); ?> <?php echo esc_html( $item['startTime'] ); ?></div>
			<?php endif; ?>

			<?php if ( ! empty( $item['location'] ) ) : ?>
				<div class="cp-group--item--meta--location"><?php echo Helpers::get_icon( 'location' ); ?> <?php echo esc_html( $item['location'] ); ?></div>
			<?php endif; ?>
		</div>

		<div class="cp-group-single--content"><?php echo wp_kses_post( $item['desc'] ); ?></div>

		<div class="cp-group-item--attributes">
			<?php if ( $item['handicap'] ) : ?>
				<span class="cp-group-item--attributes--accessible"><?php echo Helpers::get_icon( 'accessible' ); ?> <?php _e( 'Accessible', 'cp-groups' ); ?></span>
			<?php endif; ?>

			<?php if ( $item['kidFriendly'] ) : ?>
				<span class="cp-group-item--attributes--kid-friendly"><?php echo Helpers::get_icon( 'child' ); ?> <?php _e( 'Kid Friendly', 'cp-groups' ); ?></span>
			<?php endif; ?>

			<?php if ( $item['isFull'] ) : ?>
				<span class="cp-group-item--attributes--is-full"><?php echo Helpers::get_icon( 'report' ); ?> <?php _e( 'Full', 'cp-groups' ); ?></span>
			<?php endif; ?>
		</div>

		<div class="cp-group-single--actions">
			<?php if ( Settings::get_advanced( 'hide_details' ) !== 'on' && $public_url = get_post_meta( $item['id'], 'public_url', true ) ) : ?>
				<div class="cp-group-single--registration-url"><a href="<?php echo esc_url( $public_url ); ?>" class="cp-button is-large" target="_blank"><?php _e( 'View Details', 'cp-groups' ); ?></a></div>
			<?php endif; ?>

			<?php if ( $item['registration_url'] && Settings::get_advanced( 'hide_registration' ) !== 'on' ) : ?>
				<div class="cp-group-single--registration-url"><a href="<?php echo str_contains( $item['registration_url'], 'mailto' ) ? '#' : esc_url( $item['registration_url'] ); ?>" class="cp-button" target="_blank"><?php _e( 'Register Now', 'cp-groups' ); ?></a></div>
			<?php endif; ?>

			<?php if ( $item['contact_url'] && Settings::get_advanced( 'contact_action' ) !== 'hide' ) : ?>
				<div class="cp-group-single--contact-url"><a href="<?php echo str_contains( $item['contact_url'], 'mailto' ) ? '#' : esc_url( $item['contact_url'] ); ?>" class="cp-button is-light" target="_blank"><?php _e( 'Contact', 'cp-groups' ); ?></a></div>
			<?php endif; ?>
		</div>

		<?php do_action( 'cp_group_single_after_content', $item ); ?>
	</div>

</div>
