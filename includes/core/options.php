<?php
/**
 * WordCamp Talks Options.
 *
 * List of options used to customize the plugins
 * @see  admin/settings
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
		'_wc_talks_autolog_enabled'     => 0,
		'_wc_talks_editing_timeout'     => '+1 hour',
		'_wc_talks_slack_webhook_url'   => '',
	);

	// Multisite options
	if ( is_multisite() ) {
		$default_options = array_merge( $default_options, array(
			'_wc_talks_allow_signups'     => 0,
			'_wc_talks_user_default_role' => 0,
		) );
	}

	/**
	 * Filter here to edit the default options.
	 *
	 * @since  1.0.0
	 *
	 * @param  array $default_options list of options
	 */
	return apply_filters( 'wct_get_default_options', $default_options );
}

/**
 * Add default plugin's options
 *
 * @since 1.0.0
 */
function wct_add_options() {
	// Add default options
	foreach ( wct_get_default_options() as $key => $value ) {
		add_option( $key, $value );
	}
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
 * Use a custom captions for rating stars?
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

	return get_option( '_wc_talks_hint_list', $default );
}

/**
 * Are Private profile fields set?
 *
 * @since 1.0.0
 *
 * @param  array $default Default value
 * @return array          The list of private profile fields.
 */
function wct_user_private_fields_list( $default = array() ) {
	return (array) get_option( '_wc_talks_private_fields_list', $default );
}

/**
 * Are Public profile fields set?
 *
 * @since 1.0.0
 *
 * @param  array $default Default value
 * @return array          The list of private profile fields.
 */
function wct_user_public_fields_list( $default = array() ) {
	return (array) get_option( '_wc_talks_public_fields_list', $default );
}

/**
 * Get the signup fields.
 *
 * @since 1.0.0
 *
 * @param  array $default Default value
 * @return array          The list of fields to display into the signup form.
 */
function wct_user_signup_fields( $default = array() ) {
	return (array) get_option( '_wc_talks_signup_fields', $default );
}

/**
 * Should the user be automagically logged in after a successful signup ?
 *
 * @since 1.0.0
 *
 * @param  bool $default default value
 * @return bool         True if enabled, false otherwise
 */
function wct_user_autolog_after_signup( $default = 0 ) {
	return (bool) get_option( '_wc_talks_autolog_enabled', $default );
}

/**
 * Time the speaker has to edit his/her talk.
 *
 * @since  1.1.0
 *
 * @param  string $default Default value is set to 1 hour once the talk is submitted.
 * @return string          Time the speaker has to edit his/her talk.
 */
function wct_talk_editing_timeout( $default = '+1 hour' ) {
	return get_option( '_wc_talks_editing_timeout', $default );
}

/**
 * Slack webhook to notify of new talks.
 *
 * @since  1.1.0
 *
 * @param  string $default Defaults to no webhook URL.
 * @return string          The slack webhook URL.
 */
function wct_talk_slack_webhook_url( $default = '' ) {
	return get_option( '_wc_talks_slack_webhook_url', $default );
}

/**
 * Should the plugin manage signups for the blog?
 *
 * @since 1.0.0
 *
 * @param  int $default default value
 * @return bool         True if enabled, false otherwise
 */
function wct_allow_signups( $default = 0 ) {
	/**
	 * Filter here to disable signups by returnin false.
	 * Used internally when the Plugin is activated on a WordCamp.org site.
	 *
	 * @since  1.0.0
	 *
	 * @param  boolean $default True when signups are allowed. False otherwise.
	 */
	return (bool) apply_filters( 'wct_allow_signups', get_option( '_wc_talks_allow_signups', $default ) );
}

/**
 * Should we make sure the user posting a talk on the site has the default role?
 *
 * @since 1.0.0
 *
 * @param  int     $default default value
 * @return boolean          True if enabled, false otherwise.
 */
function wct_get_user_default_role( $default = 0 ) {
	return (bool) get_option( '_wc_talks_user_default_role', $default );
}
