<?php

require_once getenv( 'WP_DEVELOP_DIR' ) . '/tests/phpunit/includes/functions.php';

function _bootstrap_wct() {
	// load WP Idea Stream
	require dirname( __FILE__ ) . '/../../wordcamp-talks.php';
}
tests_add_filter( 'muplugins_loaded', '_bootstrap_wct' );


function _wct_assets_lang_dir( $mofile_path = '', $mofile = '' ) {
	$assets_dir = dirname( __FILE__ ) . '/assets';
	return $assets_dir . '/' . $mofile;

}
tests_add_filter( 'wordcamp_talks_lang_dir', '_wct_assets_lang_dir', 10, 2 );

require getenv( 'WP_DEVELOP_DIR' ) . '/tests/phpunit/includes/bootstrap.php';

// include our testcase
require( 'testcase.php' );
