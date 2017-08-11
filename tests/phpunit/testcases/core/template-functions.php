<?php

class WordCampTalkProposalsTest_Core_Template_Functions extends WordCampTalkProposalsTest {

	public function test_wct_parse_query() {
		$this->go_to( wct_get_root_url() );

		$this->assertTrue( wct_is_talks() );
	}

	public function test_wct_parse_query_action_add() {
		$this->go_to( wct_get_form_url() );

		$this->assertTrue( wct_is_addnew() );
	}

	public function test_wct_parse_query_user_profile() {
		$current_user_id = get_current_user_id();

		$u = $this->factory->user->create( array(
			'user_login' => 'foobar',
		) );

		wp_set_current_user( $u );

		$this->go_to( wct_users_get_logged_in_profile_url() );

		$this->assertTrue( wct_is_user_profile() );

		wp_set_current_user( $current_user_id );
	}
}
