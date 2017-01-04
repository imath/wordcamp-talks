<?php

/**
 * Talks Categories Widget
 *
 * "Extends".. It's more limit WP_Widget_Categories widget feature
 * disallow the dropdown because the javascript part is not "filterable"
 * disallow the hierarchy.. I still wonder why i've chosen to ? But we'll
 * see if we can use it in a future release.
 *
 * @package WordCamp Talks
 * @subpackage talks/widgets
 *
 * @since 1.0.0
 */
 class WordCamp_Talk_Widget_Categories extends WP_Widget_Categories {

 	/**
	 * Constructor
	 *
	 * @package WordCamp Talks
	 * @subpackage talks/widgets
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$widget_ops = array( 'description' => __( 'A list of Talk categories', 'wordcamp-talks' ) );
		WP_Widget::__construct( false, $name = __( 'WordCamp Talks categories', 'wordcamp-talks' ), $widget_ops );
	}

	/**
	 * Register the widget
	 *
	 * @package WordCamp Talks
	 * @subpackage talks/widgets
	 *
	 * @since 1.0.0
	 */
	public static function register_widget() {
		register_widget( 'WordCamp_Talk_Widget_Categories' );
	}

	/**
	 * Forces the talk category taxonomy to be used
	 *
	 * @package WordCamp Talks
	 * @subpackage talks/widgets
	 *
	 * @since 1.0.0
	 *
	 * @param  array  $category_args the arguments to get the list of categories
	 * @return array                 same arguments making sure talk taxonomy is set
	 */
	public function use_talks_category( $category_args = array() ) {
		// It's that simple !!
		$category_args['taxonomy'] = wct_get_category();

		// Now return these args
		return $category_args;
	}

	/**
	 * Displays the content of the widget
	 *
	 * Temporarly adds and remove filters and use parent category widget display
	 *
	 * @param  array  $args
	 * @param  array  $instance
	 */
	public function widget( $args = array(), $instance = array() ) {
		// Add filter so that the taxonomy used is cat-talks
		add_filter( 'widget_categories_args', array( $this, 'use_talks_category' ) );

		// Use WP_Widget_Categories::widget()
		parent::widget( $args, $instance );

		// Remove filter to reset the taxonomy for other widgets
		add_filter( 'widget_categories_args', array( $this, 'use_talks_category' ) );
	}

	/**
	 * Display the form in Widgets Administration
	 *
	 * @package WordCamp Talks
	 * @subpackage talks/widgets
	 *
	 * @since 1.0.0
	 */
	public function form( $instance = array() ) {
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => '') );
		$title = esc_attr( $instance['title'] );
		$count = isset($instance['count']) ? (bool) $instance['count'] :false;
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'wordcamp-talks' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		</p>

		<p>
			<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>"<?php checked( $count ); ?> />
			<label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php _e( 'Show talk counts', 'wordcamp-talks' ); ?></label><br />
		</p>
		<?php
	}
}
