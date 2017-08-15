<?php
/**
 * User infos template
 *
 * @package WordCamp Talks
 * @subpackage templates
 *
 * @since 1.0.0
 */
?>

<div<?php wct_users_profile_classes(); ?>>

	<?php if ( wct_users_can_edit_profile() ) : ?>

		<form action="" method="post" class="standard-form">

	<?php endif; ?>

	<?php foreach ( wct_users_public_profile_infos() as $info ) :

		if ( ! wct_users_public_profile_has_info( $info ) ) :
			wct_users_public_empty_info();

			if ( ! wct_users_can_edit_profile() ) {
				continue;
			}

		endif; ?>

		<div class="info-field">

			<div class="info-label"><?php wct_users_public_profile_label( $info ); ?></div>
			<div class="info-value"><?php wct_users_public_profile_value( $info ); ?></div>

		</div>

	<?php endforeach;

	if ( wct_users_can_edit_profile() ) : ?>

			<div class="submit">
				<?php wct_users_public_profile_submit(); ?>
			</div>
		</form>

	<?php elseif ( wct_users_public_empty_profile() ) :

		wct_user_feedback();

	endif ; ?>

</div>
