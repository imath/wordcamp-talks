<?php

// No, Thanks. Direct file access forbidden.
! defined( 'ABSPATH' ) and exit;

/**
* Adjusts the publish box in the admin UI for talk proposals
*/
class Talk_Status_View_Publish_Box {
	function __construct() {
		//
	}

	/**
	 * After the object is created, this tells it to start doing work
	 *
	 * @return void
	 */
	public function run() {
		add_action( 'admin_init', [ $this, 'admin_init' ] );
	}

	public function admin_init() {
		//
	}
}
