<?php

class WordCampTalkProposalsTest_Functions extends WordCampTalkProposalsTest {

	public function test_wct_register_post_type() {
		$registered_post_types = get_post_types();

		$this->assertTrue( isset( $registered_post_types['talks'] ) );
	}

	public function test_wct_register_post_statuses() {
		$registered_post_statuses = get_post_stati( array(
			'private'                   => true,
			'show_in_admin_status_list' => false,
		) );

		$this->assertEquals( array_values( $registered_post_statuses ), array(
			'wct_pending',
			'wct_shortlist',
			'wct_selected',
			'wct_rejected',
		) );
	}

	public function test_wct_register_taxonomies() {
		$taxonomies = array(
			wct_get_tag(),
			wct_get_category(),
		);

		$exists = array_map( 'taxonomy_exists', $taxonomies );

		$this->assertTrue( 2 === count( array_filter( $exists ) ) );
	}

	public function test_wct_add_rewrite_tags() {
		$rewrite = $GLOBALS['wp_rewrite'];

		$rewrite_tags = array(
			'%' . wct_user_rewrite_id() . '%',
			'%' . wct_user_rates_rewrite_id() . '%',
			'%' . wct_user_to_rate_rewrite_id() . '%',
			'%' . wct_user_talks_rewrite_id() . '%',
			'%' . wct_user_comments_rewrite_id() . '%',
			'%' . wct_action_rewrite_id() . '%',
			'%' . wct_search_rewrite_id() . '%',
			'%' . wct_cpage_rewrite_id() . '%',
		);

		$this->assertTrue( count( array_intersect( $rewrite->rewritecode, $rewrite_tags ) ) === count( $rewrite_tags ) );
	}

	public function test_wct_add_rewrite_rules() {
		$rewrite = $GLOBALS['wp_rewrite'];

		$this->assertTrue( isset( $rewrite->rules[sprintf( '%s/([^/]+)/?$', wct_action_slug() )] ) );
	}

	public function test_wct_add_permastructs() {
		$rewrite = $GLOBALS['wp_rewrite'];

		$this->assertTrue( isset( $rewrite->extra_permastructs[ wct_user_rewrite_id() ] ) );
		$this->assertTrue( isset( $rewrite->extra_permastructs[ wct_action_rewrite_id() ] ) );
	}

	public function test_wct_load_textdomain() {
		$this->assertTrue( is_textdomain_loaded( 'wordcamp-talks' ) );
	}
}
