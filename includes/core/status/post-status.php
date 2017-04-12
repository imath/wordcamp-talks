<?php

// No, Thanks. Direct file access forbidden.
! defined( 'ABSPATH' ) and exit;

/**
* Filters and adjusts the Talk post status to always be private, and removes some artifacts from the Core UI as a result
*/
class Talk_Status_Post_Status {
	function __construct() {
		//
	}

	/**
	 * After the object is created, this tells it to start doing work
	 *
	 * @return void
	 */
	public function run() {
		// @TODO: Set the private status via filter so the actual post status is irrelevant
		add_filter( 'wp_insert_post_data', [ $this, 'force_type_private' ] );

		// @TODO: Set the default status in the editor UI of new posts to private
		// @TODO: Remove the bolded private marker Core adds in the posts list as it's pointless here
	}

	/**
	 * Forces talk posts to always be private
	 * @param  [type] $post [description]
	 * @return [type]       [description]
	 */
	function force_type_private( $post ) {
	    if ( 'talks' === $post['post_type'] ) {
	    	if ( ! in_array( $post['post_status'] , [ 'trash', 'auto-draft', 'inherit' ] ) ) {
			    $post['post_status'] = 'private';
			}
		}
	    return $post;
	}
}
