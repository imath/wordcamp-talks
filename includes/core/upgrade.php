<?php
/**
 * WordCamp Talks Upgrade functions.
 *
 * @package WordCamp Talks
 * @subpackage core/upgrade
 *
 * @since 1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Compares the current plugin version to the DB one to check if it's an upgrade.
 *
 * @since 1.0.0
 *
 * @return bool True if update, False if not
 */
function wct_is_upgrade() {
	$db_version     = wct_db_version();
	$plugin_version = wct_get_version();

	return (bool) version_compare( $db_version, $plugin_version, '<' );
}

/**
 * Checks if an upgrade is needed.
 *
 * @since 1.0.0
 */
function wct_maybe_upgrade() {
	// Bail if no update needed
	if ( ! wct_is_upgrade() ) {
		return;
	}

	// Let's upgrade!
	wct_upgrade();
}

/**
 * Upgrade routine.
 *
 * @since 1.0.0
 */
function wct_upgrade() {
	$db_version = wct_db_version();

	if ( ! empty( $db_version ) ) {
		if ( version_compare( $db_version, '1.0.0', '<' ) ) {
			wct_add_options();
		}

		update_option( '_wc_talks_version', wct_get_version() );

	// It's a new install
	} else {
		wct_install();
	}

	// Force a rewrite rules reset
	wct_delete_rewrite_rules();
}

/**
 * First install routine
 *
 * @since 1.0.0
 */
function wct_install() {
	wct_add_options();
}
