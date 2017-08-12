<?php

class WordCampTalkProposalsTest_Talks_Classes extends WordCampTalkProposalsTest {
	public function test_registered_widget() {
		$widgets = $GLOBALS['wp_registered_widgets'];
		$registered_names = wp_list_pluck( $widgets, 'name' );

		$names = array( 'WordCamp Talk Proposal categories', 'WordCamp Talks Popular Talks' );

		$this->assertTrue( 2 === count( array_intersect( $registered_names, $names ) ) );
	}
}
