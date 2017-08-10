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
		'_wc_talks_closing_date'        => '',
		'_wc_talks_hint_list'           => array(),
		'_wc_talks_private_fields_list' => array(),
		'_wc_talks_public_fields_list'  => array(),
		'_wc_talks_signup_fields'       => array(),
		'_wc_talks_embed_profile'       => 0,
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
 * @since  1.0.0
 * @since  1.1.0 The option has been removed.
 *
 * @return string Title of the Talks archive page
 */
function wct_archive_title() {
	return _x( 'Talk Proposals', 'Title of the main archive page.', 'wordcamp-talks' );
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
