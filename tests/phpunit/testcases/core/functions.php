<?php

class WordCampTalkProposalsTest_Functions extends WP_UnitTestCase {
	public function test_wct_register_post_type() {
		$registered_post_types = get_post_types();

		$this->assertTrue( isset( $registered_post_types['talks'] ) );
	}
}