<?php
/**
 * WordCamp Integration functions.
 *
 * Functions to integrate with WordCamp Post types:
 * - session
 * - speaker
 *
 * @since 1.1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Signups are not allowed, WordCamp Sites are using the WordPress SSO.
 * As a result, new users need to register on https://login.wordpress.org.
 */
add_filter( 'wct_allow_signups', '__return_false' );
