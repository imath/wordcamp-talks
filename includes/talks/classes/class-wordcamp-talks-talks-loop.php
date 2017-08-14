<?php
/**
 * WordCamp Talks classes.
 *
 * @package WordCamp Talks
 * @subpackage talks/classes
 *
 * @since 1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WordCamp_Talks_Talks_Loop' ) ) :
/**
 * Talks loop Class.
 *
 * @since 1.0.0
 * @since 1.1.0 Renamed from WordCamp_Talks_Loop_Talks to WordCamp_Talks_Talks_Loop.
 */
class WordCamp_Talks_Talks_Loop extends WordCamp_Talks_Core_Loop {

	/**
	 * The constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $args the loop args
	 */
	public function __construct( $args = array() ) {

		if ( ! empty( $args ) && empty( $args['is_widget'] ) ) {
			$paged = get_query_var( 'paged' );

			// Set which pagination page
			if ( ! empty( $paged ) ) {
				$args['page'] = $paged;

			// Checking query string just in case
			} else if ( ! empty( $_GET['paged'] ) ) {
				$args['page'] = absint( $_GET['paged'] );

			// Checking in page args
			} else if ( ! empty( $args['page'] ) ) {
				$args['page'] = absint( $args['page'] );

			// Default to first page
			} else {
				$args['page'] = 1;
			}
		}

		// Only get the talk requested
		if ( ! empty( $args['talk_name'] ) ) {

			$query_loop = wct_get_global( 'query_loop' );

			if ( empty( $query_loop->talk ) ) {
				$talk  = wct_talks_get_talk_by_name( $args['talk_name'] );
			} else {
				$talk  = $query_loop->talk;
			}

			// can't do this too ealy
			$reset_data = array_merge( (array) $talk, array( 'is_page' => true ) );
			wct_reset_post( $reset_data );

			// this needs a "reset postdata"!
			wct_set_global( 'needs_reset', true );

			$talks = array(
				'talks'    => array( $talk ),
				'total'    => 1,
				'get_args' => array(
					'page'     => 1,
					'per_page' => 1,
				),
			);

		// Get the talks
		} else {
			$talks = wct_talks_get_talks( $args );
		}

		if ( ! empty( $talks['get_args'] ) ) {
			foreach ( $talks['get_args'] as $key => $value ) {
				$this->{$key} = $value;
			}
		} else {
			return false;
		}

		$params = array(
			'plugin_prefix'    => 'wct',
			'item_name'        => 'talk',
			'item_name_plural' => 'talks',
			'items'            => $talks['talks'],
			'total_item_count' => $talks['total'],
			'page'             => $this->page,
			'per_page'         => $this->per_page,
		);

		$paginate_args = array();

		// No pretty links
		if ( ! wct_is_pretty_links() ) {
			$paginate_args['base'] = add_query_arg( 'paged', '%#%' );

		} else {

			// Is it the main archive page ?
			if ( wct_is_talks_archive() ) {
				$base = trailingslashit( wct_get_root_url() ) . '%_%';

			// Or the category archive page ?
			} else if ( wct_is_category() ) {
				$base = trailingslashit( wct_get_category_url() ) . '%_%';

			// Or the tag archive page ?
			} else if ( wct_is_tag() ) {
				$base = trailingslashit( wct_get_tag_url() ) . '%_%';

			// Or the displayed user rated talks ?
			} else if ( wct_is_user_profile_rates() ) {
				$base = trailingslashit( wct_users_get_displayed_profile_url( 'rates' ) ) . '%_%';

			// Or the displayed user talks "to rate"  ?
			} else if ( wct_is_user_profile_to_rate() ) {
				$base = trailingslashit( wct_users_get_displayed_profile_url( 'to_rate' ) ) . '%_%';

			// Or the displayed user published talks ?
			} else if ( wct_is_user_profile_talks() ) {
				$base = trailingslashit( wct_users_get_displayed_profile_url( 'talks' ) ) . '%_%';

			// Or the displayed user home page ?
			} else if ( wct_is_user_profile_home() ) {
				$base = trailingslashit( wct_users_get_displayed_profile_url() ) . '%_%';

			// Or nothing i've planed ?
			} else {

				/**
				 * Create your own pagination base if not handled by the plugin
				 *
				 * @param string empty string
				 */
				$base = apply_filters( 'wct_talks_pagination_base', '' );
			}

			$paginate_args['base']   = $base;
			$paginate_args['format'] = wct_paged_slug() . '/%#%/';
		}

		// Is this a search ?
		if ( wct_get_global( 'is_search' ) ) {
			$paginate_args['add_args'] = array( wct_search_rewrite_id() => $_GET[ wct_search_rewrite_id() ] );
		}

		// Do we have a specific order to use ?
		$orderby = wct_get_global( 'orderby' );

		if ( ! empty( $orderby ) && 'date' != $orderby ) {
			$merge = array();

			if ( ! empty( $paginate_args['add_args'] ) ) {
				$merge = $paginate_args['add_args'];
			}
			$paginate_args['add_args'] = array_merge( $merge, array( 'orderby' => $orderby ) );
		}

		/**
		 * Use this filter to override the pagination
		 *
		 * @param array $paginate_args the pagination arguments
		 */
		parent::start( $params, apply_filters( 'wct_talks_pagination_args', $paginate_args ) );
	}
}

endif;
