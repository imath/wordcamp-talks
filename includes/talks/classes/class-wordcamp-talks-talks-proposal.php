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

if ( ! class_exists( 'WordCamp_Talks_Talks_Proposal' ) ) :
/**
 * Talk Class.
 *
 * @since 1.0.0
 * @since 1.1.0 Renamed from WordCamp_Talks_Talk to WordCamp_Talks_Talks_Proposal.
 */
class WordCamp_Talks_Talks_Proposal {

	/**
	 * The ID of the talk
	 *
	 * @access  public
	 * @var     integer
	 */
	public $id;

	/**
	 * The name of the talk
	 *
	 * @access  public
	 * @var     string
	 */
	public $name;

	/**
	 * The ID of the author
	 *
	 * @access  public
	 * @var     integer
	 */
	public $author;

	/**
	 * The title of the talk
	 *
	 * @access  public
	 * @var     string
	 */
	public $title;

	/**
	 * The content of the talk
	 *
	 * @access  public
	 * @var     string
	 */
	public $description;

	/**
	 * The status of the talk
	 *
	 * @access  public
	 * @var     string
	 */
	public $status;

	/**
	 * Associative Array containing terms for
	 * the tag and category taxonomies
	 *
	 * @access  public
	 * @var     array
	 */
	public $taxonomies;

	/**
	 * Associative Array meta_key => meta_value
	 *
	 * @access  public
	 * @var     array
	 */
	public $metas;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param  mixed int|string ID or name of the talk
	 */
	function __construct( $id = 0 ){
		if ( ! empty( $id ) ) {
			if ( is_numeric( $id ) ) {
				$this->id = $id;
			} else {
				$this->name = $id;
			}
			$this->populate();
		}
	}

	/**
	 * Get a talk.
	 *
	 * @since 1.0.0)
	 */
	public function populate() {

		if ( empty( $this->id ) ) {
			// Let's try to get an ID thanks to its name.
			if ( ! empty( $this->name ) ) {
				$this->talk = self::get_talk_by_name( $this->name );
			}
		} else {
			$this->talk  = get_post( $this->id );
		}

		$this->id          = $this->talk->ID;
		$this->author      = $this->talk->post_author;
		$this->title       = $this->talk->post_title;
		$this->description = $this->talk->post_content;
		$this->status      = $this->talk->post_status;

		// Build an array of taxonomies
		$this->taxonomies = array();

		// Look in categories
		$categories = wp_get_object_terms( $this->id, wct_get_category(), array( 'fields' => 'ids' ) );

		if ( ! empty( $categories ) ) {
			$this->taxonomies = array_merge( $this->taxonomies, array(
				wct_get_category() => $categories,
			) );
		}

		// Look in tags
		$tags = wp_get_object_terms( $this->id, wct_get_tag(), array( 'fields' => 'slugs' ) );

		if ( ! empty( $tags ) ) {
			$this->taxonomies = array_merge( $this->taxonomies, array(
				wct_get_tag() => join( ',', $tags )
			) );
		}

		// Build an array of post metas
		$this->metas = array();

		$metas = get_post_custom( $this->id );

		foreach ( $metas as $key => $meta ) {
			if ( false === strpos( $key, '_wc_talks_' ) ) {
				continue;
			}

			$wctalks_key = str_replace( '_wc_talks_', '', $key );

			if ( count( $meta ) == 1 ) {
				$this->metas[ $wctalks_key ] = maybe_unserialize( $meta[0] );
			} else {
				$this->metas[ $wctalks_key ] = array_map( 'maybe_unserialize', $meta );
			}

			$this->metas['keys'][] = $wctalks_key;
		}
	}

	/**
	 * Save a talk.
	 *
	 * @since 1.0.0
	 */
	public function save() {
		$this->id          = apply_filters_ref_array( 'wct_id_before_save',          array( $this->id,          &$this ) );
		$this->author      = apply_filters_ref_array( 'wct_author_before_save',      array( $this->author,      &$this ) );
		$this->title       = apply_filters_ref_array( 'wct_title_before_save',       array( $this->title,       &$this ) );
		$this->description = apply_filters_ref_array( 'wct_description_before_save', array( $this->description, &$this ) );
		$this->status      = apply_filters_ref_array( 'wct_status_before_save',      array( $this->status,      &$this ) );
		$this->taxonomies  = apply_filters_ref_array( 'wct_taxonomies_before_save',  array( $this->taxonomies,  &$this ) );
		$this->metas       = apply_filters_ref_array( 'wct_metas_before_save',       array( $this->metas,       &$this ) );

		// Use this, not the filters above
		do_action_ref_array( 'wct_before_save', array( &$this ) );

		if ( empty( $this->author ) || empty( $this->title ) ) {
			return false;
		}

		if ( empty( $this->status ) ) {
			$this->status = wct_default_talk_status();
		}

		$post_args = array(
			'post_author'  => $this->author,
			'post_title'   => $this->title,
			'post_type'    => wct_get_post_type(),
			'post_content' => $this->description,
			'post_status'  => $this->status,
			'tax_input'    => $this->taxonomies,
		);

		// Update.
		if ( $this->id ) {
			$post_args = array_merge( array(
				'ID' => $this->id,
			), $post_args );

			$result = wp_update_post( $post_args );
		// Insert.
		} else {
			$result = wp_insert_post( $post_args );
		}

		if ( ! empty( $result ) && ! empty( $this->metas ) ) {

			foreach ( $this->metas as $meta_key => $meta_value ) {
				// Do not update these keys.
				$skip_keys = apply_filters( 'wct_meta_key_skip_save', array( 'keys', 'rates', 'average_rate', 'workflow_state' ) );
				if ( in_array( $meta_key, $skip_keys ) ) {
					continue;
				}

				if ( empty( $meta_value ) ) {
					wct_talks_delete_meta( $result, $meta_key );
				} else {
					wct_talks_update_meta( $result, $meta_key, $meta_value );
				}
			}
		}

		do_action_ref_array( 'wct_after_save', array( $result, &$this ) );

		return $result;
	}

	/**
	 * The selection query.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $args arguments to customize the query
	 * @return array associative array containing talks and total count.
	 */
	public static function get( $args = array() ) {

		$defaults = array(
			'author'     => 0,
			'per_page'   => 10,
			'page'       => 1,
			'search'     => '',
			'exclude'    => '',
			'include'    => '',
			'orderby'    => 'date',
			'order'      => 'DESC',
			'meta_query' => array(),
			'tax_query'  => array(),
		);

		$r = wp_parse_args( $args, $defaults );

		/**
		 * Allow status to be filtered
		 * @see wct_talks_get_status()
		 */
		$talks_status = wct_talks_get_status();

		if ( ! empty( $r['is_widget'] ) && ! current_user_can( 'rate_talks' ) ) {
			$talks_status = array( 'publish' );
		}

		$query_args = array(
			'post_status'    => $talks_status,
			'post_type'      => 'talks',
			'posts_per_page' => $r['per_page'],
			'paged'          => $r['page'],
			'orderby'        => $r['orderby'],
			'order'          => $r['order'],
			's'              => $r['search'],
		);

		if ( ! empty( $r['author'] ) ) {
			$query_args['author'] = $r['author'];
		}

		if ( ! empty( $r['exclude'] ) ) {
			$query_args['post__not_in'] = wp_parse_id_list( $r['exclude'] );
		}

		if ( ! empty( $r['include'] ) ) {
			$query_args['post__in'] = wp_parse_id_list( $r['include'] );
		}

		if ( 'rates_count' == $r['orderby'] ) {
			$r['meta_query'][] = array(
				'key'     => '_wc_talks_average_rate',
				'compare' => 'EXISTS'
			);
		}

		if ( ! empty( $r['meta_query'] ) ) {
			$query_args['meta_query'] = $r['meta_query'];
		}

		if ( ! empty( $r['tax_query'] ) ) {
			$query_args['tax_query'] = $r['tax_query'];
		}

		// Get the main order
		$main_order = wct_get_global( 'orderby' );

		// Apply the one requested
		wct_set_global( 'orderby', $r['orderby'] );

		$talks = new WP_Query( $query_args );

		// Reset to main order
		wct_set_global( 'orderby', $main_order );

		return array( 'talks' => $talks->posts, 'total' => $talks->found_posts );
	}

	/**
	 * Get a talk using its name.
	 *
	 * @since 1.0.0
	 *
	 * @global $wpdb
	 * @param  string name of the talk
	 * @return WP_Post the talk object
	 */
	public static function get_talk_by_name( $name = '' ) {
		global $wpdb;

		$where = $wpdb->prepare( 'post_name = %s AND post_type = %s', $name, wct_get_post_type() );
		$id = $wpdb->get_var( "SELECT ID FROM {$wpdb->posts} WHERE {$where}" );

		return get_post( $id );
	}
}

endif;
