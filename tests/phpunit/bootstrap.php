<?php

require_once getenv( 'WP_DEVELOP_DIR' ) . '/tests/phpunit/includes/functions.php';

function _bootstrap_wct() {
	// load WP Idea Stream
	require dirname( __FILE__ ) . '/../../wordcamp-talks.php';
}
tests_add_filter( 'muplugins_loaded', '_bootstrap_wct' );

require getenv( 'WP_DEVELOP_DIR' ) . '/tests/phpunit/includes/bootstrap.php';

// include our testcase
require( 'testcase.php' );
