<?php
/**
 * WordCamp Talks Screens Class.
 *
 * @package WordCamp Talks
 * @subpackage core/classes
 *
 * @since 1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WordCamp_Talks_Core_Screens' ) ) :

/**
 * Replace the content when in a plugin's front end part
 *
 * @since  1.0.0
 * @since  1.1.0 Moved in its own file.
 */
class WordCamp_Talks_Core_Screens {
	public function __construct( $template_args = null ) {
		if ( ! empty( $template_args ) ) {
			$this->template_args = $template_args;
		}

		add_filter( 'the_content', array( $this, 'replace_the_content' ), 10, 1 );
	}

	public static function start( $context, $template_args ) {
		$wct = wct();

		if ( empty( $wct->screens ) ) {
			$wct->screens = new self( $template_args );
		}

		return $wct->screens;
	}

	public function replace_the_content( $content ) {
		if ( 'single-talk' === $this->template_args['context'] ) {
			// Do not filter the content inside the document header
			if ( doing_action( 'wp_head' ) ) {
				return $content;
			}

			$content = wct_buffer_single_talk( $content );
		} else {
			$content = wct_buffer_template_part( $this->template_args['template_slug'], $this->template_args['template_name'], false );
		}

		return $content;
	}
}

endif;
