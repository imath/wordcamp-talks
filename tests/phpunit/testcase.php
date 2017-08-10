<?php

class WordCampTalkProposalsTest extends WP_UnitTestCase {

	function setUp() {
		parent::setUp();

		$this->set_permalink_structure( '/%postname%/' );
	}

	function tearDown() {
		parent::tearDown();

		$this->set_permalink_structure( '' );
	}
}
