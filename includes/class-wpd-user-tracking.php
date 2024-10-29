<?php
/**
 *
 * Track website traffic for reporting
 *
 * @package Alpha Insights
 * @version 1.0.0
 * @author WPDavies
 * @link https://wpdavies.dev/
 *
 */
defined( 'ABSPATH' ) || exit;

// Main Class
class WPD_AI_User_Tracking {

	public function __construct() {

		add_action( 'add_meta_boxes', array( $this, 'register_order_admin_meta_box_analytics' ) );

	}

	/**
	 *
	 *	Add meta box for analytics tracking
	 *
	 */
	public function register_order_admin_meta_box_analytics() {

	    add_meta_box ( 
	    	'wpd-ai-analytics',											// ID
	    	'Alpha Insights User Session Data',  						// Title
	    	array( $this, 'order_admin_meta_box_analytics_output' ), 	// Callback
	    	'shop_order', 												// Screen
	    	'advanced', 												// Context
	    	'high' 														// Priority
	    );

	}

	/**
	 *
	 *	Order Page Session Analytics
	 *
	 */
	public function order_admin_meta_box_analytics_output() {

		echo '<p>This data is only available in the <a href="https://wpdavies.dev/plugins/alpha-insights/?utm_source=alpha-insights-free&utm_content=product-report" target="_blank">Pro Version</a>. of Alpha Insights</p>';		

	}

}

// Initialize
$WPD_User_Tracking = new WPD_AI_User_tracking();