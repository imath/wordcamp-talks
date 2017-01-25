<?php
/**
 * WordCamp Talks Options.
 *
 * List of options used to customize the plugins
 * @see  admin/settings
 *
 * Mainly inspired by bbPress way of dealing with options
 * @see bbpress/includes/core/options.php
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Get the default plugin's options and their values.
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @return array Filtered option names and values
 */
function wct_get_default_options() {
	// Default options

	$default_options = array(

		/** DB Version ********************************************************/
		'_wc_talks_version' => wct_get_version(),

		/** Core Settings **********************************************************/
		'_wc_talks_archive_title'       => 'Talks',
		'_wc_talks_closing_date'        => '',
		'_wc_talks_submit_status'       => 'private',
		'_wc_talks_editor_image'        => 1,
		'_wc_talks_editor_link'         => 1,
		'_wc_talks_moderation_message'  => '',
		'_wc_talks_login_message'       => '',
		'_wc_talks_hint_list'           => array(),
		'_wc_talks_private_fields_list' => array(),
		'_wc_talks_public_fields_list'  => array(),
		'_wc_talks_signup_fields'       => array(),
		'_wc_talks_disjoin_comments'    => 1,
		'_wc_talks_allow_comments'      => 1,
		'_wc_talks_embed_profile'       => 0,
		'_wc_talks_featured_images'     => 1,
		'_wc_talks_to_rate_disabled'    => 0,
		'_wc_talks_autolog_enabled'     => 0,
	);

	// Multisite options
	if ( is_multisite() ) {
		$default_options = array_merge( $default_options, array(
			'_wc_talks_allow_signups'     => 0,
			'_wc_talks_user_default_role' => 0,
		) );
	}

	/**
	 * @param  array $default_options list of options
	 */
	return apply_filters( 'wct_get_default_options', $default_options );
}

/**
 * Add default plugin's options
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 */
function wct_add_options() {

	// Add default options
	foreach ( wct_get_default_options() as $key => $value ) {
		add_option( $key, $value );
	}

	// Allow plugins to append their own options.
	do_action( 'wct_add_options' );
}

/**
 * Main archive page title
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @param  string $default default value
 * @return string          default value or customized one
 */
function wct_archive_title( $default = 'Talks' ) {
	return apply_filters( 'wct_archive_title', get_option( '_wc_talks_archive_title', $default ) );
}

/**
 * Gets the timestamp or mysql date closing limit
 *
 * @since 1.0.0
 *
 * @param  bool $timestamp true to get the timestamp
 * @return mixed int|string timestamp or mysql date closing limit
 */
function wct_get_closing_date( $timestamp = false ) {
	$closing = get_option( '_wc_talks_closing_date', '' );

	if ( ! empty( $timestamp ) ) {
		return $closing;
	}

	if ( is_numeric( $closing ) ) {
		$closing = date_i18n( 'Y-m-d H:i', $closing );
	}

	return $closing;
}

/**
 * Default publishing status (private/publish/pending)
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @param  string $default default value
 * @return string default value or customized one
 */
function wct_default_talk_status( $default = 'private' ) {
	$default_status = get_option( '_wc_talks_submit_status', $default );

	// Make sure admins will have a publish status whatever the settings choice
	if ( 'pending' === $default_status && wct_user_can( 'wct_talks_admin' ) ) {
		$wct            = wct();
		$current_screen = false;

		if ( function_exists( 'get_current_screen' ) ) {
			$current_screen = get_current_screen();
		}

		// In administration screens we need to be able to change the status
		if ( empty( $wct->admin->is_plugin_settings ) && ( empty( $current_screen->post_type ) || 'talks' !== $current_screen->post_type ) ) {
			$default_status = 'publish';
		}
	}

	/**
	 * @param  string $default_status
	 */
	return apply_filters( 'wct_default_talk_status', $default_status );
}

/**
 * Should the editor include the add image url button ?
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @param  int $default default value
 * @return bool         True if enabled, false otherwise
 */
function wct_talk_editor_image( $default = 1 ) {
	return (bool) apply_filters( 'wct_talk_editor_image(', (bool) get_option( '_wc_talks_editor_image', $default ) );
}

/**
 * Should the editor include the add/remove link buttons ?
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @param  int $default default value
 * @return bool         True if enabled, false otherwise
 */
function wct_talk_editor_link( $default = 1 ) {
	return (bool) apply_filters( 'wct_talk_editor_link', (bool) get_option( '_wc_talks_editor_link', $default ) );
}

/**
 * Use a custom moderation message ?
 *
 * This option depends on the default publish status one. If pending
 * is set, it will be possible to customize a moderation message.
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @param  string $default default value
 * @return string       the moderation message
 */
function wct_moderation_message( $default = '' ) {
	return apply_filters( 'wct_moderation_message', get_option( '_wc_talks_moderation_message', $default ) );
}

/**
 * Use a custom login message ?
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @param  string $default default value
 * @return string       the moderation message
 */
function wct_login_message( $default = '' ) {
	return apply_filters( 'wct_login_message', get_option( '_wc_talks_login_message', $default ) );
}

/**
 * Use a custom captions for rating stars ?
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @param  array $default default value
 * @return array        the list of rating stars captions.
 */
function wct_hint_list( $default = array() ) {
	if ( ! $default ) {
		$default = array( 'poor', 'good', 'great' );
	}

	return apply_filters( 'wct_hint_list', get_option( '_wc_talks_hint_list', $default ) );
}

/**
 * Are Private profile fields set?
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @param  array $default Default value
 * @return array          The list of private profile fields.
 */
function wct_user_private_fields_list( $default = array() ) {
	return (array) apply_filters( 'wct_user_private_fields_list', get_option( '_wc_talks_private_fields_list', $default ) );
}

/**
 * Are Public profile fields set?
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @param  array $default Default value
 * @return array          The list of private profile fields.
 */
function wct_user_public_fields_list( $default = array() ) {
	return (array) apply_filters( 'wct_user_public_fields_list', get_option( '_wc_talks_public_fields_list', $default ) );
}

/**
 * Get the signup fields.
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @param  array $default Default value
 * @return array          The list of fields to display into the signup form.
 */
function wct_user_signup_fields( $default = array() ) {
	return (array) apply_filters( 'wct_user_signup_fields', get_option( '_wc_talks_signup_fields', $default ) );
}

/**
 * Should the user be automagically logged in after a successful signup ?
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @param  bool $default default value
 * @return bool         True if enabled, false otherwise
 */
function wct_user_autolog_after_signup( $default = 0 ) {
	return (bool) apply_filters( 'wct_user_autolog_after_signup', (bool) get_option( '_wc_talks_autolog_enabled', $default ) );
}

/**
 * Should we disjoin comments about talks from regular comments ?
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @param  bool $default default value
 * @return bool         True if enabled, false otherwise
 */
function wct_is_comments_disjoined( $default = 1 ) {
	return (bool) apply_filters( 'wct_is_comments_disjoined', (bool) get_option( '_wc_talks_disjoin_comments', $default ) );
}

/**
 * Are comments about talks globally allowed
 *
 * Thanks to this option, plugin will be able to neutralize comments about
 * talks without having to rely on WordPress discussion settings. If this
 * option is enabled, it's still possible from the edit Administration screen
 * of the talk to neutralize for each specific talk the comments.
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @param  bool $default default value
 * @return bool         True if enabled, false otherwise
 */
function wct_is_comments_allowed( $default = 1 ) {
	return (bool) apply_filters( 'wct_is_comments_allowed', (bool) get_option( '_wc_talks_allow_comments', $default ) );
}

/**
 * Can profile be embed ?
 *
 * @since 1.0.0
 *
 * @param  bool $default default value
 * @return bool          The id of the Page Utility if enabled, 0 otherwise
 */
function wct_is_embed_profile( $default = 0 ) {
	return (int) apply_filters( 'wct_is_embed_profile', get_option( '_wc_talks_embed_profile', $default ) );
}

/**
 * Featured images for talks ?
 *
 * @since 1.0.0
 *
 * @param  int $default default value
 * @return bool         True if enabled, false otherwise
 */
function wct_featured_images_allowed( $default = 1 ) {
	return (bool) apply_filters( 'wct_featured_images_allowed', (bool) get_option( '_wc_talks_featured_images', $default ) );
}

/**
 * Is user's to rate profile tab disabled ?
 *
 * @since 1.0.0
 *
 * @param  int        $default        default value
 * @param  null|bool  $rates_disabled Whether built-in rating system is disabled or not.
 * @return bool                       True if disabled, false otherwise.
 */
function wct_is_user_to_rate_disabled( $default = 0, $rates_disabled = null ) {
	if ( is_null( $rates_disabled ) ) {
		$rates_disabled = wct_is_rating_disabled();
	}

	if ( $rates_disabled ) {
		return true;
	}

	return (bool) apply_filters( 'wct_is_user_to_rate_disabled', (bool) get_option( '_wc_talks_to_rate_disabled', $default ) );
}

/**
 * Build the talk slug (root + talk ones)
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @return string       the talk slug (prefixed by the root one)
 */
function wct_talk_slug() {
	return apply_filters( 'wct_talk_slug', 'talks/talk' );
}

/**
 * Build the category slug (root + category ones)
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @return string the category slug (prefixed by the root one)
 */
function wct_category_slug() {
	return apply_filters( 'wct_category_slug', 'talks/category' );
}

/**
 * Build the tag slug (root + tag ones)
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @return string       the tag slug (prefixed by the root one)
 */
function wct_tag_slug() {
	return apply_filters( 'wct_tag_slug', 'talks/tag' );
}

/**
 * Build the user's profile slug (root + user ones)
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @return string the user slug (prefixed by the root one)
 */
function wct_user_slug() {
	return apply_filters( 'wct_user_slug', 'talks/user' );
}

/**
 * Customize the user's profile to rate slug of the plugin
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @param  string $default default value
 * @return string       the user's profile to rate slug
 */
function wct_user_to_rate_slug( $default = '' ) {
	return 'to-rate';
}

/**
 * Customize the user's profile talks section slug of the plugin
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @param  string $default default value
 * @return string          the user's profile talks section slug
 */
function wct_user_talks_slug( $default = '' ) {
	return 'talks';
}

/**
 * Customize the user's profile comments slug of the plugin
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @param  string $default default value
 * @return string       the user's profile comments slug
 */
function wct_user_comments_slug( $default = '' ) {
	return 'comments';
}

/**
 * Build the action slug (root + action ones)
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @return string       the action slug (prefixed by the root one)
 */
function wct_action_slug() {
	return apply_filters( 'wct_action_slug', 'talks' . '/' . wct_get_action_slug() );
}

/**
 * Customize the action slug of the plugin
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @param  string $default default value
 * @return string       the action slug
 */
function wct_get_action_slug( $default = '' ) {
	return 'action';
}

/**
 * Customize the add (action) slug of the plugin
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @param  string $default default value
 * @return string       the add (action) slug
 */
function wct_addnew_slug( $default = '' ) {
	return 'add';
}

/**
 * Build the signup slug (root + signup one)
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @param  string the default signup slug
 * @return string the signup slug (prefixed by the root one)
 */
function wct_signup_slug( $default = '' ) {
	return 'sign-up';
}

/**
 * Customize the comment pagination slug of the plugin in user's profile
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @param  string $default default value
 * @return string       the comment pagination slug
 */
function wct_cpage_slug( $default = '' ) {
	return 'cpage';
}

/**
 * Should the plugin manage signups for the blog?
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @param  int $default default value
 * @return bool         True if enabled, false otherwise
 */
function wct_allow_signups( $default = 0 ) {
	return (bool) apply_filters( 'wct_allow_signups', get_option( '_wc_talks_allow_signups', $default ) );
}

/**
 * Should we make sure the user posting an talk on the site has the default role ?
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @param  int $default default value
 * @return bool         True if enabled, false otherwise
 */
function wct_get_user_default_role( $default = 0 ) {
	return (bool) apply_filters( 'wct_get_user_default_role', get_option( '_wc_talks_user_default_role', $default ) );
}
