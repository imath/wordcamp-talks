<?php
/**
 * Class written by Franz Josef Kaiser, taken from
 * https://gist.github.com/franz-josef-kaiser/2930190
 */
class WP_Custom_Post_Status {
	/**
	 * Name of the post status
	 * Must be lower case
	 *
	 * @access protected
	 * @var string
	 */
	public $post_status;


	/**
	 * Post type (slug) where the post status should appear
	 *
	 * @access protected
	 * @var string/array
	 */
	public $post_type = array( 'post', 'page' );


	/**
	 * Custom Args for the post status
	 *
	 * @access protected
	 * @var string
	 */
	public $args;

	/**
	 * Track jQuery firing
	 *
	 * @access protected
	 * @var boolean
	 */
	private static $jquery_ran = false;


	/**
	 * Construct
	 * @return void
	 */
	public function __construct() {
		#echo '<pre>'; print_r( $this ); echo '</pre>';
		// We need to have at least a post status name
		if ( ! isset( $this->post_status ) ) {
			return;
		}

		add_action( 'init', array( $this, 'add_post_status' ), 0 );

		foreach ( array( 'post', 'post-new' ) as $hook ) {
			if ( ! has_action( "admin_footer-{$hook}.php", array( $this, 'extend_submitdiv_post_status' ) ) ) {
				add_action( "admin_footer-{$hook}.php", array( $this, 'extend_submitdiv_post_status' ) );
			}
		}
		add_action( 'admin_footer-edit.php', array( $this, 'add_to_quickedit' ) );
		add_filter( 'display_post_states', array( $this, 'display_status_label' ) );
	}


	/**
	 * Add a new post status of "Unavailable"
	 *
	 * @return void
	 */
	public function add_post_status() {
		global $wp_post_statuses;

		$defaults = array(
			 'label_count'               => false
			// defaults to FALSE
			,'hierarchical'              => false
			// defaults to FALSE
			,'public'                    => true
			// If NULL, then inherits "public"
			,'publicly_queryable'        => null

			// most important switch
			,'internal'                  => false
			// If NULL, inherits from "internal"
			,'exclude_from_search'       => null
			// If NULL, inherits from "internal"
			,'show_in_admin_all_list'    => null
			// If NULL, inherits from "internal"
			,'show_in_admin_status_list' => null

			// If NULL, will be set to FALSE
			,'protected'                 => null
			// If NULL, will be set to FALSE
			,'private'                   => null
			// not set by the core function - defaults to NULL
			,'show_in_admin_all'         => null
			// defaults to "post"
			,'capability_type'           => 'post'
			,'single_view_cap'           => null
			// @internal use only - don't touch
			,'_builtin'                  => false
			,'_edit_link'                => 'post.php?post=%d'
		);

		// if FALSE, will take the 1st fn arg
		$defaults['label'] = __(
		 	 ucwords( str_replace(
		 	 	 array( '_', '-' )
				,array( ' ', ' ' )
		 	 	,$this->post_status
			 ) )
		 	,'cps_textdomain'
		 );

		// Care about counters:
		// If FALSE, will be set to array( $args->label, $args->label ), which is not desired
		$defaults['label_count'] = _n_noop(
			"{$defaults['label']} <span class='count'>(%s)</span>",
			"{$defaults['label']} <span class='count'>(%s)</span>",
			'cps_textdomain'
		);

		// Register the status: Merge Args with defaults
		register_post_status(
			$this->post_status,
			wp_parse_args(
				$this->args,
				$defaults
			 )
		);
	}

	function add_to_quickedit() {
		// Abort if we're on the wrong post type, but only if we got a restriction
		if ( isset( $this->post_type ) ) {
			global $post_type;
			if ( is_array( $this->post_type ) ) {
				if ( ! in_array( $post_type, $this->post_type ) ) {
					return;
				}
			} elseif ( $this->post_type !== $post_type ) {
				return;
			}
		}

		if ( self::$jquery_ran ) {
			return;
		}
		self::$jquery_ran = true;
		echo "<script>
		jQuery(document).ready( function() {\n";

		// Our post status and post type objects
		global $wp_post_statuses;

		foreach ( $wp_post_statuses as $status ) {
			if ( ! $status->_builtin ) {
				?>
				jQuery( 'select[name=\"_status\"]' ).append( '<option value=\"<?php echo esc_attr( $status->name );?>\"><?php echo esc_html( $status->label ); ?></option>' );
				<?php
			}
		}
		echo '});
		</script>';
	}


	/**
	 * Adds post status to the "submitdiv" Meta Box and post type WP List Table screens
	 *
	 * @return void
	 */
	public function extend_submitdiv_post_status() {
		// Abort if we're on the wrong post type, but only if we got a restriction
		if ( isset( $this->post_type ) ) {
			global $post_type;
			if ( ! is_array( $this->post_type ) ) {
				if ( in_array( $post_type, $this->post_type ) ) {
					return;
				}
			} elseif ( $this->post_type !== $post_type ) {
				return;
			}
		}

		if ( self::$jquery_ran ) {
			return;
		}
		self::$jquery_ran = true;

		// Our post status and post type objects
		global $wp_post_statuses, $post;

		// Get all non-builtin post status and add them as <option>
		$options = $display = '';
		foreach ( $wp_post_statuses as $status ) {
			if ( ! $status->_builtin ) {
				// Match against the current posts status
				$selected = selected( $post->post_status, $status->name, false );

				// If we one of our custom post status is selected, remember it
				$selected and $display = $status->label;

				// Build the options
				$options .= "<option{$selected} value='{$status->name}'>{$status->label}</option>";
			}
		}
		?>
		<script type="text/javascript">
	        jQuery( document ).ready( function($)
	        {
	            var appended = false;
	            <?php
	            // Add the selected post status label to the "Status: [Name] (Edit)"
	            if ( ! empty( $display ) ) :
	            ?>
	                $( '#post-status-display' ).html( '<?php echo $display; ?>' )
	            <?php
	            endif;

	            // Add the options to the <select> element
	            ?>
	            $( '.edit-post-status' ).on( 'click', function()
	            {
	                if ( !appended )
	                {
	                    var select = $( '#post-status-select' ).find( 'select' );
	                    $( select ).append( "<?php echo $options; ?>" );
	                    appended = true;
	                }
	            } );
	        } );
		</script>
		<?php
	}

	function display_status_label( $statuses ) {
		global $post; // we need it to check current post status
		if ( get_query_var( 'post_status' ) !== $this->post_status ) { // not for pages with all posts of this status
			if ( $post->post_status == $this->post_status ) { // если статус поста - Архив
				return array( $this->post_status ); // returning our status label
			}
		}
		return $statuses; // returning the array with default statuses
	}
}
