<?php

class WordCampTalkProposalsTest_Users_Functions extends WordCampTalkProposalsTest {

	public function setUp() {
		parent::setUp();

		$this->current_user_id = get_current_user_id();

		$u = $this->factory->user->create( array(
			'user_login' => 'foobar',
		) );

		wp_set_current_user( $u );
	}

	public function tearDown() {
		wp_set_current_user( $this->current_user_id );

		parent::tearDown();
	}

	public function test_wct_users_current_user_id() {
		$this->assertTrue( (int) get_current_user_id() === (int) wct_users_current_user_id() );
	}
}
