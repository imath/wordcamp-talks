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

// Registers needed objects.
add_action( 'init', 'wct_register_objects',                         15 );
add_action( 'init', array( 'WordCamp_Talks_Talks_Meta', 'start' ), 100 );

// Loads the Administration
if ( is_admin() ) {
	add_action( 'plugins_loaded', array( 'WordCamp_Talks_Admin',          'start' ), 10 );
	add_action( 'plugins_loaded', array( 'WordCamp_Talks_Admin_Comments', 'start' ), 11 );
}

// Actions hooking loaded (rewrites/comments disjoin)
add_action( 'plugins_loaded', 'wct_load_textdomain',                             7 );
add_action( 'plugins_loaded', 'wct_cache_global_group',                         11 );
add_action( 'plugins_loaded', array( 'WordCamp_Talks_Core_Rewrites', 'start' ), 11 );
add_action( 'plugins_loaded', array( 'WordCamp_Talks_Comments',      'start' ), 11 );

// Sets up the Talks query and current candidate.
add_action( 'parse_query',      'wct_parse_query',             2 );
add_action( 'set_current_user', 'wct_users_set_current_user', 10 );

// Comments actions
add_action( 'wp_set_comment_status', 'wct_comments_clean_count_cache', 10, 2 );
add_action( 'delete_comment',        'wct_comments_clean_count_cache', 10, 1 );
add_action( 'wp_insert_comment',     'wct_comments_clean_count_cache', 10, 2 );

// Template actions
add_action( 'wct_talk_header',             'wct_users_the_user_talk_rating', 1 );
add_action( 'wct_before_archive_main_nav', 'wct_talks_taxonomy_description'    );

// Adjust WordCamp Talks screens
add_action( 'wct_set_core_template',   array( 'WordCamp_Talks_Core_Screens', 'start' ), 0, 2 );
add_action( 'wct_set_single_template', array( 'WordCamp_Talks_Core_Screens', 'start' ), 0, 2 );

// Actions to handle user actions (eg: submit new talk)
add_action( 'template_redirect', 'wct_actions', 12 );

// Rates actions
add_action( 'wp_ajax_wct_rate', 'wct_ajax_rate'                      );
add_action( 'wct_added_rate',   'wct_clean_rates_count_cache', 10, 2 );
add_action( 'wct_deleted_rate', 'wct_clean_rates_count_cache', 10, 2 );

// Upgrades
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
