<?php
/**
 * WordCamp Talks Classes.
 *
 * @package WordCamp Talks
 * @subpackage core/classes
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** Post Statuses  ************************************************************/

require( 'post_statuses/wp_custom_post_status.php' );
require( 'post_statuses/shortlist.php' );
require( 'post_statuses/selected.php' );
require( 'post_statuses/rejected.php' );

if ( ! class_exists( 'WordCamp_Talks_Post_Status' ) ) :
	require( 'poststatus.php' );
endif;

/** Rewrites ******************************************************************/

if ( ! class_exists( 'WordCamp_Talks_Rewrites' ) ) :
	require( 'wordcamp-talks-rewrites.php' );
endif;

/** Template Loader class *****************************************************/

if ( ! class_exists( 'WordCamp_Talks_Template_Loader' ) ) :
	require( 'wordcamp-talks-template-loader.php' );
endif;

/** Loop **********************************************************************/

if ( ! class_exists( 'WordCamp_Talks_Loop' ) ) :
	require( 'wordcamp-talks-loop.php' );
endif;
