<?php

// No, Thanks. Direct file access forbidden.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
* Adjusts the publish box in the admin UI for talk proposals
*/
class Talk_Status_View_Publish_Box {

	private static $ran = false;
	function __construct() {
		//
	}

	/**
	 * After the object is created, this tells it to start doing work
	 *
	 * @return void
	 */
	public function run() {
		add_action( 'post_submitbox_misc_actions', [ $this, 'set_publishing_actions' ] );
		add_action( 'post_submitbox_misc_actions', [ $this, 'selection_control' ] );
		add_action( 'update_post', [ $this, 'save_post' ] );
		add_action( 'save_post', [ $this, 'save_post' ], 1, 1 );
	}

	function set_publishing_actions() {
		global $post;
		if ( 'talks' === $post->post_type ) {
			echo '<style type="text/css">
			#minor-publishing-actions,
			.misc-pub-section.misc-pub-visibility,
			.misc-pub-section.misc-pub-post-status,
			.misc-pub-section.curtime.misc-pub-curtime {
				display: none;
			}
			#post-body #talk_status:before {
			    content: "\f173";
			    font: 400 20px/1 dashicons;
			    speak: none;
			    display: inline-block;
			    margin-left: -1px;
			    padding-right: 3px;
			    vertical-align: middle;
			    -webkit-font-smoothing: antialiased;
			    -moz-osx-font-smoothing: grayscale;
			}
			</style>';
		}
	}

	function selection_control() {
		global $post;
		if ( 'talks' !== get_post_type( $post ) ) {
			return;
		}
		if ( true === self::$ran ) {
			return;
		}
		self::$ran = true;
		wp_nonce_field( plugin_basename( __FILE__ ), 'wct_publish_box_nonce' );
		$terms = get_the_terms( $post->ID, 'wordcamp-talks-status' );
		$status = '';
		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			$status = $terms[0]->slug;
		}
		$statuses = [
			'' => 'Newly submitted',
			'rejected' => 'Rejected',
			'shortlisted' => 'Shortlisted',
			'selected' => 'Selected',
			'backup' => 'Backup',
		];
		?>
		<div class="misc-pub-section misc-pub-section-last" id="talk_status" style="border-top: 1px solid #eee;">
			<label>
				Status: 
				<select name="wct_talk_status">
				<?php
				foreach ( $statuses as $key => $value ) {
					?>
					<option <?php selected( $status, $key ); ?> value="<?php echo esc_attr( $key ) ?>"><?php echo esc_html( $value ); ?></option>
					<?php
				}
				?>
				</select>
			</label>
		</div>
		<?php
		/*echo '<input type="radio" name="article_or_box" id="article_or_box-article" value="article" '.checked($val,'article',false).' /> <label for="article_or_box-article" class="select-it">Article</label><br />';
		echo '<input type="radio" name="article_or_box" id="article_or_box-box" value="box" '.checked($val,'box',false).'/> <label for="article_or_box-box" class="select-it">Box</label>';*/
	}

	function save_post( $post_id ) {
		//wct_publish_box_nonce
		if ( ! isset( $_POST['post_type'] ) ) {
			return $post_id;
		}

		if ( ! wp_verify_nonce( $_POST['wct_publish_box_nonce'], plugin_basename( __FILE__ ) ) ) {
			return $post_id;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		if ( 'talks' == $_POST['post_type'] && ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		if ( ! isset( $_POST['wct_talk_status'] ) ) {
			return $post_id;
		}

		$status = sanitize_title( wp_unslash( $_POST['wct_talk_status'] ) );
		if ( !empty( $status ) ) {
			wp_set_object_terms( $post_id, $status, 'wordcamp-talks-status', false );
		}
	}
}
