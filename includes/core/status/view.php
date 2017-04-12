<?php

// No, Thanks. Direct file access forbidden.
! defined( 'ABSPATH' ) and exit;

/**
* Sets up the admin UI for setting a proposals status
*/
class Talk_Status_View {
	private $posts_list;
	private $publish_box;

	function __construct( Talk_Status_View_Posts_List $posts_list, Talk_Status_View_Publish_Box $publish_box ) {
		$this->posts_list = $posts_list;
		$this->publish_box = $publish_box;
	}

	/**
	 * After the object is created, this tells it to start doing work
	 *
	 * @return void
	 */
	public function run() {
		$this->posts_list->run();
		$this->publish_box->run();
	}
}
