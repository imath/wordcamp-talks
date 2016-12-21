<?php
/*
Plugin Name: WordCamp Talks
Plugin URI: https://github.com/imath/wordcamp-talks/
Description: A WordCamp Talk Submission System
Version: 1.0.0-beta2
Requires at least: 4.6.1
Tested up to: 4.7
License: GNU/GPL 2
Author: imath
Author URI: http://imathi.eu/
Text Domain: wordcamp-talks
Domain Path: /languages/
*/

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WordCamp_Talks' ) ) :
require( 'includes/wordcamp-talks.php' );

endif;

/**
 * Plugin's Bootstrap Function
 *
 * @package WordCamp Talks
 *
 * @since 1.0.0
 */
function wct() {
	return WordCamp_Talks::start( __FILE__ );
}
add_action( 'plugins_loaded', 'wct' );
