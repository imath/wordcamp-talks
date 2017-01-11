<?php
/**
 * WordCamp Talks Post Status Admin.
 *
 * @package WordCamp Talks
 * @subpackage admin/admin
 *
 * @since 1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WordCamp_Talks_Post_Status' ) ) :

	class WordCamp_Talks_Post_Status {

		public static function start() {
			$wct = wct();

			if ( empty( $wct->statuses ) ) {
				$wct->statuses = new self;
			}

			return $wct->statuses;
		}

		public function __construct() {
			// register the various hooks
			add_action( 'admin_head-post.php', array( $this, 'set_publishing_actions' ) );
			add_action( 'admin_head-post-new.php', array( $this, 'set_publishing_actions' ) );
		}

		function set_publishing_actions() {
			global $post;
			if ( 'talks' === $post->post_type ) {
				echo '<style type="text/css">
				.misc-pub-section.misc-pub-visibility,
				.misc-pub-section.curtime.misc-pub-curtime {
					display: none;
				}
				</style>';
			}
		}
	}
endif;
