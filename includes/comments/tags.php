<?php
/**
 * WordCamp Talks User's comments tags
 *
 * @package WordCamp Talks
 * @subpackage comments/tags
 *
 * @since 1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** Comment Loop **************************************************************/

/**
 * Initialize the user's comments loop.
 *
 * @since 1.0.0
 *
 * @param array $args {
 *     Arguments for customizing comments retrieved in the loop.
 *     Arguments must be passed as an associative array.
 *
 *     @type integer $user_id To restrict the loop to one user (defaults to displayed user)
 *     @type string  $status  To limit the query to comments having a certain status (defaults to approve).
 *     @type integer $number  Number of results per page.
 *     @type integer $page    The page of results to display.
 * }
 * @return bool True if comments were found, false otherwise.
 */
function wct_comments_has_comments( $args = array() ) {

	$r = wp_parse_args( $args, array(
		'user_id' => wct_users_displayed_user_id(),
		'status'  => 'approve',
		'number'  => wct_talks_per_page(),
		'page'    => 1,
	) );

	// Get the WordCamp Talks
	$comment_query_loop = new WordCamp_Talks_Comments_Loop( array(
		'user_id' => (int) $r['user_id'],
		'status'  => $r['status'],
		'number'  => (int) $r['number'],
		'page'    => (int) $r['page'],
	) );

	// Setup the global query loop
	wct()->comment_query_loop = $comment_query_loop;

	return $comment_query_loop->has_items();
}

/**
 * Get the comments returned by the template loop.
 *
 * @since 1.0.0
 *
 * @return array List of comments.
 */
function wct_comments_the_comments() {
	return wct()->comment_query_loop->items();
}

/**
 * Get the current comment object in the loop.
 *
 * @since 1.0.0
 *
 * @return object The current comment within the loop.
 */
function wct_comments_the_comment() {
	return wct()->comment_query_loop->the_item();
}

/** Loop Output ***************************************************************/

/**
 * Displays a message if no comments were found.
 *
 * @since 1.0.0
 */
function wct_comments_no_comment_found() {
	echo wct_comments_get_no_comment_found();
}

	/**
	 * Gets a message if no comments were found.
	 *
	 * @since 1.0.0
	 *
	 * @return string the message if no comments were found
	 */
	function wct_comments_get_no_comment_found() {
		return sprintf(
			__( 'It looks like %s has not commented on any Talk Proposals yet', 'wordcamp-talks' ),
			wct_users_get_displayed_user_displayname()
		);
	}

/**
 * Output the pagination count for the current comments loop.
 *
 * @since 1.0.0
 */
function wct_comments_pagination_count() {
	echo wct_comments_get_pagination_count();
}

	/**
	 * Return the pagination count for the current comments loop.
	 *
	 * @since 1.0.0
	 *
	 * @return string HTML for the pagination count.
	 */
	function wct_comments_get_pagination_count() {
		$query_loop = wct()->comment_query_loop;
		$start_num  = intval( ( $query_loop->page - 1 ) * $query_loop->per_page ) + 1;
		$from_num   = number_format_i18n( $start_num );
		$to_num     = number_format_i18n( ( $start_num + ( $query_loop->per_page - 1 ) > $query_loop->total_comment_count ) ? $query_loop->total_comment_count : $start_num + ( $query_loop->number - 1 ) );
		$total      = number_format_i18n( $query_loop->total_comment_count );

		return sprintf( _n( 'Viewing %1$s to %2$s (of %3$s comment)', 'Viewing %1$s to %2$s (of %3$s comments)', $total, 'wordcamp-talks' ), $from_num, $to_num, $total );
	}

/**
 * Output the pagination links for the current comments loop.
 *
 * @since 1.0.0
 */
function wct_comments_pagination_links() {
	echo wct()->comment_query_loop->pag_links;
}

/**
 * Output the ID of the comment currently being iterated on.
 *
 * @since 1.0.0
 */
function wct_comments_the_comment_id() {
	echo wct_comments_get_comment_id();
}

	/**
	 * Return the ID of the comment currently being iterated on.
	 *
	 * @since 1.0.0
	 *
	 * @return integer ID of the current comment.
	 */
	function wct_comments_get_comment_id() {
		return wct()->comment_query_loop->comment->comment_ID;
	}

/**
 * Output the avatar of the author of the comment currently being iterated on.
 *
 * @since 1.0.0
 */
function wct_comments_the_comment_author_avatar() {
	echo wct_comments_get_comment_author_avatar();
}

	/**
	 * Return the avatar of the author of the comment currently being iterated on.
	 *
	 * @since 1.0.0
	 *
	 * @return string the avatar.
	 */
	function wct_comments_get_comment_author_avatar() {
		$author = wct()->comment_query_loop->comment->user_id;
		$avatar = get_avatar( $author );

		return sprintf( '<a href="%1$s" title="%2$s">%3$s</a>',
			esc_url( wct_users_get_user_profile_url( $author ) ),
			esc_attr__( 'View user\'s profile', 'wordcamp-talks' ),
			$avatar
		);
	}

/**
 * Output the mention to add before the title of the comment currently being iterated on.
 *
 * @since 1.0.0
 */
function wct_comments_before_comment_title() {
	echo wct_comments_get_before_comment_title();
}

	/**
	 * Return the mention to add before the title of the comment currently being iterated on.
	 *
	 * @since 1.0.0
	 *
	 * @return string the mention to prefix the title with.
	 */
	function wct_comments_get_before_comment_title() {
		/**
		 * Filter here to edit the current comment prefix.
		 *
		 * @since  1.0.0
		 *
		 * @param  string $value  The prefix.
		 */
		return apply_filters( 'wct_comments_get_before_comment_title', esc_html__( 'In reply to:', 'wordcamp-talks' ) );
	}

/**
 * Output the permalink of the comment currently being iterated on.
 *
 * @since 1.0.0
 */
function wct_comments_the_comment_permalink() {
	echo wct_comments_get_comment_permalink();
}

	/**
	 * Return the permalink of the comment currently being iterated on.
	 *
	 * @since 1.0.0
	 *
	 * @return string the comment's permalink.
	 */
	function wct_comments_get_comment_permalink() {
		$comment = wct()->comment_query_loop->comment;
		$comment_link = wct_comments_get_comment_link( $comment );

		return esc_url( $comment_link );
	}

/**
 * Output the title attribute of the comment currently being iterated on.
 *
 * @since 1.0.0
 */
function wct_comments_the_comment_title_attribute() {
	echo wct_comments_get_comment_title_attribute();
}

	/**
	 * Return the title attribute of the comment currently being iterated on.
	 *
	 * @since 1.0.0
	 *
	 * @return string the title attribute.
	 */
	function wct_comments_get_comment_title_attribute() {
		$comment = wct()->comment_query_loop->comment;
		$title = '';

		$talk = $comment->comment_post_ID;

		if ( ! empty( $comment->talk ) ) {
			$talk = $comment->talk;
		}

		$talk = get_post( $talk );

		if ( ! empty( $talk->post_password ) ) {
			$title = _x( 'Protected:', 'talk permalink title protected attribute', 'wordcamp-talks' ) . ' ';
		} else if ( ! empty( $talk->post_status ) && 'private' == $talk->post_status ) {
			$title = _x( 'Private:', 'talk permalink title private attribute', 'wordcamp-talks' ) . ' ';
		}

		$title .= $talk->post_title;

		/**
		 * Filter here to edit the title attribute of the link.
		 *
		 * @since  1.0.0
		 *
		 * @param  string   $title   The title attribute.
		 * @param  WP_Post  $talk    The talk object.
		 * @param  object   $comment The comment object.
		 */
		return apply_filters( 'wct_comments_get_comment_title_attribute', esc_attr( $title ), $talk, $comment );
	}

/**
 * Output the title of the comment currently being iterated on.
 *
 * @since 1.0.0
 */
function wct_comments_the_comment_title() {
	echo wct_comments_get_comment_title();
}

	/**
	 * Return the title of the comment currently being iterated on.
	 *
	 * @since 1.0.0
	 *
	 * @return string the title.
	 */
	function wct_comments_get_comment_title() {
		$comment = wct()->comment_query_loop->comment;

		/**
		 * When the talk has a private status, we're applying a dashicon to a span
		 * So we need to only allow this tag when sanitizing the output
		 */
		if ( isset( $comment->post_status ) && 'publish' !== $comment->post_status ) {
			$title = wp_kses( get_the_title( $comment->comment_post_ID ), array( 'span' => array( 'class' => array() ) ) );
		} else {
			$title = esc_html( get_the_title( $comment->comment_post_ID ) );
		}

		return $title;
	}

/**
 * Output the excerpt of the comment currently being iterated on.
 *
 * @since 1.0.0
 */
function wct_comments_the_comment_excerpt() {
	echo wct_comments_get_comment_excerpt();
}

	/**
	 * Return the excerpt of the comment currently being iterated on.
	 *
	 * @since 1.0.0
	 *
	 * @return string the excerpt.
	 */
	function wct_comments_get_comment_excerpt() {
		$comment = wct()->comment_query_loop->comment;
		$title = '';

		$talk = $comment->comment_post_ID;

		if ( ! empty( $comment->talk ) ) {
			$talk = $comment->talk;
		}

		$talk = get_post( $talk );

		if ( post_password_required( $talk ) ) {
			$excerpt = __( 'The Talk Proposal the comment was posted on is password protected: you will need the password to view its content.', 'wordcamp-talks' );

		// Private
		} else if ( ! empty( $talk->post_status ) && 'private' == $talk->post_status && ! current_user_can( 'read_talk', $talk->ID ) ) {
			$excerpt = __( 'The Talk Proposal the comment was posted on is private: you cannot view its content.', 'wordcamp-talks' );

		// Public
		} else {
			$excerpt = get_comment_excerpt( wct()->comment_query_loop->comment->comment_ID );
		}

		/**
		 * Filter here to sanitize the comment excerpt.
		 *
		 * @since  1.0.0
		 *
		 * @param  string $excerpt the comment excerpt.
		 */
		return apply_filters( 'wct_comments_get_comment_excerpt', $excerpt );
	}

/**
 * Output the footer of the comment currently being iterated on.
 *
 * @since 1.0.0
 */
function wct_comments_the_comment_footer() {
	echo wct_comments_get_comment_footer();
}

	/**
	 * Return the footer of the comment currently being iterated on.
	 *
	 * @since 1.0.0
	 *
	 * @return string the comment footer.
	 */
	function wct_comments_get_comment_footer() {
		return sprintf( esc_html__( 'This comment was posted on %s', 'wordcamp-talks' ), get_comment_date( '', wct()->comment_query_loop->comment->comment_ID ) );
	}
