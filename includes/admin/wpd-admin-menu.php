<?php
/**
 *
 * Register Alpha Insights Menu
 *
 * @package Alpha Insights
 * @version 1.0.0
 * @author WPDavies
 * @link https://wpdavies.dev/
 *
 */
defined( 'ABSPATH' ) || exit;

/**
 *
 * Create pages
 *
 */
add_action( 'admin_menu', 'wpd_ai_admin_menu_page_setup' );
function wpd_ai_admin_menu_page_setup() {

	$capability = 'publish_pages';

	/**
	 *
	 *	Dashboard / Register Main Menu
	 *
	 */
	add_menu_page(
		'Dashboard',												// Page Title
		'Alpha Insights', 											// Menu Title
		$capability, 											// Capability /*Demo Version Edit*/
		'wpd-alpha-insights', 										// Menu Slug
		'wpd_ai_report_dashboard',										// Callback (page content)
		WPD_AI_FREE_URL_PATH . 'assets/img/Alpha-Insights-Icon-20x20.png', 	// Icon URL
		5															// Position
	);

	/**
	 *
	 *	Dashboard - Override submenu label
	 *
	 */
	add_submenu_page( 	
		'wpd-alpha-insights',			// Parent Slug
		'Dashboard', 					// Page Title
		'Dashboard', 					// Menu Title
		$capability, 				// Capability
		'wpd-alpha-insights',			// Menu Slug
		'',								// Callback (page content)
		null							// Position
	);

	/**
	 *
	 *	Profit Reports
	 *
	 */
	add_submenu_page( 	
		'wpd-alpha-insights',			// Parent Slug
		'Reports', 						// Page Title
		'Reports', 						// Menu Title
		$capability, 				// Capability
		'wpd-profit-reports',			// Menu Slug
		'wpd_ai_profit_reports_page',		// Callback (page content)
		null							// Position
	);

	/**
	 *
	 *	Analytics - Excluded from Demo
	 *
	 */
/*	add_submenu_page( 	
		'wpd-alpha-insights',			// Parent Slug
		'Analytics', 					// Page Title
		'Analytics', 					// Menu Title
		$capability, 				// Capability
		'wpd-analytics',				// Menu Slug
		'wpd_ai_analytics_page',			// Callback (page content)
		null							// Position
	);*/

	/**
	 *
	 *	Expense Reports
	 *
	 */
	add_submenu_page( 
		'', 							// Parent Slug (no parent = not showing on submenu)
		'Expense Reports', 				// Page Title
		'Expense Reports', 				// Menu Title
		$capability, 				// Capability
		'wpd-expense-reports', 			// Menu Slug
		'wpd_ai_expense_reports_page', 	// Callback (page content)
		null 							// Position
	);

	/**
	 *
	 *	Inventory Management
	 *
	 */
	add_submenu_page( 
		'wpd-alpha-insights', 				// Parent Slug
		'Inventory',  						// Page Title
		'Inventory', 						// Menu Title
		$capability, 						// Capability
		'wpd-inventory-management', 		// Menu Slug
		'wpd_ai_inventory_management_page',	// Callback (page content)
		null 								// Position
	);

	/**
	 *
	 *	P&L
	 *
	 */
	add_submenu_page( 
		'wpd-alpha-insights', 			// Parent Slug
		'P&L Statement',  				// Page Title
		'P&L Statement', 				// Menu Title
		$capability, 					// Capability
		'wpd-pl-statement', 			// Menu Slug
		'wpd_ai_pl_statement_page', 		// Callback (page content)
		null 							// Position
	);

	/**
	 *
	 *	Settings
	 *
	 */
	add_submenu_page( 
		'wpd-alpha-insights',  			// Parent slug
		'Settings', 					// Page title
		'Settings', 					// Menu Title
		$capability, 					// Capability
		'wpd-ai-settings', 				// Menu Slug
		'wpd_ai_settings_page', 			// Callback (page content)
		null 							// Position
	);

}

/**
 *
 *	Nav tabs - Global
 *
 */
add_action( 'admin_notices', 'wpd_ai_nav_tabs', 0 );
function wpd_ai_nav_tabs() {

	if ( ! is_wpd_page() ) return false; 

	$current_url 	= wpd_ai_get_current_page_url();

	$main_menu_items = array( 

		'Dashboard' 		=> '/wp-admin/admin.php?page=wpd-alpha-insights',
		'Reports' 			=> '/wp-admin/admin.php?page=wpd-profit-reports',
		// 'Analytics' 		=> '/wp-admin/admin.php?page=wpd-analytics', // Excluded from Demo version
		'Manage Expenses' 	=> '/wp-admin/admin.php?page=wpd-expense-reports',
		'Inventory' 		=> '/wp-admin/admin.php?page=wpd-inventory-management',
		'P&L Statement' 	=> '/wp-admin/admin.php?page=wpd-pl-statement',
		'Settings' 			=> '/wp-admin/admin.php?page=wpd-ai-settings',

	);

	echo '<div class="wpd-nav-wrapper"><div class="wrap">';

	echo '<h3 class="nav-tab-wrapper wpd-nav-tab-wrapper" id="wpd-ai-menu">';

	echo '<span class="wpd-plugin-logo"><img height="50" src="' . WPD_AI_FREE_URL_PATH . '/assets/img/Alpha-Insights-Icon-Large.png" class="alpha-insights-menu-logo"><span class="product-subtitle">Alpha Insights</span></span>';

	foreach( $main_menu_items as $key => $value ) {

		$name 		= $key;
		$url 		= $value;
		$active 	= wpd_ai_check_active_nav_menu_item( $current_url, $url, $name );

		( $active ) ? $current = 'nav-tab-active' : $current = null;

		echo '<a class="wpd-nav-tab nav-tab ' . esc_attr( $current ) . '" href="' . esc_url_raw( $url ) . '">' . esc_attr( $name ) . '</a>';

	}

	echo '</h3>';

	// Echo submenu 
	wpd_ai_submenu_html();

	echo '</div></div>'; // end wp-davies-nav-wrapper

	echo '<div class="wrap"><h2></h2></div>'; // echo empty h2 so that admin notices are pushed here

}

/**
 *
 *	Check active nav menu item
 *
 */
function wpd_ai_check_active_nav_menu_item( $current_url, $menu_item_slug, $menu_item_name ) {

	$bool 				= false;
	$page 				= ( ! empty($_GET['page']) ) ? sanitize_text_field( $_GET['page'] ) : null;
	$post_type 			= ( ! empty($_GET['post_type']) ) ? sanitize_text_field( $_GET['post_type'] ) : null;
	$taxonomy 			= ( ! empty($_GET['taxonomy']) ) ? sanitize_text_field( $_GET['taxonomy'] ) : null;
	$current_screen 	= get_current_screen();
	$screen_post_type	= ( is_object($current_screen) ) ? $current_screen->post_type : null;

	/**
	 *
	 *	Set currently active menu item
	 *
	 */
	if ( $current_url == $menu_item_slug ) $bool = true;
	if ( $page == 'wpd-analytics' && $menu_item_name == 'Analytics' ) $bool = true;
	if ( $page == 'wpd-alpha-insights' && $menu_item_name == 'Dashboard' ) $bool = true;
	if ( $page == 'wpd-expense-reports' && $menu_item_name == 'Reports' ) $bool = true;
	if ( $page == 'wpd-pl-statement' && $menu_item_name == 'P&L Statement' ) $bool = true;
	if ( $page == 'wpd-ai-settings' &&  $menu_item_name == 'Settings') $bool = true;
	if ( $page == 'wpd-profit-reports' &&  $menu_item_name == 'Reports') $bool = true;
	if ( $page == 'wpd-inventory-management' &&  $menu_item_name == 'Inventory') $bool = true;

	// Return results
	return $bool;

}

/**
 *
 *`Output submenu html
 *
 */
function wpd_ai_submenu_html() {

	$menu_data = wpd_ai_active_submenu();
	( isset($menu_data['submenus']) ) ? $links = $menu_data['submenus'] : $links = null; ?>

	<ul class="wpd-sub-menu">
		<?php foreach( $links as $link ) :
			$active = wpd_ai_check_active_submenu_item( $link['title'], $link['url'] );
			( $active ) ? $current = 'nav-tab-active' : $current = null;
			$slug = str_replace( ' ', '-', strtolower( $link['title'] ) );
		?>
			<li class="wpd-sub-menu-li">
				<a href="<?php echo esc_url_raw( $link['url'] ) ?>" class="wpd-sub-menu-item <?php echo esc_attr( $current ) ?> <?php echo esc_attr( $slug ) ?>">
					<?php echo esc_attr($link['title']) ?>
				</a>
			</li>
		<?php endforeach; ?>
		<!-- Extra Menu Items -->
		<?php (isset($_GET['page']) && $_GET['page'] == 'wpd-ai-about') ? $current = 'nav-tab-active' : $current = null; ?>
		<li class="wpd-sub-menu-li pull-right additional-items <?php echo $slug ?>">
			<a href="<?php echo admin_url( 'admin.php?page=wpd-ai-about') ?>" class="wpd-sub-menu-item <?php echo esc_attr( $current ) ?>">About / Help</a>
		</li>
	</ul>

<?php

}

/**
 *
 *	Check which submenu item is active
 *
 */
function wpd_ai_check_active_submenu_item( $menu_item_name, $menu_item_url ) {

	$bool = false;
	$current_url = wpd_ai_get_current_page_url();

	(isset($_GET['subpage'])) ? $current_subpage = sanitize_text_field( $_GET['subpage'] ) : $current_subpage = '';
	(isset($_GET['page'])) ? $current_page = sanitize_text_field( $_GET['page'] ) : $current_page = '';
	(isset($_GET['post_type'])) ? $post_type = sanitize_text_field( $_GET['post_type'] ) : $post_type = '';
	(isset($_GET['taxonomy'])) ? $taxonomy = sanitize_text_field( $_GET['taxonomy'] ) : $taxonomy = '';

	// Main check
	if ( ! empty($current_subpage) ) {
		if ( strpos($menu_item_url, $current_subpage) != false ) $bool = true;
	}
	// Setting overrides
	if ( ! $current_subpage && $current_page == 'wpd-ai-settings' && $menu_item_name == 'General Settings' ) $bool = true;
	//Expense overrides
	if ( $menu_item_name == 'Manage Expenses' ) {

		if ( strpos($current_url, 'edit.php?post_type=expense' ) != false ) $bool = true;

	} elseif ( $menu_item_name == 'Bulk Add Expenses' ) {

		if ( $current_page == 'wpd-bulk-add-expense' ) $bool = true;

	} elseif ( $menu_item_name == 'Add Expense Type' ) {

		if ( strpos( $current_url, 'edit-tags.php?taxonomy=expense_category&post_type=expense') != false ) $bool = true;

	} elseif ( $menu_item_name == 'Add Single Expense' ) {

		if ( strpos( $current_url, 'post-new.php?post_type=expense' ) != false ) $bool = true;

	} elseif ( $menu_item_name == 'Expenses' ) {

		if ( strpos( $current_url, '?page=wpd-expense-reports' ) != false ) $bool = true;

	} elseif ( $menu_item_name == 'Dashboard' ) {

		if ( strpos( $current_url, '?page=wpd-alpha-insights' ) != false ) $bool = true;
		if ( strpos( $current_url, '?page=wpd-analytics' ) != false ) $bool = true;

	} elseif ( $menu_item_name == 'Inventory Report' ) {

		if ( strpos( $current_url, '?page=wpd-inventory-management' ) != false ) $bool = true;

	} elseif ( $menu_item_name == 'Profit & Loss Statement' ) {

		if ( strpos( $current_url, '?page=wpd-pl-statement' ) != false ) $bool = true;

	}

	//Profit by report
	if ( $current_page == 'wpd-profit-reports' && ! $current_subpage && $menu_item_name == 'Profit By Orders' ) $bool = true;

	return $bool;

}

/**
 *
 *	Work out which admin section we are in
 *
 */
function wpd_ai_active_submenu() {

	// Active Section
	$results['section'] = '';

	/**
	 *
	 *	Check our query params
	 *	@todo set this as shorthand if else so I can set default to null
	 *
	 */
	$page_parameter 	= ( ! empty($_GET['page'] ) ) ? sanitize_text_field( $_GET['page'] ) : null;
	$post_type 			= ( ! empty($_GET['post_type'] ) ) ? sanitize_text_field( $_GET['post_type'] ) : null;
	$taxonomy 			= ( ! empty($_GET['expense_category'] ) ) ? sanitize_text_field( $_GET['expense_category'] ) : null;
	$current_screen 	= get_current_screen();
	$screen_post_type	= ( is_object($current_screen) ) ? $current_screen->post_type : null;

	/**
	 *
	 *	Define the current section
	 *
	 */
	if ( $page_parameter == 'wpd-alpha-insights' ) {

		$section = 'dashboard';

		$results['submenus'][] = array(
			'title' 	=> 'Dashboard',
			'url' 		=> admin_url( 'admin.php?page=wpd-alpha-insights')
		);

	} elseif ( $page_parameter == 'wpd-profit-reports' || $page_parameter == 'wpd-expense-reports' ) {

		$section = 'profit-reports';

		// Setup our links
		$results['submenus'][] = array(
			'title' 	=> 'Profit By Orders',
			'url' 		=> admin_url( 'admin.php?page=wpd-profit-reports&subpage=orders')
		);
		$results['submenus'][] = array(
			'title' 	=> 'Profit By Products & Categories',
			'url' 		=> admin_url( 'admin.php?page=wpd-profit-reports&subpage=products')
		);
		$results['submenus'][] = array(
			'title' 	=> 'Profit By Customers',
			'url' 		=> admin_url( 'admin.php?page=wpd-profit-reports&subpage=customers')
		);
/*		$results['submenus'][] = array(
			'title' 	=> 'Profit By Acquisition',
			'url' 		=> admin_url( 'admin.php?page=wpd-profit-reports&subpage=acquisition')
		);*/
		$results['submenus'][] = array(
			'title' 	=> 'Expenses',
			'url' 		=> admin_url( 'admin.php?page=wpd-expense-reports')
		);
	} elseif ( $page_parameter == 'wpd-analytics' ) {

		$section = 'analytics';

		$results['submenus'][] = array(
			'title' 	=> 'Dashboard',
			'url' 		=> admin_url( 'admin.php?page=wpd-analytics')
		);

	} elseif ( $post_type == 'expense' || $page_parameter == 'wpd-bulk-add-expense' || $taxonomy == 'expense_category' || $screen_post_type == 'expense'  ) {

		$section = 'expense-management';

		$results['submenus'][] = array(
			'title' 	=> 'Manage Expenses',
			'url' 		=> admin_url( 'edit.php?post_type=expense&orderby=date_paid&order=desc')
		);
		$results['submenus'][] = array(
			'title' 	=> 'Bulk Add Expenses',
			'url' 		=> admin_url( 'admin.php?page=wpd-bulk-add-expense')
		);
		$results['submenus'][] = array(
			'title' 	=> 'Add Expense Type',
			'url' 		=> admin_url( 'edit-tags.php?taxonomy=expense_category&post_type=expense')
		);
		$results['submenus'][] = array(
			'title' 	=> 'Add Single Expense',
			'url' 		=> admin_url( 'post-new.php?post_type=expense')
		);

	} elseif ( $page_parameter == 'wpd-inventory-management' ) {

		$section = 'inventory-management';

		$results['submenus'][] = array(
			'title' 	=> 'Inventory Report',
			'url' 		=> admin_url( 'admin.php?page=wpd-inventory-management')
		);

	} elseif ( $page_parameter == 'wpd-pl-statement' ) {

		$section = 'pl-statement';

		$results['submenus'][] = array(
			'title' 	=> 'Profit & Loss Statement',
			'url' 		=> admin_url( 'admin.php?page=wpd-pl-statement')
		);

	} elseif ( $page_parameter == 'wpd-ai-settings' ) {

		$section = 'settings';

		$results['submenus'][] = array(
			'title' 	=> 'General Settings',
			'url' 		=> admin_url( 'admin.php?page=wpd-ai-settings')
		);

		$results['submenus'][] = array(
			'title' 	=> 'Currency Settings',
			'url' 		=> admin_url( 'admin.php?page=wpd-ai-settings&subpage=currency')
		);

/*		$results['submenus'][] = array(
			'title' 	=> 'Appearance Settings',
			'url' 		=> admin_url( 'admin.php?page=wpd-ai-settings&subpage=appearance')
		);*/

		$results['submenus'][] = array(
			'title' 	=> 'Import / Bulk Update COGS',
			'url' 		=> admin_url( 'admin.php?page=wpd-ai-settings&subpage=import')
		);

		$results['submenus'][] = array(
			'title' 	=> 'Email Settings',
			'url' 		=> admin_url( 'admin.php?page=wpd-ai-settings&subpage=email')
		);

		$results['submenus'][] = array(
			'title' 	=> 'License',
			'url' 		=> admin_url( 'admin.php?page=wpd-ai-settings&subpage=license')
		);

		if ( WPD_AI_FREE_DEBUG ) {

			$results['submenus'][] = array(
				'title' 	=> 'Debug',
				'url' 		=> admin_url( 'admin.php?page=wpd-ai-settings&subpage=debug')
			);

		}

	}

	// Set array
	$results['section'] = $section;

	/**
	 *
	 *	Return results
	 *
	 */
	return $results;
	
}

/** 
*
*	Returns currnet page URL
*
*/
function wpd_ai_get_current_page_url() {

	$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

	return $actual_link;

}