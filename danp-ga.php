<?php

/**
  * Plugin Name:        DanP Google Analytics Pageview Sync
  * Plugin URI:         https://dan-p.net/wordpress-plugins/danp-google-analytics-pageview-sync
  * Description:        Sync Google Analytics views statistics to your WordPress database, sort by views in WP_Query, output pageviews on your pages/posts &amp; see views in WordPress Admin.
  * Version:            1.0.0
  * Author:             Dan Poulton
  * Author URI:         https://dan-p.net
  * License:            GPL v2 or later
  * License URI:        https://www.gnu.org/licenses/gpl-2.0.html
  * Requires at least:  5.0
  * Requires PHP:       8.0.0
  * Domain:             danpga
  */


// Prevent file being called directly
if ( ! defined( 'WPINC' ) ) {
	die;
}

// If in the administrative interface
if(is_admin()) {
  // Include the plugin class
  require plugin_dir_path( __FILE__ ) . 'danp-ga-class.php';
  // Call the plugin class
  $danp_dot_net_google_analytics = new DanPdotNetGoogleAnalytics();
}

// Shortcode
add_shortcode( 'danp-ga-pageviews', 'danp_dot_net_ga_views_shortcode' );
function danp_dot_net_ga_views_shortcode( $atts ) {
	// If the ID is set, use that, if not, use get_the_ID()
  $atts = shortcode_atts( array(
    'id' => get_the_ID()
  ), $atts, 'danp-ga-pageviews' );
	// Get the pageviews
	$views = get_post_meta( $atts['id'], 'danp-dot-net-ga-page-views', true );
	// Cast variable to integer
	$views = intval($views);
	// If views are greater than zero, return it
	if($views > 0) {
		return $views;
	}
	// Fallback return: "0"
  return 0;
}

?>
