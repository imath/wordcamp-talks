<?php
/**
 * WordCamp Talks Classes.
 *
 * @package WordCamp Talks
 * @subpackage core/classes
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** Status ********************************************************************/

require_once( 'status/views/publish-box.php');
require_once( 'status/views/posts-list.php');
require_once( 'status/view.php');
require_once( 'status/taxonomy.php');
require_once( 'status/post-status.php');
require_once( 'status/controller.php');

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