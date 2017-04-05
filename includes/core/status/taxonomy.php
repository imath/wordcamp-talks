<?php

// No, Thanks. Direct file access forbidden.
! defined( 'ABSPATH' ) and exit;

/**
* Registers the talk status taxonomy
*/
class Talk_Status_Taxonomy {
	function __construct() {
		//
	}

	/**
	 * After the object is created, this tells it to start doing work
	 *
	 * @return void
	 */
	public function run() {
		add_action( 'init', [ $this, 'init' ] );
	}

	public function init() {
		//
	}
}