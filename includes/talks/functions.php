<?php
/**
 * WordCamp Talks Talks functions.
 *
 * Functions that are specifics to talks
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** Set/Get Talk(s) ***********************************************************/

/**
 * Default status used in talk 'get' queries
 *
 * By default, 'publish'
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 *
 * @return array          the post status of talks to retrieve
 */
function wct_talks_get_status() {
	$status = array( 'publish' );

	if ( is_user_logged_in() ) {
		$status = array_merge( $status, array_keys( wct_get_statuses() ) );
	}

	/**
	 * Use this filter to override post status of talks to retieve
	 *
	 * @param  array $status
	 */
	return apply_filters( 'wct_talks_get_status', $status );
}

/**
 * Gets all WordPress built in post status (to be used in filters)
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 *
 * @param  array  $status
 * @return array          the available post status
 */
function wct_talks_get_all_status( $status = array() ) {
	return array_keys( get_post_statuses() );
}

/**
 * How much talks to retrieve per page ?
 *
 * By default, same value than regular posts
 * Uses the WordPress posts per page setting
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 *
 * @return array           the post status of talks to retrieve
 */
function wct_talks_per_page() {
	return apply_filters( 'wct_talks_per_page', wct_get_global( 'per_page' ) );
}

/**
 * Get Talks matching the query args
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 *
 * @param  array  $args custom args to merge with default ones
 * @return array        requested talks
 */
function wct_talks_get_talks( $args = array() ) {
	$get_args = array();
	$talks    = array();

	$default = array(
		'author'     => 0,
		'per_page'   => wct_talks_per_page(),
		'page'       => 1,
		'search'     => '',
		'exclude'    => '',
		'include'    => '',
		'orderby'    => 'date',
		'order'      => 'DESC',
		'meta_query' => array(),
		'tax_query'  => array(),
	);

	if ( ! empty( $args ) ) {
		$get_args = $args;
	} else {
		$main_query = wct_get_global( 'main_query' );

		if ( ! empty( $main_query['query_vars'] ) ) {
			$get_args = $main_query['query_vars'];
			unset( $main_query['query_vars'] );
		}

		$talks = $main_query;
	}

	// Parse the args
	$r = wp_parse_args( $get_args, $default );

	if ( empty( $talks ) ) {
		$talks = WordCamp_Talks_Talks_Proposal::get( $r );

		// Reset will need to be done at the end of the loop
		wct_set_global( 'needs_reset', true );
	}

	$talks = array_merge( $talks, array( 'get_args' => $r ) );

	/**
	 * @param  array $talks     associative array to find talks, total count and loop args
	 * @param  array $r         merged args
	 * @param  array $get_args  args before merge
	 */
	return apply_filters( 'wct_talks_get_talks', $talks, $r, $get_args );
}

/**
 * Gets a talk with additional metas and terms
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 *
 * @param  string                        $id_or_name ID or post_name of the talk to get.
 * @return WordCamp_Talks_Talks_Proposal             The talk object.
 */
function wct_talks_get_talk( $id_or_name = '' ) {
	if ( empty( $id_or_name ) ) {
		return false;
	}

	$talk = new WordCamp_Talks_Talks_Proposal( $id_or_name );

	/**
	 * @param  WordCamp_Talks_Talks_Proposal $talk        The talk object.
	 * @param  mixed                         $id_or_name  The ID or slug of the talk.
	 */
	return apply_filters( 'wct_talks_get_talk', $talk, $id_or_name );
}

/**
 * Gets a talk by its slug without additional metas or terms
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 *
 * @param  string $name the post_name of the talk to get
 * @return WP_Post the talk object
 */
function wct_talks_get_talk_by_name( $name = '' ) {
	if ( empty( $name ) ) {
		return false;
	}

	$talk = WordCamp_Talks_Talks_Proposal::get_talk_by_name( $name );

	/**
	 * @param  WP_Post $talk the talk object
	 * @param  string  $name the post_name of the talk
	 */
	return apply_filters( 'wct_talks_get_talk_by_name', $talk, $name );
}

/**
 * Registers a new talks meta
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 *
 * @param  string $meta_key  the identifier of the meta key to register
 * @param  string $meta_args the arguments (array of callback functions)
 */
function wct_talks_register_meta( $meta_key = '', $meta_args = '' ) {
	if ( empty( $meta_key ) || ! is_array( $meta_args ) ) {
		return false;
	}

	$wc_talks_metas = wct_get_global( 'wc_talks_metas' );

	if ( empty( $wc_talks_metas ) ) {
		$wc_talks_metas = array();
	}

	$key = sanitize_key( $meta_key );

	$args = wp_parse_args( $meta_args, array(
		'meta_key' => $key,
		'label'    => '',
		'admin'    => 'wct_meta_admin_display',
		'form'     => '',
		'single'   => 'wct_meta_single_display',
	) );

	$wc_talks_metas[ $key ] = (object) $args;

	wct_set_global( 'wc_talks_metas', $wc_talks_metas );
}

/**
 * Gets a talk meta data
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 *
 * @param  int     $talk_id  the ID of the talk
 * @param  string  $meta_key the meta key to get
 * @param  bool    $single   whether to get an array of meta or unique one
 * @return mixed             the meta value
 */
function wct_talks_get_meta( $talk_id = 0, $meta_key = '', $single = true ) {
	if ( empty( $talk_id ) || empty( $meta_key ) ) {
		return false;
	}

	$sanitized_key   = sanitize_key( $meta_key );
	$sanitized_value = false;

	$meta_value = get_post_meta( $talk_id, '_wc_talks_' . $sanitized_key, $single );

	if ( empty( $meta_value ) ) {
		return false;
	}

	// Custom sanitization
	if ( has_filter( "wct_meta_{$sanitized_key}_sanitize_display" ) ) {
		/**
		 * Use this filter if you need to apply custom sanitization to
		 * the meta value
		 * @param  mixed   $meta_value the meta value
		 * @param  string  $meta_key  the meta_key
		 */
		$sanitized_value = apply_filters( "wct_meta_{$sanitized_key}_sanitize_display", $meta_value, $meta_key );

	// Fallback to generic sanitization
	} else {

		if ( is_array( $meta_value) ) {
			$sanitized_value = array_map( 'sanitize_text_field', $meta_value );
		} else {
			$sanitized_value = sanitize_text_field( $meta_value );
		}
	}

	return apply_filters( 'wct_talks_get_meta', $sanitized_value, $meta_key, $talk_id );
}

/**
 * Updates a talk meta data
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 *
 * @param  int     $talk_id    the ID of the talk
 * @param  string  $meta_key   the meta key to update
 * @param  mixed   $meta_value the meta value to update
 * @return bool                the update meta result
 */
function wct_talks_update_meta( $talk_id = 0, $meta_key = '', $meta_value = '' ) {
	if ( empty( $talk_id ) || empty( $meta_key ) || empty( $meta_value ) ) {
		return false;
	}

	$sanitized_key   = sanitize_key( $meta_key );
	$sanitized_value = false;

	// Custom sanitization
	if ( has_filter( "wct_meta_{$sanitized_key}_sanitize_db" ) ) {
		/**
		 * Use this filter if you need to apply custom sanitization to
		 * the meta value
		 * @param  mixed   $meta_value the meta value
		 * @param  string  $meta_key  the meta_key
		 */
		$sanitized_value = apply_filters( "wct_meta_{$sanitized_key}_sanitize_db", $meta_value, $meta_key );

	// Fallback to generic sanitization
	} else {

		if ( is_array( $meta_value) ) {
			$sanitized_value = array_map( 'sanitize_text_field', $meta_value );
		} else {
			$sanitized_value = sanitize_text_field( $meta_value );
		}
	}

	if ( empty( $sanitized_value ) ) {
		return false;
	}

	return update_post_meta( $talk_id, '_wc_talks_' . $sanitized_key, $sanitized_value );
}

/**
 * Deletes a talk meta data
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 *
 * @param  int     $talk_id    the ID of the talk
 * @param  string  $meta_key   the meta key to update
 * @return bool                the delete meta result
 */
function wct_talks_delete_meta( $talk_id = 0, $meta_key = '' ) {
	if ( empty( $talk_id ) || empty( $meta_key ) ) {
		return false;
	}

	$sanitized_key = sanitize_key( $meta_key );

	return delete_post_meta( $talk_id, '_wc_talks_' . $sanitized_key );
}

/**
 * Gets talk terms given a taxonomy and args
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 *
 * @param  string $taxonomy the taxonomy identifier
 * @param  array  $args     the arguments to get the terms
 * @return array|WP_Error List of Term Objects and their children. Will return WP_Error, if any of $taxonomies
 *                        do not exist.
 */
function wct_talks_get_terms( $taxonomy = '', $args = array() ) {
	if ( empty( $taxonomy ) || ! is_array( $args ) ) {
		return false;
	}

	// Merge args
	$term_args = wp_parse_args( $args, array(
		'orderby'    => 'count',
		'hide_empty' => 0
	) );

	// get the terms for the requested taxonomy and args
	$terms = get_terms( $taxonomy, $term_args );

	/**
	 * @param  array|WP_Error $terms    the list of terms of the taxonomy
	 * @param  string         $taxonomy the taxonomy of the terms retrieved
	 * @param  array          $args     the arguments to get the terms
	 */
	return apply_filters( 'wct_talks_get_terms', $terms, $taxonomy, $args );
}

/**
 * Sets the post status of a talk
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 *
 * @param  array  $talkarr the posted arguments
 * @return string          the post status of the talk
 */
function wct_talks_insert_status( $talkarr = array() ) {
	/**
	 * @param  string  the default post status for a talk
	 * @param  array   $talkarr  the arguments of the talk to save
	 */
	return apply_filters( 'wct_talks_insert_status', wct_default_talk_status(), $talkarr );
}

/**
 * Checks if another user is editing a talk, if not
 * locks the talk for the current user.
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 *
 * @param  int $talk_id The ID of the talk to edit
 * @return int                the user id editing the talk
 */
function wct_talks_lock_talk( $talk_id = 0 ) {
	$user_id = false;

	// Bail if no ID to check
	if ( empty( $talk_id ) ) {
		return $user_id;
	}

	// Include needed file
	require_once( ABSPATH . '/wp-admin/includes/post.php' );

	$user_id = wp_check_post_lock( $talk_id );

	// If not locked, then lock it as current user is editing it.
	if( empty( $user_id ) ) {
		wp_set_post_lock( $talk_id );
	}

	return $user_id;
}

/**
 * HeartBeat callback to check if a talk is being edited by an admin
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 *
 * @param  array  $response the heartbeat response
 * @param  array  $data     the data sent by heartbeat
 * @return array            Response to heartbeat
 */
function wct_talks_heartbeat_check_locked( $response = array(), $data = array() ) {

	if ( empty( $data['wc_talks_heartbeat_current_talk'] ) ) {
		return $response;
	}

	$response['wc_talks_heartbeat_response'] = wct_talks_lock_talk( $data['wc_talks_heartbeat_current_talk'] );

	return $response;
}

/**
 * Talk's editing timeout options for speakers.
 *
 * @since  1.1.0
 *
 * @return array Timeout options.
 */
function wct_talks_editing_timeout_options() {
	return array(
		'+5 minutes' => __( '5 minutes', 'wordcamp-talks' ),
		'+1 hour'    => __( 'an hour', 'wordcamp-talks' ),
		'+1 day'     => __( 'a day', 'wordcamp-talks' ),
	);
}

/**
 * Checks if a user can edit a talk
 *
 * A user can edit the talk if :
 * - he is the author
 *   - and talk was created 0 to 5 mins ago
 *   - no comment was posted on the talk
 *   - no rates was given to the talk
 *   - nobody else is currently editing the talk
 * - he is a super admin.
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 *
 * @param  WP_Post $talk the talk object
 * @return bool          whether the user can edit the talk (true), or not (false)
 */
function wct_talks_can_edit( $talk = null ) {
	// Default to can't edit !
	$retval = false;

	// Bail if we can't check anything
	if ( empty( $talk ) || ! is_a( $talk, 'WP_Post' ) ) {
		return $retval;
	}

	// Map to edit others talks if current user is not the author
	if ( (int) wct_users_current_user_id() !== (int) $talk->post_author ) {

		// Do not edit talks of the super admin
		if ( ! is_super_admin( $talk->post_author ) ) {
			return current_user_can( 'edit_others_talks' );
		} else {
			return $retval;
		}

	}

	/** Now we're dealing with author's capacitiy to edit the talk ****************/

	/**
	 * First, give the possibility to early override
	 *
	 * If you want to avoid the comment/rate and time lock, you
	 * can use this filter.
	 *
	 * @param bool whether to directly check user's capacity
	 * @param WP_Post $talk   the talk object
	 */
	$early_can_edit = apply_filters( 'wct_talks_pre_can_edit', false, $talk );

	if ( ! empty( $early_can_edit ) || is_super_admin() ) {
		return current_user_can( 'edit_talk', $talk->ID );
	}

	// Talk was commented or rated
	if ( ! empty( $talk->comment_count ) || get_post_meta( $talk->ID, '_wc_talks_average_rate', true ) ) {
		return $retval;
	}

	// Talk status has changed.
	if ( 'wct_pending' !== get_post_status( $talk ) ) {
		return $retval;
	}

	/**
	 * This part is based on bbPress's bbp_past_edit_lock() function
	 *
	 * In the case of an Talk Management system, i find the way bbPress
	 * manage the time a content can be edited by its author very interesting
	 * and simple (simplicity is allways great!)
	 */

	// Bail if empty date
	if ( empty( $talk->post_date_gmt ) ) {
		return $retval;
	}

	// Period of time
	$lockable  = apply_filters( 'wct_talks_can_edit_time', wct_talk_editing_timeout() );

	// Now
	$cur_time  = current_time( 'timestamp', true );

	// Add lockable time to post time
	$lock_time = strtotime( $lockable, strtotime( $talk->post_date_gmt ) );

	// Compare
	if ( $cur_time <= $lock_time ) {
		$retval = current_user_can( 'edit_talk', $talk->ID );
	}

	/**
	 * Late filter
	 *
	 * @param bool    $retval whether to allow the user's to edit the talk
	 * @param WP_Post $talk   the talk object
	 */
	return apply_filters( 'wct_talks_can_edit', $retval, $talk );
}

/**
 * Saves a talk entry in posts table
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 *
 * @param  array  $talkarr the posted arguments
 * @return int    the ID of the created or updated talk
 */
function wct_talks_save_talk( $talkarr = array() ) {
	if ( ! is_array( $talkarr ) ) {
		return false;
	}

	if ( empty( $talkarr['_the_title'] ) || empty( $talkarr['_the_content'] ) ) {
		return false;
	}

	// Init update vars
	$update         = false;
	$old_taxonomies = array();
	$old_metas      = array();

	if ( ! empty( $talkarr['_the_id'] ) ) {
		/**
		 * Passing the id attribute to WordCamp_Talks_Talks_Proposal will get the previous version of the talk
		 * In this case we don't need to set the author or status
		 */
		$talk = new WordCamp_Talks_Talks_Proposal( absint( $talkarr['_the_id'] ) );

		if ( ! empty( $talk->id ) ) {
			$update = true;

			// Get old metas
			if ( ! empty( $talk->metas['keys'] ) ) {
				$old_metas = $talk->metas['keys'];
			}

			// Get old taxonomies
			if ( ! empty( $talk->taxonomies ) )  {
				$old_taxonomies = $talk->taxonomies;
			}

		// If we don't find the talk, stop!
		} else {
			return false;
		}

	} else {
		$talk         = new WordCamp_Talks_Talks_Proposal();
		$talk->author = wct_users_current_user_id();
		$talk->status = wct_talks_insert_status( $talkarr );
	}

	// Set the title and description of the talk
	$talk->title       = $talkarr['_the_title'];
	$talk->description = $talkarr['_the_content'];

	// Handling categories
	if ( ! empty( $talkarr['_the_category'] ) && is_array( $talkarr['_the_category'] ) ) {
		$categories = wp_parse_id_list( $talkarr['_the_category'] );

		$talk->taxonomies = array(
			wct_get_category() => $categories
		);

	// In case of an update, we need to eventually remove all categories
	} else if ( empty( $talkarr['_the_category'] ) && ! empty( $old_taxonomies[ wct_get_category() ] ) ) {

		// Reset categories if some were set
		if ( is_array( $talk->taxonomies ) ) {
			$talk->taxonomies[ wct_get_category() ] = array();
		} else {
			$talk->taxonomies = array( wct_get_category() => array() );
		}
	}

	// Handling tags
	if ( ! empty( $talkarr['_the_tags'] ) && is_array( $talkarr['_the_tags'] ) ) {
		$tags = array_map( 'strip_tags', $talkarr['_the_tags'] );

		$tags = array(
			wct_get_tag() => join( ',', $tags )
		);

		if ( ! empty( $talk->taxonomies ) ) {
			$talk->taxonomies = array_merge( $talk->taxonomies, $tags );
		} else {
			$talk->taxonomies = $tags;
		}

	// In case of an update, we need to eventually remove all tags
	} else if ( empty( $talkarr['_the_tags'] ) && ! empty( $old_taxonomies[ wct_get_tag() ] ) ) {

		// Reset tags if some were set
		if ( is_array( $talk->taxonomies ) ) {
			$talk->taxonomies[ wct_get_tag() ] = '';
		} else {
			$talk->taxonomies = array( wct_get_tag() => '' );
		}
	}

	// Handling metas. By default none, but can be useful for plugins
	if ( ! empty( $talkarr['_the_metas'] ) && is_array( $talkarr['_the_metas'] ) ) {
		$talk->metas = $talkarr['_the_metas'];
	}

	// Check if some metas need to be deleted
	if ( ! empty( $old_metas ) && is_array( $talk->metas ) ) {
		$to_delete = array_diff( $old_metas, array_keys( $talk->metas ) );

		if ( ! empty( $to_delete ) ) {
			$to_delete = array_fill_keys( $to_delete, 0 );
			$talk->metas = array_merge( $talk->metas, $to_delete );
		}
	}

	/**
	 * Do stuff before the talk is saved
	 *
	 * @param  array $talkarr the posted values
	 * @param  bool  $update  whether it's an update or not
	 */
	do_action( 'wct_talks_before_talk_save', $talkarr, $update );

	$saved_id = $talk->save();

	if ( ! empty( $saved_id ) ) {

		$hook = 'insert';

		if ( ! empty( $update ) ) {
			$hook = 'update';
		}

		/**
		 * Do stuff after the talk was saved
		 *
		 * Call wct_talks_after_insert_talk for a new talk
		 * Call wct_talks_after_update_talk for an updated talk
		 *
		 * @param  int    $inserted_id the inserted id
		 * @param  object $talk the talk
		 */
		do_action( "wct_talks_after_{$hook}_talk", $saved_id, $talk );
	}

	return $saved_id;
}

/** Talk urls *****************************************************************/

/**
 * Gets the permalink to the talk
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 *
 * @param  WP_Post|int  $talk the talk object or its ID
 * @return string|bool     the permalink to the talk, false if the talk is not set
 */
function wct_talks_get_talk_permalink( $talk = null ) {
	// Bail if not set
	if ( empty( $talk ) ) {
		return false;
	}

	// Not a post, try to get it
	if ( ! is_a( $talk, 'WP_Post' ) ) {
		$talk = get_post( $talk );
	}

	if ( empty( $talk->ID ) ) {
		return false;
	}

	/**
	 * @param  string        permalink to the talk
	 * @param  WP_Post $talk the talk object
	 */
	return apply_filters( 'wct_talks_get_talk_permalink', get_permalink( $talk ), $talk );
}

/**
 * Gets the comment link of a talk
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 *
 * @param  WP_Post $talk the talk object or its ID
 * @return string          the comment link of a talk
 */
function wct_talks_get_talk_comments_link( $talk = null ) {
	$comments_link = wct_talks_get_talk_permalink( $talk ) . '#comments';

	/**
	 * @param  string  $comments_link comment link
	 * @param  WP_Post $talk          the talk object
	 */
	return apply_filters( 'wct_talks_get_talk_comments_link', $comments_link, $talk );
}

/** Template functions ********************************************************/

/**
 * Adds needed scripts to rate the talk or add tags to it
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 */
function wct_talks_enqueue_scripts() {
	if ( ! wct_is_talks() ) {
		return;
	}

	$deps    = array();
	$url     = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
	$js_vars = array(
		'canonical' => remove_query_arg( array( 'success', 'error', 'info' ), $url ),
	);

	// Single talk > ratings
	if ( wct_is_single_talk() && ! wct_is_edit() && ! wct_is_rating_disabled() ) {
		$user_id = wct_users_current_user_id();

		if ( ! current_user_can( 'view_talk_rates' ) ) {
			$talk_id = 0;

			if ( ! empty( wct()->query_loop->talk->ID ) ) {
				$talk_id = wct()->query_loop->talk->ID;
			}

			$ratings  = array( 'average' => wct_count_ratings( $talk_id, $user_id ), 'users' => array() );
			$users_nb = (int) ! empty( $ratings['average'] );

			if ( $users_nb ) {
				$ratings['users'] = array( $user_id );
			}

			$one_rate    = '';
			$success_msg = __( 'Thanks for your rating:', 'wordcamp-talks' );

		} else {
			$ratings     = (array) wct_count_ratings();
			$users_nb    = count( $ratings['users'] );
			$one_rate    = esc_html__( 'One rating', 'wordcamp-talks' );
			$success_msg = esc_html__( 'Thanks! The average rating is now:', 'wordcamp-talks' );
		}

		$hintlist = (array) wct_get_hint_list();

		$js_vars = array_merge( $js_vars, array(
			'raty_loaded'  => 1,
			'ajaxurl'      => admin_url( 'admin-ajax.php', 'relative' ),
			'wait_msg'     => esc_html__( 'Saving your rating; please wait', 'wordcamp-talks' ),
			'success_msg'  => $success_msg,
			'error_msg'    => esc_html__( 'Oops! Something went wrong', 'wordcamp-talks' ),
			'average_rate' => $ratings['average'],
			'rate_nb'      => $users_nb,
			'one_rate'     => $one_rate,
			'x_rate'       => esc_html__( '% ratings', 'wordcamp-talks' ),
			'readonly'     => true,
			'can_rate'     => current_user_can( 'rate_talks' ),
			'not_rated'    => esc_html__( 'Not rated yet', 'wordcamp-talks' ),
			'hints'        => $hintlist,
			'hints_nb'     => count( $hintlist ),
			'wpnonce'      => wp_create_nonce( 'wct_rate' ),
		) );

		if ( current_user_can( 'rate_talks' ) ) {
			$js_vars['readonly'] = ( 0 != $users_nb ) ? in_array( $user_id, $ratings['users'] ) : false;
		}

		$deps = array( 'jquery-raty' );
	}

	// Form > tags
	if ( wct_is_addnew() || wct_is_edit() ) {
		// Default dependencies
		$deps = array( 'tagging' );

		// Defaul js vars
		$js_vars = array_merge( $js_vars, array(
			'tagging_loaded'  => 1,
			'taginput_name'   => 'wct[_the_tags][]',
			'duplicate_tag'   => __( 'Duplicate tag:',       'wordcamp-talks' ),
			'forbidden_chars' => __( 'Forbidden character:', 'wordcamp-talks' ),
			'forbidden_words' => __( 'Forbidden word:',      'wordcamp-talks' ),
		) );

		// Add HeartBeat if talk is being edited
		if ( wct_is_edit() ) {
			$deps = array_merge( $deps, array( 'heartbeat' ) );
			$js_vars = array_merge( $js_vars, array(
				'talk_id' => wct_get_single_talk_id(),
				'pulse'   => 'fast',
				'warning' => esc_html__( 'An admin is currently editing this Talk Proposal, please try to edit your Talk Proposal later.', 'wordcamp-talks' ),
			) );
		}

		$js_vars = apply_filters( 'wct_talks_form_script_vars', $js_vars );
	}

	wp_enqueue_script ( 'wc-talks-script', wct_get_js_script( 'script' ), $deps, wct_get_version(), true );
	wp_localize_script( 'wc-talks-script', 'wct_vars', $js_vars );
}
add_action( 'wp_enqueue_scripts', 'wct_talks_enqueue_scripts', 12 );

/**
 * Builds the loop query arguments
 *
 * By default,it's an empty array as the plugin is first
 * using WordPress main query & retrieved posts. This function
 * allows to override it with custom arguments usfin the filter
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 *
 * @param  string $type is this a single talk?
 * @return array        the loop args
 */
function wct_talks_query_args( $type = '' ) {
	/**
	 * Use this filter to overide loop args
	 * @see wct_talks_has_talks() for the list of available ones
	 *
	 * @param  array by default an empty array
	 */
	$query_args = apply_filters( 'wct_talks_query_args', array() );

	if ( 'single' == $type ) {
		$query_arg = array_intersect_key( $query_args, array( 'talk_name' => false ) );
	}

	return $query_args;
}

/**
 * Sets the available orderby possible filters
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 */
function wct_talks_get_order_options() {
	$order_options =  array(
		'date'           => __( 'Latest', 'wordcamp-talks' ),
		'comment_count'  => __( 'Most commented', 'wordcamp-talks' ),
	);

	// Only if not disabled.
	if ( ! wct_is_rating_disabled() && current_user_can( 'view_talk_rates' ) ) {
		$order_options['rates_count'] = __( 'Highest Rating', 'wordcamp-talks' );
	}

	if ( ! current_user_can( 'view_talk_comments' ) ) {
		unset( $order_options['comment_count'] );
	}

	/**
	 * @param  array $order_options the list of available order options
	 */
	return apply_filters( 'wct_talks_get_order_options', $order_options );
}

/**
 * Prefix the Talk title with its status
 *
 * @since  1.1.0
 *
 * @param  string  $title The Talk title.
 * @param  WP_Post $talk  The Talk object
 * @return string         The Talk title.
 */
function wct_talks_status_title_prefix( $title = '', $talk = null ) {
	$status = get_post_status( $talk );

	if ( ! wct_is_supported_statuses( $status ) || ! is_single() ) {
		return $title;
	}

	/**
	 * The did_the_content global makes sure the prefix is only added
	 * to the entry main title.
	 *
	 * We're waiting for wp_head to be completed as some plugins are using
	 * the_content inside the header tag to customize the description meta tag.
	 */
	if ( did_action( 'wp_head' ) && doing_filter( 'the_content' ) ) {
		wct_set_global( 'did_the_content', true );
	}

	if ( wct_get_global( 'did_the_content' ) ) {
		return $title;
	}

	return wct_talks_status_get_title_prefix( $status, $title );
}

/**
 * Returns the status prefix output.
 *
 * @since  1.1.0
 *
 * @param  string $status The name of the status
 * @param  string $title  The Text to be prefixed
 * @return string         The status prefix or the prefixed text.
 */
function wct_talks_status_get_title_prefix( $status = '', $title = '' ) {
	if ( ! $status ) {
		return false;
	}

	$status_o = get_post_status_object( $status );

	if ( ! isset( $status_o->label ) ) {
		return false;
	}

	if ( empty( $title ) ) {
		$title = '';
	}

	return sprintf( '<span class="talk-status %1$s">%2$s</span> %3$s',
		sanitize_html_class( $status ),
		$status_o->label,
		$title
	);
}

/** Handle Talk actions *******************************************************/

/**
 * Handles posting talks
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 */
function wct_talks_post_talk() {
	// Bail if not a post request
	if ( 'POST' != strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
		return;
	}

	// Bail if not a post talk request
	if ( empty( $_POST['wct'] ) || ! is_array( $_POST['wct'] ) ) {
		return;
	}

	// Bail if it's an update
	if ( ! empty( $_POST['wct']['_the_id'] ) ) {
		return;
	}

	// Check nonce
	check_admin_referer( 'wct_save' );

	$redirect = wct_get_redirect_url();

	// Check capacity
	if ( ! current_user_can( 'publish_talks' ) ) {
		// Redirect to main archive page and inform the user he cannot publish talks.
		wp_safe_redirect( add_query_arg( 'error', 3, $redirect ) );
		exit();
	}

	$posted = array_diff_key( $_POST['wct'], array( 'save' => 'submit' ) );

	// Title & content are required
	if ( empty( $posted['_the_title'] ) || empty( $posted['_the_content'] ) ) {
		// Add feedback to the user
		wct_add_message( array(
			'error' => array( 4 ),
		) );

		// Simply stop, so that the user keeps the posted values.
		return;
	}

	$id = wct_talks_save_talk( $posted );

	if ( empty( $id ) ) {
		// Redirect to an empty form and inform him the talk was not created.
		wp_safe_redirect( add_query_arg( 'error', 5, wct_get_form_url() ) );
		exit();

	} else {
		$talk = get_post( $id );

		/**
		 * Filter here to add custom feedback message IDs.
		 *
		 * @since 1.1.0
		 *
		 * @param array   $feedback_message The list of feedback message ids.
		 * @param WP_Post $talk             The inserted Talk Proposal object.
		 */
		$feedback_message = array(
			'error'   => array(),
			'success' => array( 3 ),
			'info'    => array(),
		);

		if ( 'pending' == $talk->post_status ) {
			// Build pending message.
			$feedback_message['info'][] = 2;

		// redirect to the talk
		} else {
			$redirect = wct_talks_get_talk_permalink( $talk );
		}

		if ( ! wct_users_get_current_user_description() ) {
			$feedback_message['info'][] = 5;
		} elseif ( 1 === wct_users_talks_count_by_user( 1, $talk->post_author ) ) {
			$feedback_message['info'][] = 6;
		}

		wp_safe_redirect( wct_add_feedback_args( array_filter( $feedback_message ), $redirect ) );
		exit();
	}
}

/**
 * Handles updating a talk
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 */
function wct_talks_update_talk() {
	global $wp_query;
	// Bail if not a post request
	if ( 'POST' != strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
		return;
	}

	// Bail if not a post talk request
	if ( empty( $_POST['wct'] ) || ! is_array( $_POST['wct'] ) ) {
		return;
	}

	// Bail if it's not an update
	if ( empty( $_POST['wct']['_the_id'] ) ) {
		return;
	}

	// Check nonce
	check_admin_referer( 'wct_save' );

	$redirect = wct_get_redirect_url();

	// Get talk name
	$talk_name = get_query_var( wct_get_post_type() );

	// Get Talk Object
	$talk = get_queried_object();

	// If queried object doesn't match or wasn't helpfull, try to get the talk using core function
	if ( empty( $talk->post_name ) || empty( $talk_name ) || $talk_name != $talk->post_name ) {
		$talk = wct_talks_get_talk_by_name( $talk_name );
	}

	// Found no talk, redirect and inform the user
	if ( empty( $talk->ID ) ) {
		// Redirect to main archive page
		wp_safe_redirect( add_query_arg( 'error', 9, $redirect ) );
		exit();
	}

	// Checks if the user can edit the talk
	if ( ! wct_talks_can_edit( $talk ) ) {
		// Redirect to main archive page
		wp_safe_redirect( add_query_arg( 'error', 2, $redirect ) );
		exit();
	}

	$updated = array_diff_key( $_POST['wct'], array( 'save' => 'submit' ) );

	// Title & content are required
	if ( empty( $updated['_the_title'] ) || empty( $updated['_the_content'] ) ) {
		// Add feedback to the user
		wct_add_message( array(
			'error' => array( 4 ),
		) );

		// Simply stop, so that the user keeps the posted values.
		return;
	}

	// Reset '_the_id' param to the ID of the talk found
	$updated['_the_id'] = $talk->ID;
	$feedback_message = array(
		'error'   => array(),
		'success' => array(),
		'info'    => array(),
	);

	// Update the talk
	$id = wct_talks_save_talk( $updated );

	if ( empty( $id ) ) {
		// Set the feedback for the user
		$feedback_message['error'][] = 10;

		// Redirect to the form
		$redirect = wct_get_form_url( wct_edit_slug(), $talk_name );

	// Redirect to the talk
	} else {
		$feedback_message['success'][] = 4;
		$redirect = wct_talks_get_talk_permalink( $id );
	}

	wp_safe_redirect( wct_add_feedback_args( array_filter( $feedback_message ), $redirect ) );
	exit();
}

function wct_do_embed( $content ) {
	global $wp_embed;

	return $wp_embed->autoembed( $content );
}
