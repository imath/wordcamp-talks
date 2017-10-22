<?php
/**
 * WordCamp Talks template functions.
 *
 * @package   WordCamp Talks
 * @subpackage core/template-functions
 *
 * @since 1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Check the main WordPress query to match WordCamp Talks conditions
 * Eventually Override query vars and set global template conditions / vars
 *
 * This the key function of the plugin, it is definining the templates
 * to load and is setting the displayed user.
 *
 * @since 1.0.0
 *
 * @param WP_Query $posts_query The WP_Query instance
 */
function wct_parse_query( $posts_query = null ) {
	// Bail if $posts_query is not the main loop
	if ( ! $posts_query->is_main_query() ) {
		return;
	}

	// Bail if filters are suppressed on this query
	if ( true === $posts_query->get( 'suppress_filters' ) ) {
		return;
	}

	// Handle the specific queries in the plugin's Admin screens
	if ( wct_is_admin() ) {

		// Build meta_query if orderby rates is set
		if ( ! wct_is_rating_disabled() && ! empty( $_GET['orderby'] ) && 'rates_count' == $_GET['orderby'] ) {
			$posts_query->set( 'meta_query', array(
				array(
					'key'     => '_wc_talks_average_rate',
					'compare' => 'EXISTS'
				)
			) );

			// Set the orderby talk var
			wct_set_global( 'orderby', 'rates_count' );
		}

		// Build a meta query to filter by workflow state
		if ( ! empty( $_REQUEST['workflow_states'] ) ) {
			$admin_meta_query = array();

			if ( 'pending' == $_REQUEST['workflow_states'] ) {
				$admin_meta_query = array(
					'key'     => '_wc_talks_workflow_state',
					'compare' => 'NOT EXISTS'
				);
			} else {
				$admin_meta_query = array(
					'key'     => '_wc_talks_workflow_state',
					'compare' => '=',
					'value'   => $_REQUEST['workflow_states']
				);
			}

			$posts_query->set( 'meta_query', array( $admin_meta_query ) );
		}

		do_action( 'wct_admin_request', $posts_query );

		return;
	}

	// Bail if else where in admin
	if ( is_admin() ) {
		return;
	}

	// Talks post type for a later use
	$talk_post_type = wct_get_post_type();

	/** User's profile ************************************************************/

	// Are we requesting the user-profile template ?
	$user = $posts_query->get( wct_user_rewrite_id() );

	if ( ! empty( $user ) ) {

		if ( ! is_numeric( $user ) ) {
			// Get user by his username
			$user = wct_users_get_user_data( 'slug', $user );
		} else {
			// Get user by his id
			$user = wct_users_get_user_data( 'id', $user );
		}

		// No user id: no profile!
		if ( empty( $user->ID ) || true === apply_filters( 'wct_users_is_spammy', is_multisite() && is_user_spammy( $user ), $user ) ) {
			$posts_query->set_404();
			return;
		}

		// Set the displayed user id
		wct_set_global( 'is_user', absint( $user->ID ) );

		// Make sure the post_type is set to talks.
		$posts_query->set( 'post_type', $talk_post_type );

		// Are we requesting user talks.
		if ( user_can( $user->ID, 'publish_talks' ) ) {
			$user_talks = $posts_query->get( wct_user_talks_rewrite_id() );
		}

		if ( user_can( $user->ID, 'rate_talks' ) ) {
			// Are we requesting user rates
			$user_rates    = $posts_query->get( wct_user_rates_rewrite_id() );

			// Are we requesting user's ideas to rate?
			$user_to_rate  = $posts_query->get( wct_user_to_rate_rewrite_id() );
		}

		if ( user_can( $user->ID, 'comment_talks' ) ) {
			// Or user comments ?
			$user_comments = $posts_query->get( wct_user_comments_rewrite_id() );
		}

		if ( ! empty( $user_rates ) && ! wct_is_rating_disabled() ) {
			// We are viewing user's rates
			wct_set_global( 'is_user_rates', true );

			// Define the Meta Query to get his rates
			$posts_query->set( 'meta_query', array(
				array(
					'key'     => '_wc_talks_rates',
					'value'   => ';i:' . $user->ID .';',
					'compare' => 'LIKE'
				)
			) );

		} else if ( ! empty( $user_to_rate ) ) {
			// We are viewing user's ideas to rate
			wct_set_global( 'is_user_to_rate', true );

			// Define the Meta Query to get the not rated yet talks
			$posts_query->set( 'meta_query', array(
				'relation' => 'OR',
				array(
					'key'     => '_wc_talks_rates',
					'value'   => ';i:' . $user->ID .';',
					'compare' => 'NOT LIKE'
				),
				array(
					'key'     => '_wc_talks_average_rate',
					'compare' => 'NOT EXISTS'
				)
			) );

			// We need to see all ideas, not only the one of the current displayed user
			$posts_query->set( 'author', 0 );

		} else if ( ! empty( $user_comments ) ) {
			// We are viewing user's comments
			wct_set_global( 'is_user_comments', true );

			/**
			 * Make sure no result.
			 * Query will be built later in user comments loop
			 */
			$posts_query->set( 'p', -1 );

		} elseif ( ! empty( $user_talks ) ) {
			// We are viewing user's talks
			wct_set_global( 'is_user_talks', true );

			// Set the author of the talks as the displayed user
			$posts_query->set( 'author', $user->ID  );

		} else {
			wct_set_global( 'is_user_home', true );
		}

		// No stickies on user's profile
		$posts_query->set( 'ignore_sticky_posts', true );

		// Set the displayed user.
		wct_users_set_displayed_user( $user );

		if ( current_user_can( 'view_other_profiles', $user->ID ) ) {
			// Make sure no 404
			$posts_query->is_404  = false;
		} else {
			// Make sure it is a 404!
			$posts_query->is_404  = true;
			return;
		}
	}

	/** Actions (New Talk) ********************************************************/

	$action = $posts_query->get( wct_action_rewrite_id() );

	if ( ! empty( $action ) ) {
		// Make sure the post type is set to talks
		$posts_query->set( 'post_type', $talk_post_type );

		// Define a global to inform we're dealing with an action
		wct_set_global( 'is_action', true );

		// Is the new talk form requested ?
		if ( wct_addnew_slug() == $action ) {
			// Yes so set the corresponding var
			wct_set_global( 'is_new', true );

			/**
			 * Make sure no result.
			 * We are not querying any content, but creating one
			 */
			$posts_query->set( 'p', -1 );

		// Edit action ?
		} else if ( wct_edit_slug() == $action ) {
			// Yes so set the corresponding var
			wct_set_global( 'is_edit', true );

		// Signup support
		} else if ( wct_signup_slug() == $action && wct_is_signup_allowed_for_current_blog() ) {
			// Set the signup global var
			wct_set_global( 'is_signup', true );

			/**
			 * Make sure no result.
			 * We are not querying any content, but creating one
			 */
			$posts_query->set( 'p', -1 );

		} else if ( has_action( 'wct_custom_action' ) ) {
			/**
			 * Allow plugins to other custom talk actions
			 *
			 * @param string   $action      The requested action
			 * @param WP_Query $posts_query The WP_Query instance
			 */
			do_action( 'wct_custom_action', $action, $posts_query );
		} else {
			$posts_query->set_404();
			return;
		}
	}

	/** Talks by category *********************************************************/

	$category = $posts_query->get( wct_get_category() );

	if ( ! empty( $category ) ) {
		// Make sure the post type is set to talks
		$posts_query->set( 'post_type', $talk_post_type );

		// Define the current category
		wct_set_global( 'is_category', $category );
	}

	/** Talks by tag **************************************************************/

	$tag = $posts_query->get( wct_get_tag() );

	if ( ! empty( $tag ) ) {
		// Make sure the post type is set to talks
		$posts_query->set( 'post_type', $talk_post_type );

		// Define the current tag
		wct_set_global( 'is_tag', $tag );
	}

	/** Searching talks ***********************************************************/

	$search = $posts_query->get( wct_search_rewrite_id() );

	if ( ! empty( $search ) ) {
		// Make sure the post type is set to talks
		$posts_query->set( 'post_type', $talk_post_type );

		// Define the query as a search one
		$posts_query->set( 'is_search', true );

		/**
		 * Temporarly set the 's' parameter of WP Query
		 * This will be reset while building talks main_query args
		 * @see wct_set_template()
		 */
		$posts_query->set( 's', $search );

		// Set the search conditionnal var
		wct_set_global( 'is_search', true );
	}

	/** Changing order ************************************************************/

	// Here we're using built-in var
	$orderby = $posts_query->get( 'orderby' );

	// Make sure we are ordering talks
	if ( ! empty( $orderby ) && $talk_post_type == $posts_query->get( 'post_type' ) ) {

		if ( ! wct_is_rating_disabled() && 'rates_count' == $orderby ) {
			/**
			 * It's an order by rates request, set the meta query to achieve this.
			 * Here we're not ordering yet, we simply make sure to get talks that
			 * have been rated.
			 * Order will happen thanks to wct_set_rates_count_orderby()
			 * filter.
			 */
			$posts_query->set( 'meta_query', array(
				array(
					'key'     => '_wc_talks_average_rate',
					'compare' => 'EXISTS'
				)
			) );
		}

		// Set the order by var
		wct_set_global( 'orderby', $orderby );
	}

	// Set the talk archive var if viewing talks archive
	if ( $posts_query->is_post_type_archive() ) {
		wct_set_global( 'is_talks_archive', true );
	}

	// Finally if post_type is talks, then we're in a plugin's area.
	if ( $talk_post_type === $posts_query->get( 'post_type' ) ) {
		wct_set_global( 'is_talks', true );

		// Reset the pagination
		if ( -1 !== $posts_query->get( 'p' ) ) {
			$posts_query->set( 'posts_per_page', wct_talks_per_page() );
		}
	}
}

/**
 * Loads the plugin's stylesheet
 *
 * @since 1.0.0
 */
function wct_enqueue_style() {
	$style_deps = apply_filters( 'wct_style_deps', array( 'dashicons' ) );
	wp_enqueue_style( 'wc-talks-style', wct_get_stylesheet(), $style_deps, wct_get_version() );
}

/** Conditional template tags *************************************************/

/**
 * Is this a plugin's Administration screen?
 *
 * @since 1.0.0
 *
 * @return boolean True if displaying a plugin's admin screen. False otherwise
 */
function wct_is_admin() {
	$retval = false;

	// using this as is_admin() can be true in case of AJAX
	if ( ! function_exists( 'get_current_screen' ) ) {
		return $retval;
	}

	// Get current screen
	$current_screen = get_current_screen();

	// Make sure the current screen post type is step and is the talks one
	if ( ! empty( $current_screen->post_type ) && wct_get_post_type() == $current_screen->post_type ) {
		$retval = true;
	}

	return $retval;
}

/**
 * Is this Plugin's front end area?
 *
 * @since 1.0.0
 *
 * @return boolean True if viewing a plugin's front end page. False otherwise
 */
function wct_is_talks() {
	return (bool) wct_get_global( 'is_talks' );
}

/**
 * Is this the new talk form?
 *
 * @since 1.0.0
 *
 * @return boolean True if on the addnew form. False otherwise.
 */
function wct_is_addnew() {
	return (bool) wct_get_global( 'is_new' );
}

/**
 * Is this the edit talk form?
 *
 * @since 1.0.0
 *
 * @return boolean True if on the edit form. False otherwise.
 */
function wct_is_edit() {
	return (bool) wct_get_global( 'is_edit' );
}

/**
 * Is this the signup form?
 *
 * @since 1.0.0
 *
 * @return boolean True if on the edit form. False otherwise.
 */
function wct_is_signup() {
	return (bool) wct_get_global( 'is_signup' );
}

/**
 * Are we viewing a single talk?
 *
 * @since 1.0.0
 *
 * @return boolean True if on a single talk template. False otherwise.
 */
function wct_is_single_talk() {
	return (bool) is_singular( wct_get_post_type() );
}

/**
 * Current ID for the talk being viewed.
 *
 * @since 1.0.0
 *
 * @return integer The current talk ID.
 */
function wct_get_single_talk_id() {
	return (int) wct_get_global( 'single_talk_id' );
}

/**
 * Are we viewing talks archive?
 *
 * @since 1.0.0
 *
 * @return boolean True if on talks archive. False otherwise.
 */
function wct_is_talks_archive() {
	$retval = false;

	if ( is_post_type_archive( wct_get_post_type() ) || wct_get_global( 'is_talks_archive' ) ) {
		$retval = true;
	}

	return $retval;
}

/**
 * Are we viewing talks by category?
 *
 * @since 1.0.0
 *
 * @return boolean True if viewing talks categorized in a sepecific term. False otherwise.
 */
function wct_is_category() {
	$retval = false;

	if ( is_tax( wct_get_category() ) || wct_get_global( 'is_category' ) ) {
		$retval = true;
	}

	return $retval;
}

/**
 * Are we viewing talks by tag ?
 *
 * @since 1.0.0
 *
 * @return boolean True if viewing talks tagged with a sepecific term. False otherwise.
 */
function wct_is_tag() {
	$retval = false;

	if ( is_tax( wct_get_tag() ) || wct_get_global( 'is_tag' ) ) {
		$retval = true;
	}

	return $retval;
}

/**
 * Get / Set the current term being viewed.
 *
 * @since 1.0.0
 *
 * @return object The current term.
 */
function wct_get_current_term() {
	$current_term = wct_get_global( 'current_term' );

	if ( empty( $current_term ) ) {
		$current_term = get_queried_object();
		wct_set_global( 'current_term', $current_term );
	}

	return $current_term;
}

/**
 * Get the current term name.
 *
 * @since 1.0.0
 *
 * @return string The term name.
 */
function wct_get_term_name() {
	$term = wct_get_current_term();

	return $term->name;
}


/**
 * Get the search query.
 *
 * @since  1.1.0
 *
 * @return string The search query
 */
 function wct_get_search_query() {
 	return get_query_var( wct_search_rewrite_id() );
 }

/**
 * Are we searching talks?
 *
 * @since 1.0.0
 *
 * @return boolean True if a talk search is performed. False otherwise.
 */
function wct_is_search() {
	$retval = false;

	if ( get_query_var( wct_search_rewrite_id() ) || wct_get_global( 'is_search' ) ) {
		$retval = true;
	}

	return $retval;
}

/**
 * Has the order changed to the type being checked?
 *
 * @since 1.0.0
 *
 * @param  string  $type The order to check
 * @return boolean       True if the order has changed from default one. False otherwise.
 */
function wct_is_orderby( $type = '' ) {
	$retval = false;

	$orderby = wct_get_global( 'orderby' );

	if ( empty( $orderby ) ) {
		$orderby = get_query_var( 'orderby' );
	}

	if ( ! empty( $orderby ) && $orderby == $type ) {
		$retval = true;
	}

	return $retval;
}

/**
 * Are viewing a user's profile?
 *
 * @since 1.0.0
 *
 * @return boolean True a user's profile is being viewed. False otherwise.
 */
function wct_is_user_profile() {
	return (bool) wct_get_global( 'is_user' );
}

/**
 * Are we viewing comments in user's profile?
 *
 * @since 1.0.0
 *
 * @return boolean True if viewing user's profile comments. False otherwise.
 */
function wct_is_user_profile_comments() {
	return (bool) wct_get_global( 'is_user_comments' );
}

/**
 * Are we viewing rates in user's profile?
 *
 * @since 1.0.0
 *
 * @return boolean True if viewing user's profile rates. False otherwise.
 */
function wct_is_user_profile_rates() {
	return (bool) wct_get_global( 'is_user_rates' );
}

/**
 * Are we viewing the "to rate" area of the user's profile?
 *
 * @since 1.0.0
 *
 * @return boolean True if viewing user's profile to rate. False otherwise.
 */
function wct_is_user_profile_to_rate() {
	return (bool) wct_get_global( 'is_user_to_rate' );
}

/**
 * Are we viewing talks in user's profile?
 *
 * @since 1.0.0
 *
 * @return boolean True if viewing talks in the user's profile. False otherwise.
 */
function wct_is_user_profile_talks() {
	return (bool) wct_get_global( 'is_user_talks' );
}

/**
 * Are we viewing the "home" page of the user's profile?
 *
 * @since 1.0.0
 *
 * @return boolean True if viewing user's profile home page. False otherwise.
 */
function wct_is_user_profile_home() {
	return (bool) wct_get_global( 'is_user_home' );
}

/**
 * Is this self profile?
 *
 * @since 1.0.0
 *
 * @return boolean True if current user is viewing his profile. False otherwise.
 */
function wct_is_current_user_profile() {
	$current_user      = wct_get_global( 'current_user' );
	$displayed_user_id = wct_get_global( 'is_user' );

	if ( empty( $current_user->ID ) ) {
		return false;
	}

	return (int) $current_user->ID === (int) $displayed_user_id;
}

/**
 * Reset the page (post) title depending on the context.
 *
 * @since 1.0.0
 *
 * @param  string $context The WordCamp Talk context to build the title for.
 * @return string          The post title.
 */
function wct_reset_post_title( $context = '' ) {
	$post_title = wct_archive_title();

	switch( $context ) {
		case 'archive' :
			if ( current_user_can( 'publish_talks' ) ) {
				$post_title =  '<a href="' . esc_url( wct_get_root_url() ) . '">' . $post_title . '</a>';
				$post_title .= ' <a href="' . esc_url( wct_get_form_url() ) .'" class="button wpis-title-button">' . esc_html__( 'Add new', 'wordcamp-talks' ) . '</a>';
			}
			break;

		case 'taxonomy' :
			$post_title = '<a href="' . esc_url( wct_get_root_url() ) . '">' . $post_title . '</a>';
			$post_title .= '<span class="talk-title-sep"></span>' . wct_get_term_name();
			break;

		case 'user-profile':
			$post_title = '<a href="' . esc_url( wct_get_root_url() ) . '">' . $post_title . '</a>';
			$post_title .= '<span class="talk-title-sep"></span>' . esc_html__( 'Speaker&#39;s profile', 'wordcamp-talks' );
			break;

		case 'new-talk' :
			$post_title = '<a href="' . esc_url( wct_get_root_url() ) . '">' . $post_title . '</a>';
			$post_title .= '<span class="talk-title-sep"></span>' . __( 'New Talk', 'wordcamp-talks' );
			break;

		case 'edit-talk' :
			$post_title = '<a href="' . esc_url( wct_get_root_url() ) . '">' . $post_title . '</a>';
			$post_title .= '<span class="talk-title-sep"></span>' . __( 'Edit Talk', 'wordcamp-talks' );
			break;

		case 'signup' :
			$post_title = '<a href="' . esc_url( wct_get_root_url() ) . '">' . $post_title . '</a>';
			$post_title .= '<span class="talk-title-sep"></span>' . __( 'Create an account', 'wordcamp-talks' );
			break;
	}

	/**
	 * Filter here to edit the post title.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $post_title The title for the template
	 * @param  string $context    The WordCamp Talk Proposals context.
	 */
	return apply_filters( 'wct_reset_post_title', $post_title, $context );
}

/**
 * Filters the <title> content.
 *
 * @since 1.0.0
 *
 * @param  array  $title The title parts.
 * @return string        The page title meta tag.
 */
function wct_title( $title_array = array() ) {
	if ( ! wct_is_talks() ) {
		return $title_array;
	}

	$new_title = array();

	if ( wct_is_addnew() ) {
		$new_title[] = esc_attr__( 'New Talk Proposal', 'wordcamp-talks' );
	} elseif ( wct_is_edit() ) {
		$new_title[] = esc_attr__( 'Edit Talk Proposal', 'wordcamp-talks' );
	} elseif ( wct_is_user_profile() ) {
		$new_title[] = sprintf( esc_html__( '%s&#39;s profile', 'wordcamp-talks' ), wct_users_get_displayed_user_displayname() );
	} elseif ( wct_is_single_talk() ) {
		$new_title[] = single_post_title( '', false );
	} elseif ( is_tax() ) {
		$term = wct_get_current_term();
		if ( $term ) {
			$tax = get_taxonomy( $term->taxonomy );

			// Catch the term for later use
			wct_set_global( 'current_term', $term );

			$new_title[] = single_term_title( '', false );
			$new_title[] = $tax->labels->name;
		}
	} elseif ( wct_is_signup() ) {
		$new_title[] = esc_html__( 'Create an account', 'wordcamp-talks' );
	} else {
		$new_title[] = esc_html__( 'Talk Proposals', 'wordcamp-talks' );
	}

	// Compare new title with original title
	if ( empty( $new_title ) ) {
		return $title_array;
	}

	$title_array = array_diff( $title_array, $new_title );
	$new_title_array = array_merge( $title_array, $new_title );

	/**
	 * Filter here to edit the <title> tag for a WordCamp Talk Proposals page.
	 *
	 * NB: only for themes not using the WordPress generated Document title.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $new_title The filtered title.
	 * @param  string $sep       The separator
	 * @param  string $seplocation
	 * @param  string $title the original title meta tag
	 */
	return apply_filters( 'wct_title', $new_title_array, $title_array, $new_title );
}

/**
 * Set the document title for plugin's front end pages.
 *
 * @since  1.0.0
 *
 * @param  array  $document_title The WordPress Document title.
 * @return array                  The Document title.
 */
function wct_document_title_parts( $document_title = array() ) {
	if ( ! wct_is_talks() ) {
		return $document_title;
	}

	$new_document_title = $document_title;

	// Reset the document title if needed
	if ( ! wct_is_single_talk() ) {
		$title = (array) wct_title();

		// On user's profile, add some piece of info
		if ( wct_is_user_profile() && count( $title ) === 1 ) {
			// Seeing comments of the user
			if ( wct_is_user_profile_comments() ) {
				$title[] = __( 'Talk Proposal Comments', 'wordcamp-talks' );

				// Get the pagination page
				if ( get_query_var( wct_cpage_rewrite_id() ) ) {
					$cpage = get_query_var( wct_cpage_rewrite_id() );

				} elseif ( ! empty( $_GET[ wct_cpage_rewrite_id() ] ) ) {
					$cpage = $_GET[ wct_cpage_rewrite_id() ];
				}

				if ( ! empty( $cpage ) ) {
					$title['page'] = sprintf( __( 'Page %s', 'wordcamp-talks' ), (int) $cpage );
				}

			// Seeing Ratings for the user
			} elseif( wct_is_user_profile_rates() ) {
				$title[] = __( 'Talk Proposal Ratings', 'wordcamp-talks' );

			// Seeing To rates for the user
			} elseif( wct_is_user_profile_to_rate() ) {
				$title[] = __( 'Talk Proposals to rate', 'wordcamp-talks' );

			// Seeing Talks of the user
			} elseif( wct_is_user_profile_talks() ) {
				$title[] = __( 'Talk Proposals', 'wordcamp-talks' );

			// Seeing The root profile
			} else {
				$title[] = __( 'Profile', 'wordcamp-talks' );
			}
		}

		// Get WordPress Separator
		$sep = apply_filters( 'document_title_separator', '-' );

		$new_document_title['title'] = implode( " $sep ", array_filter( $title ) );;
	}

	// Set the site name if not already set.
	if ( ! isset( $new_document_title['site'] ) ) {
		$new_document_title['site'] = get_bloginfo( 'name', 'display' );
	}

	// Unset tagline for Plugin's front end Pages
	if ( isset( $new_document_title['tagline'] ) ) {
		unset( $new_document_title['tagline'] );
	}

	/**
	 * Filter here to edit the <title> tag for a WordCamp Talk Proposals page.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $new_document_title The WordCamp Talk Proposal generated document title.
	 * @param  string $document_title     The original WordPress generated document title.
	 */
	return apply_filters( 'wct_document_title_parts', $new_document_title, $document_title );
}

/**
 * Remove the site description from title.
 *
 * @since 1.0.0
 *
 * @param  string $new_title the filtered title
 * @param  string $sep
 * @param  string $seplocation
 */
function wct_title_adjust( $title = '', $sep = '&raquo;', $seplocation = '' ) {
	if ( ! wct_is_talks() ) {
		return $title;
	}

	$site_description = get_bloginfo( 'description', 'display' );
	if ( ! empty( $sep ) ) {
		$site_description = ' ' . $sep . ' ' . $site_description;
	}

	return str_replace( $site_description, '', $title );
}

/**
 * Output a body class if in a plugin's front end area.
 *
 * @since 1.0.0
 *
 * @param  array $wp_classes     WordPress generated Body Classes.
 * @param  array $custom_classes Custom classes if added. Defaults to false.
 * @return array                 The WordCamp Talk Proposals Body Classes.
 */
function wct_body_class( $wp_classes, $custom_classes = false ) {

	$talks_classes = array();

	/** Plugin's area *********************************************************/

	if ( wct_is_talks() ) {
		$talks_classes[] = 'talks';

		// Force Twentyseventeen to display the one column style
		if ( 'twentyseventeen' === get_template() ) {
			$wp_classes = array_diff( $wp_classes, array( 'has-sidebar', 'page-two-column', 'blog' ) );
			$talks_classes[] = 'page-one-column';
		}
	}

	/** Clean up **************************************************************/

	// Merge WP classes with Plugin's classes and remove any duplicates
	$classes = array_unique( array_merge( (array) $talks_classes, (array) $wp_classes ) );

	/**
	 * Filter here to edit the WordCamp Talk Proposals body classes.
	 *
	 * @since  1.0.0
	 *
	 * @param array $classes returned classes.
	 * @param array $wc_talks_classes specific classes to the plugin.
	 * @param array $wp_classes regular WordPress classes.
	 * @param array $custom_classes.
	 */
	return apply_filters( 'wct_body_class', $classes, $talks_classes, $wp_classes, $custom_classes );
}

/**
 * Adds a 'type-page' class as the page template is the the most commonly targetted
 * as the root template.
 *
 * @since  1.0.0
 *
 * @param  $wp_classes
 * @param  $theme_class
 * @return array Plugin's Post Classes
 */
function wct_post_class( $wp_classes, $theme_class ) {
	if ( wct_is_talks() ) {
		$classes = array_unique( array_merge( array( 'type-page' ), (array) $wp_classes ) );
	} else {
		$classes = $wp_classes;
	}

	return $classes;
}

/**
 * Reset postdata if needed
 *
 * @since 1.0.0
 */
function wct_maybe_reset_postdata() {
	if ( wct_get_global( 'needs_reset' ) ) {
		wp_reset_postdata();

		do_action( 'wct_maybe_reset_postdata' );
	}
}

/**
 * Get the WP Nav Items for the WordCamp Talk Proposals main areas.
 *
 * @since  1.1.0
 *
 * @return array A list of WP Nav Items object.
 */
function wct_get_nav_items() {
	$nav_items = array(
		'wct_archive'    => array(
			'id'         => 'wct-archive',
			'title'      => html_entity_decode( wct_archive_title(), ENT_QUOTES, get_bloginfo( 'charset' ) ),
			'url'        => esc_url_raw( wct_get_root_url() ),
			'object'     => 'wct-archive',
		),
		'wct_new'        => array(
			'id'         => 'wct-new',
			'title'      => html_entity_decode( __( 'New Talk Proposal', 'wordcamp-talks' ), ENT_QUOTES, get_bloginfo( 'charset' ) ),
			'url'        => esc_url_raw( wct_get_form_url() ),
			'object'     => 'wct-new',
		),
		'wct_my_profile' => array(
			'id'         => 'wct-profile',
			'title'      => html_entity_decode( __( 'My profile', 'wordcamp-talks' ), ENT_QUOTES, get_bloginfo( 'charset' ) ),
			'url'        => esc_url_raw( wct_users_get_logged_in_profile_url() ),
			'object'     => 'wct-profile',
		),
	);

	if ( wct_is_signup_allowed() ) {
		$nav_items['wct_signup'] = array(
			'id'         => 'wct-signup',
			'title'      => html_entity_decode( __( 'Sign up', 'wordcamp-talks' ), ENT_QUOTES, get_bloginfo( 'charset' ) ),
			'url'        => esc_url_raw( wct_users_get_signup_url() ),
			'object'     => 'wct-signup',
		);
	}

	foreach ( $nav_items as $nav_item_key => $nav_item ) {
		$nav_items[ $nav_item_key ] = array_merge( $nav_item, array(
			'type'       => 'wct_nav_item',
			'type_label' => _x( 'Custom Link', 'customizer menu type label', 'wordcamp-talks' ),
			'object_id'  => -1,
		) );
	}

	return $nav_items;
}

/**
 * Validate & Populate nav item URLs.
 *
 * @since  1.1.0
 *
 * @param  array  $menu_items WP Nav Items list.
 * @return array              WP Nav Items list.
 */
function wct_validate_nav_menu_items( $menu_items = array() ) {
	$nav_items = wp_list_filter( $menu_items, array( 'type' => 'wct_nav_item' ) );

	if ( empty( $nav_items ) ) {
		return $menu_items;
	}

	$nav_items_urls = wp_list_pluck( wct_get_nav_items(), 'url', 'id' );

	if ( ! is_admin() && ! is_customize_preview() ) {
		if ( ! is_user_logged_in() ) {
			unset( $nav_items_urls['wct-profile'] );
		} else {
			unset( $nav_items_urls['wct-signup'] );
		}
	}

	foreach ( $menu_items as $km => $om ) {
		if ( ! isset( $nav_items_urls[ $om->object ] ) ) {
			if ( 'wct_nav_item' === $om->type ) {
				unset( $menu_items[ $km ] );
			}

			continue;
		}

		$menu_items[ $km ]->url = $nav_items_urls[ $om->object ];

		if ( ( 'wct-archive' === $om->object && wct_is_talks() )
			|| ( 'wct-profile' === $om->object && wct_is_current_user_profile() )
			|| ( 'wct-new' === $om->object && wct_is_addnew() )
			|| ( 'wct-signup' === $om->object && wct_is_signup() )
		) {
			$menu_items[ $km ]->classes = array_merge( $om->classes, array( 'current-menu-item', 'current_page_item' ) );
		}
	}

	return $menu_items;
}

/**
 * Add WordCamp Talk Proposals Nav Items to the Customizer.
 *
 * @since  1.1.0
 *
 * @param  array  $items  The array of menu items.
 * @param  string $type   The object type.
 * @param  string $object The object name.
 * @param  int    $page   The current page number.
 * @return array          The array of menu items.
 */
function wct_customizer_get_nav_menus_items( $items = array(), $type = '', $object = '', $page = 0 ) {
	if ( 'wct_nav_item' !== $type ) {
		return $items;
	}

	// Get the nav items.
	$items = array_values( wct_get_nav_items() );

	return array_slice( $items, 10 * $page, 10 );
}

/**
 * Add WordCamp Talk Proposals Nav item type to the available Customizer Post types.
 *
 * @since  1.1.0
 *
 * @param  array $item_types Custom menu item types.
 * @return array             Custom menu item types WordCamp Talk Proposals item type.
 */
function wct_customizer_set_nav_menus_item_types( $item_types = array() ) {
	$item_types = array_merge( $item_types, array(
		'wct' => array(
			'title'  => _x( 'Talk Proposals', 'customizer menu section title', 'wordcamp-talks' ),
			'type'   => 'wct_nav_item',
			'object' => 'wct',
		),
	) );

	return $item_types;
}

/**
 * Filters edit post link to avoid its display when needed.
 *
 * @since 1.0.0
 *
 * @param  string $edit_link The link to edit the post.
 * @return mixed             False if needed, original edit link otherwise.
 */
function wct_edit_post_link( $edit_link = '', $post_id = 0 ) {
	/**
	 * using the capability check prevents edit link to display in case current user is the
	 * author of the talk and don't have the minimal capability to open the talk in WordPress
	 * Administration edit screen
	 */
	if ( wct_is_talks() && ( 0 === $post_id || ! current_user_can( 'edit_talks' ) ) ) {
		/**
		 * @param  bool   false to be sure the edit link won't show
		 * @param  string $edit_link
		 * @param  int    $post_id
		 */
		return apply_filters( 'wct_edit_post_link', false, $edit_link, $post_id );
	}

	return $edit_link;
}
