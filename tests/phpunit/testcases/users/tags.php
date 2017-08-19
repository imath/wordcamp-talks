<?php

/**
 * @group tags
 */
class WordCampTalkProposalsTest_Users_Tags extends WordCampTalkProposalsTest {

	public function setUp() {
		parent::setUp();

		$this->current_user_id = get_current_user_id();

		$u = $this->factory->user->create( array(
			'user_login'  => 'foobar',
			'description' => 'I am Foo Bar',
			'user_url'    => 'https://foo.bar',
		) );

		wp_set_current_user( $u );
	}

	public function tearDown() {
		wp_set_current_user( $this->current_user_id );

		parent::tearDown();
	}

	public function test_wct_users_public_profile_infos_self() {
		$this->go_to( wct_users_get_logged_in_profile_url() );

		// Init the profile fields loop.
		wct_users_public_profile_infos();

		$displayed = wct_users_displayed_user();

		$this->assertTrue( 'I am Foo Bar' === $displayed->data_to_edit['user_description'] );
		$this->assertTrue( 'https://foo.bar' === $displayed->data_to_edit['user_url'] );
	}

	public function test_wct_users_public_profile_infos_other() {
		$u = $this->factory->user->create( array(
			'user_login'  => 'barfoo',
		) );

		$this->go_to( wct_users_get_user_profile_url( $u ) );

		// Init the profile fields loop.
		wct_users_public_profile_infos();

		$displayed = wct_users_displayed_user();

		$this->assertFalse( isset( $displayed->data_to_edit ) );
	}
}
