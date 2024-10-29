<?php 
/**
 *
 * Customer Report
 *
 * @package Alpha Insights
 * @version 1.0.0
 * @author WPDavies
 * @link https://wpdavies.dev/
 *
 */
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WPD_AI_Profit_Reports_Customers extends WP_List_Table {

	/**
	 *
	 *	This is our base currency
	 *
	 */
	public $wc_currency;

	/**
	 *
	 *	Chart Height
	 *
	 */
	public $chart_height;

	/**
	 *
	 *	Store data so we don't need to requery
	 *
	 */
	public $data;

	/**
	 *
	 *	Expense Data
	 *
	 */
	public $taxonomy_data;

	/**
	 *
	 *	Expense Data
	 *
	 */
	public $data_totals;

	/**
	 *
	 *	Expense Data
	 *
	 */
	public $data_totals_by_date;

	/**
	 *
	 *	I will store data results here
	 *	@default 25
	 *
	 */
	public $per_page = 25;

	/**
	 *
	 *	Filter, in case I want to filter the results
	 *
	 */
	public $filter = array();

	/**
	 *
	 *	Requesting URL for overriding filters
	 *
	 */
	public $requesting_url;

	/**
	 *
	 *	Constructor
	 *
	 */
    public function __construct( $requesting_url = null ) {
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'order',     //singular name of the listed records
            'plural'    => 'orders',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );

        if ( $requesting_url ) {

        	$this->requesting_url = $requesting_url;

        }

        /**
         *
         *	Set base currency to Woocommerce currency
		 *
		 */
        $this->load_filters();
        $this->wc_currency 			= wpd_ai_get_base_currency();
        $this->chart_height 		= '400px';
        $this->raw_data();	
        $this->data_by_date();

    }

    /**
	 *
	 *	Get the data we want and return to table
	 *	@todo change to https://github.com/woocommerce/woocommerce/wiki/wc_get_orders-and-WC_Order_Query#date
	 *
	 */
	public function raw_data() {

		$start 						= $this->selected_date_range('start'); 	// date in the past
        $end 						= $this->selected_date_range('end'); 	// current date
        $number_of_days 			= $this->x_days_range();
        $totals_data 				= array();
		$customer_data 				= array();
		$customer_type_data 		= array();
		$location_data 				= array();
		$date_data 					= array();
		$date_data 					= array();
		$customer_totals 			= array();
		$device_category_totals 	= array();
		$device_browser_totals 		= array();
		$device_type_totals 		= array();
        $total_order_count 								= 0;
        $total_product_count 							= 0;
        $highest_product_count 							= 0;
        $highest_product_quantity 						= 0;
        $highest_revenue_amount 						= 0;
        $total_orders_with_refunds  					= 0;
 		$number_of_customers_purchased_more_than_once 	= 0;
 		$number_of_customers_who_refunded 				= 0;
		$status 										= wpd_ai_paid_order_status();
		$total_order_revenue 							= 0;
		$total_order_profit 							= 0;
		$total_product_revenue_value_rrp 				= 0;
		$total_product_revenue 							= 0;
		$total_product_discounts_applied 				= 0;
		$total_product_profit 							= 0;
		$total_product_cost 							= 0;
		$total_quantity_sold 							= 0;
		$total_products_sold 							= 0;
		$total_products_refunded 						= 0;
		$total_amount_refunded 							= 0;
		$total_refund_amount 							= 0;

		/**
		 *
		 *	Query orders
		 *
		 */
		$args = array(

		    'limit' 			=> -1,
		    'orderby' 			=> 'date',
		    'order' 			=> 'DESC',
		    'date_created' 		=> $start . "..." . $end, //'2018-02-01...2018-02-28',
		    'type' 				=> 'shop_order',
		    'status' 			=> $status,

		);

		// Fetch posts
		$orders = wc_get_orders( $args );

		if ( empty($orders) ) {

			return false;
			
		}

		/**
		 *
		 *	Loop through orders
		 *
		 */
		foreach ( $orders as $order ) {	

			if ( ! is_object($order) ) continue; 	// Safety Check

			/**
			 *
			 *	Check memory usage
			 *	If memory use is higher than 90%, dont try and find anymore
			 *
			 */
			if ( wpd_ai_is_memory_usage_greater_than(90) ) {

				$memory_limit = ini_get('memory_limit');
				wpd_ai_admin_notice( 'You\'ve exhausted your memory usage. Increase your PHP memory limit or reduce the date range. Your current PHP memory limit is ' . esc_attr( $memory_limit ) . '.' );

				break; // Break the entire process if were hitting the memory limits

			}

			/**
			 *
			 *	Setup customer variables
			 *
			 */
			$email_address 												= strtolower( $order->get_billing_email() );
			$order_id 													= $order->get_id();
			$shipping_country 											= $order->get_shipping_country();
			$shipping_state 											= $order->get_shipping_state();
			$customer_data[$email_address]['user_id'] 					= $order->get_user_id();
		    $customer_data[$email_address]['fname'] 					= $order->get_billing_first_name();
		    $customer_data[$email_address]['lname'] 					= $order->get_billing_last_name();
		    $customer_data[$email_address]['email'] 					= $email_address;

			/**
			 *
			 *	Do a few checks and formatting
			 *
			 */
		    if ( empty( $shipping_country ) ) {
		    	$shipping_country = $order->get_billing_country();
		    	if ( empty($shipping_country) ) {
		    		$shipping_country = 'Unknown';
		    	}
		    }
		    if ( empty( $shipping_state ) ) {
		    	$shipping_state = $order->get_billing_state();
		    	if ( empty( $shipping_state ) ) {
		    		$shipping_state = 'Unknown';
		    	}
		    }
			if ( $customer_data[$email_address]['user_id'] === 0 ) {
				$customer_type = 'guest';
			} else {
				$customer_type = 'registered';
			}
			$avatar 	= '<img src="' . get_avatar_url( $email_address, array('size' => 75) ) . '" class="wpd-user-thumbnail">';
			$customer 	= '<span class="customer-name">'.$customer_data[$email_address]['fname'].' '.$customer_data[$email_address]['lname'].'</span><div class="wpd-meta">'.ucfirst($customer_type).'</div>';

			/**
			 *
			 *	Store this formatted data
			 *
			 */
			$customer_data[$email_address]['country'] 					= $shipping_country;
			$customer_data[$email_address]['state'] 					= $shipping_state;
			$customer_data[$email_address]['customer_type'] 			= $customer_type;
		    $customer_data[$email_address]['customer_avatar'] 			= $avatar;
		    $customer_data[$email_address]['customer'] 					= $customer;

			/**
			 *
			 *	Setup order variables
			 *
			 */
			$order_data 							= wpd_ai_calculate_cost_profit_by_order( $order_id, false );
			$order_date 							= $order->get_date_created()->getOffsetTimestamp();
			$order_date 							= date( 'Y-m-d', $order_date );
			$refunds 								= $order->get_refunds();
			$order_revenue_before_refunds 			= $order_data['total_order_revenue_before_refunds'];
			$order_revenue 							= $order_data['total_order_revenue'];
			$order_refund_amount 					= $order_data['total_refund_amount'];
			$order_refund_quantity 					= $order_data['total_refund_quantity'];
			$order_total_cost 						= $order_data['total_order_cost'];
			$order_profit 							= $order_data['total_order_profit'];
			$order_product_cost 					= $order_data['total_product_cost'];
			$order_shipping_charged 				= $order_data['total_shipping_charged'];
			$order_shipping_cost 					= $order_data['total_shipping_cost'];
			$order_payment_gateway_cost 			= $order_data['payment_gateway_cost'];
			$order_tax_paid 						= $order_data['order_tax_paid'];
			$order_average_margin 					= $order_data['total_order_margin'];
			$order_discounts_applied 				= $order_data['total_discounts_applied'];
			$order_product_revenue 					= $order_data['total_product_revenue'];
			$order_product_revenue_at_retail_price 	= $order_data['total_product_revenue_at_rrp'];
			$order_product_quantity_sold	 		= $order_data['total_qty_sold'];
			$order_products_sold 					= $order_data['total_skus_sold'];
			$order_product_discounts_applied 		= $order_data['total_product_discounts'];
			$order_product_profit 					= $order_data['total_product_profit'];
			if ( ! empty($refunds) ) {
				$total_orders_with_refunds++;
				$total_refund_amount += $order_refund_amount;
				if ( ! isset($customer_data[$email_address]['refunds']) ) $customer_data[$email_address]['refunds'] = 0;
				if ( ! isset($customer_data[$email_address]['refund_amount']) ) $customer_data[$email_address]['refund_amount'] = 0;
				$customer_data[$email_address]['refunds']++;
				$customer_data[$email_address]['refund_amount'] += $order_refund_amount;
			}

			/**
			 *
			 *	Store Customer data
			 *
			 */
			if ( ! isset($customer_data[$email_address]['total_orders_placed']) ) $customer_data[$email_address]['total_orders_placed'] = 0;
			if ( ! isset($customer_data[$email_address]['total_order_revenue']) ) $customer_data[$email_address]['total_order_revenue'] = 0;
			if ( ! isset($customer_data[$email_address]['total_order_profit']) ) $customer_data[$email_address]['total_order_profit'] = 0;
			if ( ! isset($customer_data[$email_address]['total_product_revenue_value_rrp']) ) $customer_data[$email_address]['total_product_revenue_value_rrp'] = 0;
			if ( ! isset($customer_data[$email_address]['total_product_revenue']) ) $customer_data[$email_address]['total_product_revenue'] = 0;
			if ( ! isset($customer_data[$email_address]['total_product_discounts_applied']) ) $customer_data[$email_address]['total_product_discounts_applied'] = 0;
			if ( ! isset($customer_data[$email_address]['total_product_cost']) ) $customer_data[$email_address]['total_product_cost'] = 0;
			if ( ! isset($customer_data[$email_address]['total_product_profit']) ) $customer_data[$email_address]['total_product_profit'] = 0;
			if ( ! isset($customer_data[$email_address]['total_quantity_sold']) ) $customer_data[$email_address]['total_quantity_sold'] = 0;
			if ( ! isset($customer_data[$email_address]['total_product_quantity_refunded']) ) $customer_data[$email_address]['total_product_quantity_refunded'] = 0;
			if ( ! isset($customer_data[$email_address]['total_refund_amount']) ) $customer_data[$email_address]['total_refund_amount'] = 0;
			if ( ! isset($customer_data[$email_address]['total_products_sold']) ) $customer_data[$email_address]['total_products_sold'] = 0;

			$customer_data[$email_address]['total_orders_placed']++;	
			$customer_data[$email_address]['total_order_revenue'] 					+= $order_revenue;
			$customer_data[$email_address]['total_order_profit'] 					+= $order_profit;
			$customer_data[$email_address]['formatted_total_order_revenue'] 		= wc_price( $customer_data[$email_address]['total_order_revenue'] );
			$customer_data[$email_address]['formatted_total_order_profit'] 			= wc_price( $customer_data[$email_address]['total_order_profit'] );
			$customer_data[$email_address]['total_product_revenue_value_rrp'] 		+= $order_product_revenue_at_retail_price;
			$customer_data[$email_address]['total_product_revenue'] 				+= $order_product_revenue;
			$customer_data[$email_address]['total_product_discounts_applied'] 		+= $order_product_discounts_applied;
			$customer_data[$email_address]['total_product_cost'] 					+= $order_product_cost;
			$customer_data[$email_address]['total_product_profit'] 					+= $order_product_profit;
			$customer_data[$email_address]['total_quantity_sold'] 					+= $order_product_quantity_sold;
			$customer_data[$email_address]['total_product_quantity_refunded'] 		+= $order_refund_quantity;
			$customer_data[$email_address]['total_refund_amount'] 					+= $order_refund_amount;
			$customer_data[$email_address]['total_products_sold']					+= $order_products_sold;
			$customer_data[$email_address]['formatted_total_product_revenue'] 		= wc_price( $customer_data[$email_address]['total_product_revenue'] );
			$customer_data[$email_address]['formatted_total_product_profit'] 		= wc_price( $customer_data[$email_address]['total_product_profit'] );
			$customer_data[$email_address]['average_margin'] = wpd_ai_calculate_percentage( 
				$customer_data[$email_address]['total_order_profit'], 
				$customer_data[$email_address]['total_order_revenue']
			);
			$customer_data[$email_address]['average_discount'] = wpd_ai_calculate_percentage( 
				$customer_data[$email_address]['total_product_discounts_applied'], 
				$customer_data[$email_address]['total_product_revenue_value_rrp']
			);

			/**
			 *
			 *	Store Customer Type Data
			 *	
			 */
			if ( ! isset($customer_type_data[$customer_type]['revenue']) ) $customer_type_data[$customer_type]['revenue'] = 0;
			if ( ! isset($customer_type_data[$customer_type]['profit']) ) $customer_type_data[$customer_type]['profit'] = 0;
			if ( ! isset($customer_type_data[$customer_type]['cost']) ) $customer_type_data[$customer_type]['cost'] = 0;
			if ( ! isset($customer_type_data[$customer_type]['qty_sold']) ) $customer_type_data[$customer_type]['qty_sold'] = 0;
			if ( ! isset($customer_type_data[$customer_type]['products_sold']) ) $customer_type_data[$customer_type]['products_sold'] = 0;

			$customer_type_data[$customer_type]['revenue'] 			+= round( $order_revenue, 2 );
			$customer_type_data[$customer_type]['profit']			+= round( $order_profit, 2 );
			$customer_type_data[$customer_type]['cost']				+= round( $order_total_cost, 2 );
			$customer_type_data[$customer_type]['qty_sold']			+= $order_product_quantity_sold;
			$customer_type_data[$customer_type]['products_sold'] 	+= $order_products_sold;

			/**
			 *
			 *	Store Location Data by Country
			 *	
			 */
			if ( ! isset($location_data[$shipping_country]['total']['revenue']) ) $location_data[$shipping_country]['total']['revenue'] = 0;
			if ( ! isset($location_data[$shipping_country]['total']['profit']) ) $location_data[$shipping_country]['total']['profit'] = 0;
			if ( ! isset($location_data[$shipping_country]['total']['cost']) ) $location_data[$shipping_country]['total']['cost'] = 0;
			if ( ! isset($location_data[$shipping_country]['total']['qty_sold']) ) $location_data[$shipping_country]['total']['qty_sold'] = 0;
			if ( ! isset($location_data[$shipping_country]['total']['products_sold']) ) $location_data[$shipping_country]['total']['products_sold'] = 0;
			if ( ! isset($location_data[$shipping_country]['total']['customers']) ) $location_data[$shipping_country]['total']['customers'] = array();
			$location_data[$shipping_country]['total']['revenue'] 			+= round( $order_revenue, 2 );
			$location_data[$shipping_country]['total']['profit']			+= round( $order_profit, 2 );
			$location_data[$shipping_country]['total']['cost']				+= round( $order_total_cost, 2 );
			$location_data[$shipping_country]['total']['qty_sold']			+= $order_product_quantity_sold;
			$location_data[$shipping_country]['total']['products_sold']		+= $order_products_sold;
			$location_data[$shipping_country]['total']['country']			=  $shipping_country;
			if ( ! in_array($email_address, $location_data[$shipping_country]['total']['customers']) ) {
				$location_data[$shipping_country]['total']['customers'][] = $email_address;
				if ( ! isset($location_data[$shipping_country]['total']['customer_count']) ) $location_data[$shipping_country]['total']['customer_count'] = 0;
				$location_data[$shipping_country]['total']['customer_count']++;
			}
			$country_data[$shipping_country] = $location_data[$shipping_country]['total'];

			/**
			 *
			 *	Store Location Data By State
			 *	
			 */
			if (  !isset($location_data[$shipping_country][$shipping_state]['revenue']) ) $location_data[$shipping_country][$shipping_state]['revenue'] = 0;
			if (  !isset($location_data[$shipping_country][$shipping_state]['profit']) ) $location_data[$shipping_country][$shipping_state]['profit'] = 0;
			if (  !isset($location_data[$shipping_country][$shipping_state]['cost']) ) $location_data[$shipping_country][$shipping_state]['cost'] = 0;
			if (  !isset($location_data[$shipping_country][$shipping_state]['products_sold']) ) $location_data[$shipping_country][$shipping_state]['products_sold'] = 0;
			if (  !isset($location_data[$shipping_country][$shipping_state]['qty_sold']) ) $location_data[$shipping_country][$shipping_state]['qty_sold'] = 0;
			if ( ! isset($location_data[$shipping_country][$shipping_state]['customers']) ) $location_data[$shipping_country][$shipping_state]['customers'] = array();

			$location_data[$shipping_country][$shipping_state]['revenue'] 			+= round( $order_revenue, 2 );
			$location_data[$shipping_country][$shipping_state]['profit']			+= round( $order_profit, 2 );
			$location_data[$shipping_country][$shipping_state]['cost']				+= round( $order_total_cost, 2 );
			$location_data[$shipping_country][$shipping_state]['products_sold']		+= $order_products_sold;
			$location_data[$shipping_country][$shipping_state]['qty_sold']			+= $order_product_quantity_sold;
			$location_data[$shipping_country][$shipping_state]['country']			=  $shipping_country;
			$location_data[$shipping_country][$shipping_state]['state']				=  $shipping_state;
			if ( ! in_array($email_address, $location_data[$shipping_country][$shipping_state]['customers']) ) {
				$location_data[$shipping_country][$shipping_state]['customers'][] = $email_address;
				if (  !isset($location_data[$shipping_country][$shipping_state]['customer_count']) ) $location_data[$shipping_country][$shipping_state]['customer_count'] = 0;
				$location_data[$shipping_country][$shipping_state]['customer_count']++;
			}

			/**
			 *
			 *	Store Device Category Data
			 *	
			 */
			if (  !isset($device_category_totals[$device_category]['revenue']) ) $device_category_totals[$device_category]['revenue'] = 0;
			if (  !isset($device_category_totals[$device_category]['profit']) ) $device_category_totals[$device_category]['profit'] = 0;
			if (  !isset($device_category_totals[$device_category]['cost']) ) $device_category_totals[$device_category]['cost'] = 0;
			if (  !isset($device_category_totals[$device_category]['qty_sold']) ) $device_category_totals[$device_category]['qty_sold'] = 0;
			if (  !isset($device_category_totals[$device_category]['products_sold']) ) $device_category_totals[$device_category]['products_sold'] = 0;
			if (  !isset($device_category_totals[$device_category]['orders']) ) $device_category_totals[$device_category]['orders'] = 0;
			if ( ! isset($device_category_totals[$device_category]['customers']) ) $device_category_totals[$device_category]['customers'] = array();

			$device_category_totals[$device_category]['revenue'] 			+= round( $order_revenue, 2 );
			$device_category_totals[$device_category]['profit']				+= round( $order_profit, 2 );
			$device_category_totals[$device_category]['cost']				+= round( $order_total_cost, 2 );
			$device_category_totals[$device_category]['qty_sold']			+= $order_product_quantity_sold;
			$device_category_totals[$device_category]['products_sold']		+= $order_products_sold;
			$device_category_totals[$device_category]['device_category']	=  $device_category;
			$device_category_totals[$device_category]['orders']++;
			if ( ! in_array($email_address, $device_category_totals[$device_category]['customers']) ) {
				$device_category_totals[$device_category]['customers'][] = $email_address;
				if (  !isset($device_category_totals[$device_category]['customer_count']) ) $device_category_totals[$device_category]['customer_count'] = 0;
				$device_category_totals[$device_category]['customer_count']++;
			}

			/**
			 *
			 *	Store Device Browser Data
			 *	
			 */
			if ( ! isset($device_browser_totals[$device_browser]['revenue']) ) $device_browser_totals[$device_browser]['revenue'] = 0;
			if ( ! isset($device_browser_totals[$device_browser]['profit']) ) $device_browser_totals[$device_browser]['profit'] = 0;
			if ( ! isset($device_browser_totals[$device_browser]['cost']) ) $device_browser_totals[$device_browser]['cost'] = 0;
			if ( ! isset($device_browser_totals[$device_browser]['qty_sold']) ) $device_browser_totals[$device_browser]['qty_sold'] = 0;
			if ( ! isset($device_browser_totals[$device_browser]['products_sold']) ) $device_browser_totals[$device_browser]['products_sold'] = 0;
			if ( ! isset($device_browser_totals[$device_browser]['device_category']) ) $device_browser_totals[$device_browser]['device_category'] = 0;
			if ( ! isset($device_browser_totals[$device_browser]['orders']) ) $device_browser_totals[$device_browser]['orders'] = 0;
			if ( ! isset($device_browser_totals[$device_browser]['customers']) ) $device_browser_totals[$device_browser]['customers'] = array();

			$device_browser_totals[$device_browser]['revenue'] 			+= round( $order_revenue, 2 );
			$device_browser_totals[$device_browser]['profit']			+= round( $order_profit, 2 );
			$device_browser_totals[$device_browser]['cost']				+= round( $order_total_cost, 2 );
			$device_browser_totals[$device_browser]['qty_sold']			+= $order_product_quantity_sold;
			$device_browser_totals[$device_browser]['products_sold']	+= $order_products_sold;
			$device_browser_totals[$device_browser]['device_category']	=  $device_category;
			$device_browser_totals[$device_browser]['orders']++;
			if ( ! in_array($email_address, $device_browser_totals[$device_browser]['customers']) ) {
				$device_browser_totals[$device_browser]['customers'][] = $email_address;
				if ( ! isset($device_browser_totals[$device_browser]['customer_count']) ) $device_browser_totals[$device_browser]['customer_count'] = 0;
				$device_browser_totals[$device_browser]['customer_count']++;
			}

			/**
			 *
			 *	Store Device Type Data
			 *	
			 */
			if ( ! isset($device_type_totals[$device_type]['revenue']) ) $device_type_totals[$device_type]['revenue'] = 0;
			if ( ! isset($device_type_totals[$device_type]['profit']) ) $device_type_totals[$device_type]['profit'] = 0;
			if ( ! isset($device_type_totals[$device_type]['cost']) ) $device_type_totals[$device_type]['cost'] = 0;
			if ( ! isset($device_type_totals[$device_type]['products_sold']) ) $device_type_totals[$device_type]['products_sold'] = 0;
			if ( ! isset($device_type_totals[$device_type]['qty_sold']) ) $device_type_totals[$device_type]['qty_sold'] = 0;
			if ( ! isset($device_type_totals[$device_type]['device_category']) ) $device_type_totals[$device_type]['device_category'] = 0;
			if ( ! isset($device_type_totals[$device_type]['orders']) ) $device_type_totals[$device_type]['orders'] = 0;
			if ( ! isset($device_type_totals[$device_type]['customers']) ) $device_type_totals[$device_type]['customers'] = array();

			$device_type_totals[$device_type]['revenue'] 			+= round( $order_revenue, 2 );
			$device_type_totals[$device_type]['profit']				+= round( $order_profit, 2 );
			$device_type_totals[$device_type]['cost']				+= round( $order_total_cost, 2 );
			$device_type_totals[$device_type]['products_sold']		+= $order_products_sold;
			$device_type_totals[$device_type]['qty_sold']			+= $order_product_quantity_sold;
			$device_type_totals[$device_type]['device_category']	=  $device_category;
			$device_type_totals[$device_type]['orders']++;
			if ( ! in_array($email_address, $device_type_totals[$device_type]['customers']) ) {
				$device_type_totals[$device_type]['customers'][] = $email_address;
			if ( ! isset($device_type_totals[$device_type]['customer_count']) ) $device_type_totals[$device_type]['customer_count'] = 0;
				$device_type_totals[$device_type]['customer_count']++;

			}

			/**
			 *
			 *	Create running totals
			 *
			 */
			$total_order_count++;
			$total_order_revenue 					+= $order_revenue;
			$total_order_profit 					+= $order_profit;
			$average_order_margin 					= wpd_ai_calculate_percentage( $total_order_profit, $total_order_revenue );
			$total_product_revenue_value_rrp 		+= $order_product_revenue_at_retail_price;
			$total_product_revenue 					+= $order_product_revenue;
			$total_product_discounts_applied 		+= $order_product_discounts_applied;
			$total_product_profit 					+= $order_product_profit;
			$total_product_cost 					+= $order_product_cost;
			$total_quantity_sold 					+= $order_product_quantity_sold;
			$total_products_sold 					+= $order_products_sold;
			$total_products_refunded 				+= $order_refund_quantity;
			$total_amount_refunded 					+= $order_refund_amount;

			/**
			 *
			 *	Store Data by Date
			 *
			 */
			if ( ! isset($date_data[$order_date][$customer_type]) ) $date_data[$order_date][$customer_type] = 0;
			if ( ! isset($date_data[$order_date]['total_orders']) ) $date_data[$order_date]['total_orders'] = 0;

			$date_data[$order_date]['date'] = $order_date;
			$date_data[$order_date][$customer_type]++;
			$date_data[$order_date]['total_orders']++;

			/**
			 *
			 *	Store highest values and a few extras
			 *
			 */
		    if ( $highest_product_count < $order_products_sold ) {
		    	$highest_product_count = $order_products_sold;
		    }
		    if ( $highest_product_quantity < $order_product_quantity_sold ) {
		    	$highest_product_quantity = $order_product_quantity_sold;
		    }
		   	if ( $highest_revenue_amount < $order_revenue ) {
		    	$highest_revenue_amount = $order_revenue;
		    }
		    if ( $customer_data[$email_address]['total_orders_placed'] === 2 ) {
		    	$number_of_customers_purchased_more_than_once++;
		    }
		    if ( ! isset($customer_totals['customers']) ) $customer_totals['customers'] = array();
		    if ( ! isset($customer_totals['registered_count']) ) $customer_totals['registered_count'] = 0;
		    if ( ! isset($customer_totals['guest_count']) ) $customer_totals['guest_count'] = 0;

    		if ( ! in_array($email_address, $customer_totals['customers']) ) {
				$customer_totals['customers'][] = $email_address;
				if ( ! isset($customer_totals['count']) ) $customer_totals['count'] = 0;
				if ( ! isset($customer_totals[$customer_type . '_count']) ) $customer_totals[$customer_type . '_count'] = 0;
				$customer_totals['count']++;
				$customer_totals[$customer_type . '_count']++;
			}

		}

		/**
		 *
		 *	Sort a few of our arrays
		 *
		 */
		$sorted_country_data 									= wpd_ai_sort_multi_level_array( $country_data, 'customer_count' );
		$most_popular_country_code 								= array_key_first( $sorted_country_data );
		$most_popular_state_data 								= $location_data[$most_popular_country_code];
		$most_popular_state_data['total']['number_of_states'] 	= count($most_popular_state_data) - 1; // Minus one so I don't count the total
		$sorted_state_data 										= wpd_ai_sort_multi_level_array( $most_popular_state_data, 'customer_count' );
		$most_popular_state_code 								= array_keys($sorted_state_data)[1]; // array key [0] is "total"

		/**
		 *
		 *	Store further total data
		 *
		 */
		$customer_totals['avg_revenue_per_customer'] 		= $total_order_revenue / $customer_totals['count'];
		$customer_totals['avg_profit_per_customer'] 		= $total_order_profit / $customer_totals['count'];
		$customer_totals['avg_margin_per_customer'] 		= wpd_ai_calculate_percentage( $customer_totals['avg_profit_per_customer'], $customer_totals['avg_revenue_per_customer'] );
		$customer_totals['purchase_rate'] 					= round( $total_order_count / $customer_totals['count'], 2 );
		$customer_totals['products_per_customer'] 			= round( $total_products_sold / $customer_totals['count'], 2 );
		$customer_totals['refunds_per_customer'] 			= round( $total_orders_with_refunds / $customer_totals['count'], 2 );
		$customer_totals['number_of_customers_purchased_more_than_once'] = $number_of_customers_purchased_more_than_once;
		$customer_totals['country_with_most_customers'] 	= WC()->countries->countries[ $most_popular_country_code ]; 
		$customer_totals['state_with_most_customers'] 		= WC()->countries->get_states( $most_popular_country_code )[$most_popular_state_code];

		/**
		 *
		 *	Setup Totals
		 *
		 */
		$totals_data = array (

			'customer_totals' 						=> $customer_totals,
			'number_of_customers' 					=> $customer_totals['count'],
			'total_order_count' 					=> $total_order_count,
			'total_order_count_per_day' 			=> round( $total_order_count / $number_of_days, 2 ),
			'total_order_revenue' 					=> $total_order_revenue,
			'total_order_profit' 					=> $total_order_profit,
			'total_products_sold' 					=> $total_products_sold,
			'total_products_sold_per_day' 			=> round( $total_products_sold / $number_of_days, 2 ),
			'total_quantity_sold' 					=> $total_quantity_sold,
			'total_quantity_sold_per_day' 			=> round( $total_quantity_sold / $number_of_days, 2 ),
			'total_product_revenue_value_rrp' 		=> $total_product_revenue_value_rrp,
			'total_product_revenue' 				=> $total_product_revenue,
			'total_product_cost' 					=> $total_product_cost,
			'total_product_discounts_applied' 		=> $total_product_discounts_applied,
			'average_product_discount' 				=> wpd_ai_calculate_percentage( $total_product_discounts_applied, $total_product_revenue_value_rrp),
			'total_product_profit' 					=> $total_product_profit,
			'total_product_profit_at_rrp' 			=> $total_product_revenue_value_rrp - $total_product_cost,
			'average_product_profit_per_product' 	=> round($total_product_profit / $total_products_sold, 2 ),
			'average_profit_margin' 				=> wpd_ai_calculate_percentage( $total_order_profit, $total_order_revenue ),
			'average_profit_margin_at_rrp' 			=> wpd_ai_calculate_percentage( $total_product_revenue_value_rrp - $total_product_cost, $total_product_revenue ),
			'total_products_refunded' 				=> $total_products_refunded,
			'total_amount_refunded' 				=> $total_refund_amount,
			'total_orders_with_refunds' 			=> $total_orders_with_refunds,
			'total_products_sold'					=> $total_products_sold,
			'highest_product_count' 				=> $highest_product_count,
			'highest_product_quantity' 				=> $highest_product_quantity,
			'highest_revenue_amount' 				=> $highest_revenue_amount,
			'date_data' 							=> $date_data,
			'customer_type_data' 					=> $customer_type_data,
			'location_data' 						=> $location_data,
			'number_of_countries' 					=> count($sorted_country_data),
			'sorted_country_data' 					=> $sorted_country_data,
			'location_data_most_popular_state' 		=> $sorted_state_data,
			'device_category' 						=> $device_category_totals,
			'device_type' 							=> $device_type_totals,
			'device_browser' 						=> $device_browser_totals,

		);

		/**
		 *
		 *	Store my data in properties
		 *
		 */
		$customer_data 		= wpd_ai_sort_multi_level_array( $customer_data, 'total_order_revenue' );
		$this->data 		= $customer_data;
		$this->data_totals 	= $totals_data;

		// Return results if required
		return $customer_data;

	}

	/**
	 *
	 *	Create date data
	 *
	 */
	public function data_by_date() {

		// Orders
		$data_totals 		= $this->data_totals;
		$customer_date_data = $data_totals['date_data'];
		$max_date 			= $this->selected_date_range('end'); 	// date in the past
        $min_date 			= $this->selected_date_range('start'); 	// current date
		$date_range 		= $this->date_range( $min_date, $max_date, '+1 day', 'Y-m-d' );

		/**
		 *
		 *	Setup 
		 *
		 */
		foreach ( $date_range as $date_array_val ) {

			$guest_user_order_array[$date_array_val] = array(
				'x'	=>	$date_array_val,
				'y' =>	0			
			);

			$registered_user_order_array[$date_array_val] = array(
				'x'	=>	$date_array_val,
				'y' =>	0
			);

		}

		/**
		 *
		 *	Store revenue and profit as summed array against date
		 *
		 */
		foreach ( $customer_date_data as $order_date ) {

			if ( ! isset($order_date['registered']) ) $order_date['registered'] = 0;
			if ( ! isset($order_date['guest']) ) $order_date['guest'] = 0;

	        $date 										=  $order_date['date'];
			$guest_user_order_array[$date]['y'] 		+= $order_date['guest'];
			$registered_user_order_array[$date]['y'] 	+= $order_date['registered'];

		}

		$this->data_totals_by_date = array(

			'guest_orders_by_date' 		=> $guest_user_order_array,
			'registered_orders_by_date' => $registered_user_order_array,

		);

	}

	/**
     *
     *	Load filters
     *
     */
    public function load_filters() {

    	// Per page
    	if ( isset($_GET['wpd-per-page']) && ! empty($_GET['wpd-per-page']) ) {

    		$this->per_page = absint( $_GET['wpd-per-page'] );

    	} else {

    		$this->per_page = 25; // default 25

    	}

    	// Store filter
    	if ( isset($_GET['wpd-filter']) && ! empty($_GET['wpd-filter']) ) {

    		$this->filter = wc_clean( $_GET['wpd-filter'] );

    	}

    	// Store dates
    	if ( isset( $_GET['wpd-report-from-date'] ) && isset( $_GET['wpd-report-to-date'] ) ) {

			$this->filter['start_date'] 	= preg_replace("([^0-9-])", "", $_GET['wpd-report-from-date']);
			$this->filter['end_date'] 		= preg_replace("([^0-9-])", "", $_GET['wpd-report-to-date']);

		}

    	/**
    	 *
    	 *	For AJAX calls we have to allow a URL to be parsed
    	 *	
    	 */
    	if ( ! empty( $this->requesting_url ) ) {

    		// If array we are looking at args
    		if ( is_array( $this->requesting_url ) ) {

    			if ( isset( $this->requesting_url['from_date'] ) & ! empty( $this->requesting_url['from_date'] ) ) {
	    			$this->filter['start_date'] = $this->requesting_url['from_date'];
    			}
    			if ( isset( $this->requesting_url['to_date'] ) & ! empty( $this->requesting_url['to_date'] ) ) {
	    			$this->filter['end_date'] = $this->requesting_url['to_date'];
    			}

    		} else { // if string we are looking at url

	    		$parsed_url = parse_url( $this->requesting_url );
	    		parse_str( $parsed_url['query'], $new_filter );
	    		$this->filter = $new_filter['wpd-filter'];
	    		if ( isset( $new_filter['wpd-report-from-date'] ) && isset($new_filter['wpd-report-to-date']) ) {
	    			$this->filter['start_date'] = $new_filter['wpd-report-from-date'];
	    			$this->filter['end_date'] 	= $new_filter['wpd-report-to-date'];
				}

    		}

    	}

    }

	/**
	 *
	 *	Define columns to be used
	 *
	 */
	public function get_columns() {

	  $columns = array (

	  	'customer_avatar'					=> '',
		'customer'							=> 'Customer',
		'formatted_total_order_revenue' 	=> 'Revenue',
		'formatted_total_order_profit' 		=> 'Profit',
		'total_orders_placed' 				=> '# Orders', // How many orders has this been sold in
		'total_quantity_sold'				=> 'Qty Sold',
		'total_refund_amount'  				=> 'Refund Amount',

	  );

	  return $columns;

	}

	/**
	 *
	 *	Setup table
	 *	@todo make the URL string query fix better
	 *
	 */
	function prepare_items() {

		// Prevent URL string getting too long
		$_SERVER['REQUEST_URI'] = remove_query_arg( '_wp_http_referer', $_SERVER['REQUEST_URI'] );
		
		// Settings
        $columns 		= $this->get_columns();
        $hidden 		= $this->get_hidden_columns();
        $sortable 		= $this->get_sortable_columns();
        $data 			= $this->data;
        $total_items 	= count($data);
        $per_page 		= $this->per_page;
        $current_page 	= $this->get_pagenum();

        $this->set_pagination_args( 
        	array(
	            'total_items' => $total_items,
	            'per_page'    => $per_page
        	) 
        );

        $data 					= array_slice( $data, (( $current_page - 1 ) * $per_page), $per_page );
        $this->_column_headers 	= array( $columns, $hidden, $sortable );
        $this->items 			= $data;

	}

	/**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns() {

        return array('ID', 'status', 'date');

    }

    /**
     *
     *	Column defaults
     *
     */
    public function column_default( $item, $column_name ) {

	    return $item[$column_name];

	}

	/**
	 *
	 *	Override row HTML - Add Order ID to class
	 *
	 */
	public function single_row( $item ) {

	    echo '<tr class="wpd-table-row">';

	    $this->single_row_columns( $item );

	    echo '</tr>';

	}

	/**
	 *
	 *	Per page
	 *
	 */
	public function extra_tablenav( $which ) {

		?>
		<div class="actions" style="float:left;">
	        <?php wpd_ai_per_page_selector( $this->per_page ) ?>
	        <?php submit_button('Filter', 'wpd-input primary', 'submit', false); ?>
		</div>
		<?php

	}

	/**
	 *
	 *	Filters
	 *
	 */
	function output_filters() {

		$totals 	= $this->data_totals;
		$start  	= $this->selected_date_range('start', 'F j, Y');
		$end  		= $this->selected_date_range('end', 'F j, Y');

		?>
			<div class="wpd-white-block wpd-filter">
		        <div class="wrapper">
	        		<div class="wpd-col-10">
		        		<div class="wpd-section-heading">Filter</div>
	        			<div class="wpd-filter-wrapper">
	        				<?php echo esc_html( $this->date_selector_html() ); ?>
	        			</div>
	        			<div class="wpd-filter-wrapper">
	        				<?php submit_button('Filter', 'wpd-input primary', 'submit', false); ?>
	        			</div>
	        		</div>
		        	<div class="wpd-col-2" style="text-align:center;">
		        		<table class="fixed">
		        			<tr>
		        				<td>
									<?php wpd_ai_export_to_csv_icon( 'export-customers-to-csv', 'Export Customer Data To CSV' ); ?>
		        				</td>
		        				<td>
									<?php wpd_ai_export_to_csv_icon( 'export-customer-totals-to-csv', 'Export Customer Totals To CSV' ); ?>
		        				</td>
		        			</tr>
		        		</table>
					</div>
				</div>
			</div>
		<?php

	}

    /**
     *
     *	Check if we are limiting range by X days
     *
     */
    public function x_days_range() {

    	$days = 30;

    	if ( isset( $this->filter['start_date'] ) && isset( $this->filter['end_date'] ) ) {

			$start 	= new DateTime( $this->filter['start_date'] );
			$end 	= new DateTime( $this->filter['end_date'] );
			// Only execute this if we're sure these are now objects
			if ( is_a($start, 'DateTime') && is_a($end, 'DateTime') ) {

				$days = $end->diff( $start )->format('%a') + 1;
		    	return $days;

			}

    	}

    	return $days;

    }

    /**
     *
     *	Returns selected date range
     *
     */
    public function selected_date_range( $result = 'start', $format = 'Y-m-d' ) {

    	$days_in_past 	= (string) '-' . $this->x_days_range() . ' days';
    	$wp_timestamp 	= current_time( 'timestamp' );

        if ( $result == 'start' ) {

    		$start = date($format, strtotime( $days_in_past, $wp_timestamp ) ); // this needs to be based on wp time as below

    		if ( isset( $this->filter['start_date'] ) && ! empty($this->filter['start_date']) ) {
    			$start = date( $format, strtotime($this->filter['start_date']) );
    		}

        	return $start;

        } elseif ( $result == 'end' ) {

        	$end = current_time( $format ); 

        	if ( isset($this->filter['end_date']) && ! empty($this->filter['end_date']) ) {
    			$end = date( $format, strtotime($this->filter['end_date']));
    		}

        	return $end;

        }

    }

	/**
	 *
	 *	@return date range || array[]
	 *
	 */
	public function date_range( $first, $last, $step = '+1 day', $output_format = 'Y-m-d' ) {

	    $dates 		= array();
	    $current 	= strtotime($first);
	    $last 		= strtotime($last);

	    while( $current <= $last ) {

	        $dates[] = date($output_format, $current);
	        $current = strtotime($step, $current);
	    }

	    return $dates;
	}

	/**
	 *
	 *	Date range selector HTML output
	 *
	 */
	public function date_selector_html() {

		$start 		= $this->selected_date_range('start'); 		// date in the past
        $end 		= $this->selected_date_range('end'); 		// current date

        ?>
		<p style="margin: 0px; padding: 0px;">Select Your Date Range</p>
		<?php echo wpd_ai_date_picker( $start, 'wpd-report-from-date' ); ?>
		<?php echo wpd_ai_date_picker( $end, 'wpd-report-to-date' ); ?>
		<?php

	}

	/**
	 *
	 *	Order reporting dashboard
	 *
	 */
	public function output_insights() {

		// Sorry mate, not in the free version :)
		return false;

	}

	/**
	 *
	 *	Bundle all JS together
	 *
	 */
	public function javascript_output() {

		// Sorry mate, not in the free version :)
		return false;

	}

	/**
	 *
	 *	Prepare and return CSV data
	 *
	 */
	public function csv_data() {

		// Sorry mate, not in the free version :)
		return false;

	}

	/**
	 *
	 *	Prepare and return CSV data
	 *
	 */
	public function csv_data_totals() {

		// Sorry mate, not in the free version :)
		return false;
		
	}

}

