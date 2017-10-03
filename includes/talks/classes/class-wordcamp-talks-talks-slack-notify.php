<?php
/**
 * WordCamp Talks Slack notifications.
 *
 * @package WordCamp Talks
 * @subpackage talks/classes
 *
 * @since 1.1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;
/**
 * Slack notifications Class.
 *
 * @since 1.1.0
 */
class WordCamp_Talks_Talks_Slack_Notify {
	/**
	 * The Slack Attachments
	 *
	 * @var array
	 */
	public $attachments = array();

	/**
	 * The Constructor
	 *
	 * @since  1.0.0
	 *
	 * @param WordCamp_Talks_Talks_Proposal $topic The talk Object.
	 */
	public function __construct( WordCamp_Talks_Talks_Proposal $talk ) {
		$title = sprintf(
			__( '[%1$s] New talk proposal submitted: %2$s', 'wordcamp-talks' ),
			get_bloginfo( 'name', 'display' ),
			sprintf( '<%1$s|%2$s>',
				esc_url_raw( get_post_permalink( $talk->id ) ),
				esc_html__( 'Review it!', 'wordcamp-talks' )
			)
		);

		$this->attachments[] = (object) array(
			'fallback' => $title,
			'pretext'  => $title,
			'color'    => '#bb2458',
			'fields'   => array(),
		);

		$this->attachments[0]->fields[] = (object) array(
			'title' => sprintf( __( 'Speaker: %s', 'wordcamp-talks' ), esc_html( wct_users_get_user_data( 'id', $talk->author, 'display_name' ) ) ),
			'value' => esc_html( wp_trim_words( $talk->description, 30 ) ),
			'short' => false,
		);
	}

	/**
	 * Encodes the Payload in JSON.
	 *
	 * @since  1.0.0
	 *
	 * @return string The payload object encoded in JSON.
	 */
	public function get_json() {
		return json_encode( $this );
	}
}
