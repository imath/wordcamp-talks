<?php

// No, Thanks. Direct file access forbidden.
! defined( 'ABSPATH' ) and exit;

/**
* Runs all the relevant classes regarding talk statuses
*/
class Talk_Status_Controller {
	private $taxonomy;
	private $view;
	private $post_status;

	/**
	 * Sets up the controller with the things it needs
	 * @param Talk_Status_Post_Status $taxonomy An object to make core post statuses behave
	 * @param Talk_Status_Taxonomy $taxonomy An object to set up the data layer
	 * @param Talk_Status_View $view An object to set up the UI
	 */
	function __construct( Talk_Status_Post_Status $post_status, Talk_Status_Taxonomy $taxonomy, Talk_Status_View $view ) {
		$this->post_status = $post_status;
		$this->taxonomy = $taxonomy;
		$this->view = $view;
	}

	/**
	 * After the object is created, this tells it to start doing work
	 *
	 * @return void
	 */
	public function run() {
		$this->post_status->run();
		$this->taxonomy->run();
		$this->view->run();
	}
}
