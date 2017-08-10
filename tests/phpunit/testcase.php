<?php

class WordCampTalkProposalsTest extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();

		$this->set_permalink_structure( '/%postname%/' );
	}

	public function tearDown() {
		parent::tearDown();

		$this->set_permalink_structure( '' );
	}
}
