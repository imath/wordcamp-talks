<?php

/**
 * Talk metas Class.
 *
 * Tries to ease the process of managing custom fields for talks
 * @see  wct_talks_register_meta() talks/functions to
 * register new talk metas.
 *
 * @package WordCamp Talks
 * @subpackage talk/tags
 *
 * @since 1.0.0
 */
class WordCamp_Talk_Metas {

	/**
	 * List of meta objects
	 *
	 * @access  public
	 * @var     array
	 */
	public $metas;

	/**
	 * The constructor
	 *
	 * @package WordCamp Talks
	 * @subpackage talks/classes
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->do_metas();
	}

	/**
	 * Starts the class
	 *
	 * @package WordCamp Talks
	 * @subpackage talks/classes
	 *
	 * @since 1.0.0
	 */
	public static function start() {
		$wct = wct();

		if ( empty( $wct->talk_metas ) ) {
			$wct->talk_metas = new self;
		}

		return $wct->talk_metas;
	}

	/**
	 * Checks if talk metas are registered and hooks to some key actions/filters
	 *
	 * @package WordCamp Talks
	 * @subpackage talks/classes
	 *
	 * @since 1.0.0
	 */
	private function do_metas() {
		$this->metas = wct_get_global( 'wc_talks_metas' );

		if ( empty( $this->metas ) || ! is_array( $this->metas ) ) {
			return;
		}

		/** Admin *********************************************************************/
		add_filter( 'wct_admin_get_meta_boxes', array( $this, 'register_metabox' ), 10, 1 );
		add_action( 'wct_save_metaboxes',       array( $this, 'save_metabox' ),     10, 3 );

		/** Front *********************************************************************/
		add_action( 'wct_talks_the_talk_meta_edit', array( $this, 'front_output'  ) );
		add_action( 'wct_before_talk_footer',       array( $this, 'single_output' ) );
	}

	/**
	 * Registers a new metabox for custom fields
	 *
	 * @package WordCamp Talks
	 * @subpackage talks/classes
	 *
	 * @since 1.0.0
	 *
	 * @param  array  $metaboxes the metabox list
	 * @return array            the new list
	 */
	public function register_metabox( $metaboxes = array() ) {
		$metas_metabox = array(
			'wc_talks_metas' => array(
				'id'            => 'wct_metas_box',
				'title'         => __( 'Custom fields', 'wordcamp-talks' ),
				'callback'      => array( $this, 'do_metabox' ),
				'context'       => 'advanced',
				'priority'      => 'high'
		) );

		return array_merge( $metaboxes, $metas_metabox );
	}

	/**
	 * Outputs the fields in the Custom Field Talk metabox
	 *
	 * @package WordCamp Talks
	 * @subpackage talks/classes
	 *
	 * @since 1.0.0
	 *
	 * @param  WP_Post $talk the talk object
	 * @return string       HTML output
	 */
	public function do_metabox( $talk = null ) {
		if ( empty( $talk->ID ) || ! is_array( $this->metas ) ) {
			esc_html_e( 'No custom fields available', 'wordcamp-talks' );
			return;
		}

		$meta_list = array_keys( $this->metas );
		?>
		<div id="wc-talks_list_metas">
			<ul>
			<?php foreach ( $this->metas as $meta_object ) :?>
				<li id="wc-talks-meta-<?php echo esc_attr( $meta_object->meta_key );?>"><?php $this->display_meta( $talk->ID, $meta_object, 'admin' );?></li>
			<?php endforeach;?>
			</ul>

			<input type="hidden" value="<?php echo join( ',', $meta_list );?>" name="wct[meta_keys]"/>
		</div>
		<?php
		wp_nonce_field( 'admin-wc-talks-metas', '_admin_wc_talks_metas' );
	}

	/**
	 * Displays an talk's meta
	 *
	 * Used for forms (admin or front) and single outputs
	 *
	 * @package WordCamp Talks
	 * @subpackage talks/classes
	 *
	 * @since 1.0.0
	 *
	 * @param  int     $talk_id     the ID of the talk
	 * @param  object  $meta_object the meta object to send to callback function
	 * @param  string  $context     the context (admin/single/form)
	 * @return string               HTML Output
	 */
	public function display_meta( $talk_id = 0, $meta_object = null, $context = 'form' ) {
		// bail if no meta key
		if ( empty( $meta_object->meta_key ) ) {
			return;
		}

		$meta_object->field_name  = 'wct[_the_metas]['. $meta_object->meta_key .']';
		$meta_object->field_value = false;
		$meta_object->talk_id     = $talk_id;
		$display_meta             = '';

		if ( empty( $meta_object->label ) ) {
			$meta_object->label = ucfirst( str_replace( '_', ' ', $meta_object->meta_key ) );
		}

		if ( ! empty( $talk_id ) ) {
			$meta_object->field_value = wct_talks_get_meta( $talk_id, $meta_object->meta_key );
		}

		if ( empty( $meta_object->form ) ) {
			$meta_object->form = $meta_object->admin;
		}

		if ( 'single' == $context && empty( $meta_object->field_value ) ) {
			return;
		}

		if ( ! is_callable( $meta_object->{$context} ) ) {
			return;
		}

		// We apply the callback as an action
		add_action( 'wct_talks_meta_display', $meta_object->{$context}, 10, 3 );

		// Generate the output for the meta object
		do_action( 'wct_talks_meta_display', $display_meta, $meta_object, $context );

		// Remove the action for other metas
		remove_action( 'wct_talks_meta_display', $meta_object->{$context}, 10, 3 );
	}

	/**
	 * Saves the custom fields when edited from the admin screens (edit/post new)
	 *
	 * @package WordCamp Talks
	 * @subpackage talks/classes
	 *
	 * @since 1.0.0
	 *
	 * @param  int      $id     the talk ID
	 * @param  WP_Post  $talk   the talk object
	 * @param  bool     $update whether it's an update or not
	 * @return int         		the ID of the talk
	 */
	public function save_metabox( $id = 0, $talk = null, $update = false ) {
		// Bail if no meta to save
		if ( empty( $_POST['wct']['meta_keys'] ) )  {
			return $id;
		}

		check_admin_referer( 'admin-wc-talks-metas', '_admin_wc_talks_metas' );

		$the_metas = array();
		if ( ! empty( $_POST['wct']['_the_metas'] ) ) {
			$the_metas = $_POST['wct']['_the_metas'];
		}

		$meta_keys = explode( ',', $_POST['wct']['meta_keys'] );
		$meta_keys = array_map( 'sanitize_key', (array) $meta_keys );

		foreach ( $meta_keys as $meta_key ) {
			if ( empty( $the_metas[ $meta_key ] ) && wct_talks_get_meta( $id, $meta_key ) ) {
				wct_talks_delete_meta( $id, $meta_key );
			} else if ( ! empty( $the_metas[ $meta_key ] ) ) {
				wct_talks_update_meta( $id, $meta_key, $the_metas[ $meta_key ] );
			}
		}

		return $id;
	}

	/**
	 * Displays metas for form/single display
	 *
	 * @package WordCamp Talks
	 * @subpackage talks/classes
	 *
	 * @since 1.0.0
	 *
	 * @param  string $context the context (single/form)
	 * @return string          HTML Output
	 */
	public function front_output( $context = '' ) {
		if ( empty( $this->metas ) ) {
			return;
		}

		if ( empty( $context ) ) {
			$context = 'form';
		}

		$wct = wct();

		$talk_id = 0;

		if ( ! empty( $wct->query_loop->talk->ID ) ) {
			$talk_id = $wct->query_loop->talk->ID;
		}

		foreach ( $this->metas as $meta_object ) {
			$this->display_meta( $talk_id, $meta_object, $context );
		}
	}

	/**
	 * Displays metas for single display
	 *
	 * @package WordCamp Talks
	 * @subpackage talks/classes
	 *
	 * @since 1.0.0
	 *
	 * @return string          HTML Output
	 */
	public function single_output() {
		if ( ! wct_is_single_talk() ) {
			return;
		}

		return $this->front_output( 'single' );
	}
}
