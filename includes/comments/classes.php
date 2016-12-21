<?php
/**
 * WordCamp Talks Comments classes.
 *
 * @package WordCamp Talks
 * @subpackage comments/classes
 *
 * @since 1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** Disjoin comments **********************************************************/

if ( ! class_exists( 'WordCamp_Talks_Comments' ) ) :
	require( 'wordcamp-talks-comments.php' );
endif;

/** Comment Loop **************************************************************/

if ( ! class_exists( 'WordCamp_Talks_Loop_Comments' ) ) :
	require( 'wordcamp-talks-loop-comments.php' );
endif;
