<?php

class WordCampTalkProposalsTest_Talks_Functions extends WordCampTalkProposalsTest {

	public function test_wct_talks_enqueue_scripts_new() {
		$enqueued = wp_scripts()->queue;
		wct_set_global( 'is_talks', true );
		wct_set_global( 'is_new', true );

		do_action( 'wp_enqueue_scripts' );

		$script = reset( wp_scripts()->queue );

		$this->assertTrue( isset( wp_scripts()->registered[ $script ] ) );
		$this->assertTrue( in_array( 'tagging', wp_scripts()->registered[ $script ]->deps, true ) );

		wct_set_global( 'is_talks', false );
		wct_set_global( 'is_new', false );
		wp_scripts()->queue = $enqueued;
	}
}
