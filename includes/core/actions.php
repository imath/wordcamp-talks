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

add_action( 'init',             'wct_init',                    9 );
add_action( 'parse_query',      'wct_parse_query',             2 );
add_action( 'set_current_user', 'wct_users_set_current_user', 10 );

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
add_action( 'template_redirect', 'wct_actions',               12 );
add_action( 'wct_actions',       'wct_set_user_feedback',      5 );
add_action( 'wct_actions',       'wct_talks_post_talk'           );
add_action( 'wct_actions',       'wct_talks_update_talk'         );
add_action( 'wct_actions',       'wct_users_signup_user',  10, 0 );
add_action( 'wct_actions',       'wct_users_edit_profile', 10, 0 );

// Rates actions
add_action( 'wp_ajax_wct_rate', 'wct_ajax_rate'                      );
add_action( 'wct_added_rate',   'wct_clean_rates_count_cache', 10, 2 );
add_action( 'wct_deleted_rate', 'wct_clean_rates_count_cache', 10, 2 );

// Admin
add_action( 'admin_init', 'wct_maybe_upgrade', 999 );

// Widgets
add_action( 'widgets_init', array( 'WordCamp_Talks_Talks_List_Categories',  'register_widget' ), 11 );
add_action( 'widgets_init', array( 'WordCamp_Talks_Talks_Popular',          'register_widget' ), 12 );
add_action( 'widgets_init', array( 'WordCamp_Talks_Users_Top_Contributors', 'register_widget' ), 14 );
add_action( 'widgets_init', array( 'WordCamp_Talks_Comments_Recent',        'register_widget' ), 15 );

// User deleted
add_action( 'deleted_user', 'wct_users_delete_user_data', 10, 1 );

// Signups
add_action( 'wct_set_core_template', 'wct_user_signup_redirect',      10, 1 );
add_action( 'login_form_register',   'wct_user_signup_redirect',      10    );
add_action( 'login_form_rp',         'wct_user_setpassword_redirect', 10    );

// Admin Menu Bar
add_action( 'admin_bar_menu', 'wct_adminbar_menu', 999 );

// Talk Embeds Meta
add_action( 'embed_content_meta', 'wct_talks_embed_meta', 9 );

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
