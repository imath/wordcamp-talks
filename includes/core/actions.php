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

add_action( 'parse_query', 'wct_parse_query', 2 );

// Actions to register post_type, metas, taxonomies & rewrite stuff
add_action( 'init', array( 'WordCamp_Talk_Metas', 'start' ), 100 );

// Actions hooking loaded (rewrites/comments disjoin)
add_action( 'init', array( 'WordCamp_Talks_Rewrites', 'start' ), 1 );
add_action( 'init', 'wct_cache_global_group' );
add_action( 'init', array( 'WordCamp_Talks_Comments', 'start' ) );

// Comments actions
add_action( 'wp_set_comment_status', 'wct_comments_clean_count_cache', 10, 2 );
add_action( 'delete_comment',        'wct_comments_clean_count_cache', 10, 1 );
add_action( 'wp_insert_comment',     'wct_comments_clean_count_cache', 10, 2 );

// Actions hooking enqueue_scripts (tags, rates UI)
add_action( 'wp_enqueue_scripts', 'wct_talks_enqueue_scripts', 10 );
add_action( 'wp_enqueue_scripts', 'wct_users_enqueue_scripts', 11 );

// Template actions
add_action( 'wct_talk_header',             'wct_users_the_user_talk_rating', 1 );
add_action( 'wct_before_archive_main_nav', 'wct_talks_taxonomy_description'    );

// Actions to handle user actions (eg: submit new talk)
add_action( 'template_redirect',           'wct_set_user_feedback',                   5 );
add_action( 'template_redirect',           'wct_talks_post_talk'                        );
add_action( 'template_redirect',           'wct_talks_update_talk'                      );
add_action( 'template_redirect',           'wct_users_signup_user',               10, 0 );

// Rates actions
add_action( 'wp_ajax_wct_rate', 'wct_ajax_rate'                      );
add_action( 'wct_added_rate',   'wct_clean_rates_count_cache', 10, 2 );
add_action( 'wct_deleted_rate', 'wct_clean_rates_count_cache', 10, 2 );

// Admin
add_action( 'admin_init', 'wct_maybe_upgrade',          999 );

// Widgets
add_action( 'widgets_init', array( 'WordCamp_Talks_Navig', 'register_widget' ), 10 );

// User deleted
add_action( 'deleted_user', 'wct_users_delete_user_data', 10, 1 );

// Signups
add_action( 'wct_set_core_template', 'wct_user_signup_redirect',      10, 1 );
add_action( 'login_form_register',   'wct_user_signup_redirect',      10    );
add_action( 'login_form_rp',         'wct_user_setpassword_redirect', 10    );

// Admin Menu Bar
add_action( 'admin_bar_menu', 'wct_adminbar_menu', 999 );
