<?php

// No, Thanks. Direct file access forbidden.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
		add_action( 'admin_head-post.php', array( $this, 'set_publishing_actions' ) );
	}

	function set_publishing_actions() {
		global $post;
		if ( 'talks' === $post->post_type ) {
			echo '<style type="text/css">
			.misc-pub-section.misc-pub-visibility,
			.misc-pub-section.curtime.misc-pub-curtime {
				display: none;
			}
			</style>';
		}
	}
}
