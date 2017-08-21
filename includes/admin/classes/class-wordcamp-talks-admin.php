<?php
/**
 * WordCamp Talks Admin.
 *
 * @package WordCamp Talks
 * @subpackage admin/classes
 *
 * @since 1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WordCamp_Talks_Admin' ) ) :

class WordCamp_Talks_Admin {

	/** Variables ****************************************************************/

	/**
	 * @access  private
	 * @var string The talks post type
	 */
	private $post_type = '';

	/**
	 * @access  public
	 * @var string path to includes dir
	 */
	private $includes_dir = '';

	/**
	 * @access  public
	 * @var string the parent slug for submenus
	 */
	public $parent_slug;

	/**
	 * @access  public
	 * @var array the list of available metaboxes
	 */
	public $metaboxes;

	/**
	 * @access  public
	 * @var bool whether it's plugin's settings page or not
	 */
	public $is_plugin_settings;

	/**
	 * The Admin constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->setup_globals();
		$this->includes();
		$this->hooks();
	}

	/**
	 * Starts the Admin class.
	 *
	 * @since 1.0.0
	 */
	public static function start() {
		if ( ! is_admin() ) {
			return;
		}

		$wct = wct();

		if ( empty( $wct->admin ) ) {
			$wct->admin = new self;
		}

		return $wct->admin;
	}

	/**
	 * Setups some globals.
	 *
	 * @since 1.0.0
	 */
	private function setup_globals() {
		$this->post_type     = wct_get_post_type();
		$this->includes_dir  = trailingslashit( wct()->includes_dir . 'admin' );
		$this->parent_slug   = false;

		$this->metaboxes          = array();
		$this->is_plugin_settings = false;
		$this->downloading_csv    = false;

		// Globalize states.
		$this->workflow_states = wct_get_statuses();
	}

	/**
	 * Includes the needed admin files.
	 *
	 * @since 1.0.0
	 */
	private function includes() {
		// Settings
		require $this->includes_dir . 'settings.php';
	}

	/**
	 * Setups the action and filters to hook to.
	 *
	 * @since 1.0.0
	 */
	private function hooks() {

		/** Actions *******************************************************************/

		// Build the submenus.
		add_action( 'admin_menu', array( $this, 'admin_menus' ), 10 );

		// Loading the talks edit screen
		add_action( 'load-edit.php', array( $this, 'load_edit_talk' ) );

		// Make sure Editing a plugin's taxonomy highlights the plugin's nav
		add_action( 'load-edit-tags.php', array( $this, 'taxonomy_highlight' ) );

		// Add metaboxes for the post type
		add_action( "add_meta_boxes_{$this->post_type}", array( $this, 'add_metaboxes' ),       10, 1 );
		// Save metabox inputs
		add_action( "save_post_{$this->post_type}",      array( $this, 'save_metaboxes' ),      10, 3 );

		// Display upgrade notices
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );

		// Register the settings
		add_action( 'admin_init', array( $this, 'register_admin_settings' ) );

		add_action( 'load-settings_page_wc_talks', array( $this, 'settings_load' ) );

		// Talks columns (in post row)
		add_action( "manage_{$this->post_type}_posts_custom_column", array( $this, 'column_data' ), 10, 2 );

		// Add Talks statuses near title.
		add_filter( 'display_post_states', array( $this, 'talk_states' ), 10, 2 );

		// Maybe neutralize quick edit
		add_action( 'post_row_actions', array( $this, 'talk_row_actions'), 10, 2 );

		// Add the Workflow inline edit field
		add_action( 'quick_edit_custom_box', array( $this, 'inline_edit_workflow' ), 10, 2 );

		// Do some global stuff here (custom css rule)
		add_action( 'admin_head', array( $this, 'admin_head' ), 10 );

		/** Filters *******************************************************************/

		// Updated message
		add_filter( 'post_updated_messages',      array( $this, 'talks_updated_messages' ),      10, 1 );
		add_filter( 'bulk_post_updated_messages', array( $this, 'talks_updated_bulk_messages' ), 10, 2 );

		// Redirect
		add_filter( 'redirect_post_location', array( $this, 'redirect_talk_location' ), 10, 2 );

		// Filter the WP_List_Table views to include custom views.
		add_filter( "views_edit-{$this->post_type}", array( $this, 'talk_views' ), 10, 1 );

		// temporarly remove bulk edit
		add_filter( "bulk_actions-edit-{$this->post_type}", array( $this, 'talk_bulk_actions' ), 10, 1 );

		// Talks column headers.
		add_filter( "manage_{$this->post_type}_posts_columns", array( $this, 'column_headers' ) );

		// Add a link to About & settings page in plugins list
		add_filter( 'plugin_action_links', array( $this, 'modify_plugin_action_links' ), 10, 2 );

		// Add a specific metabox to manage WordCamp Talk Proposal menus.
		add_action( 'load-nav-menus.php', array( $this, 'menu_accordion' ), 10, 1 );

		/** Specific case: ratings ****************************************************/

		// Only sort by rates & display people who voted if ratings is not disabled.
		if ( ! wct_is_rating_disabled() ) {
			add_action( "manage_edit-{$this->post_type}_sortable_columns", array( $this, 'sortable_columns' ), 10, 1 );

			// Manage votes
			add_filter( 'wct_admin_get_meta_boxes', array( $this, 'ratings_metabox'   ),   9, 1 );
			add_action( 'load-post.php',            array( $this, 'maybe_delete_rate' )         );

			// Custom feedback
			add_filter( 'wct_admin_updated_messages', array( $this, 'ratings_updated' ), 10, 1 );

			// Help tabs
			add_filter( 'wct_get_help_tabs', array( $this, 'rates_help_tabs' ), 11, 1 );
		}
	}

	/**
	 * Builds the different Admin menus and submenus.
	 *
	 * @since 1.0.0
	 */
	public function admin_menus() {
		$menus = array();
		$this->parent_slug = add_query_arg( 'post_type', $this->post_type, 'edit.php' );

		/**
		 * @param array list of menu items
		 */
		$menus = apply_filters( 'wct_admin_menus', array(
			/* Settings has a late order to be at last position */
			10 => array(
				'type'          => 'settings',
				'parent_slug'   => $this->parent_slug,
				'page_title'    => esc_html__( 'Settings',  'wordcamp-talks' ),
				'menu_title'    => esc_html__( 'Settings',  'wordcamp-talks' ),
				'capability'    => 'wct_talks_admin',
				'slug'          => add_query_arg( 'page', 'wc_talks', 'options-general.php' ),
				'function'      => '',
				'alt_screen_id' => 'settings_page_wc_talks',
				'actions'       => array(
					'admin_head-%page%' => array( $this, 'settings_menu_highlight' ),
				),
			),
		) );

		// Fake an option page to register the handling function
		// Then remove it hooking admin_head.
		add_options_page(
			esc_html__( 'Settings',  'wordcamp-talks' ),
			esc_html__( 'Settings',  'wordcamp-talks' ),
			'manage_options',
			'wc_talks',
			'wct_settings'
		);

		// Sort the menus
		ksort( $menus );

		// Build the sub pages and particular hooks
		foreach ( $menus as $menu ) {
			$screen_id = add_submenu_page(
				$menu['parent_slug'],
				$menu['page_title'],
				$menu['menu_title'],
				$menu['capability'],
				$menu['slug'],
				$menu['function']
			);

			if ( ! empty( $menu['alt_screen_id'] ) ) {
				$screen_id = $menu['alt_screen_id'];
			}

			foreach ( $menu['actions'] as $key => $action ) {
				add_action( str_replace( '%page%', $screen_id, $key ), $action );
			}
		}
	}

	/**
	 * Hooks Admin edit screen load to eventually perform
	 * actions before the WP_List_Table is generated.
	 *
	 * @since 1.0.0
	 */
	public function load_edit_talk() {
		// Make sure it's a plugin's admin screen
		if ( ! wct_is_admin() ) {
			return;
		}

		if ( ! empty( $_GET['csv'] ) ) {

			check_admin_referer( 'wct_is_csv' );

			$this->downloading_csv = true;

			// Add content row data
			add_action( 'wct_admin_column_data', array( $this, 'talk_row_extra_data'), 10, 2 );

			$this->csv_export();

		// Other plugins can do stuff here
		} else {
			do_action( 'wct_load_edit_talk' );
		}
	}

	/**
	 * Add metaboxes in Edit and Post New Talk screens.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $talk the talk object
	 */
	public function add_metaboxes( $talk = null ) {
		remove_meta_box( 'submitdiv', get_current_screen(), 'side' );

		/**
		 * @see  $this->ratings_metabox() for an example of use
		 * @param array $metaboxes list of metaboxes to add
		 */
		$this->metaboxes = apply_filters( 'wct_admin_get_meta_boxes', $this->get_workflow_metabox() );

		if ( empty( $this->metaboxes ) ) {
			return;
		}

		foreach ( $this->metaboxes as $metabox ) {
			$m = array_merge( array(
				'id'            => '',
				'title'         => '',
				'callback'      => '',
				'context'       => '',
				'priority'      => '',
				'callback_args' => array()
			), $metabox );

			if ( empty( $m['id'] ) || empty( $m['title'] ) || empty( $m['callback'] ) ) {
				continue;
			}

			// Add the metabox
			add_meta_box(
				$m['id'],
				$m['title'],
				$m['callback'],
				$this->post_type,
				$m['context'],
				$m['priority'],
				$m['callback_args']
			);
		}

		/**
		 * @param WP_Post $talk the talk object
		 */
		do_action( 'wct_add_metaboxes', $talk );
	}

	/**
	 * Fire an action to save the metabox entries.
	 *
	 * @since 1.0.0
	 *
	 * @param  int      $id     the talk ID
	 * @param  WP_Post  $talk   the talk object
	 * @param  boolean $update  whether it's an update or not
	 */
	public function save_metaboxes( $id = 0, $talk = null, $update = false ) {
		// Bail if doing an autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $id;
		}

		// Bail if not a post request
		if ( 'POST' != strtoupper( $_SERVER['REQUEST_METHOD'] ) || empty( $_POST['wct_workflow_metabox_metabox'] ) ) {
			return $id;
		}

		check_admin_referer( 'wct_workflow_metabox_save', 'wct_workflow_metabox_metabox' );

		// Capability check
		if ( ! current_user_can( 'select_talks' ) ) {
			return $id;
		}

		/**
		 * @param  int      $id     the talk ID
		 * @param  WP_Post  $talk   the talk object
		 * @param  boolean $update  whether it's an update or not
		 */
		do_action( 'wct_save_metaboxes', $id, $talk, $update );
	}

	/**
	 * Create specific updated messages for talks.
	 *
	 * @since 1.0.0
	 *
	 * @global $post
	 * @param  array  $messages list of available updated messages.
	 * @return array  the original updated messages if not on a plugin's screen, custom messages otherwise.
	 */
	public function talks_updated_messages( $messages = array() ) {
		global $post;

		// Bail if not posting/editing a talk.
		if ( ! wct_is_admin() ) {
			return $messages;
		}

		/**
		 * @param  array list of updated messages
		 */
		$messages[ $this->post_type ] = apply_filters( 'wct_admin_updated_messages', array(
			 0 => '', // Unused. Messages start at index 1.
			 1 => sprintf( __( 'Talk Proposal updated. <a href="%s">View Talk Proposal</a>', 'wordcamp-talks' ), esc_url( wct_talks_get_talk_permalink( $post ) ) ),
			 2 => __( 'Custom field updated.', 'wordcamp-talks' ),
			 3 => __( 'Custom field deleted.', 'wordcamp-talks' ),
			 4 => __( 'Talk Proposal updated.', 'wordcamp-talks'),
			 5 => isset( $_GET['revision'] ) ? sprintf( __( 'Talk Proposal restored to revision from %s', 'wordcamp-talks' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			 6 => sprintf( __( 'Talk Proposal submitted. <a href="%s">View Talk</a>', 'wordcamp-talks' ), esc_url( wct_talks_get_talk_permalink( $post ) ) ),
			 7 => __( 'Talk saved.', 'wordcamp-talks' ),
			 8 => sprintf( __( 'Talk Proposal submitted. <a target="_blank" href="%s">Preview Talk Proposal</a>', 'wordcamp-talks' ), esc_url( add_query_arg( 'preview', 'true', wct_talks_get_talk_permalink( $post  ) ) ) ),
			 9 => sprintf(
			 		__( 'Talk Proposal scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Talk Proposal</a>', 'wordcamp-talks' ),
					date_i18n( _x( 'M j, Y @ G:i', 'Talk Publish box date format', 'wordcamp-talks' ), strtotime( $post->post_date ) ),
					esc_url( wct_talks_get_talk_permalink( $post ) )
				),
			10 => sprintf( __( 'Talk Proposal draft updated. <a target="_blank" href="%s">Preview Talk Proposal</a>', 'wordcamp-talks' ), esc_url( add_query_arg( 'preview', 'true', wct_talks_get_talk_permalink( $post ) ) ) ),
		) );

		return $messages;
	}

	/**
	 * Create specific updated bulk messages for talks.
	 *
	 * @since 1.0.0
	 *
	 * @param  array  $bulk_messages list of available updated bulk messages.
	 * @param  array  $bulk_counts   count list by type.
	 * @return array  the original updated bulk messages if not on a plugin's screen, custom messages otherwise.
	 */
	public function talks_updated_bulk_messages( $bulk_messages = array(), $bulk_counts = array() ) {
		// Bail if not posting/editing a talk
		if ( ! wct_is_admin() ) {
			return $bulk_messages;
		}

		$bulk_messages[ $this->post_type ] = apply_filters( 'wct_admin_updated_bulk_messages', array(
			'updated'   => _n( '%s Talk Proposal updated.', '%s Talk Proposals updated.', $bulk_counts['updated'], 'wordcamp-talks' ),
			'locked'    => _n( '%s Talk Proposal not updated; somebody is editing it.', '%s Talk Proposals not updated; somebody is editing them.', $bulk_counts['locked'], 'wordcamp-talks' ),
			'deleted'   => _n( '%s Talk Proposal permanently deleted.', '%s Talk Proposals permanently deleted.', $bulk_counts['deleted'], 'wordcamp-talks' ),
			'trashed'   => _n( '%s Talk Proposal moved to the Trash.', '%s Talk Proposals moved to the Trash.', $bulk_counts['trashed'], 'wordcamp-talks' ),
			'untrashed' => _n( '%s Talk Proposal restored from the Trash.', '%s Talk Proposals restored from the Trash.', $bulk_counts['untrashed'], 'wordcamp-talks' ),
		) );

		return $bulk_messages;
	}

	/**
	 * Build a specific redirect url to handle specific feedbacks.
	 *
	 * @since 1.0.0
	 *
	 * @param  string  $location url to redirect to.
	 * @param  int     $talk_id  the ID of the talk.
	 * @return string            url to redirect to.
	 */
	public function redirect_talk_location( $location = '', $talk_id = 0 ) {
		// Bail if not posting/editing a talk
		if ( ! wct_is_admin() || empty( $talk_id ) ) {
			return $location;
		}

		if ( ! empty( $_POST['addmeta'] ) || ! empty( $_POST['deletemeta'] ) ) {
			return $location;
		}

		$messages = wct_get_global( 'feedback' );

		if ( empty( $messages['updated_message'] ) ) {
			return $location;
		}

		return add_query_arg( 'message', $messages['updated_message'], get_edit_post_link( $talk_id, 'url' ) );
	}

	/**
	 * The talk edit screen views (Over the top of WP_List_Table).
	 *
	 * @since 1.0.0
	 * @since 1.1.0 Reorder views so that the Trash one is always last.
	 *
	 * @param  array  $views list of views for the edit talks screen.
	 * @return array         talk views.
	 */
	public function talk_views( $views = array() ) {
		$talk_views = array_diff_key( $views, array( 'trash' => false ) );
		$trash_view = array_intersect_key( $views, array( 'trash' => true ) );

		$csv_args = array(
			'post_type' => $this->post_type,
			'csv'       => 1,
		);

		if ( ! empty( $_GET['post_status'] ) ) {
			$csv_args['post_status'] = $_GET['post_status'];
		}

		$csv_url = add_query_arg(
			$csv_args,
			admin_url( 'edit.php' )
		);

		$csv_link = sprintf( '<a href="%s" id="wordcamp-talks-csv" title="%s"><span class="dashicons dashicons-media-spreadsheet"></span></a>',
			esc_url( wp_nonce_url( $csv_url, 'wct_is_csv' ) ),
			esc_attr__( 'Download all Talk Proposals in a csv spreadsheet', 'wordcamp-talks' )
		);

		return array_merge( $talk_views, $trash_view, array(
			'csv_talks' => $csv_link
		) );
	}

	/**
	 * Displays notices if needed.
	 *
	 * @since 1.0.0
	 * @since 1.1.0 Adds a way to output error message on Talk Admin Edit Screen.
	 *
	 * @return string HTML output
	 */
	public function admin_notices() {
		$notices = wct_get_global( 'feedback' );

		if ( ! empty( $notices['admin_notices'] ) ) {
			?>
			<div class="update-nag">
				<?php foreach ( $notices['admin_notices'] as $notice ) : ?>
					<p><?php echo $notice; ?></p>
				<?php endforeach ;?>
			</div>
			<?php

		// There's no error message output on the Admin UI to edit a post.
		} elseif ( wct_is_admin() && isset( $_REQUEST['error'] ) ) {
			$message  = wct_get_feedback_messages( 'error' );
			$feedback = '';
			if ( isset( $message[$_REQUEST['error']] ) ) {
				$feedback = $message[$_REQUEST['error']];
			}

			if ( $feedback ) {
				printf(
					'<div id="message" class="error notice notice-error is-dismissible"><p>%s</p></div>',
					wct_sanitize_feedback( $feedback )
				);
			}
		}
	}

	/**
	 * Registers Global settings.
	 *
	 * @since 1.0.0
	 */
	public function register_admin_settings() {
		// Bail if no sections available
		$sections = wct_get_settings_sections();

		if ( empty( $sections ) )
			return false;

		// Loop through sections
		foreach ( (array) $sections as $section_id => $section ) {

			// Only proceed if current user can see this section
			if ( ! current_user_can( 'wct_talks_admin' ) )
				continue;

			// Only add section and fields if section has fields
			$fields = wct_get_settings_fields_for_section( $section_id );

			if ( empty( $fields ) ) {
				continue;
			}

			// Add the section
			add_settings_section( $section_id, $section['title'], $section['callback'], $section['page'] );

			// Loop through fields for this section
			foreach ( (array) $fields as $field_id => $field ) {

				// Add the field
				add_settings_field( $field_id, $field['title'], $field['callback'], $section['page'], $section_id, $field['args'] );

				// Register the setting
				register_setting( $section['page'], $field_id, $field['sanitize_callback'] );
			}
		}
	}

	/**
	 * Make sure the settings save messages are displayed.
	 *
	 * @since 1.0.0
	 */
	public function settings_load() {
		// First restore settings feedback lost as $parent file is no more options-general.php
		add_action( 'all_admin_notices', array( $this, 'restore_settings_feedback' ) );

		// Then flush rewrite rules if needed.
		if ( wct_is_pretty_links() && isset( $_GET['settings-updated'] ) && isset( $_GET['page'] ) ) {
			flush_rewrite_rules();
		}

		$this->is_plugin_settings = true;
	}

	/**
	 * Include options head file to restore settings feedback.
	 *
	 * @since 1.0.0
	 */
	public function restore_settings_feedback() {
		require( ABSPATH . 'wp-admin/options-head.php' );
	}

	/**
	 * Customize the highlighted parent menu for plugin's settings.
	 *
	 * @since 1.0.0
	 *
	 * @global $parent_file
	 * @global $submenu_file
	 * @global $typenow
	 */
	public function settings_menu_highlight() {
		global $parent_file, $submenu_file, $typenow;

		$parent_file  = add_query_arg( 'post_type', $this->post_type, 'edit.php' );
		$submenu_file = add_query_arg( 'page', 'wc_talks', 'options-general.php' );
		$typenow = $this->post_type;
	}

	/**
	 * Make sure the highlighted menus are the plugin's ones for its specific taxonomies.
	 *
	 * @since 1.0.0
	 *
	 * @global $current_screen
	 * @global $taxnow
	 */
	public function taxonomy_highlight() {
		global $current_screen, $taxnow;

		if ( is_a( $current_screen, 'WP_Screen' ) && ! empty( $taxnow ) && in_array( $taxnow, array( wct_get_tag(), wct_get_category() ) ) ) {
 			$current_screen->post_type = $this->post_type;
 		}
	}

	/**
	 * Restrict Bulk actions to only keep the delete one.
	 *
	 * @since 1.0.0
	 *
	 * @param  array  $bulk_actions list of available bulk actions.
	 * @return array                the new list.
	 */
	public function talk_bulk_actions( $bulk_actions = array() ) {
		if ( in_array( 'edit', array_keys( $bulk_actions ) ) ) {
			unset( $bulk_actions['edit'] );
		}
		return $bulk_actions;
	}

	/**
	 * Edit the current row post states to include Talk Proposals statuses.
	 *
	 * @since  1.1.0
	 *
	 * @param  array  $states The post row states.
	 * @param  WP_Post $post  The post row object.
	 * @return array          The current row post states.
	 */
	public function talk_states( $states = array(), $post ) {
		if ( $this->post_type !== get_post_type( $post ) ) {
			return $states;
		}

		$talk_states    = wct_get_statuses();
		$current_status = get_post_status( $post );

		if ( isset( $talk_states[$current_status] ) ) {
			$states = array_merge( $states, array( $current_status => $talk_states[$current_status] ) );
		}

		return $states;
	}

	/**
	 * Maybe disable the quick edit row action.
	 *
	 * @since 1.0.0
	 *
	 * @param  array  $actions list of available row actions.
	 * @return array           the new list
	 */
	public function talk_row_actions( $actions = array(), $talk = null ) {
		if ( empty( $talk ) || $talk->post_type != $this->post_type ) {
			return $actions;
		}

		// No row actions in case downloading talks
		if ( ! empty( $this->downloading_csv ) ) {
			return array();
		}

		return $actions;
	}

	/**
	 * Add new columns to the Talks WP List Table.
	 *
	 * @since 1.0.0
	 *
	 * @param  array  $columns the WP List Table columns.
	 * @return array           the new columns.
	 */
	public function column_headers( $columns = array() ) {
		$new_columns = array(
			'cat_talks'      => _x( 'Categories', 'talks admin category column header',        'wordcamp-talks' ),
			'tag_talks'      => _x( 'Tags',       'talks admin tag column header',             'wordcamp-talks' ),
		);

		if ( ! wct_is_rating_disabled() ) {
			$new_columns['rates'] = '<span class="vers"><span title="' . esc_attr__( 'Average Rating', 'wordcamp-talks' ) .'" class="talk-rating-bubble"></span></span>';
		}

		$temp_remove_columns = array( 'comments', 'date' );
		$has_columns = array_intersect( $temp_remove_columns, array_keys( $columns ) );

		// Reorder
		if ( $has_columns == $temp_remove_columns ) {
			$new_columns['comments'] = $columns['comments'];
			$new_columns['date'] = $columns['date'];
			unset( $columns['comments'], $columns['date'] );
		}

		// Merge
		$columns = array_merge( $columns, $new_columns );


		if ( ! empty( $this->downloading_csv ) ) {
			unset( $columns['cb'], $columns['date'] );

			if ( ! empty( $columns['title'] ) ) {
				$csv_columns = array(
					'title'        => $columns['title'],
					'talk_content' => esc_html_x( 'Content', 'downloaded csv content header', 'wordcamp-talks' ),
					'talk_link'    => esc_html_x( 'Link', 'downloaded csv link header', 'wordcamp-talks' ),
				);
			}

			$columns = array_merge( $csv_columns, $columns );

			// Replace dashicons to text
			if ( ! empty( $columns['comments'] ) ) {
				$columns['comments'] = esc_html_x( '# comments', 'downloaded csv comments num header', 'wordcamp-talks' );
			}

			if ( ! empty( $columns['rates'] ) ) {
				$columns['rates'] = esc_html_x( 'Average rating', 'downloaded csv rates num header', 'wordcamp-talks' );
			}

			/**
			 * Use this filter to only add columns for the downloaded csv file
			 *
			 * @param array $columns the columns specific to the csv output
			 */
			$columns = apply_filters( 'wct_admin_csv_column_headers', $columns );
		}

		return $columns;
	}

	/**
	 * Fills the custom columns datarows.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $column_name the column name.
	 * @param  int    $talk_id     the ID of the talk (row).
	 */
	public function column_data( $column_name = '', $talk_id = 0 ) {
		switch( $column_name ) {
			case 'rates' :
				$rate = wct_talks_get_average_rating( $talk_id );

				if ( ! empty( $rate ) ) {
					echo $rate;
				} else {
					echo '&#8212;';
				}
				break;

			case 'cat_talks' :
			case 'tag_talks' :
				if ( 'cat_talks' == $column_name ) {
					$taxonomy = wct_get_category();
				} elseif ( 'tag_talks' == $column_name ) {
					$taxonomy = wct_get_tag();
				} else {
					$taxonomy = false;
				}

				if ( empty( $taxonomy ) ) {
					return;
				}

				$taxonomy_object = get_taxonomy( $taxonomy );
				$terms = wp_get_object_terms( $talk_id, $taxonomy, array( 'fields' => 'all' ) );

				if ( empty( $terms ) ) {
					echo '&#8212;';
					return;
				}

				$output = array();
				foreach ( $terms as $term ) {
					$query_vars = array(
						'post_type'                 => $this->post_type,
						$taxonomy_object->query_var => $term->slug,
					);

					$out[] = sprintf( '<a href="%s">%s</a>',
						esc_url( add_query_arg( $query_vars, 'edit.php' ) ),
						esc_html( sanitize_term_field( 'name', $term->name, $term->term_id, $taxonomy, 'display' ) )
					);
				}

				echo join( _x( ', ', 'separator for tags list', 'wordcamp-talks' ), $out );
				break;

			default:
				/**
				 * @param  string $column_name the column name
				 * @param  int    $talk_id     the ID of the talk (row)
				 */
				do_action( 'wct_admin_column_data', $column_name, $talk_id );
				break;
		}
	}

	/**
	 * Add extra info to downloaded csv file.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $column_name the column name.
	 * @param  int    $talk_id     the ID of the talk (row).
	 * @return string HTML Output
	 */
	public function talk_row_extra_data( $column_name = '', $talk_id = '' ) {
		if ( 'talk_content' == $column_name ) {
			// Temporarly remove filters
			remove_filter( 'the_content', 'wptexturize'     );
			remove_filter( 'the_content', 'convert_smilies' );
			remove_filter( 'the_content', 'convert_chars'   );

			the_content();

			// Restore just in case
			add_filter( 'the_content', 'wptexturize'     );
			add_filter( 'the_content', 'convert_smilies' );
			add_filter( 'the_content', 'convert_chars'   );
		} else if ( 'talk_link' == $column_name ) {
			the_permalink();
		}
	}

	/**
	 * Gets the sortable columns.
	 *
	 * @since 1.0.0
	 *
	 * @param  array  $sortable_columns the list of sortable columns
	 * @return array                   the new list
	 */
	public function sortable_columns( $sortable_columns = array() ) {
		// No sortable columns if downloading talks
		if ( ! empty( $this->downloading_csv ) ) {
			return array();
		}

		$sortable_columns['rates'] = array( 'rates_count', true );

		return $sortable_columns;
	}

	/**
	 * Adds the list of ratings in a new metabox.
	 *
	 * @since 1.0.0
	 *
	 * @param  array  $metaboxes list of metaboxes
	 * @return array             the new list
	 */
	public function ratings_metabox( $metaboxes = array() ) {
		$rating_metabox = array(
			'rates' => array(
				'id'            => 'wct_ratings_box',
				'title'         => _x( 'Rates', 'Ratings metabox title', 'wordcamp-talks' ),
				'callback'      => array( $this, 'ratings_do_metabox' ),
				'context'       => 'advanced',
				'priority'      => 'core'
		) );

		return array_merge( $metaboxes, $rating_metabox );
	}

	/**
	 * Displays the ratings metabox.
	 *
	 * @since 1.0.0
	 *
	 * @param  WP_Post $talk the talk object
	 * @return string HTML output
	 */
	public function ratings_do_metabox( $talk = null ) {
		$id = $talk->ID;

		$ratings_stats = wct_count_ratings( $id, 0, true );
		$users_count  = count( $ratings_stats['users'] );

		$edit_link = get_edit_post_link( $id );

		if ( empty( $users_count ) ) {
			esc_html_e( 'Not rated yet', 'wordcamp-talks' );
		} else {
			$hintlabels = wct_get_hint_list();
			$hintlist = array_keys( $hintlabels );
			?>
			<p class="description">
				<?php echo esc_html( sprintf( _n(
					'%1$s member rated the Talk Proposal. Its Average rating is: %2$s',
					'%1$s members rated the Talk Proposal. Its Average rating is: %2$s',
					$users_count,
					'wordcamp-talks'
				), number_format_i18n( $users_count ), number_format_i18n( $ratings_stats['average'], 1 ) ) ); ?>
			</p>
			<ul class="admin-talk-rates">
				<?php foreach ( $hintlist as $hintlabel ) :
					$hint = $hintlabel + 1;
				?>
				<li>
					<div class="admin-talk-rates-star"><?php echo esc_html( ucfirst( $hintlabels[ $hintlabel ] ) ); ?></div>
					<div class="admin-talk-rates-users">
						<?php if ( empty( $ratings_stats['details'][ $hint ] ) ) : ?>
							&#8212;
						<?php else :
							foreach ( $ratings_stats['details'][ $hint ] as $user_id ) : ?>
							<span class="user-rated">
								<a href="<?php echo esc_url( wct_users_get_user_profile_url( $user_id ) );?>"><?php echo get_avatar( $user_id, 40 ); ?></a>

								<?php $edit_user_link = wp_nonce_url( add_query_arg( 'remove_vote', $user_id, $edit_link ), 'talk_remove_vote_' . $user_id ); ?>

								<a href="<?php echo esc_url( $edit_user_link ); ?>" class="del-rate" title="<?php esc_attr_e( 'Delete this rating', 'wordcamp-talks' );?>" data-userid="<?php echo $user_id; ?>">
									<div class="dashicons dashicons-trash"></div>
								</a>
							</span>
						<?php endforeach; endif; ?>
					</div>
				</li>
				<?php endforeach; ?>
			</ul>
			<?php
		}
	}

	/**
	 * Checks if a rate is to be deleted.
	 *
	 * @since 1.0.0
	 */
	public function maybe_delete_rate() {
		if ( ! wct_is_admin() ) {
			return;
		}

		if ( ! current_user_can( 'edit_talks' ) ) {
			return;
		}

		if ( empty( $_GET['remove_vote'] ) || empty( $_GET['post'] ) || empty( $_GET['action'] ) ) {
			return;
		}

		$talk_id = absint( $_GET['post'] );
		$user_id = absint( $_GET['remove_vote'] );

		// nonce check
		check_admin_referer( 'talk_remove_vote_' . $user_id );

		if( false !== wct_delete_rate( $talk_id, $user_id ) ) {
			$message = 11;
		} else {
			$message = 12;
		}

		// Utimate and not necessary check...
		if ( ! empty( $_GET['remove_vote'] ) ) {
			$redirect = add_query_arg( 'message', $message, get_edit_post_link( $talk_id, 'url' ) );
			wp_safe_redirect( $redirect );
			exit();
		}
	}

	/**
	 * Adds ratings specific updated messages.
	 *
	 * @since 1.0.0
	 *
	 * @param  array  $messages List of updated messages.
	 * @return array            New list.
	 */
	public function ratings_updated( $messages = array() ) {

		$messages[11] = esc_html__( 'Rating successfully deleted', 'wordcamp-talks' );
		$messages[12] = esc_html__( 'Something went wrong while trying to delete the rating.', 'wordcamp-talks' );

		return $messages;
	}

	/**
	 * Forces the query to include all talks
	 * Used to "feed" the downloaded csv spreadsheet.
	 *
	 * @since 1.0.0
	 *
	 * @param  WP_Query $posts_query.
	 */
	public function get_talks_by_status( $posts_query = null ) {
		if ( ! empty( $posts_query->query_vars['posts_per_page'] ) ) {
			$posts_query->query_vars['posts_per_page'] = -1;
		}

		// Unset the post status if not registered
		if ( ! empty( $_GET['post_status'] ) && ! get_post_status_object( $_GET['post_status'] ) ) {
			unset( $posts_query->query_vars['post_status'] );
		}
	}

	/**
	 * Temporarly restrict all user caps to 2 talk caps
	 * This is to avoid get_inline_data() to add extra html in title column.
	 *
	 * @since 1.0.0
	 *
	 * @param  array  $all_caps user's caps.
	 * @return array            restricted user's caps.
	 */
	public function filter_has_cap( $all_caps = array() ) {
		return array( 'read_private_talks' => true, 'edit_others_talks' => true );
	}

	/**
	 * Buffer talks list and outputs an csv file.
	 *
	 * @since 1.0.0
	 *
	 * @return String text/csv
	 */
	public function csv_export() {
		// Strip edit inline extra html
		remove_filter( 'map_meta_cap', 'wct_map_meta_caps', 10, 4 );
		add_filter( 'user_has_cap', array( $this, 'filter_has_cap' ), 10, 1 );

		// Get all talks
		add_action( 'wct_admin_request', array( $this, 'get_talks_by_status' ), 10, 1 );

		$html_list_table = _get_list_table( 'WP_Posts_List_Table' );
		$html_list_table->prepare_items();
		ob_start();
		?>
		<table>
			<tr>
				<?php $html_list_table->print_column_headers(); ?>
			</tr>
				<?php $html_list_table->display_rows_or_placeholder(); ?>
			</tr>
		</table>
		<?php
		$output = ob_get_clean();

		// Keep only table tags
		$allowed_html = array(
			'table' => array(),
			'tbody' => array(),
			'td'    => array(),
			'th'    => array(),
			'tr'    => array()
		);

		$output = wp_kses( $output, $allowed_html );

		$comma = ',';

		// If some users are still using Microsoft ;)
		if ( preg_match( "/Windows/i", $_SERVER['HTTP_USER_AGENT'] ) ) {
			$comma = ';';
			$output = utf8_decode( $output );
		}

		// $output to csv
		$csv = array();
		preg_match( '/<table(>| [^>]*>)(.*?)<\/table( |>)/is', $output, $b );
		$table = $b[2];
		preg_match_all( '/<tr(>| [^>]*>)(.*?)<\/tr( |>)/is', $table, $b );
		$rows = $b[2];
		foreach ( $rows as $row ) {
			//cycle through each row
			if ( preg_match( '/<th(>| [^>]*>)(.*?)<\/th( |>)/is', $row ) ) {
				//match for table headers
				preg_match_all( '/<th(>| [^>]*>)(.*?)<\/th( |>)/is', $row, $b );
				$csv[] = '"' . implode( '"' . $comma . '"', array_map( 'wct_generate_csv_content', $b[2] ) ) . '"';
			} else if ( preg_match( '/<td(>| [^>]*>)(.*?)<\/td( |>)/is', $row ) ) {
				//match for table cells
				preg_match_all( '/<td(>| [^>]*>)(.*?)<\/td( |>)/is', $row, $b );
				$csv[] = '"' . implode( '"' . $comma . '"', array_map( 'wct_generate_csv_content', $b[2] ) ) . '"';
			}
		}

		$file = implode( "\n", $csv );

		status_header( 200 );
		header( 'Cache-Control: cache, must-revalidate' );
		header( 'Pragma: public' );
		header( 'Content-Description: File Transfer' );
		header( 'Content-Disposition: attachment; filename=' . sprintf( '%s-%s.csv', esc_attr_x( 'talks', 'prefix of the downloaded csv', 'wordcamp-talks' ), date('Y-m-d-his' ) ) );
		header( 'Content-Type: text/csv;' );
		print( $file );
		exit();
	}

	/**
	 * Gets the help tabs for a given Administration screen.
	 *
	 * @since 1.0.0
	 *
	 * @param  string  $screen_id the Administration screen ID
	 * @return array   The help tabs
	 */
	public function get_help_tabs( $screen_id = '' ) {
		// Help urls
		$plugin_forum         = '<a href="https://github.com/imath/wordcamp-talks/issues">';
		$help_tabs            = false;
		$nav_menu_page        = '<a href="' . esc_url( admin_url( 'nav-menus.php' ) ) . '">';
		$widgets_page         = '<a href="' . esc_url( admin_url( 'widgets.php' ) ) . '">';

		/**
		 * @param array associative array to list the help tabs
		 */
		$help = array(
			'edit-talks' => array(
				'add_help_tab'     => array(
					array(
						'id'      => 'edit-talks-overview',
						'title'   => esc_html__( 'Overview', 'wordcamp-talks' ),
						'content' => array(
							esc_html__( 'This screen provides access to all the Talk Proposals users of your site shared. You can customize the display of this screen to suit your workflow.', 'wordcamp-talks' ),
							esc_html__( 'You can customize the display of this screen&#39;s contents in a number of ways:', 'wordcamp-talks' ),
							array(
								esc_html__( 'You can hide/display columns based on your needs and decide how many Talk Proposals to list per screen using the Screen Options tab.', 'wordcamp-talks' ),
								esc_html__( 'You can filter the Talk Proposals by status using the last dropdown box above the list.', 'wordcamp-talks' ),
								esc_html__( 'You can view Talk Proposals in a simple title list or with an excerpt. Choose the view you prefer by clicking on the icons at the top of the list on the right.', 'wordcamp-talks' ),
							),
						),
					),
					array(
						'id'      => 'edit-talks-row-actions',
						'title'   => esc_html__( 'Actions', 'wordcamp-talks' ),
						'content' => array(
							esc_html__( 'Hovering over a row in the Talk Proposals list will display action links that allow you to manage a Talk Proposal. You can perform the following actions:', 'wordcamp-talks' ),
							array(
								esc_html__( 'Edit takes you to the editing screen for that Talk Proposal. You can also reach that screen by clicking on the Talk Proposal title.', 'wordcamp-talks' ),
								esc_html__( 'Trash removes the Talk Proposal from this list and places it in the trash, from which you can permanently delete it.', 'wordcamp-talks' ),
								esc_html__( 'View opens the Talk Proposal in the WordCamp Talk Proposals&#39 part of your site.', 'wordcamp-talks' ),
							),
						),
					),
					array(
						'id'      => 'edit-talks-bulk-actions',
						'title'   => esc_html__( 'Bulk Actions', 'wordcamp-talks' ),
						'content' => array(
							esc_html__( 'You can also move multiple Talk Proposals to the trash at once. Select the Talk Proposals you want to trash using the checkboxes, then select the &#34;Move to Trash&#34; action from the Bulk Actions menu and click Apply.', 'wordcamp-talks' ),
						),
					),
					array(
						'id'      => 'edit-talks-sort-filter',
						'title'   => esc_html__( 'Sorting & filtering', 'wordcamp-talks' ),
						'content' => array(
							esc_html__( 'Clicking on specific column headers will sort the Talk Proposals list. You can sort the Talk Proposals alphabetically using the Title column header or by popularity:', 'wordcamp-talks' ),
							array(
								esc_html__( 'Click on the column header having a dialog buble icon to sort by number of comments.', 'wordcamp-talks' ),
								esc_html__( 'Click on the column header having a star icon to sort by rating.', 'wordcamp-talks' ),
							),
							esc_html__( 'Inside the rows, you can filter the Talk Proposals by categories or tags clicking on the corresponding terms.', 'wordcamp-talks' ),
						),
					),
				),
			),
			'talks' => array(
				'add_help_tab' => array(
					array(
						'id'      => 'talks-overview',
						'title'   => esc_html__( 'Overview', 'wordcamp-talks' ),
						'content' => array(
							esc_html__( 'The title field and the big Talk Proposal Editing Area are fixed in place, but you can reposition all the other boxes using drag and drop. You can also minimize or expand them by clicking the title bar of each box. Use the Screen Options tab to hide/show boxes.', 'wordcamp-talks' ),
						),
					),
				),
			),
			'settings_page_wc_talks' => array(
				'add_help_tab' => array(
					array(
						'id'      => 'settings-overview',
						'title'   => esc_html__( 'Overview', 'wordcamp-talks' ),
						'content' => array(
							esc_html__( 'This is the place where you can customize the behavior of WordCamp Talk Proposals.', 'wordcamp-talks' ),
							esc_html__( 'Please see the additional help tabs for more information on each individual section.', 'wordcamp-talks' ),
						),
					),
					array(
						'id'      => 'settings-main',
						'title'   => esc_html__( 'Main Settings', 'wordcamp-talks' ),
						'content' => array(
							sprintf( esc_html__( 'Just before the first option, you will find the link to the main archive page of the plugin. If you wish, you can use it to define a new custom link %1$smenu item%2$s.', 'wordcamp-talks' ), $nav_menu_page, '</a>' ),
							sprintf( esc_html__( 'If you do so, do not forget to update the link in case you change your permalink settings. Another possible option is to use the %1$sWordCamp Talk Proposals Navigation%2$s widget in one of your dynamic sidebars.', 'wordcamp-talks' ), $widgets_page, '</a>' ),
							esc_html__( 'In the Main Settings you have a number of options:', 'wordcamp-talks' ),
							array(
								esc_html__( 'Closing date: define the date when the call for speakers will end.', 'wordcamp-talks' ),
								esc_html__( 'Rating stars hover captions: fill a comma separated list of captions to replace default one. On front end, the number of rating stars will depend on the number of comma separated captions you defined in this setting.', 'wordcamp-talks' ),
							),
						),
					),
				),
			),
			'edit-category-talks' => array(
				'add_help_tab' => array(
					array(
						'id'      => 'talks-category-overview',
						'title'   => esc_html__( 'Overview', 'wordcamp-talks' ),
						'content' => array(
							esc_html__( 'Talk Proposal Categories can only be created by the site Administrator. To add a new Talk Proposal category please fill the following fields:', 'wordcamp-talks' ),
							array(
								esc_html__( 'Name - The name is how it appears on your site (in the category checkboxes of the Talk Proposal front end submit form, in the Talk Proposal&#39;s footer part or in the title of WordCamp Talk Proposals&#39 category archive pages).', 'wordcamp-talks' ),
								esc_html__( 'Slug - The &#34;slug&#34; is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.', 'wordcamp-talks' ),
								esc_html__( 'Description - If you set a description for your category, it will be displayed over the list of Talk Proposals in the category archive page.', 'wordcamp-talks' ),
							),
							esc_html__( 'You can change the display of this screen using the Screen Options tab to set how many items are displayed per screen and to display/hide columns in the table.', 'wordcamp-talks' ),
						),
					),
				),
			),
			'edit-tag-talks' => array(
				'add_help_tab' => array(
					array(
						'id'      => 'talks-tag-overview',
						'title'   => esc_html__( 'Overview', 'wordcamp-talks' ),
						'content' => array(
							esc_html__( 'Talk Proposal Tags can be created by any logged in user of the site from the Talk Proposal front end submit form. From this screen, to add a new Talk Proposal tag please fill the following fields:', 'wordcamp-talks' ),
							array(
								esc_html__( 'Name - The name is how it appears on your site (in the tag cloud, in the tags editor of the Talk Proposal front end submit form, in the Talk Proposal&#39;s footer part or in the title of WordCamp Talk Proposals&#39; tag archive pages).', 'wordcamp-talks' ),
								esc_html__( 'Slug - The &#34;slug&#34; is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.', 'wordcamp-talks' ),
								esc_html__( 'Description - If you set a description for your tag, it will be displayed over the list of Talk Proposals in the tag archive page.', 'wordcamp-talks' ),
							),
							esc_html__( 'You can change the display of this screen using the Screen Options tab to set how many Talk Proposals are displayed per screen and to display/hide columns in the table.', 'wordcamp-talks' ),
						),
					),
				),
			),
		);

		/**
		 * @param array $help associative array to list the help tabs
		 */
		$help = apply_filters( 'wct_get_help_tabs', $help );

		if ( ! empty( $help[ $screen_id ] ) ) {
			$help_tabs = array_merge( $help[ $screen_id ], array(
				'set_help_sidebar' => array(
					array(
						'strong'   => esc_html__( 'For more information:', 'wordcamp-talks' ),
						'content' => array(
							sprintf( esc_html_x( '%1$sSupport Forums (en)%2$s', 'help tab links', 'wordcamp-talks'   ), $plugin_forum, '</a>' ),
						),
					),
				),
			) );
		}

		return $help_tabs;
	}

	/**
	 * Adds the Ratings help tabs.
	 *
	 * @since 1.0.0
	 *
	 * @param  array  $help_tabs the list of help tabs.
	 * @return array             the new list of help tabs.
	 */
	public function rates_help_tabs( $help_tabs = array() ) {
		if ( ! empty( $help_tabs['talks']['add_help_tab'] ) ) {
			$talks_help_tabs = wp_list_pluck( $help_tabs['talks']['add_help_tab'], 'id' );
			$talks_overview = array_search( 'talks-overview', $talks_help_tabs );

			if ( isset( $help_tabs['talks']['add_help_tab'][ $talks_overview ]['content'] ) ) {
				$help_tabs['talks']['add_help_tab'][ $talks_overview ]['content'][] = esc_html__( 'The Ratings metabox allows you to manage the ratings the Talk Proposal has received.', 'wordcamp-talks' );
			}
		}

		return $help_tabs;
	}

	/**
	 * Prints a dropdown to select workflow state
	 *
	 * @since  1.0.0
	 *
	 * @param  string $selected  the db state
	 * @param  string $select_id the name/id of select field
	 * @return string HTML Output
	 */
	public function print_dropdown_workflow( $selected = '', $select_id = 'post_status' ) {
		printf( '<select name="%s" id="%s">', esc_attr( $select_id ), esc_attr( $select_id ) );

		if ( 'workflow-states' === $select_id ) {
			printf( '<option value="">%s</option>', esc_attr__( 'Filter by state', 'wordcamp-talks' ) );
		}

		foreach ( $this->workflow_states as $key_state => $state ) {
			$current = selected( $selected, $key_state, false );
			printf(
				'<option value="%s" %s>%s</option>',
				esc_attr( $key_state ),
				$current,
				esc_html( $state )
			);
		}

		echo '</select>';
	}

	/**
	 * Registers the workflow metabox.
	 *
	 * @since  1.0.0
	 *
	 * @return array The workflow metabox parameters.
	 */
	public function get_workflow_metabox() {
		return array(
			'workflow' => array(
				'id'            => 'wct_workflow_metabox',
				'title'         => __( 'Talk Proposal status', 'wordcamp-talks' ),
				'callback'      => array( $this, 'workflow_do_metabox' ),
				'context'       => 'side',
				'priority'      => 'high',
			),
		);
	}

	/**
	 * Outputs the workflow metabox.
	 *
	 * @since  1.0.0
	 *
	 * @param  WP_Post $selected  the db state.
	 */
	public function workflow_do_metabox( $talk = null ) {
		$id     = $talk->ID;
		$status = get_post_status( $talk );

		if ( ! empty( $status ) ) {
			$state = sanitize_key( $status );
		} else {
			$state = 'wct_pending';
		}

		/* translators: Publish box date format, see https://secure.php.net/date */
		$datef = __( 'M j, Y @ H:i', 'wordcamp-talks' );

		if ( 0 !== $id ) {
			$date          = date_i18n( $datef, strtotime( $talk->post_date ) );
			$modified_date = date_i18n( $datef, strtotime( $talk->post_modified ) );
		} else {
			$date = $modified_date = date_i18n( $datef, strtotime( current_time( 'mysql' ) ) );
		}
		?>

		<div id="misc-publishing-actions">
			<div class="misc-pub-section curtime misc-pub-curtime">
				<dl id="talk-timestamps">
					<dt><?php esc_html_e( 'Created on:', 'wordcamp-talks' ); ?></dt>
					<dd><?php echo $date; ?></dd>
					<dt><?php esc_html_e( 'Last Modified:', 'wordcamp-talks' ); ?></dt>
					<dd><?php echo $modified_date; ?></dd>
				</dl>
			</div>

			<p class="talk-major-action">
				<label class="screen-reader-text" for="post_status"><?php esc_html_e( 'Status', 'wordcamp-talks' ); ?></label>

				<?php
				$this->print_dropdown_workflow( $state );

				wp_nonce_field( 'wct_workflow_metabox_save', 'wct_workflow_metabox_metabox' );
				submit_button( __( 'Update', 'wordcamp-talks' ), 'secondary large right', 'save', false ); ?>
			</p>

			<?php
			/**
			 * Hook here to add custom misc actions.
			 *
			 * @since  1.1.0
			 *
			 * @param  WP_Post $talk The current talk object.
			 */
			do_action( 'wct_admin_workflow_metabox_misc', $talk ); ?>

			<div class="clear"></div>
		</div>

		<div id="major-publishing-actions">
			<?php
			/**
			 * Hook here to add custom major actions.
			 *
			 * Eg: a link to generate a session when on a WordCamp Site.
			 *
			 * @since  1.1.0
			 *
			 * @param  WP_Post $talk The current talk object.
			 */
			do_action( 'wct_admin_workflow_metabox_major', $talk );

			if ( current_user_can( 'delete_talk', $talk->ID ) ) {
				$delete_text = __( 'Move to Trash', 'wordcamp-talks' );

				if ( ! EMPTY_TRASH_DAYS ) {
					$delete_text = __( 'Delete Permanently', 'wordcamp-talks' );
				}

				printf( '<div id="delete-action"><a class="submitdelete deletion" href="%1$s">%2$s</a></div>',
					esc_url( get_delete_post_link( $talk->ID ) ),
					esc_html( $delete_text )
				);
			}
			?>

			<div class="clear"></div>
		</div>
		<?php
	}

	/**
	 * Register the menu accordeon.
	 *
	 * @since 1.1.0
	 */
	public function menu_accordion() {
		add_meta_box(
			'wct-nav-menu',
			_x( 'Talk Proposals', 'menu admin accordion box title', 'wordcamp-talks' ),
			array( $this, 'do_accordion' ),
			'nav-menus',
			'side',
			'default'
		);
	}
	/**
	 * Output the Menu Accordion section.
	 *
	 * @since 1.1.0
	 */
	public function do_accordion() {
		global $_nav_menu_placeholder, $nav_menu_selected_id;

		$nav_items = wct_get_nav_items();

		if ( ! $nav_items ) {
			return;
		}
		?>
		<div class="wct-nav-menu" id="wct">

			<div id="tabs-panel-posttype-wct_nav_item" class="tabs-panel tabs-panel-active">
				<ul id="wct_nav_item-menu-checklist" class="categorychecklist form-no-clear">

					<?php foreach ( $nav_items as $nav_item ) :
						// Decrease on each loop
						if ( 0 > $_nav_menu_placeholder ) {
							$_nav_menu_placeholder = $_nav_menu_placeholder - 1;
						}  else {
							$_nav_menu_placeholder = -1;
						}
					?>

						<li>
							<label class="menu-item-title">
								<input type="checkbox" class="menu-item-checkbox" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-object-id]" value="-1"> <?php echo esc_html( $nav_item['title'] ) ; ?>
							</label>

							<input type="hidden" class="menu-item-object" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-object]" value="<?php echo esc_attr( $nav_item['object'] ) ; ?>" />
							<input type="hidden" class="menu-item-url" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-url]" value="<?php echo esc_url( $nav_item['url'] ) ; ?>">
							<input type="hidden" class="menu-item-title" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-title]" value="<?php echo esc_attr( $nav_item['title'] ) ; ?>">
							<input type="hidden" class="menu-item-type" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-type]" value="<?php echo esc_attr( $nav_item['type'] ) ; ?>" />
						</li>

					<?php endforeach; ?>

				</ul>
			</div>

			<p class="button-controls wp-clearfix">

				<?php if ( count( $nav_items ) > 1 ) : ?>
					<span class="list-controls">
						<a href="<?php echo esc_url( add_query_arg( 'selectall', 1, admin_url( 'nav-menus.php' ) ) ); ?>#wct" class="select-all aria-button-if-js">
							<?php esc_html_e( 'Select All', 'wordcamp-talks' ); ?>
						</a>
					</span>
				<?php endif ; ?>

				<span class="add-to-menu">
					<input type="submit"<?php wp_nav_menu_disabled_check( $nav_menu_selected_id ); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to menu', 'wordcamp-talks' ); ?>" name="add-wct-menu-item" id="submit-wct" />
					<span class="spinner"></span>
				</span>
			</p>

		</div><!-- /#wct -->
		<?php
	}

	/**
	 * Add the inline edit workflow state control.
	 *
	 * @since  1.O.0
	 *
	 * @param  string $column_name the column name.
	 * @param  string $post_type   the post type identifier.
	 * @return string HTML output
	 */
	public function inline_edit_workflow( $column_name = '', $post_type = '' ) {
		// Only in Edit Talks screen!
		if ( $this->post_type !== $post_type || 'rates' !== $column_name ) {
			return;
		}
		?>

		<fieldset class="inline-edit-col-right">
			<div class="inline-edit-group">
				<label class="inline-edit-workflow-state alignleft">
					<span class="title"><?php esc_html_e( 'Status', 'wordcamp-talks' ); ?></span>
					<?php $this->print_dropdown_workflow( 'pending', '_status' ); ?>
				</label>
			</div>
		</fieldset>

		<?php
	}

	/**
	 * Remove some submenus and add some custom styles.
	 *
	 * @since 1.0.0
	 */
	public function admin_head() {
 		// Remove the fake Settings submenu
 		remove_submenu_page( 'options-general.php', 'wc_talks' );

 		//Generate help if one is available for the current screen
 		if ( wct_is_admin() || ! empty( $this->is_plugin_settings ) ) {

 			$screen = get_current_screen();

 			if ( ! empty( $screen->id ) && ! $screen->get_help_tabs() ) {
 				$help_tabs_list = $this->get_help_tabs( $screen->id );

 				if ( ! empty( $help_tabs_list ) ) {
 					// Loop through tabs
 					foreach ( $help_tabs_list as $key => $help_tabs ) {
 						// Make sure types are a screen method
 						if ( ! in_array( $key, array( 'add_help_tab', 'set_help_sidebar' ) ) ) {
 							continue;
 						}

 						foreach ( $help_tabs as $help_tab ) {
 							$content = '';

 							if ( empty( $help_tab['content'] ) || ! is_array( $help_tab['content'] ) ) {
 								continue;
 							}

 							if ( ! empty( $help_tab['strong'] ) ) {
 								$content .= '<p><strong>' . $help_tab['strong'] . '</strong></p>';
 							}

 							foreach ( $help_tab['content'] as $tab_content ) {
								if ( is_array( $tab_content ) ) {
									$content .= '<ul><li>' . join( '</li><li>', $tab_content ) . '</li></ul>';
								} else {
									$content .= '<p>' . $tab_content . '</p>';
								}
							}

							$help_tab['content'] = $content;

 							if ( 'add_help_tab' == $key ) {
 								$screen->add_help_tab( $help_tab );
 							} else {
 								$screen->set_help_sidebar( $content );
 							}
 						}
 					}
 				}
 			}
 		}

 		$post_type = sanitize_html_class( $this->post_type );

 		// Add some css
		?>

		<style type="text/css" media="screen">
		/*<![CDATA[*/

			/* Bubble style for Main Post type menu */
			#adminmenu .wp-menu-open.menu-icon-<?php echo $post_type;?> .awaiting-mod {
				background-color: #2ea2cc;
				color: #fff;
			}

			#wordcamp-talks-csv span.dashicons-media-spreadsheet {
				vertical-align: text-bottom;
			}

			body.post-type-<?php echo $post_type;?> .wp-list-table tr.status-wct_pending th {
				border-left-width: 4px;
				border-left-style: solid;
				border-left-color: #ffba00;
			}

			body.post-type-<?php echo $post_type;?> .fixed th.column-rates {
				width: 10%;
			}

			body.post-type-<?php echo $post_type;?> .fixed th.column-cat_talks,
			body.post-type-<?php echo $post_type;?> .fixed th.column-tag_talks {
				width: 15%;
			}

			body.post-type-<?php echo $post_type;?> #poststuff #wct_workflow_metabox .inside {
				margin: 0;
				padding: 0;
			}

			body.post-type-<?php echo $post_type;?> .talk-major-action {
				padding: 6px 10px 8px;
			}

			body.post-type-<?php echo $post_type;?> #wct_workflow_metabox .submitdelete {
				color: #a00;
			}

			body.post-type-<?php echo $post_type;?> #wct_workflow_metabox .submitdelete:hover {
				color: #dc3232;
				border: none;
			}

			body.post-type-<?php echo $post_type;?> #talk-timestamps dt {
				font-weight: bold;
			}

			body.post-type-<?php echo $post_type;?> #talk-timestamps dt:before {
				font: 400 20px/1 dashicons;
				speak: none;
				display: inline-block;
				margin-left: -1px;
				padding-right: 3px;
				vertical-align: top;
				-webkit-font-smoothing: antialiased;
				-moz-osx-font-smoothing: grayscale;
				content: "\f145";
			}

			body.post-type-<?php echo $post_type;?> #talk-timestamps dd {
				margin-left: 22px;
			}

			body.post-type-<?php echo $post_type;?> .talk-major-action select {
				width: 65%;
			}

			body.post-type-<?php echo $post_type;?> #wct-session-action a {
				display: inline-block;
				margin-bottom: 1.5em;
				width: 100%;
				text-align: center;
			}

			<?php if ( wct_is_admin() && ! wct_is_rating_disabled() ) : ?>
				/* Rating stars in screen options and in talks WP List Table */
				.metabox-prefs .talk-rating-bubble:before,
				th .talk-rating-bubble:before {
					font: normal 20px/.5 'dashicons';
					speak: none;
					display: inline-block;
					padding: 0;
					top: 4px;
					left: -4px;
					position: relative;
					vertical-align: top;
					-webkit-font-smoothing: antialiased;
					-moz-osx-font-smoothing: grayscale;
					text-decoration: none !important;
					color: #444;
				}

				th .talk-rating-bubble:before,
				.metabox-prefs .talk-rating-bubble:before {
					content: '\f155';
				}

				.metabox-prefs .talk-rating-bubble:before {
					vertical-align: baseline;
				}

				/* Rates management */
				#wct_ratings_box ul.admin-talk-rates {
					width: 100%;
					list-style: none;
					clear: both;
					margin: 0;
					padding: 0;
				}

				#wct_ratings_box ul.admin-talk-rates li {
					list-style: none;
					overflow: hidden;
					position: relative;
					padding:15px 0;
					border-bottom:dotted 1px #ccc;
				}

				#wct_ratings_box ul.admin-talk-rates li:last-child {
					border:none;
				}

				#wct_ratings_box ul.admin-talk-rates li div.admin-talk-rates-star {
					float:left;
				}

				#wct_ratings_box ul.admin-talk-rates li div.admin-talk-rates-star {
					width:20%;
					font-weight: bold;
				}

				#wct_ratings_box ul.admin-talk-rates li div.admin-talk-rates-users {
					margin-left: 20%;
				}

				#wct_ratings_box ul.admin-talk-rates li div.admin-talk-rates-users span.user-rated {
					display:inline-block;
					margin:5px;
					padding:5px;
					-webkit-box-shadow: 0 1px 1px 1px rgba(0,0,0,0.1);
					box-shadow: 0 1px 1px 1px rgba(0,0,0,0.1);
				}

				#wct_ratings_box ul.admin-talk-rates li div.admin-talk-rates-users a.del-rate {
					text-decoration: none;
				}

				#wct_ratings_box ul.admin-talk-rates li div.admin-talk-rates-users a.del-rate div {
					vertical-align: baseline;
				}

				body.post-type-<?php echo $post_type;?> .inline-edit-status,
				body.post-type-<?php echo $post_type;?> .inline-edit-col-left .inline-edit-group {
					display: none;
				}

			<?php endif; ?>

		/*]]>*/
		</style>
		<?php
	}

	/**
	 * Modifies the links in plugins table.
	 *
	 * @since 1.0.0
	 *
	 * @param  array  $links The existing links
	 * @param  string $file  The file of plugins
	 * @return array         The existing links & the new ones.
	 */
	public function modify_plugin_action_links( $links, $file ) {
		if ( wct_get_basename() !== $file ) {
			return $links;
		}

		// Add a few links to the existing links array
		return array_merge( $links, array(
			'settings' => '<a href="' . esc_url( add_query_arg( 'page', 'wc_talks', admin_url( 'options-general.php' ) ) ) . '">' . esc_html__( 'Settings', 'wordcamp-talks' ) . '</a>',
		) );
	}
}

endif;
