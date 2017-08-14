<?php
/**
 * WordCamp Talks Actions.
 *
 * @package WordCamp Talks
 * @subpackage core/actions
 *
 * @since 1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

add_action( 'plugins_loaded',           'wct_loaded',                 11 );
add_action( 'init',                     'wct_init',                   9  );
add_action( 'parse_query',              'wct_parse_query',            2  );
add_action( 'enqueue_embed_scripts',    'wct_enqueue_embed_scripts',  10 );
add_action( 'wp_head',                  'wct_head',                   10 );
add_action( 'wp_footer',                'wct_footer',                 10 );
add_action( 'after_setup_theme',        'wct_after_setup_theme',      10 );
add_action( 'template_redirect',        'wct_template_redirect',      8  );

// Init the Talks Meta class.
add_action( 'init', array( 'WordCamp_Talks_Talks_Meta', 'start' ), 100 );

// Actions hooking loaded (rewrites/comments disjoin)
if ( is_admin() ) {
	add_action( 'plugins_loaded', array( 'WordCamp_Talks_Admin',          'start' ), 10 );
	add_action( 'plugins_loaded', array( 'WordCamp_Talks_Admin_Comments', 'start' ), 11 );
}

add_action( 'plugins_loaded', 'wct_cache_global_group',                         11 );
add_action( 'plugins_loaded', array( 'WordCamp_Talks_Core_Rewrites', 'start' ), 11 );
add_action( 'plugins_loaded', array( 'WordCamp_Talks_Comments',      'start' ), 11 );

// Comments actions
add_action( 'wp_set_comment_status', 'wct_comments_clean_count_cache', 10, 2 );
add_action( 'delete_comment',        'wct_comments_clean_count_cache', 10, 1 );
add_action( 'wp_insert_comment',     'wct_comments_clean_count_cache', 10, 2 );

// Template actions
add_action( 'wct_talk_header',             'wct_users_the_user_talk_rating', 1 );
add_action( 'wct_before_archive_main_nav', 'wct_talks_taxonomy_description'    );

// Actions to handle user actions (eg: submit new talk)
add_action( 'wct_template_redirect', 'wct_actions',                             4 );
add_action( 'wct_actions',           'wct_set_user_feedback',                   5 );
add_action( 'wct_actions',           'wct_talks_post_talk'                        );
add_action( 'wct_actions',           'wct_talks_update_talk'                      );
add_action( 'wct_actions',           'wct_users_signup_user',               10, 0 );

// Rates actions
add_action( 'wp_ajax_wct_rate', 'wct_ajax_rate'                      );
add_action( 'wct_added_rate',   'wct_clean_rates_count_cache', 10, 2 );
add_action( 'wct_deleted_rate', 'wct_clean_rates_count_cache', 10, 2 );

// Admin
add_action( 'admin_head', 'wct_admin_head', 10 );
add_action( 'admin_init', 'wct_maybe_upgrade', 999 );

// Widgets
add_action( 'widgets_init', array( 'WordCamp_Talks_Core_Navig',             'register_widget' ), 11 );
add_action( 'widgets_init', array( 'WordCamp_Talks_Talks_List_Categories',  'register_widget' ), 12 );
add_action( 'widgets_init', array( 'WordCamp_Talks_Talks_Popular',          'register_widget' ), 14 );
add_action( 'widgets_init', array( 'WordCamp_Talks_Users_Top_Contributors', 'register_widget' ), 15 );
add_action( 'widgets_init', array( 'WordCamp_Talks_Comments_Recent',        'register_widget' ), 16 );

// User deleted
add_action( 'deleted_user', 'wct_users_delete_user_data', 10, 1 );

// Signups
add_action( 'wct_set_core_template', 'wct_user_signup_redirect',      10, 1 );
add_action( 'login_form_register',   'wct_user_signup_redirect',      10    );
add_action( 'login_form_rp',         'wct_user_setpassword_redirect', 10    );

// Admin Menu Bar
add_action( 'admin_bar_menu', 'wct_adminbar_menu', 999 );

// Embeds
add_action( 'embed_content_meta',        'wct_talks_embed_meta',         9 );
add_action( 'wct_enqueue_embed_scripts', 'wct_enqueue_embed_style'         );
add_action( 'wct_embed_content_meta',    'wct_users_embed_content_meta'    );
add_action( 'wct_head',                  'wct_oembed_add_discovery_links'  );

/**
 * Fire the 'wct_init' action.
 *
 * @package WordCamp Talks
 * @subpackage core/actions
 *
 * @since 1.0.0
 */
function wct_init() {
	do_action( 'wct_init' );
}

/**
 * Fire the 'wct_loaded' action.
 *
 * @package WordCamp Talks
 * @subpackage core/actions
 *
 * @since 1.0.0
 */
function wct_loaded() {
	do_action( 'wct_loaded' );
}

/**
 * Fire the 'wct_enqueue_embed_scripts' action.
 * But do it only if needed
 *
 * Used to register and enqueue custom scripts for embed templates
 *
 * @since 1.0.0
 */
function wct_enqueue_embed_scripts() {
	// Bail if not a talk or not an embed profile
	if ( ( wct_get_post_type() === get_query_var( 'post_type' ) && ! wct_is_rating_disabled() )
		|| ( wct_get_global( 'is_user_embed' ) && wct_is_embed_profile() )
	) {
		do_action( 'wct_enqueue_embed_scripts' );
	}
}

/**
 * Fire the 'wct_head' action.
 *
 * @package WordCamp Talks
 * @subpackage core/actions
 *
 * @since 1.0.0
 */
function wct_head() {
	do_action( 'wct_head' );
}

/**
 * Fire the 'wct_footer' action.
 *
 * @package WordCamp Talks
 * @subpackage core/actions
 *
 * @since 1.0.0
 */
function wct_footer() {
	do_action( 'wct_footer' );
}

/**
 * Fire the 'wct_template_redirect' action.
 *
 * @package WordCamp Talks
 * @subpackage core/actions
 *
 * @since 1.0.0
 */
function wct_template_redirect() {
	do_action( 'wct_template_redirect' );
}

/**
 * Fire the 'wct_add_rewrite_tags' action.
 *
 * Used in core/rewrites to add user's profile, search & action
 * custom tags
 *
 * @package WordCamp Talks
 * @subpackage core/actions
 *
 * @since 1.0.0
 */
function wct_add_rewrite_tags() {
	do_action( 'wct_add_rewrite_tags' );
}

/**
 * Fire the 'wct_add_rewrite_rules' action.
 *
 * Used in core/rewrites to add user's profile custom rule
 *
 * @package WordCamp Talks
 * @subpackage core/actions
 *
 * @since 1.0.0
 */
function wct_add_rewrite_rules() {
	do_action( 'wct_add_rewrite_rules' );
}

/**
 * Fire the 'wct_add_permastructs' action.
 *
 * Used in core/rewrites to add custom permalink structures
 * such as the user's profile one
 *
 * @package WordCamp Talks
 * @subpackage core/actions
 *
 * @since 1.0.0
 */
function wct_add_permastructs() {
	do_action( 'wct_add_permastructs' );
}

/**
 * Fire the 'wct_after_setup_theme' action.
 *
 * @package WordCamp Talks
 * @subpackage core/actions
 *
 * @since 1.0.0
 */
function wct_after_setup_theme() {
	do_action( 'wct_after_setup_theme' );
}

/**
 * Fire the 'wct_actions' action.
 *
 * Used to handle user actions (profile update, submit talks)
 *
 * @package WordCamp Talks
 * @subpackage core/actions
 *
 * @since 1.0.0
 */
function wct_actions() {
	do_action( 'wct_actions' );
}

/** Admin *********************************************************************/

/**
 * Fire the 'wct_admin_head' action.
 *
 * @package WordCamp Talks
 * @subpackage core/actions
 *
 * @since 1.0.0
 */
function wct_admin_head() {
	do_action( 'wct_admin_head' );
}
