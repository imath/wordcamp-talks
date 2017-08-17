<?php

class WordCamp_Post_Types_Plugin {
	public function __construct() {
		add_action( 'init', array( $this, 'register' ) );
	}

	public function register() {
		register_post_type( 'wcb_speaker', array(
			'labels'            => array(
				'name'          => 'Speakers',
				'singular_name' => 'Speaker',
			),
			'public'            => true,
			'capability_type'   => 'post',
			'hierarchical'      => false,
			'query_var'         => true,
		) );

		register_post_type( 'wcb_session', array(
			'labels'            => array(
				'name'          => 'Sessions',
				'singular_name' => 'Session',
			),
			'public'            => true,
			'capability_type'   => 'post',
			'hierarchical'      => false,
			'query_var'         => true,
		) );
	}
}
new WordCamp_Post_Types_Plugin;
