<?php
/**
 * WordCamp Talks Rewrites.
 *
 * Mainly inspired by bbPress way of dealing with rewrites
 * @see bbpress main class.
 *
 * Most of the job is done in the class WordCamp_Talks_Core_Rewrites
 * @see  core/classes
 *
 * @package WordCamp Talks
 * @subpackage core
 *
 * @since 1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Checks whether the current site is using default permalink settings or custom one
 *
 * @since 1.0.0
 *
 * @return bool True if custom permalink are one, false otherwise
 */
function wct_is_pretty_links() {
	$pretty_links = wct_get_global( 'pretty_links' );
	return (bool) apply_filters( 'wct_is_pretty_links', ! empty( $pretty_links ) );
}

/**
 * Get the slug currently set in WordPress Rewrites for the requested id.
 *
 * @since  1.1.0
 *
 * @param  string $id      The slug id.
 * @param  string $default The default slug to use if the slug is not available in DB yet.
 * @return string          The slug.
 */
function wct_get_current_slug( $id = '', $default = '' ) {
	$slugs = wct()->slugs;
	$slug  = $default;

	if ( isset( $slugs[$id] ) ) {
		$slug = $slugs[$id];
	}

	return $slug;
}

/**
 * Get the slug used for paginated requests
 *
 * @since 1.0.0
 *
 * @global object $wp_rewrite The WP_Rewrite object
 * @return string The pagination slug
 */
function wct_paged_slug() {
	global $wp_rewrite;

	if ( empty( $wp_rewrite ) ) {
		return false;
	}

	return $wp_rewrite->pagination_base;
}

/**
 * Rewrite id for the user's profile.
 *
 * @since 1.0.0
 *
 * @param  string $default The rewrite id to use by default.
 * @return string          The user's profile rewrite id.
 */
function wct_user_rewrite_id( $default = 'is_user' ) {
	return apply_filters( 'wct_user_rewrite_id', $default );
}

/**
 * Rewrite id for the user's rates.
 *
 * @since 1.0.0
 *
 * @param  string $default The rewrite id to use by default.
 * @return string          The user's rates rewrite id.
 */
function wct_user_rates_rewrite_id( $default = 'is_rates' ) {
	return apply_filters( 'wct_user_rates_rewrite_id', $default );
}

/**
 * Rewrite id for the user's to rate.
 *
 * @since 1.0.0
 *
 * @param  string $default The rewrite id to use by default.
 * @return string          The user's to rate rewrite id.
 */
function wct_user_to_rate_rewrite_id( $default = 'is_to_rate' ) {
	return apply_filters( 'wct_user_to_rate_rewrite_id', $default );
}

/**
 * Rewrite id for the user's talks.
 *
 * @since 1.0.0
 *
 * @param  string $default The rewrite id to use by default.
 * @return string          The user's talks rewrite id.
 */
function wct_user_talks_rewrite_id( $default = 'is_user_talks' ) {
	return apply_filters( 'wct_user_talks_rewrite_id', $default );
}

/**
 * Rewrite id for the user's comments.
 *
 * @since 1.0.0
 *
 * @param  string $default The rewrite id to use by default.
 * @return string          The user's comments rewrite id.
 */
function wct_user_comments_rewrite_id( $default = 'is_comments' ) {
	return apply_filters( 'wct_user_comments_rewrite_id', $default );
}

/**
 * Rewrite id for actions.
 *
 * @since 1.0.0
 *
 * @param  string $default The rewrite id to use by default.
 * @return string          The actions rewrite id.
 */
function wct_action_rewrite_id( $default = 'is_action' ) {
	return apply_filters( 'wct_action_rewrite_id', $default );
}

/**
 * Rewrite id for searching in talks.
 *
 * @since 1.0.0
 *
 * @param  string $default The rewrite id to use by default.
 * @return string          Searching in talks rewrite id.
 */
function wct_search_rewrite_id( $default = 'talk_search' ) {
	return apply_filters( 'wct_search_rewrite_id', $default );
}

/**
 * Rewrite id for user's comments pagination.
 *
 * @since 1.0.0
 *
 * @param  string $default The rewrite id to use by default.
 * @return string          User's comments pagination rewrite id.
 */
function wct_cpage_rewrite_id( $default = 'cpaged' ) {
	return apply_filters( 'wct_cpage_rewrite_id', $default );
}

/**
 * Customize the root slug of the plugin.
 *
 * @since  1.0.0
 * @since  1.1.0 Not filerable anymore.
 *
 * @param  boolean $skip_option Whether to skip the slug check in DB.
 * @return string               the root slug.
 */
function wct_root_slug( $skip_option = false ) {
	/* Translators: This string is used in urls, make sure to avoid using special chars */
	$slug = _x( 'talk-proposals', 'default root slug', 'wordcamp-talks' );

	if ( ! $skip_option ) {
		$slug = wct_get_current_slug( 'root', $slug );
	}

	return $slug;
}

/**
 * Build the talk slug (root + talk ones).
 *
 * @since 1.0.0
 * @since  1.1.0 Not filerable anymore.
 *
 * @param  boolean $skip_option Whether to skip the slug check in DB.
 * @return string               the talk slug (prefixed by the root one).
 */
function wct_talk_slug( $skip_option = false ) {
	return wct_root_slug( $skip_option ) . '/' . wct_get_talk_slug( $skip_option );
}

	/**
	 * Customize the talk (post type) slug of the plugin.
	 *
	 * @since  1.0.0
	 * @since  1.1.0 Not filerable anymore.
	 *
	 * @param  boolean $skip_option Whether to skip the slug check in DB.
	 * @return string               The talk slug.
	 */
	function wct_get_talk_slug( $skip_option = false ) {
		/* Translators: This string is used in urls, make sure to avoid using special chars */
		$slug = _x( 'talk', 'default talk slug', 'wordcamp-talks' );

		if ( ! $skip_option ) {
			$slug = wct_get_current_slug( 'talk', $slug );
		}

		return $slug;
	}

/**
 * Build the category slug (root + category ones).
 *
 * @since 1.0.0
 * @since 1.1.0 Not filerable anymore.
 *
 * @param  boolean $skip_option Whether to skip the slug check in DB.
 * @return string               The category slug (prefixed by the root one).
 */
function wct_category_slug( $skip_option = false ) {
	return wct_root_slug( $skip_option ) . '/' . wct_get_category_slug( $skip_option );
}

	/**
	 * Customize the category (hierarchical taxonomy) slug of the plugin.
	 *
	 * @since  1.0.0
	 * @since  1.1.0 Not filerable anymore.
	 *
	 * @param  boolean $skip_option Whether to skip the slug check in DB.
	 * @return string               The category slug.
	 */
	function wct_get_category_slug( $skip_option = false ) {
		/* Translators: This string is used in urls, make sure to avoid using special chars */
		$slug = _x( 'category', 'default category slug', 'wordcamp-talks' );

		if ( ! $skip_option ) {
			$slug = wct_get_current_slug( 'category', $slug );
		}

		return $slug;
	}

/**
 * Build the tag slug (root + tag ones).
 *
 * @since  1.0.0
 * @since  1.1.0 Not filerable anymore.
 *
 * @param  boolean $skip_option Whether to skip the slug check in DB.
 * @return string               The tag slug (prefixed by the root one).
 */
function wct_tag_slug( $skip_option = false ) {
	return wct_root_slug( $skip_option ) . '/' . wct_get_tag_slug( $skip_option );
}

	/**
	 * Customize the tag (non hierarchical taxonomy) slug of the plugin.
	 *
	 * @since  1.0.0
	 * @since  1.1.0 Not filerable anymore.
	 *
	 * @param  boolean $skip_option Whether to skip the slug check in DB.
	 * @return string               The tag slug.
	 */
	function wct_get_tag_slug( $skip_option = false ) {
		/* Translators: This string is used in urls, make sure to avoid using special chars */
		$slug = _x( 'tag', 'default tag slug', 'wordcamp-talks' );

		if ( ! $skip_option ) {
			$slug = wct_get_current_slug( 'tag', $slug );
		}

		return $slug;
	}

/**
 * Build the user's profile slug (root + user ones).
 *
 * @since  1.0.0
 * @since  1.1.0 Not filerable anymore.
 *
 * @param  boolean $skip_option Whether to skip the slug check in DB.
 * @return string               The user slug (prefixed by the root one).
 */
function wct_user_slug( $skip_option = false ) {
	return wct_root_slug( $skip_option ) . '/' . wct_get_user_slug( $skip_option );
}

	/**
	 * Customize the user's profile slug of the plugin.
	 *
	 * @since  1.0.0
	 * @since  1.1.0 Not filerable anymore.
	 *
	 * @param  boolean $skip_option Whether to skip the slug check in DB.
	 * @return string               The user slug.
	 */
	function wct_get_user_slug( $skip_option = false ) {
		/* Translators: This string is used in urls, make sure to avoid using special chars */
		$slug = _x( 'user', 'default user slug', 'wordcamp-talks' );

		if ( ! $skip_option ) {
			$slug = wct_get_current_slug( 'user', $slug );
		}

		return $slug;
	}

/**
 * Customize the user's profile rates slug of the plugin.
 *
 * @since  1.0.0
 * @since  1.1.0 Not filerable anymore.
 *
 * @param  boolean $skip_option Whether to skip the slug check in DB.
 * @return string               The user's profile rates slug.
 */
function wct_user_rates_slug( $skip_option = false ) {
	/* Translators: This string is used in urls, make sure to avoid using special chars */
	$slug = _x( 'ratings', 'default ratings slug', 'wordcamp-talks' );

	if ( ! $skip_option ) {
		$slug = wct_get_current_slug( 'rate', $slug );
	}

	return $slug;
}

/**
 * Customize the user's profile to rate slug of the plugin.
 *
 * @since  1.0.0
 * @since  1.1.0 Not filerable anymore.
 *
 * @param  boolean $skip_option Whether to skip the slug check in DB.
 * @return string               The user's profile to rate slug.
 */
function wct_user_to_rate_slug( $skip_option = false ) {
	/* Translators: This string is used in urls, make sure to avoid using special chars */
	$slug = _x( 'to-rate', 'default user to rate slug', 'wordcamp-talks' );

	if ( ! $skip_option ) {
		$slug = wct_get_current_slug( 'to_rate', $slug );
	}

	return $slug;
}

/**
 * Customize the user's profile talks section slug of the plugin.
 *
 * @since  1.0.0
 * @since  1.1.0 Not filerable anymore.
 *
 * @return string the user's profile talks section slug.
 */
function wct_user_talks_slug() {
	return wct_root_slug();
}

/**
 * Customize the user's profile comments slug of the plugin.
 *
 * @since  1.0.0
 * @since  1.1.0 Not filerable anymore.
 *
 * @param  boolean $skip_option Whether to skip the slug check in DB.
 * @return string               The user's profile comments slug.
 */
function wct_user_comments_slug( $skip_option = false ) {
	/* Translators: This string is used in urls, make sure to avoid using special chars */
	$slug = _x( 'comments', 'default comments slug', 'wordcamp-talks' );

	if ( ! $skip_option ) {
		$slug = wct_get_current_slug( 'user_comments', $slug );
	}

	return $slug;
}

/**
 * Build the action slug (root + action ones).
 *
 * @since  1.0.0
 * @since  1.1.0 Not filerable anymore.
 *
 * @param  boolean $skip_option Whether to skip the slug check in DB.
 * @return string               The action slug (prefixed by the root one).
 */
function wct_action_slug( $skip_option = false ) {
	return wct_root_slug( $skip_option ) . '/' . wct_get_action_slug( $skip_option );
}

	/**
	 * Customize the action slug of the plugin.
	 *
	 * @since  1.0.0
	 * @since  1.1.0 Not filerable anymore.
	 *
	 * @param  boolean $skip_option Whether to skip the slug check in DB.
	 * @return string               The action slug.
	 */
	function wct_get_action_slug( $skip_option = false ) {
		/* Translators: This string is used in urls, make sure to avoid using special chars */
		$slug = _x( 'action', 'default action slug', 'wordcamp-talks' );

		if ( ! $skip_option ) {
			$slug = wct_get_current_slug( 'action', $slug );
		}

		return $slug;
	}

/**
 * Customize the add (action) slug of the plugin.
 *
 * @since  1.0.0
 * @since  1.1.0 Not filerable anymore.
 *
 * @param  boolean $skip_option Whether to skip the slug check in DB.
 * @return string               The add (action) slug.
 */
function wct_addnew_slug( $skip_option = false ) {
	/* Translators: This string is used in urls, make sure to avoid using special chars */
	$slug = _x( 'add', 'default add talk action slug', 'wordcamp-talks' );

	if ( ! $skip_option ) {
		$slug = wct_get_current_slug( 'add', $slug );
	}

	return $slug;
}

/**
 * Customize the edit (action) slug of the plugin.
 *
 * @since  1.0.0
 * @since  1.1.0 Not filerable anymore.
 *
 * @param  boolean $skip_option Whether to skip the slug check in DB.
 * @return string               The edit (action) slug.
 */
function wct_edit_slug( $skip_option = false ) {
	/* Translators: This string is used in urls, make sure to avoid using special chars */
	$slug = _x( 'edit', 'default edit talk action slug', 'wordcamp-talks' );

	if ( ! $skip_option ) {
		$slug = wct_get_current_slug( 'edit', $slug );
	}

	return $slug;
}

/**
 * Build the signup slug.
 *
 * @since 1.0.0
 * @since  1.1.0 Not filerable anymore.
 *
 * @param  boolean $skip_option Whether to skip the slug check in DB.
 * @return string               The signup slug (prefixed by the root one).
 */
function wct_signup_slug( $skip_option = false ) {
	/* Translators: This string is used in urls, make sure to avoid using special chars */
	$slug = _x( 'sign-up', 'default sign-up action slug', 'wordcamp-talks' );

	if ( ! $skip_option ) {
		$slug = wct_get_current_slug( 'signup', $slug );
	}

	return $slug;
}

/**
 * Customize the comment pagination slug of the plugin in user's profile.
 *
 * @since  1.0.0
 * @since  1.1.0 Not filerable anymore.
 *
 * @param  boolean $skip_option Whether to skip the slug check in DB.
 * @return string               The comment pagination slug.
 */
function wct_cpage_slug( $skip_option = false ) {
	/* Translators: This string is used in urls, make sure to avoid using special chars */
	$slug = _x( 'cpage', 'default comments pagination slug', 'wordcamp-talks' );

	if ( ! $skip_option ) {
		$slug = wct_get_current_slug( 'cpage', $slug );
	}

	return $slug;
}

/**
 * Delete a blogs rewrite rules, so that they are automatically rebuilt on
 * the subsequent page load.
 *
 * @since 1.0.0
 */
function wct_delete_rewrite_rules() {
	delete_option( 'rewrite_rules' );
}
