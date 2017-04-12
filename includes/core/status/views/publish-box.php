<?php

// No, Thanks. Direct file access forbidden.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
* Adjusts the publish box in the admin UI for talk proposals
*/
class Talk_Status_View_Publish_Box {
	function __construct() {
		//
	}

	/**
	 * After the object is created, this tells it to start doing work
	 *
	 * @return void
	 */
	public function run() {
		add_action( 'admin_head-post.php', [ $this, 'set_publishing_actions' ] );
		add_action( 'post_submitbox_misc_actions', [ $this, 'selection_control' ] );
	}

	function set_publishing_actions() {
		global $post;
		if ( 'talks' === $post->post_type ) {
			echo '<style type="text/css">
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
		if ( get_post_type( $post ) == 'talks' ) {
			echo '';
			wp_nonce_field( plugin_basename(__FILE__), 'article_or_box_nonce' );
			$val = get_post_meta( $post->ID, '_article_or_box', true ) ? get_post_meta( $post->ID, '_article_or_box', true ) : 'article';
			$status = '';
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
					<select>
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
	}
}
