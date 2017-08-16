<?php

/**
 * @group rewrites
 */
class WordCampTalkProposalsTest_Core_Rewrites extends WordCampTalkProposalsTest {

	public function setUp() {
		parent::setUp();

		add_filter( 'locale', array( $this, 'return_fr_FR' ) );
	}

	public function tearDown() {
		remove_filter( 'locale', array( $this, 'return_fr_FR' ) );

		parent::tearDown();
	}

	public function return_fr_FR() {
		return 'fr_FR';
	}

	public function test_wct_get_user_slug() {
		$this->assertTrue( 'user' === wct_get_user_slug() );

		unload_textdomain( 'wordcamp-talks' );

		wct_load_textdomain();

		$this->assertTrue( 'user' === wct_get_user_slug(), 'Slugs should not be translated once set.' );
	}

	public function test_wct_get_user_slug_switched_lang() {
		wct_delete_rewrite_rules();

		unload_textdomain( 'wordcamp-talks' );

		wct_load_textdomain();

		flush_rewrite_rules( false );

		$this->assertTrue( 'utilisateur' === wct_get_user_slug() );

		unload_textdomain( 'wordcamp-talks' );
		remove_filter( 'locale', array( $this, 'return_fr_FR' ) );

		wct_load_textdomain();

		flush_rewrite_rules( false );
	}
}
