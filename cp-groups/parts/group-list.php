<?php
use ChurchPlugins\Helpers;
use CP_Groups\Admin\Settings;
use CP_Groups\Templates;

try {
	$item = new \CP_Groups\Controllers\Group( get_the_ID() );
	$item = $item->get_api_data();
} catch ( \ChurchPlugins\Exception $e ) {
	error_log( $e );

	return;
}

$is_location_page = get_query_var( 'cp_location_id' );
$parishes = get_the_terms( get_the_ID(), 'cp_group_parish' );

?>
<div class="cp-group-item">

	<div class="cp-group-item--thumb">
		<div class="cp-group-item--thumb--canvas" style="background: url(<?php echo esc_url( $item['thumb'] ); ?>) 0% 0% / cover;">
			<?php if ( $item['thumb'] ) : ?>
				<img alt="<?php esc_attr( $item['title'] ); ?>" src="<?php echo esc_url( $item['thumb'] ); ?>">
			<?php endif; ?>
		</div>
	</div>

	<div class="cp-group-item--details">

		<?php if ( ! empty( $item['types'] ) || ! empty( $item['categories'] ) || ! empty( $item['lifeStages'] ) ) : ?>
			<div class="cp-group-item--categories">
				<?php if ( ! empty( $item['categories'] ) ) : ?>
					<div class="cp-group-item--category">
						<?php foreach( $item['categories'] as $slug => $label ) : ?>
							<a class="cp-button is-xsmall" href="<?php echo Templates::get_facet_link( $slug, 'cp_group_category' ); ?>"><?php echo $label; ?></a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $item['types'] ) ) : ?>
					<div class="cp-group-item--type">
						<?php foreach( $item['types'] as $slug => $label ) : ?>
							<a class="cp-button is-xsmall is-transparent" href="<?php echo Templates::get_facet_link( $slug, 'cp_group_type' ); ?>"><?php echo $label; ?></a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $item['lifeStages'] ) ) : ?>
					<div class="cp-group-item--life-stage">
						<?php foreach( $item['lifeStages'] as $slug => $label ) : ?>
							<a class="cp-button is-xsmall is-transparent" href="<?php echo Templates::get_facet_link( $slug, 'cp_group_life_stage' ); ?>"><?php echo $label; ?></a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<h3 class="cp-group-item--title"><a href="<?php the_permalink(); ?>"><?php echo $item['title']; ?></a></h3>

		<div class="cp-group-item--meta">
			<?php if ( ! empty( $item['startTime'] ) ) : ?>
				<div class="cp-group--item--meta--start-time"><?php echo Helpers::get_icon( 'date' ); ?> <?php echo esc_html( $item['startTime'] ); ?></div>
			<?php endif; ?>

			<?php if ( ! empty( $item['location'] ) ) : ?>
				<div class="cp-group--item--meta--location"><?php echo Helpers::get_icon( 'location' ); ?> <?php echo esc_html( $item['location'] ); ?></div>
			<?php endif; ?>
		</div>

		<div class="cp-group-item--content"><?php echo wp_kses_post( $item['excerpt'] ); ?></div>

		<?php if ( ! empty( $item['locations'] ) ) : ?>
			<div class="cp-group-item--locations">
				<?php foreach( $item['locations'] as $id => $location ) : ?>
					<a class="cp-button is-xsmall is-transparent" href="<?php echo Templates::get_facet_link( 'location_' . $id, 'cp_location' ); ?>"><?php echo $location['title']; ?></a>
				<?php endforeach; ?>

				<?php if ( ! empty( $parishes ) ) : ?>
					<span class="text-small">|&nbsp;</span><a class="cp-button is-xsmall is-transparent" href="<?php echo Templates::get_facet_link( $parishes[0]->slug, 'cp_group_parish' ); ?>"><?php echo esc_html( $parishes[0]->name ); ?></a>
				<?php endif; ?>
			</div>
		<?php endif; ?>

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
	</div>

	<div style="display:none;">
		<?php
			Templates::get_template_part( "parts/group-modal" );

			if( Settings::get_advanced( 'contact_action', 'action' ) == 'form' ) {
				$leader_email = get_post_meta( $item['id'], 'leader_email', true );
				$group_leader = get_post_meta( $item['id'], 'leader', true );

				if( is_email( $leader_email ) ) {
					cp_groups()->build_email_modal( 'action_contact', $leader_email, $group_leader, $item['id'] );
				}
			}

			if( Settings::get_advanced( 'hide_registration', 'off' ) == 'off' ) {
				$register_url = get_post_meta( $item['id'], 'registration_url', true );

				if( is_email( $register_url ) ) {
					cp_groups()->build_email_modal( 'action_register', $register_url, $item['title'], $item['id'] );
				}
			}
		?>
	</div>


</div>
