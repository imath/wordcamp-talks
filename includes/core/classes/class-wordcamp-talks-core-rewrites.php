<?php
/**
 * WordCamp Talks Rewrites Class.
 *
 * @package WordCamp Talks
 * @subpackage core/classes
 *
 * @since 1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WordCamp_Talks_Core_Rewrites' ) ) :

/**
 * Core Rewrites Class.
 *
 * @since  1.0.0
 * @since  1.1.0 Name changed from WordCamp_Talks_Rewrites to WordCamp_Talks_Core_Rewrites
 */
class WordCamp_Talks_Core_Rewrites {

	/**
	 * Constructor
	 *
	 * @since  1.0.0
	 * @since  1.1.0 the hooks method has been replaced with the register one.
	 */
	public function __construct() {
		$this->setup_globals();

		// Wait untill init to register
		add_action( 'init', array( $this, 'register' ), 16 );

		// Refresh slugs
		add_action( 'update_option_rewrite_rules', array( $this, 'update_slugs' ), 10, 0 );
	}

	/**
	 * Start the rewrites
	 *
	 * @since 1.0.0
	 */
	public static function start() {
		$wct = wct();

		if ( empty( $wct->rewrites ) ) {
			$wct->rewrites = new self;
		}

		return $wct->rewrites;
	}

	/**
	 * Setup the rewrite ids and slugs
	 *
	 * @since 1.0.0
	 */
	private function setup_globals() {
		/** Rewrite ids ***************************************************************/

		$this->page_rid          = 'paged'; // WordPress built-in global var
		$this->user_rid          = wct_user_rewrite_id();
		$this->user_comments_rid = wct_user_comments_rewrite_id();
		$this->user_rates_rid    = wct_user_rates_rewrite_id();
		$this->user_to_rate_rid  = wct_user_to_rate_rewrite_id();
		$this->user_talks_rid    = wct_user_talks_rewrite_id();
		$this->cpage_rid         = wct_cpage_rewrite_id();
		$this->action_rid        = wct_action_rewrite_id();
		$this->search_rid        = wct_search_rewrite_id();

		/** Rewrite slugs *************************************************************/

		$this->user_slug          = wct_user_slug( true );
		$this->user_comments_slug = wct_user_comments_slug( true );
		$this->user_rates_slug    = wct_user_rates_slug( true );
		$this->user_to_rate_slug  = wct_user_to_rate_slug( true );
		$this->user_talks_slug    = wct_user_talks_slug( true );
		$this->cpage_slug         = wct_cpage_slug( true );
		$this->action_slug        = wct_action_slug( true );
	}

	/**
	 * Register Tags, Rules and Permastructs;
	 *
	 * @since 1.1.0
	 */
	public function register() {
		// Register rewrite tags.
		$this->add_rewrite_tags();

		// Register the rewrite rules
		$this->add_rewrite_rules();

		// Register the permastructs
		$this->add_permastructs();
	}

	/**
	 * Register the rewrite tags
	 *
	 * @since 1.0.0
	 */
	public function add_rewrite_tags() {
		add_rewrite_tag( '%' . $this->user_rid          . '%', '([^/]+)'   );
		add_rewrite_tag( '%' . $this->user_comments_rid . '%', '([1]{1,})' );
		add_rewrite_tag( '%' . $this->user_rates_rid    . '%', '([1]{1,})' );
		add_rewrite_tag( '%' . $this->user_to_rate_rid  . '%', '([1]{1,})' );
		add_rewrite_tag( '%' . $this->user_talks_rid    . '%', '([1]{1,})' );
		add_rewrite_tag( '%' . $this->cpage_rid         . '%', '([^/]+)'   );
		add_rewrite_tag( '%' . $this->action_rid        . '%', '([^/]+)'   );
		add_rewrite_tag( '%' . $this->search_rid        . '%', '([^/]+)'   );
	}

	/**
	 * Register the rewrite rules
	 *
	 * @since 1.0.0
	 */
	public function add_rewrite_rules() {
		$priority  = 'top';
		$root_rule = '/([^/]+)/?$';

		$page_slug  = wct_paged_slug();
		$paged_rule = '/([^/]+)/' . $page_slug . '/?([0-9]{1,})/?$';

		// User Comments
		$user_comments_rule        = '/([^/]+)/' . $this->user_comments_slug . '/?$';
		$user_comments_paged_rule  = '/([^/]+)/' . $this->user_comments_slug . '/' . $this->cpage_slug . '/?([0-9]{1,})/?$';

		// User Rates
		$user_rates_rule       = '/([^/]+)/' . $this->user_rates_slug . '/?$';
		$user_rates_paged_rule = '/([^/]+)/' . $this->user_rates_slug . '/' . $page_slug . '/?([0-9]{1,})/?$';

		// User to rate
		$user_to_rate_rule       = '/([^/]+)/' . $this->user_to_rate_slug . '/?$';
		$user_to_rate_paged_rule = '/([^/]+)/' . $this->user_to_rate_slug . '/' . $page_slug . '/?([0-9]{1,})/?$';

		// User talks
		$user_talks_rule       = '/([^/]+)/' . $this->user_talks_slug . '/?$';
		$user_talks_paged_rule = '/([^/]+)/' . $this->user_talks_slug . '/' . $page_slug . '/?([0-9]{1,})/?$';

		// User rules
		add_rewrite_rule( $this->user_slug . $user_comments_paged_rule, 'index.php?' . $this->user_rid . '=$matches[1]&' . $this->user_comments_rid . '=1&' . $this->cpage_rid . '=$matches[2]', $priority );
		add_rewrite_rule( $this->user_slug . $user_comments_rule,       'index.php?' . $this->user_rid . '=$matches[1]&' . $this->user_comments_rid . '=1',                                      $priority );
		add_rewrite_rule( $this->user_slug . $user_rates_paged_rule,    'index.php?' . $this->user_rid . '=$matches[1]&' . $this->user_rates_rid .    '=1&' . $this->page_rid . '=$matches[2]',  $priority );
		add_rewrite_rule( $this->user_slug . $user_rates_rule,          'index.php?' . $this->user_rid . '=$matches[1]&' . $this->user_rates_rid .    '=1',                                      $priority );
		add_rewrite_rule( $this->user_slug . $user_to_rate_paged_rule,  'index.php?' . $this->user_rid . '=$matches[1]&' . $this->user_to_rate_rid .  '=1&' . $this->page_rid . '=$matches[2]',  $priority );
		add_rewrite_rule( $this->user_slug . $user_to_rate_rule,        'index.php?' . $this->user_rid . '=$matches[1]&' . $this->user_to_rate_rid .  '=1',                                      $priority );
		add_rewrite_rule( $this->user_slug . $user_talks_paged_rule,    'index.php?' . $this->user_rid . '=$matches[1]&' . $this->user_talks_rid   .  '=1&' . $this->page_rid . '=$matches[2]',  $priority );
		add_rewrite_rule( $this->user_slug . $user_talks_rule,          'index.php?' . $this->user_rid . '=$matches[1]&' . $this->user_talks_rid   .  '=1',                                      $priority );
		add_rewrite_rule( $this->user_slug . $root_rule,                'index.php?' . $this->user_rid . '=$matches[1]',                                                                         $priority );

		// Action rules (only add a new talk right now)
		add_rewrite_rule( $this->action_slug . $root_rule, 'index.php?' . $this->action_rid . '=$matches[1]', $priority );
	}

	/**
	 * Register the permastructs
	 *
	 * @since 1.0.0
	 */
	public function add_permastructs() {
		// User Permastruct
		add_permastruct( $this->user_rid, $this->user_slug . '/%' . $this->user_rid . '%', array(
			'with_front'  => false,
			'ep_mask'     => EP_NONE,
			'paged'       => true,
			'feed'        => false,
			'forcomments' => false,
			'walk_dirs'   => true,
			'endpoints'   => false,
		) );

		// Action Permastruct
		add_permastruct( $this->action_rid, $this->action_slug . '/%' . $this->action_rid . '%', array(
			'with_front'  => false,
			'ep_mask'     => EP_NONE,
			'paged'       => true,
			'feed'        => false,
			'forcomments' => false,
			'walk_dirs'   => true,
			'endpoints'   => false,
		) );
	}

	/**
	 * Update slugs once rewrite rules have been flushed.
	 *
	 * As slugs are translatable, an option to store them is
	 * needed in case the user language has been switched to
	 * prevent 404.
	 *
	 * @since 1.1.0
	 */
	public function update_slugs() {
		$slugs = array(
			'root'          => wct_root_slug( true ),
			'talk'          => wct_get_talk_slug( true ),
			'category'      => wct_get_category_slug( true ),
			'tag'           => wct_get_tag_slug( true ),
			'user'          => wct_get_user_slug( true ),
			'rate'          => wct_user_rates_slug( true ),
			'to_rate'       => wct_user_to_rate_slug( true ),
			'user_comments' => wct_user_comments_slug( true ),
			'action'        => wct_get_action_slug( true ),
			'add'           => wct_addnew_slug( true ),
			'edit'          => wct_edit_slug( true ),
			'signup'        => wct_signup_slug( true ),
			'cpage'         => wct_cpage_slug( true ),
		);

		if( update_option( '_wc_talks_slugs', $slugs ) ) {
			wct()->slugs = $slugs;
		}
	}
}

endif;
