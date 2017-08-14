<?php

class WordCampTalkProposalsTest_Talks_Classes extends WordCampTalkProposalsTest {
	public function test_registered_widget() {
		$widgets = $GLOBALS['wp_registered_widgets'];
		$registered_names = wp_list_pluck( $widgets, 'name' );

		$names = array(
			__( 'Talk Proposals categories', 'wordcamp-talks' ),
			__( 'Talk Proposals Popular', 'wordcamp-talks' ),
		);

		$this->assertTrue( 2 === count( array_intersect( $registered_names, $names ) ) );
	}
}
