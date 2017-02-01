<?php
/**
 * WordCamp Talks Users tags.
 *
 * @package WordCamp Talks
 * @subpackage users/tags
 *
 * @since 1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Outputs user's profile nav
 *
 * @package WordCamp Talks
 * @subpackage users/tags
 *
 * @since 1.0.0
 */
function wct_users_the_user_nav() {
	echo wct_users_get_user_nav();
}

	/**
	 * Gets user's profile nav
	 *
	 * @package WordCamp Talks
	 * @subpackage users/tags
	 *
	 * @since 1.0.0
	 */
	function wct_users_get_user_nav() {
		// Get displayed user id.
		$user_id = wct_users_displayed_user_id();

		// If not set, we're not on a user's profile.
		if ( empty( $user_id ) ) {
			return;
		}

		// Get username.
		$username = wct_users_get_displayed_user_username();

		// Get nav items for the user displayed.
		$nav_items = wct_users_get_profile_nav_items( $user_id, $username );

		if ( empty( $nav_items ) ) {
			return;
		}

		$user_nav = '<ul class="user-nav">';

		foreach ( $nav_items as $nav_item ) {
			$class =  ! empty( $nav_item['current'] ) ? ' class="current"' : '';
			$user_nav .= '<li' . $class .'>';
			$user_nav .= '<a href="' . esc_url( $nav_item['url'] ) . '" title="' . esc_attr( $nav_item['title'] ) . '">' . esc_html( $nav_item['title'] ) . '</a>';
			$user_nav .= '</li>';
		}

		$user_nav .= '</ul>';

		/**
		 * Filter the user nav output
		 *
		 * @param string $user_nav      User nav output
		 * @param int    $user_id       the user ID
		 * @param string $user_nicename the username
		 */
		return apply_filters( 'wct_users_get_user_nav', $user_nav, $user_id, $username );
	}

/**
 * Outputs user's profile avatar
 *
 * @package WordCamp Talks
 * @subpackage users/tags
 *
 * @since 1.0.0
 */
function wct_users_the_user_profile_avatar() {
	echo wct_users_get_user_profile_avatar();
}

	/**
	 * Gets user's profile avatar
	 *
	 * @package WordCamp Talks
	 * @subpackage users/tags
	 *
	 * @since 1.0.0
	 */
	function wct_users_get_user_profile_avatar() {
		return apply_filters( 'wct_users_get_user_profile_avatar', get_avatar( wct_users_displayed_user_id(), '150' ) );
	}

/**
 * Outputs user's profile display name
 *
 * @since 1.0.0
 */
function wct_users_user_profile_display_name() {
	echo wct_users_get_user_profile_display_name();
}

	/**
	 * Gets user's profile display name
	 *
	 * @since 1.0.0
	 */
	function wct_users_get_user_profile_display_name() {
		return esc_html( apply_filters( 'wct_users_get_user_profile_display_name', wct_users_get_displayed_user_displayname() ) );
	}


/**
 * Append displayed user's rating in talks header when viewing his rates profile
 *
 * @package WordCamp Talks
 * @subpackage users/tags
 *
 * @since 1.0.0
 *
 * @param int $id      the talk ID
 * @param int $user_id the user ID
 */
function wct_users_the_user_talk_rating( $id = 0, $user_id = 0 ) {
	if ( ! wct_user_can( 'view_talk_rates' ) ) {
		return;
	}

	echo wct_users_get_user_talk_rating( $id, $user_id );
}

	/**
	 * Gets displayed user's rating for a given talk
	 *
	 * @package WordCamp Talks
	 * @subpackage users/tags
	 *
	 * @since 1.0.0
	 *
	 * @param int $id      the talk ID
	 * @param int $user_id the user ID
	 */
	function wct_users_get_user_talk_rating( $id = 0, $user_id = 0 ) {
		if ( ! wct_is_user_profile_rates() ) {
			return;
		}

		if ( empty( $id ) ) {
			$query_loop = wct_get_global( 'query_loop' );

			if ( ! empty( $query_loop->talk->ID ) ) {
				$id = $query_loop->talk->ID;
			}
		}

		if ( empty( $user_id ) ) {
			$user_id = wct_users_displayed_user_id();
		}

		if ( empty( $user_id ) || empty( $id ) ) {
			return;
		}

		$user_rating = wct_count_ratings( $id, $user_id );

		if ( empty( $user_rating ) || is_array( $user_rating ) ) {
			return false;
		}

		$username = wct_users_get_displayed_user_username();

		$output = '<a class="user-rating-link" href="' . esc_url( wct_users_get_user_profile_url( $user_id, $username ) ) . '" title="' . esc_attr( $username ) . '">';
		$output .= get_avatar( $user_id, 20 ) . sprintf( _n( 'rated 1 star', 'rated %s stars', $user_rating, 'wordcamp-talks' ), $user_rating ) . '</a>';

		/**
		 * Filter the user talk rating output
		 *
		 * @param string $output        the rating
		 * @param int    $id            the talk ID
		 * @param int    $user_id       the user ID
		 */
		return apply_filters( 'wct_users_get_user_talk_rating', $output, $id, $user_id );
	}

function wct_users_the_signup_fields() {
	echo wct_users_get_signup_fields();
}

	function wct_users_get_signup_fields() {
		$output = '';

		foreach ( (array) wct_user_get_fields() as $key => $label ) {
			// reset
			$sanitized = array(
				'key'   => sanitize_key( $key ),
				'label' => esc_html( $label ),
				'value' => '',
			);

			if ( ! empty( $_POST['wct_signup'][ $sanitized['key'] ] ) ) {
				$sanitized['value'] = apply_filters( "wct_users_get_signup_field_{$key}", $_POST['wct_signup'][ $sanitized['key'] ] );
			}

			$required = apply_filters( 'wct_users_is_signup_field_required', false, $key );
			$required_output = false;

			if ( ! empty( $required ) || in_array( $key, array( 'user_login', 'user_email' ) ) ) {
				$required_output = '<span class="required">*</span>';
			}

			$output .= '<label for="_wct_signup_' . esc_attr( $sanitized['key'] ) . '">' . esc_html( $sanitized['label'] ) . ' ' . $required_output . '</label>';

			// Description is a text area.
			if ( 'description' === $sanitized['key'] ) {
				$output .= '<textarea id="_wct_signup_' . esc_attr( $sanitized['key'] ) . '" name="wct_signup[' . esc_attr( $sanitized['key'] ) . ']">' . esc_textarea( $sanitized['value'] ) . '</textarea>';

			// Language is a select box.
			} elseif ( 'locale' === $sanitized['key'] ) {
				$languages = get_available_languages();

				if ( empty( $languages ) ) {
					continue;
				}

				$output .=  wp_dropdown_languages( array(
					'name'                        => 'wct_signup[' . esc_attr( $sanitized['key'] ) . ']',
					'id'                          => '_wct_signup_' . esc_attr( $sanitized['key'] ),
					'selected'                    => get_locale(),
					'languages'                   => $languages,
					'show_available_translations' => false,
					'echo'                        => 0,
				) );

			// Default is text field.
			} else {
				$output .= '<input type="text" id="_wct_signup_' . esc_attr( $sanitized['key'] ) . '" name="wct_signup[' . esc_attr( $sanitized['key'] ) . ']" value="' . esc_attr( $sanitized['value'] ) . '"/>';
			}

			$output .= apply_filters( 'wct_users_after_signup_field', '', $sanitized );
		}

		return apply_filters( 'wct_users_get_signup_fields', $output );
	}

function wct_users_the_signup_submit() {
	$wct = wct();

	wp_nonce_field( 'wct_signup' );

	do_action( 'wct_users_the_signup_submit' ); ?>

	<input type="reset" value="<?php esc_attr_e( 'Reset', 'wordcamp-talks' ) ;?>"/>
	<input type="submit" value="<?php esc_attr_e( 'Sign-up', 'wordcamp-talks' ) ;?>" name="wct_signup[signup]"/>
	<?php
}

/**
 * Edit/Share profile buttons.
 *
 * Displays the edit profile URL for Admins/self profiles.
 * Displays the sharing dialog box on user's profile so
 * that people can easily get the embed code.
 *
 * @since 1.0.0
 */
function wct_users_buttons() {
	// Edit button for self profiles.
	if ( wct_is_current_user_profile() || is_super_admin() ) {
		$url = wct_users_get_user_profile_edit_url();

		if ( $url ) {
			?>
			<div class="wct-edit">
				<a href="<?php echo esc_url( $url ); ?>" class="button" aria-label="<?php esc_attr_e( 'Edit profile', 'wordcamp-talks' ); ?>">
					<span class="dashicons dashicons-edit"></span>
				</a>
			</div>
			<?php
		}
	}
}

/**
 * Set the public profile fields for the user's info template on front-end
 *
 * @since  1.0.0
 *
 * @return array The list of field keys.
 */
function wct_users_public_profile_infos() {
	/**
	 * Filter here if you need to edit the public profile infos list.
	 *
	 * @since  1.0.0
	 *
	 * @param array $value An associative array keyed by field IDs containing field labels.
	 */
	$public_labels = (array) apply_filters( 'wct_users_public_profile_infos', array_merge( array(
		'user_description' => __( 'Biographical Info', 'wordcamp-talks' ),
		'user_url'         => __( 'Website', 'wordcamp-talks' ),
	), wct_users_contactmethods( array(), 'public' ) ) );

	wct_set_global( 'public_profile_labels', $public_labels );

	return array_keys( $public_labels );
}

/**
 * Check if a field's key has a corresponding value for the user.
 *
 * @since  1.0.0
 *
 * @param  string $info The field key.
 * @return bool         True if the user has filled the field. False otherwise.
 */
function wct_users_public_profile_has_info(  $info = '' ) {
	if ( empty( $info ) ) {
		return false;
	}

	return ! empty( wct()->displayed_user->{$info} );
}

/**
 * While Iterating fields, count the empty ones.
 *
 * @since  1.0.0
 */
function wct_users_public_empty_info() {
	$empty_info = (int) wct_get_global( 'empty_info' );

	wct_set_global( 'empty_info', $empty_info + 1 );
}

/**
 * Displays the field label.
 *
 * @since  1.0.0
 *
 * @param  string $info The field key.
 */
function wct_users_public_profile_label( $info = '' ) {
	if ( empty( $info ) ) {
		return;
	}

	$labels = wct_get_global( 'public_profile_labels' );

	if ( ! isset( $labels[ $info ] ) ) {
		return;
	}

	echo esc_html( apply_filters( 'wct_users_public_label', $labels[ $info ], $info ) );
}

/**
 * Displays the field value.
 *
 * @since  1.0.0
 *
 * @param  string $info The field key.
 */
function wct_users_public_profile_value( $info = '' ) {
	if ( empty( $info ) ) {
		return;
	}

	echo apply_filters( 'wct_users_public_value', wct()->displayed_user->{$info}, $info );
}

/**
 * Check if no fields were filled by the user.
 *
 * @since  1.0.0
 *
 * @return bool True if the user didn't filled any fields. False otherwise.
 */
function wct_users_public_empty_profile() {
	$empty_info = (int) wct_get_global( 'empty_info' );
	$labels     = wct_get_global( 'public_profile_labels' );

	if ( $empty_info && $empty_info === count( $labels ) ) {
		$feedback = array( 'info' => array( 3 ) );

		if ( wct_is_current_user_profile() ) {
			$feedback = array( 'info' => array( 4 ) );
		}

		wct_set_global( 'feedback', $feedback );

		return true;
	}

	return false;
}
