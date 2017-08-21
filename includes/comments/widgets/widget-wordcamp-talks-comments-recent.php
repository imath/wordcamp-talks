<?php
/**
 * WordCamp Talks Comments Widget.
 *
 * @package WordCamp Talks
 * @subpackage comments/widgets
 *
 * @since 1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WordCamp_Talks_Comments_Recent' ) ) :
/**
 * Recent comment about talks Widget
 *
 * @since 1.0.0
 * @since 1.1.0 Renamed from WordCamp_Talk_Recent_Comments to WordCamp_Talks_Comments_Recent.
 */
 class WordCamp_Talks_Comments_Recent extends WP_Widget_Recent_Comments {

 	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$widget_ops = array( 'classname' => 'widget_talks_recent_comments', 'description' => __( 'Latest comments about Talk Proposals', 'wordcamp-talks' ) );
		WP_Widget::__construct( 'talk-recent-comments', $name = __( 'Talk Proposals latest comments', 'wordcamp-talks' ), $widget_ops );

		$this->alt_option_name = 'widget_talks_recent_comments';

		if ( is_active_widget( false, false, $this->id_base ) ) {
			add_action( 'wp_head', array( $this, 'recent_comments_style' ) );
		}
	}

	/**
	 * Register the widget.
	 *
	 * @since 1.0.0
	 */
	public static function register_widget() {
		register_widget( 'WordCamp_Talks_Comments_Recent' );
	}

	/**
	 * Override comments query args to only onclude comments about talks.
	 *
	 * @since 1.0.0
	 *
	 * @param  array  $comment_args The comments query args.
	 * @return array                The comments query args to display comments about talks.
	 */
	public function override_comment_args( $comment_args = array() ) {
		// It's that simple !!
		$comment_args['post_type'] = wct_get_post_type();

		if ( current_user_can( 'view_talk_comments' ) ) {
			$comment_args['post_status'] = wct_talks_get_status();
		}

		// Now return these args
		return $comment_args;
	}

	/**
	 * Display the widget.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $args
	 * @param  array $instance
	 */
	public function widget( $args, $instance ) {
		/**
		 * Add filter so that post type used is talks but before the dummy var
		 * @see WordCamp_Talks_Comments::comments_widget_dummy_var()
		 */
		add_filter( 'widget_comments_args', array( $this, 'override_comment_args' ), 5, 1 );

		parent::widget( $args, $instance );

		/**
		 * Once done we need to remove the filter
		 */
		remove_filter( 'widget_comments_args', array( $this, 'override_comment_args' ), 5, 1 );
	}

	/**
	 * Update the preferences for the widget.
	 *
	 * @since 1.0.0
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['number'] = absint( $new_instance['number'] );

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset( $alloptions[ 'widget_talks_recent_comments'] ) ) {
			delete_option( 'widget_talks_recent_comments' );
		}

		return $instance;
	}
}

endif;
