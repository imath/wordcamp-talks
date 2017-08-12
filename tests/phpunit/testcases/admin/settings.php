<?php
/**
 * @group admin
 */
class WordCampTalkProposalsTest_Admin_Settings extends WordCampTalkProposalsTest {
	public function setUp() {
		parent::setUp();

		$this->current_user_id = get_current_user_id();
		$this->current_screen  = get_current_screen();

		wp_set_current_user( 1 );
		set_current_screen( 'settings_page_wc_talks' );

		remove_action( 'admin_init', '_maybe_update_core' );
		remove_action( 'admin_init', '_maybe_update_plugins' );
		remove_action( 'admin_init', '_maybe_update_themes' );

		do_action( 'admin_init' );
	}

	public function tearDown() {
		unset( $GLOBALS['screen'] );
		wp_set_current_user( $this->current_user_id );

		parent::tearDown();
	}

	public function test_wct_admin_register_settings() {
		$wct_settings = wp_list_pluck( get_registered_settings(), 'group' );

		$this->assertTrue( in_array( 'wc_talks', $wct_settings, true ) );
	}
}
