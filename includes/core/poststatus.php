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
			add_action( 'admin_head-post-new.php', array( $this, 'set_publishing_actions' ) );
			add_action( 'pre_get_posts', array( $this, 'filter_talks' ) );
		}

		/**
		 * Filter which talks are seen by who
		 *
		 * @param  \WP_Query $q the query being filtered
		 * @return void       nothing, the query is passed by reference
		 */
		function filter_talks( \WP_Query $q ) {
			if ( 'talks' === $q->get( 'post_type' ) ) {
				/**
				 * logged out users should only be able to see those talks that have been picked
				 */
				if ( ! is_user_logged_in() ) {
					$q->set( 'post_status', 'private' );
					$q->set( 'post_status', 'private' );
					return;
				}

				// are we on the frontend?
				if ( ! is_admin() ) {
					/**
					 * This use is either a speaker or someone selecting, so show more post statuses.
					 * We wouldn't want to do this in the admin area, else trashed posts and
					 * published/private posts would be impossible to see, which would be difficult
					 * to debug
					 */
					$q->set( 'post_status', array( 'private' ) );
				}

				// If the user cannot select talks, they must be a speaker, only show talks belonging to them
				if ( ! wct_user_can( 'list_all_talks' ) ) {
					$q->set( 'author', get_current_user_id() );
				}
			}
		}
	}
endif;
