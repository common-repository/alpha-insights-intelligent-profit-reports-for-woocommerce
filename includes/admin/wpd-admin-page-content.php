<?php
/**
 *
 * Load admin page content
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
 *	Dashboard
 *
 */
function wpd_ai_report_dashboard() {

	?>

	<div class="wrap">

		<?php do_action( 'wpd_before_heading' ); ?>

		<h3></h3>

		<?php do_action( 'wpd_before_content' ); ?>

		<div class="wpd-wrapper">

		    <form id="wpd-profit-report" method="get" class="wpd-form">

		    	<?php $page = sanitize_text_field( $_REQUEST['page'] ); ?>

		        <input type="hidden" name="page" value="<?php echo esc_attr( $page ) ?>" />

		        <?php 

				    $wpd_dashboard = new WPD_AI_Profit_Reports_Dashboard();
					$wpd_dashboard->output_filters(); 						// Output filter
					$wpd_dashboard->output_insights(); 						// Output Chart
					$wpd_dashboard->output_navigation(); 					// Output Chart

				?>

		    </form>

		</div>

	</div>

	<?php

}

/**
 *
 * Admin Activity
 *
 */
function wpd_ai_profit_reports_page() { 

	$subpage = sanitize_text_field( $_GET['subpage'] );

	if ( ! empty( $subpage ) && $subpage == 'products' ) {

		wpd_ai_profit_reports_products();

	} elseif ( ! empty( $subpage ) && $subpage == 'customers' ) {

		wpd_ai_profit_reports_customers();

	} else {

		wpd_ai_profit_reports_orders();

	}

}

/**
 *
 *	Analytics
 *
 */
function wpd_ai_analytics_page() {

	$subpage = sanitize_text_field( $_GET['subpage'] );

	if ( ! empty($subpage) && $subpage == 'products' ) {

		// wpd_ai_profit_reports_products();

	} elseif ( ! empty($subpage) && $subpage == 'customers' ) {

		// wpd_ai_profit_reports_customers();

	} else {

		wpd_ai_analytics_dashboard();

	}

}

/**
 *
 *	Analytics Dashboard
 *
 */
function wpd_ai_analytics_dashboard() {

	?>

	<div class="wrap">

		<?php do_action( 'wpd_before_heading' ); ?>

		<h3><?php _e( 'Profit Reports - Order Insights', 'wpdavies' ); ?></h3>

		<?php do_action( 'wpd_before_content' ); ?>

		<div class="wpd-wrapper">

		    <form id="wpd-profit-report" method="get" class="wpd-form">

		    	<?php $page = sanitize_text_field( $_REQUEST['page'] ); ?>

		        <input type="hidden" name="page" value="<?php echo esc_attr( $page ) ?>" />

		        <?php 

					$wpd_ai_analytics_dashboard = new WPD_Analytics_Dashboard();
					$wpd_ai_analytics_dashboard->output_filters();
					

					if ( isset($_REQUEST['uid']) && ! empty($_REQUEST['uid']) ) {

						// User Session Template
						$wpd_ai_analytics_dashboard->output_user_sessions_table();

					} elseif ( isset($_REQUEST['sid']) && ! empty($_REQUEST['sid']) ) {

						// Session Template
						$wpd_ai_analytics_dashboard->output_session_table();

					} else {

						// Default
						$wpd_ai_analytics_dashboard->output_sessions_table();

					}

					// $wpd_ai_analytics_dashboard->output_insights(); 		// Output Chart

				?>

		    </form>

		</div>

	</div>

    <?php

}

/**
 *
 *	Profit reports - orders
 *
 */
function wpd_ai_profit_reports_orders() {

	?>

	<div class="wrap">

		<?php do_action( 'wpd_before_heading' ); ?>

		<h3><?php _e( 'Profit Reports - Order Insights', 'wpdavies' ); ?></h3>

		<?php do_action( 'wpd_before_content' ); ?>

		<div class="wpd-wrapper">

		    <form id="wpd-profit-report" method="get" class="wpd-form">

		    	<?php $page = sanitize_text_field( $_REQUEST['page'] ); ?>

		        <input type="hidden" name="page" value="<?php echo esc_attr( $page ) ?>" />

		        <?php 

					$wpd_order_insights = new WPD_AI_Profit_Reports_Orders();
					$wpd_order_insights->output_filters(); 	// Output filter
					$wpd_order_insights->output_insights(); // Output Chart
					$wpd_order_insights->views(); 			// Prepare Table
					$wpd_order_insights->prepare_items(); 	// Prepare Table
					$wpd_order_insights->display();  		// Display Table

				?>

		    </form>

		</div>

	</div>

    <?php

}

/**
 *
 *	Profit reports - orders
 *
 */
function wpd_ai_profit_reports_products() {

	?>

	<div class="wrap">

		<?php do_action( 'wpd_before_heading' ); ?>

		<h3><?php _e( 'Profit Reports - Product Insights', 'wpdavies' ); ?></h3>

		<?php do_action( 'wpd_before_content' ); ?>

		<?php wpd_ai_pro_version_only() ?>

		<div class="wpd-wrapper wpd-premium-content">

	        <?php wpd_ai_premium_content_overlay(); ?>

		    <form id="wpd-profit-report" method="get" class="wpd-form">

		        <!-- For plugins, we also need to ensure that the form posts back to our current page -->
		    	<?php $page = sanitize_text_field( $_REQUEST['page'] ); ?>
		        <input type="hidden" name="page" value="<?php echo esc_attr( $page ) ?>" />
		        <?php $subpage = sanitize_text_field( $_GET['subpage'] ); ?>
		        <input type="hidden" name="subpage" value="<?php echo esc_attr( $subpage ) ?>" />

		        <?php 

					$wpd_product_insights = new WPD_AI_Profit_Reports_Products();
					$wpd_product_insights->output_filters(); 	// Output filter

				?>

		    </form>

		</div>

		 <!-- Demo image -->
	    <div class="wpd-wrapper">
	    	<img style="max-width: 100%;" src="<?php echo WPD_AI_FREE_URL_PATH . 'assets/img/Profit-By-Product.jpg'; ?>">
	    </div>

	</div>

    <?php

}

/**
 *
 *	Profit Reports - Customers
 *	@class WPD_AI_Profit_Reports_Customers
 *
 */
function wpd_ai_profit_reports_customers() {

	?>

	<div class="wrap">

		<?php do_action( 'wpd_before_heading' ); ?>

		<h3><?php _e( 'Profit Reports - Customer Insights', 'wpdavies' ); ?></h3>

		<?php do_action( 'wpd_before_content' ); ?>

		<?php wpd_ai_pro_version_only() ?>

		<div class="wpd-wrapper wpd-premium-content">

	        <?php wpd_ai_premium_content_overlay(); ?>

		    <form id="wpd-profit-report" method="get" class="wpd-form">

		        <!-- For plugins, we also need to ensure that the form posts back to our current page -->
		    	<?php $page = sanitize_text_field( $_REQUEST['page'] ); ?>
		        <input type="hidden" name="page" value="<?php echo esc_attr( $page ) ?>" />
		        <?php $subpage = sanitize_text_field( $_GET['subpage'] ); ?>
		        <input type="hidden" name="subpage" value="<?php echo esc_attr( $subpage ) ?>" />

		        <?php 

					$wpd_product_insights = new WPD_AI_Profit_Reports_Customers();
					$wpd_product_insights->output_filters(); 	// Output filter

				?>

		    </form>

		</div>

		 <!-- Demo image -->
	    <div class="wpd-wrapper">
	    	<img style="max-width: 100%;" src="<?php echo WPD_AI_FREE_URL_PATH . 'assets/img/Profit-By-Customer.jpg'; ?>">
	    </div>

	</div>

    <?php

}

/**
 *
 * Admin Activity
 *
 */
function wpd_ai_expense_reports_page() { 

	if ( ! isset($_GET['subpage']) ) $_GET['subpage'] = null;
	?>

	<div class="wrap">

		<?php do_action( 'wpd_before_heading' ); ?>
		
		<h3><?php _e( 'Expense Reports', 'wpdavies' ); ?></h3>

		<?php do_action( 'wpd_before_content' ); ?>

		<?php wpd_ai_pro_version_only() ?>

		<div class="wpd-wrapper wpd-premium-content">

			<?php wpd_ai_premium_content_overlay(); ?>

		    <form id="wpd-expense-report" method="get" class="wpd-form">
		        <!-- Hidden input refreshes our page correctly -->
		    	<?php $page = sanitize_text_field( $_REQUEST['page'] ); ?>
		        <input type="hidden" name="page" value="<?php echo esc_attr( $page ) ?>" />
		        <?php $subpage = sanitize_text_field( $_GET['subpage'] ); ?>
		        <input type="hidden" name="subpage" value="<?php echo esc_attr( $subpage ) ?>" />

		        <?php

					$wpd_expense_report = new WPD_AI_Expense_Reports();
					$wpd_expense_report->output_filters();

				?>
		    </form>

		</div>

		<!-- Demo image -->
	    <div class="wpd-wrapper">
	    	<img style="max-width: 100%;" src="<?php echo WPD_AI_FREE_URL_PATH . 'assets/img/Expense-Report'; ?>">
	    </div>

	</div>

    <?php

}

/**
 *
 * Add expenses in bulk
 *
 */
function wpd_ai_bulk_add_expenses_page() { 

	?>

	<div class="wrap">

		<?php do_action( 'wpd_before_heading' ); ?>
 
		<h3><?php _e( 'Add Expenses In Bulk', 'wpdavies' ); ?></h3>

		<?php do_action( 'wpd_before_content' ); ?>

		<?php

			$wpd_bulk_add_expense = new WPD_Bulk_Add_Expense();
			$wpd_bulk_add_expense->display();

		?>

	</div>

	<?php

}

/**
 *
 *	Inventory Management page
 *
 */
function wpd_ai_inventory_management_page() {

	if ( ! isset($_GET['subpage'])) $_GET['subpage'] = null;
	if ( ! isset($_GET['page'])) $_GET['page'] = null;
	?>

	<div class="wrap">

		<?php do_action( 'wpd_before_heading' ); ?>
 
		<h3><?php _e( 'Inventory Management', 'wpdavies' ); ?></h3>

		<?php do_action( 'wpd_before_content' ); ?>

		<div class="wpd-wrapper">
			<?php $page = sanitize_text_field( $_REQUEST['page'] ); ?>
		 	<?php $subpage = sanitize_text_field( $_GET['subpage'] ); ?>
		    <form id="wpd-inventory-report" method="get" class="wpd-form" action="<?php echo admin_url( 'admin.php?page=' . esc_attr( $page ) ) ?>">
		        <!-- For plugins, we also need to ensure that the form posts back to our current page -->
		        <input type="hidden" name="page" value="<?php echo esc_attr( $page ) ?>" />
		        <input type="hidden" name="subpage" value="<?php echo esc_attr( $subpage ) ?>" />
		        <?php

			        $wpd_inventory_management = new WPD_AI_Inventory_Management();
			        $wpd_inventory_management->output();
					
				?>
		    </form>

		</div>

	</div>

	<?php

}

/**
 *
 *	P&L statement page
 *
 */
function wpd_ai_pl_statement_page() {

	?>

	<div class="wrap">

		<?php do_action( 'wpd_before_heading' ); ?>

		<h3><?php _e( 'Profit & Loss Statement', 'wpdavies' ); ?></h3>

		<?php do_action( 'wpd_before_content' ); ?>

		<?php wpd_ai_pro_version_only() ?>

		<div class="wpd-wrapper wpd-premium-content">

			<?php wpd_ai_premium_content_overlay(); ?>
			<?php $page = sanitize_text_field( $_REQUEST['page'] ); ?>
		    <form id="wpd-inventory-report" method="get" class="wpd-form" action="<?php echo admin_url( 'admin.php?page=' . esc_attr( $page ) ) ?>">
		        <!-- For plugins, we also need to ensure that the form posts back to our current page -->
		        <input type="hidden" name="page" value="<?php echo esc_attr( $page ) ?>" />
		        <?php
		        	
					$wpd_pl_statement = new WPD_AI_Profit_Loss_Statement();
					$wpd_pl_statement->output_filters();
					// $wpd_pl_statement->display_report( false );
					
				?>
		    </form>

		</div>

		<!-- Demo image -->
	    <div class="wpd-wrapper">
	    	<img style="max-width: 100%;" src="<?php echo WPD_AI_FREE_URL_PATH . 'assets/img/PL-Statement.jpg'; ?>">
	    </div>

	</div>

	<?php

}