<?php
/**
 * WordCamp Talks Widgets.
 *
 * @package WordCamp Talks
 * @subpackage talks/widgets
 *
 * @since 1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WordCamp_Talk_Widget_Categories' ) ) :
	require( 'wordcamp-talk-widget-categories.php' );
endif;

if ( ! class_exists( 'WordCamp_Talk_Widget_Popular' ) ) :
	require( 'wordcamp-talk-widget-popular.php' );
endif;
