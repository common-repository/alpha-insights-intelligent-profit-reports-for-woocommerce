<?php
/**
 *
 * Alpha Insights Settings
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
 * Admin Settings
 *	@todo turn into class
 *
 **/
function wpd_ai_settings_page() { 

	?>
  
	<div class="wrap">

		<?php do_action( 'wpd_before_heading' ); ?>

		<h3>Settings</h3>

		<?php do_action( 'wpd_before_content' ); ?>

		<div class="wpd-white-block">

			<form method="post" action="" id="wpd-ai-settings">

				<?php

					$subpage = sanitize_text_field( $_GET['subpage'] );

					if ( $subpage == 'import' )  {

						// Import / Bulk Update
						wpd_ai_bulk_update_cogs_template();

					} elseif ( $subpage == 'currency' ) {

						wpd_ai_currency_conversion_settings_template();

					} elseif ( $subpage == 'email' ) {

						if ( isset($_GET['email_preview']) ) {

							wpd_ai_email_previews();

						} else {

							wpd_ai_email_settings_template();

						}

					} elseif ( $subpage == 'license' ) {

						wpd_ai_license_template();

					} elseif ( $subpage == 'debug' ) {

						wpd_ai_debug_template();

					} else {

						wpd_ai_general_settings_template();

					}

				?>

			</form>

		</div>

	</div>
	<?php

}

/**
 *
 *	General settings Fields
 *
 */
function wpd_ai_general_settings_template() {

	require_once( WPD_AI_FREE_PATH . 'includes/admin/wpd-settings-general_settings.php');

}

/*
 *
 *	Table for currency conversion input
 *
 */
function wpd_ai_currency_conversion_settings_template() {

	require_once( WPD_AI_FREE_PATH . 'includes/admin/wpd-settings-currency_conversion_settings.php');
	
}

/**
 *
 *	Bulk edit / import cost of goods
 *
 */
function wpd_ai_bulk_update_cogs_template() {

	require_once( WPD_AI_FREE_PATH . 'includes/admin/wpd-settings-bulk_update_cogs.php');

}

/**
 *
 *	Bulk edit / import cost of goods
 *
 */
function wpd_ai_email_settings_template() {

	require_once( WPD_AI_FREE_PATH . 'includes/admin/wpd-settings-emails.php');

}

/**
 *
 *	Preview Emails
 *
 */
function wpd_ai_email_previews() {

	require_once( WPD_AI_FREE_PATH . 'includes/admin/wpd-settings-email-previews.php');

}

/**
 *
 *	Preview Emails
 *
 */
function wpd_ai_license_template() {

	require_once( WPD_AI_FREE_PATH . 'includes/admin/wpd-settings-license.php');

}

/**
 *
 *	Preview Emails
 *
 */
function wpd_ai_debug_template() {

	require_once( WPD_AI_FREE_PATH . 'includes/admin/wpd-settings-debug.php');

}

/**
 *
 *	Settings Example
 *	@link http://openexchangerates.org/api/currencies.json
 *
 */
add_action( 'admin_init', 'wpd_ai_register_settings' );
function wpd_ai_register_settings() {

	/**
	 *
	 *	Currency Table Defaults
	 *
	 */
	$currency_table_default_data = wpd_ai_default_currency_conversion();
	add_option( 'wpd_ai_currency_table', $currency_table_default_data ); 

	/**
	 *
	 *	Order Status Defaults
	 *
	 */
	$order_status_default_data = array( 'wc-completed', 'wc-processing' );
	add_option( 'wpd_ai_order_status', $order_status_default_data );

	/**
	 *
	 *	Cost Defaults
	 *
	 */
    $cost_default_data = array(
        'default_product_cost_percent' 	=> 30,
        'default_payment_cost_percent' 	=> 2.6,
        'default_payment_cost_fee' 		=> 0.30,
        'default_shipping_cost_percent' => 5,
        'default_shipping_cost_fee' 	=> 0,
        'tax_settings' 					=> 'include',
    );
	add_option( 'wpd_ai_cost_defaults', $cost_default_data );

	/**
	 *
	 *	Admin Style Settings
	 *
	 */
	add_option( 'wpd_ai_admin_style_override', 0 );
	add_option( 'wpd_ai_prevent_wp_notices', 0 );


	/**
	 *
	 *	Default email settings
	 *
	 */
	$admin_email = get_option( 'admin_email' );
	$email_default_settings = array (
		'appearance' => array(
			'header' => 1,
			'footer' => 1,
		),
        'profit-report' => array (
            'recipients' => $admin_email,
            'frequency' => array (
                'weekly' => 1,
            ),
            'details' => array (
                'order_revenue' => 1,
                'order_cost' => 1,
                'order_profit' => 1,
                'order_count' => 1,
                'average_order_value' => 1,
                'average_profit_per_order' => 1,
                'total_products_sold' => 1,
                'total_product_discounts' => 1,
                'total_refunds' => 1,
                'additional_expenses' => 1,
                'net_profit' => 1,
            ),
            'attachment' => array(
                'pl-statement' => 1,
            ),
        ),
        'expense-report' => array (
            'recipients' => $admin_email,
            'frequency' => array(
                'monthly' => 1,
            ),
            'details' => array(
                'total_expenses_paid' => 1,
                'total_no_expenses' => 1,
                'average_expenses_per_day' => 1,
                'parent_expenses' => 1,
                'child_expenses' => 1,
            ),
            'attachment' => array(
                'expense-report' => 1,
            ),
        ),
        'inventory-report' => array(
	        'recipients' => $admin_email,
	        'frequency' => array(
	            'monthly' => 1,
	        ),
	        'details' => array(
	            'total_stock_value_rrp' => 1,
	            'total_stock_value_cost' => 1,
	            'unrealised_profits' => 1,
	            'total_stock_on_hand' => 1,
	            'total_records_found' => 1,
	            'number_products_stock_management' => 1,
	            'number_out_of_stock_products' => 1,
	            'number_low_stock_products' => 1,
	            'number_backorder_products' => 1,
	        ),
	        'attachment' => array(
	        	'inventory-report' => 1,
	        ),
        ),
	);
	add_option( 'wpd_ai_email_settings', $email_default_settings );

	/**
	 *
	 *	TO do list
	 *
	 */
	$default_to_do_list = array(
		'default_cost_prices' 	=> false,
		'import_cost_prices' 	=> false,
		'email_preferences' 	=> false,
		'currency_conversions' 	=> false,
	);
	add_option( 'wpd_ai_to_do_list', $default_to_do_list );
	add_option( 'wpd_ai_dismiss_to_do_list', false );

	/**
	 *
	 *	License
	 *
	 */
	add_option( 'wpd_ai_api_key', null );
	add_option( 'wpd_ai_license_status',  null );

	/**
	 *
	 *	Submit POST data
	 *
	 */
	wpd_ai_save_settings();

}

/** 
 *
 *	Save and Process all settings on admin page
 *
 */
function wpd_ai_save_settings() {

	$saved = array();

	// Currency Table
	if ( isset($_POST['wpd_ai_currency_table']) && ! empty($_POST['wpd_ai_currency_table']) ) {
		$currency_values = array_map( 'wpd_ai_numbers_only', $_POST['wpd_ai_currency_table'] );
		$currency_values['USD'] = 1;
		$saved['Currency Conversion Table'] = update_option( 'wpd_ai_currency_table', $currency_values );
	}

	// API Key
	if ( isset( $_POST['wpd_profit_tracking_oer_api_key'] ) ) {
		$oer_api_key = sanitize_text_field( $_POST['wpd_profit_tracking_oer_api_key'] );
		$saved['Open Exchange Rate API Key'] = update_option( 'wpd_profit_tracking_oer_api_key', $oer_api_key );
		$to_do_list = get_option('wpd_ai_to_do_list');
		$to_do_list['currency_conversions'] = true;
		update_option( 'wpd_ai_to_do_list', $to_do_list );

	}

	// Cost Defaults
	if ( isset( $_POST['wpd_ai_cost_defaults'] ) ) {
		$saved['Product Cost Default'] = update_option( 'wpd_ai_cost_defaults',  wc_clean( $_POST['wpd_ai_cost_defaults'] ) );
		$to_do_list = get_option('wpd_ai_to_do_list');
		$to_do_list['default_cost_prices'] = true;
		update_option( 'wpd_ai_to_do_list', $to_do_list );
	}

	// Order Status Settings - wpd_ai_order_status
	if ( isset( $_POST['wpd_ai_order_status'] ) ) {
		$saved['Default Order Status'] = update_option( 'wpd_ai_order_status',  wc_clean( $_POST['wpd_ai_order_status'] ) );
	}

	// Override WP CSS
	if ( isset( $_POST['wpd_ai_admin_style_override'] ) ) {
		$admin_override = wpd_ai_numbers_only( $_POST['wpd_ai_admin_style_override'] );
		if ( is_numeric( $admin_override ) ) {
			$saved['Admin Override'] = update_option( 'wpd_ai_admin_style_override',  $admin_override );
		}
	}

	// Prevent WP Notices
	if ( isset( $_POST['wpd_ai_prevent_wp_notices'] ) ) {
		$prevent_notices = wpd_ai_numbers_only( $_POST['wpd_ai_prevent_wp_notices'] );
		if ( is_numeric($prevent_notices) ) {
			$saved['Prevent WP Notices'] = update_option( 'wpd_ai_prevent_wp_notices',  $prevent_notices );
		}
	}

	// Email settings - 	add_option( 'wpd_ai_email_settings', $email_default_settings );
	if ( isset( $_POST['wpd-email'] ) ) {

		$emails = array();

		// Check & store email addresses
		$emails['profit-report']['recipients'] = sanitize_text_field( $_POST['wpd-email']['profit-report']['recipients'] );
		$emails['expense-report']['recipients'] = sanitize_text_field( $_POST['wpd-email']['expense-report']['recipients'] );
		$emails['inventory-report']['recipients'] = sanitize_text_field( $_POST['wpd-email']['inventory-report']['recipients'] );

		// Only allows numbers through details
		$emails['profit-report']['details'] = array_map( 'wpd_ai_numbers_only', $_POST['wpd-email']['profit-report']['details'] );
		$emails['expense-report']['details'] = array_map( 'wpd_ai_numbers_only', $_POST['wpd-email']['expense-report']['details'] );
		$emails['inventory-report']['details'] = array_map( 'wpd_ai_numbers_only', $_POST['wpd-email']['inventory-report']['details'] );


		// Store details
		$saved['Email'] = update_option( 'wpd_ai_email_settings',  $emails );
		$to_do_list = get_option('wpd_ai_to_do_list');
		$to_do_list['email_preferences'] = true;
		update_option( 'wpd_ai_to_do_list', $to_do_list );

	}

	// License key
	if ( isset($_POST['wpd_ai_api_key']) ) {
		
		$api_key = sanitize_text_field( $_POST['wpd_ai_api_key'] );
		$saved['API key'] = update_option( 'wpd_ai_api_key',  $api_key );

		if ( $saved['API key'] === true ) {
			$license_response = wpd_license_activate();
			if ( isset($license_response['message']) ) {
				wpd_ai_notice( $license_response['message'] );
			}

		}

		$license_status = wpd_license_status( true );
		if ( $license_status ) {
			update_option( 'wpd_ai_license_status',  $license_status );
		}	

	}

	// Currency Exchange Update
	if ( isset($_POST['wpd_update_rate_bool']) && ! empty($_POST['wpd_update_rate_bool']) ) {

		$oxr 					= wpd_ai_collect_data_oxr();
		$options 				= get_option( 'wpd_ai_currency_table' );
		$merged_data 			= array_merge( $options, $oxr );
		$update_option 			= update_option( 'wpd_ai_currency_table', $merged_data ); 

		if ( $update_option ) {

			$response = 'Exchange rates updated succesfully.';
			$update_option_date = update_option( 'wpd_ai_currency_table_update', current_time( 'timestamp' ) ); 

		} else {

			$response = 'Could not update the exchange rate, you may have hit the max amount of requests per month.';

		}

		wpd_ai_notice( $response );

	}

	// Product COG update in bulk
	if ( isset($_POST['cogs_save_data']) && $_POST['cogs_save_data'] == 'true' ) {

		$number_of_saves = 0;
		$total_number_of_products = 0;

		if ( is_array( $_POST['_wpd_ai_product_cost'] ) && ! empty( $_POST['_wpd_ai_product_cost'] ) ) {

			foreach( $_POST['_wpd_ai_product_cost'] as $product_id => $value ) {

				$sanitized_number = wpd_ai_numbers_only($value);

				if ( is_numeric($value) ) {

					$product_update = update_post_meta( $product_id, '_wpd_ai_product_cost', $sanitized_number );

				}

				if ( $product_update ) {

					$number_of_saves++;

				}

				$total_number_of_products++;

			}

			wpd_ai_notice( $number_of_saves . ' products out of ' . $total_number_of_products . ' products have been updated.' );

			$to_do_list = get_option('wpd_ai_to_do_list');
			$to_do_list['import_cost_prices'] = true;
			update_option( 'wpd_ai_to_do_list', $to_do_list );

		}

	}

	// Output message for loading cost of goods
	if ( isset($_POST['cogs_load_data']) && $_POST['cogs_load_data'] == 'true' ) {

		if ( isset($_POST['cogs_update_meta_key']) && ! empty($_POST['cogs_update_meta_key']) ) {

			$current_meta_key = sanitize_text_field( $_POST['cogs_update_meta_key'] );

		} else {

			$current_meta_key = '_wpd_ai_product_cost';

		}

		wpd_ai_notice( $current_meta_key . ' has been loaded into the table but nothing has been saved, click save if you are happy with the results.' );

	}

	// Output message for csv upload
	if ( isset( $_FILES['csv_file'] ) ) {

		$csv_upload = wpd_ai_load_cogs_via_csv_upload();

		if ( $csv_upload ) {

			$count = $csv_upload['count'];
			wpd_ai_notice( 'Your CSV file has been uploaded succcesfully, we\'ve found ' . $count . ' rows.' );

		} elseif ( isset($csv_upload['error']) ) {

			wpd_ai_notice( $csv_upload['error'] );

		}
		
	}

	/**
	 *
	 *	Output notice for those settings that have been saved
	 *
	 */
	foreach( $saved as $setting => $save_status ) {

		if ( $save_status === true ) {

			wpd_ai_notice( $setting . ' Settings have been updated.' );

		}

	}

	/**
	 *
	 *	Also, lets call any other notices we want at this point
	 *
	 */
	wpd_ai_output_additional_notices();

}

/** 
 *
 *	Output any arbitrary notices
 *
 */
function wpd_ai_output_additional_notices() {

	if ( isset($_GET['wpd-notice']) && $_GET['wpd-notice'] == 'invalid-license' ) {

		$license_status = wpd_license_status_bool();

		if ( $license_status ) {

			$license_page = wpd_ai_admin_page_url( 'settings-license' );
			wp_redirect( $license_page );
			exit;

		} else {

			wpd_ai_notice( 'Please enter a valid license below to use Alpha Insights.' );

		}
	}

}

/**
 *
 *	Collect dtaa for COGS
 *
 */
function wpd_ai_download_product_cogs_by_csv() {

	$product_ids 	= wpd_ai_collect_product_ids();
	$row_number 	= 0;
	$csv_results 	= array();
	$target_fields 	= array(
		'product_name' 	=> 'Product Name',
		'sku' 			=> 'SKU',
		'product_id' 	=> 'Product ID',
		'rrp_price' 	=> 'RRP Price',
		'cogs' 			=> 'Cost Of Goods',
	);

	/**
	 *
	 *	Store Header Rows
	 *
	 */	
	foreach( $target_fields as $key => $value ) {

		$csv_results[$row_number][] = $value;

	}

	$row_number++;

	/**
	 *
	 *	Loop through products
	 *
	 */
	foreach( $product_ids as $product_id ) {

		$csv_results[$row_number][] 	= html_entity_decode( get_the_title( $product_id ) );
		$csv_results[$row_number][] 	= get_post_meta( $product_id, '_sku', true ); // _sku
		$csv_results[$row_number][] 	= $product_id;
		$csv_results[$row_number][] 	= get_post_meta( $product_id, '_regular_price', true );
		$csv_results[$row_number][] 	= get_post_meta( $product_id, '_wpd_ai_product_cost', true );
		$row_number++;

	}

	return $csv_results;

}

/**
 *
 *	Collect new currencie's using OER API data
 *
 *	@link https://openexchangerates.org/api/latest.json?app_id=YOUR_APP_ID
 *
 */
function wpd_ai_collect_data_oxr() {

	// 
	$app_id = get_option( 'wpd_profit_tracking_oer_api_key' );

	if ( empty($app_id) ) {
		wpd_ai_notice( 'You need to get an Open Exchange Rates API key for us to be able to download the latest currencies.' );
	}

	// Currency data fetch point
	$oxr_url = "https://openexchangerates.org/api/latest.json?app_id=" . $app_id;

	// Fetch data using native WP functions @link https://developer.wordpress.org/plugins/http-api/
	$json = wp_remote_get( $oxr_url );
	$body = wp_remote_retrieve_body( $json );

	// Decode JSON response:
	$oxr_latest = json_decode( $body );
	$rates_array = (array) $oxr_latest->rates;

	return $rates_array;

}

/**
 *
 *	Store COGS CSV Upload
 *
 */
function wpd_ai_load_cogs_via_csv_upload() {

	$result = false;
	$sanitized_array = array();

	if ( ! empty($_FILES) ) {

		$csv_mimes = array(
			'text/x-comma-separated-values', 
			'text/comma-separated-values', 
			'application/octet-stream', 
			'application/vnd.ms-excel', 
			'application/x-csv', 
			'text/x-csv', 
			'text/csv', 
			'application/csv', 
			'application/excel', 
			'application/vnd.msexcel', 
			'text/plain'
		);

		// Check mime type, make sure it's a CSV file
		if ( isset($_FILES['file']['tmp_name']) ) {

		    $finfo 	= finfo_open(FILEINFO_MIME_TYPE);
		    $mime 	= finfo_file($finfo, $_FILES['file']['tmp_name']);
		    finfo_close( $finfo );

		    if ( in_array($mime, $csv_mimes) !== true ) {

		    	return $result['error'] = 'This file is not a CSV, please try again.';

		    }

		}

		// Check for upload error
		if ( $_FILES['csv_file']['error'] !== 0 && ! empty($_FILES['csv_file']['error']) ) {

			return $result['error'] = sanitize_text_field( $_FILES['csv_file']['error'] );

		}

		// Format data
		$csv_file_name 		= $_FILES['csv_file']['tmp_name'];
		$csv_to_array 		= array_splice( array_map( 'str_getcsv', file( $csv_file_name ) ), 1 );
		$result['count'] 	= count( $csv_to_array );

		// Sanitize data
		foreach( $csv_to_array as $array ) {

			$sanitized_array[] = array_map( 'wpd_ai_numbers_only', $array );

		}

		$result['data'] = $sanitized_array;

	} else {

		$result = false;

	}

	return $result;

}

/**
 *
 *	Prevent notices if setting is enabled
 *
 */
add_action('admin_head', 'wpd_ai_hide_wp_notices');
function wpd_ai_hide_wp_notices() {

	$prevent_notices = get_option( 'wpd_ai_prevent_wp_notices' );

	if ( $prevent_notices ) {

		?>
		<style type="text/css">
			/* Hide admin notices on my pages */
			.notice, .updated, .update-nag {
			    display: none !important;
			}
			.woocommerce-embed-page .woocommerce-store-alerts {
			    display: none;
			}
			.notice.wpd-notice, .plugin-update .notice {
			    display: block !important;
			}
	  	</style>
		<?php

	}

}