<?php

// No, Thanks. Direct file access forbidden.
! defined( 'ABSPATH' ) and exit;

/**
* Adjusts the list of filters at the top of the talk proposals posts screen from statuses to tax terms
*/
class Talk_Status_View_Posts_List {
	function __construct() {
		//
	}

	/**
	 * After the object is created, this tells it to start doing work
	 *
	 * @return void
	 */
	public function run() {
		add_filter( 'display_post_states', [ $this, 'display_post_states' ], 10, 2 );
		add_action( 'admin_init', [ $this, 'admin_init' ] );
		add_filter( 'views_edit-talks', [ $this, 'update_subsubsub' ] );
	}

	public function admin_init() {
		//
	}

	function update_subsubsub( $views ) {
		unset( $views['pending'] );
		unset( $views['publish'] );
		unset( $views['private'] );
		return $views;
	}

	function display_post_states( $post_states, \WP_Post $post ) {
		if ( 'talks' === $post->post_type ) {
			return false;
		}
		return $post_states;
	}
}
