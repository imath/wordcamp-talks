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

	public function test_wct_users_enqueue_scripts() {
		$enqueued = wp_scripts()->queue;
		wct_set_global( 'is_user', get_current_user_id() );

		do_action( 'wp_enqueue_scripts' );

		$script = reset( wp_scripts()->queue );

		$this->assertTrue( isset( wp_scripts()->registered[ $script ] ) );

		wct_set_global( 'is_user', false );
		wp_scripts()->queue = $enqueued;
	}

	public function test_wct_users_talks_count_by_user_for_a_user() {
		$u1 = $this->factory->user->create();
		$u2 = $this->factory->user->create();

		$this->factory->post->create_many( 3, array(
			'post_type' => wct_get_post_type(),
			'post_status' => 'wct_pending',
			'post_author' => $u2,
		) );

		$this->factory->post->create( array(
			'post_type' => wct_get_post_type(),
			'post_status' => 'wct_pending',
			'post_author' => $u1,
		) );

		wp_set_current_user( $u2 );

		$this->assertTrue( 3 === wct_users_talks_count_by_user( 1, $u2 ) );
	}

	public function test_wct_users_talks_count_by_user_for_users() {
		$u1 = $this->factory->user->create();
		$u2 = $this->factory->user->create();

		set_current_screen( 'dashboard' );
		$this->do_admin_init();

		$u3 = $this->factory->user->create( array(
			'role' => 'rater',
		) );

		set_current_screen( 'front' );

		$this->factory->post->create_many( 4, array(
			'post_type' => wct_get_post_type(),
			'post_status' => 'wct_pending',
			'post_author' => $u2,
		) );

		$this->factory->post->create( array(
			'post_type' => wct_get_post_type(),
			'post_status' => 'wct_pending',
			'post_author' => $u1,
		) );

		// Only users having the rater capabilities can count user talks.
		wp_set_current_user( $u3 );

		$count = wp_list_pluck( wct_users_talks_count_by_user(), 'count_talks', 'post_author' );
		$this->assertTrue( (int) $count[$u2] === 4 && (int) $count[$u1] === 1 );
	}
}
