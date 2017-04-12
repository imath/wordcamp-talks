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
		$labels = array(
			'name'                       => _x( 'Talk Status', 'Taxonomy General Name', 'wordcamp-talks' ),
			'singular_name'              => _x( 'Talk Status', 'Taxonomy Singular Name', 'wordcamp-talks' ),
			'menu_name'                  => __( 'Talk Statuses', 'wordcamp-talks' ),
			'all_items'                  => __( 'All Statuses', 'wordcamp-talks' ),
			'parent_item'                => __( 'Parent Status', 'wordcamp-talks' ),
			'parent_item_colon'          => __( 'Parent Item:', 'wordcamp-talks' ),
			'new_item_name'              => __( 'New Status Name', 'wordcamp-talks' ),
			'add_new_item'               => __( 'Add New Status', 'wordcamp-talks' ),
			'edit_item'                  => __( 'Edit Status', 'wordcamp-talks' ),
			'update_item'                => __( 'Update Status', 'wordcamp-talks' ),
			'view_item'                  => __( 'View Status', 'wordcamp-talks' ),
			'separate_items_with_commas' => __( 'Separate statuses with commas', 'wordcamp-talks' ),
			'add_or_remove_items'        => __( 'Add or remove statuses', 'wordcamp-talks' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'wordcamp-talks' ),
			'popular_items'              => __( 'Popular Statuses', 'wordcamp-talks' ),
			'search_items'               => __( 'Search Statuses', 'wordcamp-talks' ),
			'not_found'                  => __( 'Not Found', 'wordcamp-talks' ),
			'no_terms'                   => __( 'No statuses', 'wordcamp-talks' ),
			'items_list'                 => __( 'Status list', 'wordcamp-talks' ),
			'items_list_navigation'      => __( 'Status list navigation', 'wordcamp-talks' ),
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => false,
			'public'                     => false,
			'show_ui'                    => false,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => false,
			'show_tagcloud'              => false,
			'rewrite'                    => false,
			'show_in_rest'               => true,
		);
		register_taxonomy( 'wordcamp-talks-status', array( 'talks' ), $args );
	}
}
