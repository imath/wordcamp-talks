<?php

/**
 * @group wordcamp
 */
class WordCampTalkProposalsTest_WordCamp_Integrations extends WordCampTalkProposalsTest {

	public function test_wct_register_post_type() {
		$expected              = array( 'wcb_speaker', 'wcb_session', 'talks' );
		$post_types_registered = array_intersect( get_post_types(), $expected );

		$this->assertEquals( $expected, array_values( $post_types_registered ) );
	}

	/**
	 * @group ms-required
	 */
	public function test_wct_is_signup_allowed_for_current_blog() {
		add_filter( 'wct_allow_signups', '__return_true', 9 );
		add_filter( 'pre_site_option_registration', array( $this, 'return_user' ), 9 );

		$this->assertFalse( wct_is_signup_allowed_for_current_blog() );

		remove_filter( 'pre_site_option_registration', array( $this, 'return_user' ), 9 );
		remove_filter( 'wct_allow_signups', '__return_true', 9 );
	}

	public function return_user() {
		return 'user';
	}
}
