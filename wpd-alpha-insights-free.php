<?php
/**
 * Plugin Name:         Alpha Insights - Intelligent Profit Reports for Woocommerce
 * Plugin URI:          https://wpdavies.dev/
 * Description:         [FREE VERSION] Track your cost of goods, stock valuation, sales, costs and expenses and produce easy to understand insightful reports rich with valuable data.
 * Author:              WP Davies
 * Author URI:          https://wpdavies.dev/
 *
 * Version:             	1.0.0
 * Requires at least:   	4.4.0
 * Tested up to:        	5.7.2
 * Requires PHP: 			5.6
 * WC requires at least: 	3.0.0
 * WC tested up to: 		5.4.1
 * 
 * Text Domain:         wp-davies
 *
 * Alpha Insights
 * Copyright (C) 2021, WP Davies, support@wpdavies.dev
 *
 * @category            Plugin
 * @copyright           Copyright WP Davies © 2021
 * @author              WP Davies
 * @package             Alpha Insights
 * @textdomain 			wp-davies
 */
defined( 'ABSPATH' ) || exit;

/**
 *
 *	Main Class, Blast off! :)
 *
 */
class WPD_Alpha_Insights_Free {

	/**
	 *
	 *	Stores bool for whether or not we've passed the version check
	 *
	 */
	public $version_check = true;

	/**
	 *
	 *	Constructor
	 *
	 */
	function __construct() {

		// Build plugin
		$this->define();
		$this->version_check();
		add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'alpha_insights_plugin_action_links' ) );

		// If we are all good, load the plugin
		if ( $this->version_check ) {

			// Load the plugin
			$this->includes();

		} else {

			// Print errors
			add_action( 'admin_notices', array( $this, 'plugin_dependency_notices' ) );

		}

	}

	/**
	 *
	 *	Extra links on plugin page
	 *
	 */
	public function alpha_insights_plugin_action_links( $links ) {

		$new_links[] = '<a href="' . esc_url( admin_url( '/admin.php?page=wpd-ai-settings' ) ) . '">' . __( 'Settings', 'textdomain' ) . '</a>';
		$new_links[] = '<a href="' . esc_url( 'https://wpdavies.dev/docs/alpha-insights/' ) . '" target="_blank">' . __( 'Docs', 'textdomain' ) . '</a>';		
		$new_links[] = '<a href="' . esc_url( 'https://wpdavies.dev/plugins/alpha-insights/' ) . '" target="_blank">' . __( 'Go Premium', 'textdomain' ) . '</a>';	
		$links = array_merge( $new_links, $links );

		return $links;

	}

	/**
	 *
	 *	
	 *
	 */
	public function version_check() {

		$version_check = true;
		$message = null;

		// Is PHP version correct
		if ( version_compare( PHP_VERSION, WPD_AI_FREE_MIN_PHP_VER, '<' ) ) {

			$message[] = 'Alpha Insights has not been fully activated as it requires at least PHP ' . WPD_AI_FREE_MIN_PHP_VER . ' to run correctly. Please upgrade your PHP version to use this plugin.';
			$version_check = false;

		}

		global $wp_version;
		// Is WP Version Correct
		if ( version_compare( $wp_version, WPD_AI_FREE_MIN_WP_VER, '<' ) ) {

			$message[] = 'Alpha Insights requires at least WordPress version ' . WPD_AI_FREE_MIN_WP_VER . ' to run. Please upgrade WordPress to use this plugin. You are currently using version ' . $wp_version;
			$version_check = false;

		}

		// Is WC active
		if ( ! defined('WC_VERSION') ) {

			$message[] = 'Alpha Insights is built for WooCommerce, we\'ve suspended Alpha Insights until you activate WooCommerce.';
			$version_check = false;

		}

		// Is WC version correct
		if ( defined('WC_VERSION') && version_compare( WC_VERSION, WPD_AI_FREE_MIN_WC_VER, '<' ) ) {

			$message[] = 'Alpha Insights requires at least WooCommerce version ' . WPD_AI_FREE_MIN_WC_VER . ' to run. Please upgrade WooCommerce to use this plugin. You are currently using version ' . WC_VERSION;
			$version_check = false;

		}

		// Do we have the free version available
		if ( defined('WPD_AI_PRO_VERSION') ) {

			$message[] = 'You already have the pro version of Alpha Insights installed, please deactivate the free version to gain access to all features. ';
			$version_check = false;

		}

		$this->version_check = $version_check;

		/**
		 *
		 *	Return Results
		 *
		 */
		return $results = array(

			'notices' 		=> $message,
			'version_check' => $version_check

		);

	}

	/**
	 *
	 *	Output error notice if we dont have dependencies
	 *
	 */
	public function plugin_dependency_notices() {

		$version_check = $this->version_check();
		$notice_messages = $version_check['notices'];

		if ( is_array($notice_messages) && ! empty($notice_messages) ) {

			foreach ( $notice_messages as $message ) {

				echo '<div class="wpd-notice notice notice-error is-dismissible"><p>' . $message . '</p></div>';

			}

		}

	}

	/**
	 *
	 *	Setup all our files
	 *
	 */
	public function includes() {

		if ( $this->version_check ) {

			require_once( WPD_AI_FREE_PATH . 'includes/wpd-functions.php');
			require_once( WPD_AI_FREE_PATH . 'includes/reports/class-wpd-dashboard-report.php');
			require_once( WPD_AI_FREE_PATH . 'includes/reports/class-wpd-profit-by-order-report.php');
			require_once( WPD_AI_FREE_PATH . 'includes/reports/class-wpd-profit-by-product-report.php');
			require_once( WPD_AI_FREE_PATH . 'includes/reports/class-wpd-profit-by-customer-report.php');
			require_once( WPD_AI_FREE_PATH . 'includes/reports/class-wpd-expense-report.php');
			require_once( WPD_AI_FREE_PATH . 'includes/reports/class-wpd-inventory-report.php');
			require_once( WPD_AI_FREE_PATH . 'includes/class-wpd-profit-loss-statement.php');
			require_once( WPD_AI_FREE_PATH . 'includes/class-wpd-cost-of-goods.php');
			require_once( WPD_AI_FREE_PATH . 'includes/class-wpd-user-tracking.php');
			require_once( WPD_AI_FREE_PATH . 'includes/wpd-scripts-styles.php' );
			require_once( WPD_AI_FREE_PATH . 'includes/wpd-ajax.php' );
			require_once( WPD_AI_FREE_PATH . 'includes/wpd-cron.php');
			require_once( WPD_AI_FREE_PATH . 'includes/admin/wpd-admin-page-content.php');
			require_once( WPD_AI_FREE_PATH . 'includes/admin/wpd-settings.php');
			require_once( WPD_AI_FREE_PATH . 'includes/admin/wpd-admin-menu.php');

		}

	}

	/**
	 *
	 *	Setup definitions
	 *
	 */
	public function define() {

		// Set path constants, always returns trailing / 
		if ( ! defined('WPD_AI_FREE_PATH') ) define( 'WPD_AI_FREE_PATH', plugin_dir_path( __FILE__ ) );
		if ( ! defined('WPD_AI_FREE_URL_PATH') ) define( 'WPD_AI_FREE_URL_PATH', plugin_dir_url( __FILE__ ) );
		if ( ! defined('WPD_AI_FREE_PHP_PRETTY_DATE') ) define( 'WPD_AI_FREE_PHP_PRETTY_DATE', 'F j, Y' );
		if ( ! defined('WPD_AI_FREE_PHP_SHORT_DATE') ) define( 'WPD_AI_FREE_PHP_SHORT_DATE', 'd-M' );
		if ( ! defined('WPD_AI_FREE_PHP_ISO_DATE') ) define( 'WPD_AI_FREE_PHP_ISO_DATE', 'Y-m-d' );
		if ( ! defined('WPD_AI_FREE_CSV_PATHE') ) define( 'WPD_AI_FREE_CSV_PATHE', WPD_AI_FREE_URL_PATH . 'exports/csv_files/' );
		if ( ! defined('WPD_AI_FREE_CSV_SYSTEM_PATH') ) define( 'WPD_AI_FREE_CSV_SYSTEM_PATH', WPD_AI_FREE_PATH . 'exports/csv_files/' );
		if ( ! defined('WPD_AI_FREE_DEBUG') ) define( 'WPD_AI_FREE_DEBUG', FALSE );
		if ( ! defined('WPD_AI_FREE_MIN_PHP_VER') ) define( 'WPD_AI_FREE_MIN_PHP_VER', '5.6.0' ); // 5.6.0
		if ( ! defined('WPD_AI_FREE_MIN_WP_VER') ) define( 'WPD_AI_FREE_MIN_WP_VER', '4.4.0' );
		if ( ! defined('WPD_AI_FREE_MIN_WC_VER') ) define( 'WPD_AI_FREE_MIN_WC_VER', '3.0.0' );
		if ( ! defined('WPD_AI_FREE_VER') ) define( 'WPD_AI_FREE_VER', '1.0.0' );
		if ( ! defined('WPD_AI_FREE_PRODUCT_ID') ) define( 'WPD_AI_FREE_PRODUCT_ID', 5585 ); //'1.0.0'
		if ( ! defined('WPD_AI_FREE_DB_VERSION') ) define( 'WPD_AI_FREE_DB_VERSION', '1.0.0' ); //'1.0.0'
		if ( ! defined('WPD_AI_FREE_VERSION') ) define( 'WPD_AI_FREE_VERSION', TRUE ); //'1.0.0'

	}

}

// Init
$WPD_Alpha_Insights_Free = new WPD_Alpha_Insights_Free();