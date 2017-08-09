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
		'_wc_talks_editor_image'        => 1,
		'_wc_talks_editor_link'         => 1,
		'_wc_talks_hint_list'           => array(),
		'_wc_talks_private_fields_list' => array(),
		'_wc_talks_public_fields_list'  => array(),
		'_wc_talks_signup_fields'       => array(),
		'_wc_talks_disjoin_comments'    => 1,
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
function wct_default_talk_status( $default = 'wct_pending' ) {
	/**
	 * @param  string $default_status
	 */
	return apply_filters( 'wct_default_talk_status', $default );
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
 * Customize the root slug of the plugin
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since  1.0.0
 * @since  1.1.0 Not filerable & not an option anymore.
 * @return string the root slug
 */
function wct_root_slug() {
	return _x( 'talks', 'default root slug', 'wordcamp-talks' );
}

/**
 * Build the talk slug (root + talk ones)
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 * @since  1.1.0 Not filerable anymore.
 *
 * @return string the talk slug (prefixed by the root one)
 */
function wct_talk_slug() {
	return wct_root_slug() . '/' . wct_get_talk_slug();
}

	/**
	 * Customize the talk (post type) slug of the plugin
	 *
	 * @package WordCamp Talks
	 * @subpackage core/options
	 *
	 * @since  1.0.0
	 * @since  1.1.0 Not filerable & not an option anymore.
	 * 
	 * @return string The talk slug
	 */
	function wct_get_talk_slug() {
		return _x( 'talk', 'default talk slug', 'wordcamp-talks' );
	}

/**
 * Build the category slug (root + category ones)
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 * @since  1.1.0 Not filerable anymore.
 *
 * @return string the category slug (prefixed by the root one)
 */
function wct_category_slug() {
	return wct_root_slug() . '/' . wct_get_category_slug();
}

	/**
	 * Customize the category (hierarchical taxonomy) slug of the plugin
	 *
	 * @package WordCamp Talks
	 * @subpackage core/options
	 *
	 * @since  1.0.0
	 * @since  1.1.0 Not filerable & not an option anymore.
	 *
	 * @return string the category slug
	 */
	function wct_get_category_slug() {
		return _x( 'category', 'default category slug', 'wordcamp-talks' );
	}

/**
 * Build the tag slug (root + tag ones)
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since  1.0.0
 * @since  1.1.0 Not filerable anymore.
 *
 * @return string the tag slug (prefixed by the root one)
 */
function wct_tag_slug() {
	return wct_root_slug() . '/' . wct_get_tag_slug();
}

	/**
	 * Customize the tag (non hierarchical taxonomy) slug of the plugin
	 *
	 * @package WordCamp Talks
	 * @subpackage core/options
	 *
	 * @since  1.0.0
	 * @since  1.1.0 Not filerable & not an option anymore.
	 *
	 * @return string          the tag slug
	 */
	function wct_get_tag_slug() {
		return _x( 'tag', 'default tag slug', 'wordcamp-talks' );
	}

/**
 * Build the user's profile slug (root + user ones)
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since  1.0.0
 * @since  1.1.0 Not filerable anymore.
 *
 * @return string the user slug (prefixed by the root one)
 */
function wct_user_slug() {
	return wct_root_slug() . '/' . wct_get_user_slug();
}

	/**
	 * Customize the user's profile slug of the plugin
	 *
	 * @package WordCamp Talks
	 * @subpackage core/options
	 *
	 * @since  1.0.0
	 * @since  1.1.0 Not filerable & not an option anymore.
	 *
	 * @return string the user slug
	 */
	function wct_get_user_slug() {
		return _x( 'user', 'default user slug', 'wordcamp-talks' );
	}

/**
 * Customize the user's profile rates slug of the plugin
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since  1.0.0
 * @since  1.1.0 Not filerable & not an option anymore.
 *
 * @return string the user's profile rates slug
 */
function wct_user_rates_slug() {
	return _x( 'ratings', 'default ratings slug', 'wordcamp-talks' );
}

/**
 * Customize the user's profile to rate slug of the plugin
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since  1.0.0
 * @since  1.1.0 Not filerable & not an option anymore.
 *
 * @return string the user's profile to rate slug
 */
function wct_user_to_rate_slug() {
	return _x( 'to-rate', 'default user to rate slug', 'wordcamp-talks' );
}

/**
 * Customize the user's profile talks section slug of the plugin
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since  1.0.0
 * @since  1.1.0 Not filerable anymore.
 *
 * @return string the user's profile talks section slug
 */
function wct_user_talks_slug() {
	return wct_root_slug();
}

/**
 * Customize the user's profile comments slug of the plugin
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since  1.0.0
 * @since  1.1.0 Not filerable & not an option anymore.
 *
 * @return string the user's profile comments slug
 */
function wct_user_comments_slug() {
	return _x( 'comments', 'default comments slug', 'wordcamp-talks' );
}

/**
 * Build the action slug (root + action ones)
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since  1.0.0
 * @since  1.1.0 Not filerable anymore.
 *
 * @return string the action slug (prefixed by the root one)
 */
function wct_action_slug() {
	return wct_root_slug() . '/' . wct_get_action_slug();
}

	/**
	 * Customize the action slug of the plugin
	 *
	 * @package WordCamp Talks
	 * @subpackage core/options
	 *
	 * @since  1.0.0
	 * @since  1.1.0 Not filerable & not an option anymore.
	 *
	 * @return string the action slug
	 */
	function wct_get_action_slug() {
		return _x( 'action', 'default action slug', 'wordcamp-talks' );
	}

/**
 * Customize the add (action) slug of the plugin
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since  1.0.0
 * @since  1.1.0 Not filerable & not an option anymore.
 *
 * @return string the add (action) slug
 */
function wct_addnew_slug() {
	return _x( 'add', 'default add talk action slug', 'wordcamp-talks' );
}

/**
 * Customize the edit (action) slug of the plugin
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since  1.0.0
 * @since  1.1.0 Not filerable & not an option anymore.
 *
 * @return string       the add (action) slug
 */
function wct_edit_slug( $default = '' ) {
	return _x( 'edit', 'default edit talk action slug', 'wordcamp-talks' );
}

/**
 * Build the signup slug (root + signup one)
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 * @since  1.1.0 Not filerable & not an option anymore.
 *
 * @return string the signup slug (prefixed by the root one)
 */
function wct_signup_slug() {
	return _x( 'sign-up', 'default sign-up action slug', 'wordcamp-talks' );
}

/**
 * Customize the comment pagination slug of the plugin in user's profile
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since  1.0.0
 * @since  1.1.0 Not filerable & not an option anymore.
 *
 * @return string       the comment pagination slug
 */
function wct_cpage_slug() {
	return _x( 'cpage', 'default comments pagination slug', 'wordcamp-talks' );
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
