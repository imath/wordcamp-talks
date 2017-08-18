<?php

class WordCampTalkProposalsTest extends WP_UnitTestCase {
	public function setUp() {
		parent::setUp();

		$this->set_permalink_structure( '/%postname%/' );

		add_filter( 'query_vars', array( $this, 'wct_query_vars' ), 10, 1 );
	}

	public function tearDown() {
		$this->set_permalink_structure( '' );

		remove_filter( 'query_vars', array( $this, 'wct_query_vars' ), 10, 1 );

		parent::tearDown();
	}

	/**
	 * Make sure the WordCamp Talks query vars are not removed from
	 * the $GLOBALS['wp']->public_query_vars when using parent::go_to()
	 * more than once.
	 */
	public function wct_query_vars( $qv = array() ) {
		return array_merge( $qv, array(
			'is_user',
			'is_comments',
			'is_rates',
			'is_to_rate',
			'is_user_talks',
			'cpaged',
			'is_action',
			'talk_search',
		) );
	}

	protected function do_admin_init() {
		remove_action( 'admin_init', '_maybe_update_core' );
		remove_action( 'admin_init', '_maybe_update_plugins' );
		remove_action( 'admin_init', '_maybe_update_themes' );

		do_action( 'admin_init' );
	}
}
