<?php
/**
 * WP.org User Profile parser.
 *
 * @since  1.1.0
 *
 * @package WordCamp Talks
 * @subpackage users\classes
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Profiles.WordPress.org Profile parser
 *
 * Extracts the Display name and Biography of the signup user.
 *
 * @since 1.1.0
 */
class WordCamp_Talks_Users_Profile_Parser {
	public function __construct() {}

	/**
	 * Gets the source content of a User's profile.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $username The username of the user signing-up.
	 * @return string           The source with the allowed tags.
	 */
	public function fetch_source_html( $username = '' ) {
		$version = wct()->version;

		if ( empty( $username ) ) {
			return new WP_Error( 'missing_parameter', __( 'The user name was not provided.', 'wordcamp-talks' ) );
		}

		$remote_url = wp_safe_remote_get( 'https://profiles.wordpress.org/' . sanitize_title( $username ), array(
			'timeout' => 30,
			// Use an explicit user-agent for the plugin.
			'user-agent' => 'WCTP Profile Parser (WordCamp Talk Proposals/' . $version . '); ' . get_bloginfo( 'url' )
		) );

		if ( is_wp_error( $remote_url ) ) {
			return $remote_url;
		}

		$useful_html_elements = array(
			'title' => array(),
			'h2'    => array(
				'class' => true,
			),
			'div' => array(
				'class' => true,
			),
		);

		$this->source_content = wp_remote_retrieve_body( $remote_url );
		$this->source_content = wp_kses( $this->source_content, $useful_html_elements );

		return $this->source_content;
	}

	/**
	 * Parses the source content to find usefull informations.
	 *
	 * @since  1.1.0
	 *
	 * @param  string $username The username of the user signing-up.
	 * @return array            An array containing at least the username. Errors on fail.
	 */
	public function parse( $username = '' ) {
		if ( empty( $username ) ) {
			return array();
		}

		// Download source page to tmp file.
		$source_content = $this->fetch_source_html( $username );
		if ( is_wp_error( $source_content ) ) {
			return array( 'errors' => $source_content->get_error_messages() );
		}

		$check_title = preg_match_all( '/<title>([^"]+)<\/title>/', $source_content, $title_matches );

		if ( empty( $title_matches[1] ) || false === strpos( $title_matches[1][0], 'Profile' ) ) {
			return array( 'errors' => new WP_Error( 'unknown_profile', sprintf( __( '%s is not a valid user name on WordPress.org', 'wordcamp-talks' ), $username ) ) );
		}

		$profile_data = array( 'user_name' => $username );

		if ( preg_match_all( '/<h2 class="fn">([^"]+)<\/h2>/', $source_content, $matches ) ) {
			if ( ! empty( $matches[1] ) ) {
				$profile_data['display_name'] = trim( reset( $matches[1] ) );
			}
		}

		if ( preg_match_all( '/<div class="item-meta-about">([^"]+)<\/div>/', $source_content, $matches ) ) {
			if ( ! empty( $matches[1] ) ) {
				$profile_data['description'] = trim( reset( $matches[1] ) );
			}
		}

		return $profile_data;
	}
}
