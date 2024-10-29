<?php 
/**
 *
 * Analytics Dashboard
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

class WPD_AI_Profit_Reports_Dashboard {

	/**
	 *
	 *	This is our base currency
	 *
	 */
	private $wc_currency;

	/**
	 *
	 *	Chart Height
	 *
	 */
	private $chart_height;

	/**
	 *
	 *	Store data so we don't need to requery
	 *
	 */
	private $data;

	/**
	 *
	 *	Expense Data
	 *
	 */
	private $expense_data;

	/**
	 *
	 *	Expense Data
	 *
	 */
	private $data_totals;

	/**
	 *
	 *	Expense Data
	 *
	 */
	private $data_totals_by_date;

	/**
	 *
	 *	Expense Data
	 *
	 */
	private $expense_data_totals;

	/**
	 *
	 *	I will store data results here
	 *	@default 25
	 *
	 */
	private $per_page = 25;

	/**
	 *
	 *	Filter, in case I want to filter the results
	 *
	 */
	private $filter = array();

	/**
	 *
	 *
	 *
	 */
	private $product_data = array();

	/**
	 *
	 *	Constructor
	 *
	 */
    public function __construct() {
                
        /**
         *
         *	Set base currency to Woocommerce currency
		 *
		 */
        $this->load_filters();
        $this->wc_currency 			= wpd_ai_get_base_currency();
        $this->chart_height 		= '400px';
       	$expense_data 				= new WPD_AI_Expense_Reports();
		$this->expense_data 		= $expense_data->raw_data();
		$this->expense_data_totals 	= $expense_data->data_totals;
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
        $totals  					= array();
        $total_order_count 			= 0;
        $payment_gateway_array 		= array();
    	$highest_revenue 			= 0;
		$highest_cost 				= 0;
		$highest_profit				= 0;
		$status 					= wpd_ai_paid_order_status();
		$order_cost 				= 0;
		$order_revenue 				= 0;
		$order_profit 				= 0;
		$order_margin 				= 0;
		$payment_gateway 			= 0;
		$total_revenue 				= 0;
		$total_cost 				= 0;
		$total_profit 				= 0;
		$margin_sum 				= 0;
		$total_shipping_charged 	= 0;
		$total_shipping_cost 		= 0;
		$total_product_cost 		= 0;
		$total_refunds 				= 0;
		$total_payment_gateway_costs = 0;
		$total_tax_paid 			= 0;
		$total_discounts_applied 	= 0;
		$total_product_revenue 		= 0;
		$total_product_revenue_at_rrp = 0;
		$total_qty_sold 			= 0;
		$total_skus_sold 			= 0;


		/**
		 *
		 *	Query orders
		 *
		 */
		$args = array(

		    'limit' 			=> -1, // -1
		    'orderby' 			=> 'date',
		    'order' 			=> 'DESC',
		    'date_created' 		=> $start . "..." . $end, //'2018-02-01...2018-02-28',
		    'type' 				=> 'shop_order',
		    'status' 			=> $status,

		);

		// Fetch posts
		$orders = wc_get_orders( $args );

		/**
		 *
		 *	Loop through orders
		 *
		 */
		foreach ( $orders as $order ) {	

			if ( ! is_object($order) ) continue;

			/**
			 *
			 *	Check memory usage
			 *	If memory use is higher than 90%, dont try and find anymore
			 *
			 */
			if ( wpd_ai_is_memory_usage_greater_than(90) ) {

				$memory_limit = ini_get('memory_limit');
				wpd_ai_admin_notice( 'You\'ve exhausted your memory usage. Increase your PHP memory limit or reduce the date range. Your current PHP memory limit is ' . $memory_limit . '.' );

				break; // Break the entire process if were hitting the memory limits

			}

			// Collect order data
			$order_id 	= $order->get_id();
			$order_data = wpd_ai_calculate_cost_profit_by_order( $order_id, false, false, false );

			// Store order data
			$order_cost 	= $order_data['total_order_cost'];
			$order_revenue 	= $order_data['total_order_revenue'];
			$order_profit 	= $order_data['total_order_profit'];
			$order_margin 	= $order_data['total_order_margin'];
			$payment_gateway = $order_data['payment_gateway'];

			// Make consecutive totals calculations
			$total_revenue 	+= $order_revenue;
			$total_cost 	+= $order_cost;
			$total_profit 	+= $order_profit;
			$margin_sum 	+= $order_margin;

			$total_shipping_charged 		+= $order_data['total_shipping_charged'];
			$total_shipping_cost 			+= $order_data['total_shipping_cost'];
			$total_product_cost 			+= $order_data['total_product_cost'];
			$total_refunds 					+= $order_data['total_refund_amount'];
			$total_payment_gateway_costs 	+= $order_data['payment_gateway_cost'];
			$total_tax_paid 				+= $order_data['order_tax_paid'];
			$total_discounts_applied 		+= $order_data['total_discounts_applied'];
			$total_product_revenue 			+= $order_data['total_product_revenue'];
			$total_product_revenue_at_rrp 	+= $order_data['total_product_revenue_at_rrp'];
			$total_qty_sold 				+= $order_data['total_qty_sold'];
			$total_skus_sold 				+= $order_data['total_skus_sold'];

			// Set highest values
			( $order_revenue > $highest_revenue ) ? $highest_revenue = $order_revenue : null;
			( $order_cost > $highest_cost ) ? $highest_cost = $order_cost : null;
			( $order_profit > $highest_profit ) ? $highest_profit = $order_profit : null;

			// Few extra things
	        $admin_order_link 	= admin_url( 'post.php?post=' . $order_id ) . '&action=edit';
			$date_created 		= $order->get_date_created()->getOffsetTimestamp();
			$order_status 		= $order->get_status();

			if ( ! isset($payment_gateway_array[$payment_gateway]) ) $payment_gateway_array[$payment_gateway] = 0;
			$payment_gateway_array[$payment_gateway]++;

			// Add total
			$total_order_count++;

			/**
			 *
			 *	Return unformatted results
			 *
			 */
			$results[] = array(

				'time_hour' 			=> date( 'ga', $date_created ),
				'standard_date' 		=> date( 'Y-m-d', $date_created ),
				'date'					=> date( 'd-M', $date_created ), //$date_created->format('d-M'),
				'column-date'			=> date( 'F j, Y', $date_created ), //$date_created->format('d-M'),
				'order-id' 				=> '<a href="'.$admin_order_link.'">#' . $order_id . '</a>',
				'status' 				=> $order_status,
				'status-nice-name' 		=> '<span class="wpd-order-status wpd-status-'.$order_status.'">' . wc_get_order_status_name( $order_status ) . '</span>',
				'unformatted_revenue' 	=> $order_revenue,
				'revenue' 				=> wc_price($order_revenue),
				'unformatted_cost'		=> $order_cost,
				'cost'					=> wc_price($order_cost),
				'unformatted_profit' 	=> $order_profit,
				'profit' 				=> wc_price($order_profit),
				'unformatted_margin' 	=> $order_margin,
				'margin' 				=> round($order_margin, 2) . '%',
				//c'currency'  			=> $currency,
				'ID' 					=> $order_id,

			);

			/**
			 *
			 *	Begin calculation of order product cost
			 *
			 */
		    if ( count( $order->get_items() ) > 0 ) {

		        foreach ( $order->get_items() as $item_id => $item ) {

		        	/**
		        	 *
		        	 *	Skip item if its not a product
		        	 *
		        	 */
					if ( ! is_a( $item, 'WC_Order_Item_Product' ) ) continue;
					if ( ! is_object($item) ) continue;

					/**
					 *
					 *	Set correct ID
					 *
					 */
					if ( method_exists($item, 'get_product_id') ) $product_id = $item->get_product_id();
					if ( method_exists($item, 'get_variation_id') ) $variation_id = $item->get_variation_id();
					if ( $variation_id == 0 || empty( $variation_id ) || ! $variation_id ) {

						$active_product_id = $product_id;

					} else {

						$active_product_id = $variation_id;

					}

					/**
					 *
					 *	Standard Product Data
					 *
					 */
					$product = wc_get_product( $active_product_id );
					if ( ! is_object($product) ) continue; 	// Safety Check
					if ( ! is_a( $product, 'WC_Product' ) ) continue;

					// Store vars
					$product_name 							= $product->get_name();
					$product_status 						= $product->get_status();
					$product_sku 							= $product->get_sku();
					$product_link 							= get_permalink( $active_product_id );
					$product_stock_qty 						= $product->get_stock_quantity();
					$product_stock_status 					= $product->get_stock_status();
					$product_type 							= $product->get_type();
					$product_rrp 							= $product->get_regular_price();
					$product_cost_price 					= wpd_ai_get_cost_price_by_product_id( $active_product_id );
					$product_image 							= get_the_post_thumbnail_url( $active_product_id, 'thumbnail' );
					if ( ! is_numeric($product_rrp) ) {
						$product_rrp = 0;
					}

					/**
					 *
					 *	If is product variation, we'll have to check parent ID
					 *
					 */
					if ( $product_type === 'variation' ) {

						$parent_id 			= $product->get_parent_id();
						$product_category	= get_the_terms( $parent_id, 'product_cat' );
						$product_tags 		= get_the_terms( $parent_id, 'product_tag' );

						/**
						 *
						 *	@todo choose to combine variations
						 *
						 */
						$active_product_id = $parent_id;
						$product_image 	   = get_the_post_thumbnail_url( $active_product_id, 'thumbnail' );
						$product 		   = wc_get_product( $active_product_id );
						$product_name 	   = $product->get_name();
						$product_sku 	   = $product->get_sku();

					} else {

						$product_category	= get_the_terms( $active_product_id, 'product_cat' );
						$product_tags 		= get_the_terms( $active_product_id, 'product_tag' );						

					}

					/**
					 *
					 *	Order data
					 *
					 */
					$line_item_product_revenue 				= $item->get_total();
					$line_item_product_revenue_pre_discount = $item->get_subtotal();
					$line_item_product_coupons_applied 		= $line_item_product_revenue_pre_discount - $line_item_product_revenue;
					$line_item_product_quantity_sold 		= $item->get_quantity();
					$line_item_product_cost 				= $product_cost_price * $line_item_product_quantity_sold;
					$line_item_profit 						= $line_item_product_revenue - $line_item_product_cost;
					$line_item_amount_refunded 				= abs($order->get_total_refunded_for_item( $item_id ));
					$line_item_qty_refunded 				= abs($order->get_qty_refunded_for_item( $item_id ));


					/**
					 *
					 *	Return unformatted results
					 *	@todo handle category data seperately
					 *
					 */
					$product_data[$active_product_id]['product_image'] 	= '<img src="'.$product_image.'" class="wpd-product-thumbnail">';
					$product_data[$active_product_id]['product_id'] 		= $active_product_id;
					$product_data[$active_product_id]['product_edit_link'] 	= '';
					$product_data[$active_product_id]['product_view_link'] 	= $product_link;
					$product_data[$active_product_id]['product_name'] 		= $product_name;
					$product_data[$active_product_id]['product_sku'] 		= $product_sku;
					$product_data[$active_product_id]['product_type']		= $product_type;
					$product_data[$active_product_id]['product_stock_qty'] 	= $product_stock_qty;
					$product_data[$active_product_id]['product_status'] 	= $product_stock_status;
					$product_data[$active_product_id]['product_rrp'] 		= $product_rrp;
					$product_data[$active_product_id]['product_cost_price'] = $product_cost_price;
					$product_data[$active_product_id]['product_category'] 	= $product_category;
					$product_data[$active_product_id]['product_tags'] 		= $product_tags;

					/**
					 *
					 *	Let's build product totals
					 *
					 */
					$product_revenue_value_rrp = (float) $product_rrp * (float) $line_item_product_quantity_sold;
					$product_discounts_applied = (float) $product_revenue_value_rrp - (float) $line_item_product_revenue;

					// Define variables
					if ( ! isset($product_data[$active_product_id]['total_product_revenue_value_rrp']) ) $product_data[$active_product_id]['total_product_revenue_value_rrp'] = 0;
					if ( ! isset($product_data[$active_product_id]['total_product_revenue']) ) $product_data[$active_product_id]['total_product_revenue'] = 0;
					if ( ! isset($product_data[$active_product_id]['total_product_revenue_pre_discount']) ) $product_data[$active_product_id]['total_product_revenue_pre_discount'] = 0;
					if ( ! isset($product_data[$active_product_id]['total_product_coupons_applied']) ) $product_data[$active_product_id]['total_product_coupons_applied'] = 0;
					if ( ! isset($product_data[$active_product_id]['total_product_discounts_applied']) ) $product_data[$active_product_id]['total_product_discounts_applied'] = 0;
					if ( ! isset($product_data[$active_product_id]['total_product_cost']) ) $product_data[$active_product_id]['total_product_cost'] = 0;
					if ( ! isset($product_data[$active_product_id]['total_product_profit']) ) $product_data[$active_product_id]['total_product_profit'] = 0;
					if ( ! isset($product_data[$active_product_id]['total_quantity_sold']) ) $product_data[$active_product_id]['total_quantity_sold'] = 0;
					if ( ! isset($product_data[$active_product_id]['total_times_refunded']) ) $product_data[$active_product_id]['total_times_refunded'] = 0;
					if ( ! isset($product_data[$active_product_id]['total_refund_amount']) ) $product_data[$active_product_id]['total_refund_amount'] = 0;
					if ( ! isset($product_data[$active_product_id]['product_sales_per_day']) ) $product_data[$active_product_id]['product_sales_per_day'] = 0;
					if ( ! isset($product_data[$active_product_id]['total_number_of_sales']) ) $product_data[$active_product_id]['total_number_of_sales'] = 0;

					$product_data[$active_product_id]['total_product_revenue_value_rrp'] 		+= $product_revenue_value_rrp;
					$product_data[$active_product_id]['total_product_revenue'] 					+= $line_item_product_revenue;
					$product_data[$active_product_id]['total_product_revenue_pre_discount'] 	+= $line_item_product_revenue_pre_discount;
					$product_data[$active_product_id]['total_product_coupons_applied'] 			+= $line_item_product_coupons_applied;
					$product_data[$active_product_id]['total_product_discounts_applied'] 		+= $product_discounts_applied;
					$product_data[$active_product_id]['total_product_cost'] 					+= $line_item_product_cost;
					$product_data[$active_product_id]['total_product_profit'] 					+= $line_item_profit;
					$product_data[$active_product_id]['total_quantity_sold'] 					+= $line_item_product_quantity_sold;
					$product_data[$active_product_id]['total_times_refunded'] 					+= $line_item_qty_refunded;
					$product_data[$active_product_id]['total_refund_amount'] 					+= $line_item_amount_refunded;
					$product_data[$active_product_id]['product_sales_per_day'] 					= 'TBD';
					$product_data[$active_product_id]['total_number_of_sales']++;					

					$purchases_rate = wpd_ai_calculate_percentage(
						$product_data[$active_product_id]['total_number_of_sales'],
						$total_order_count
					);
					$product_data[$active_product_id]['purchases_rate'] = $purchases_rate . '%';

					$average_margin = wpd_ai_calculate_percentage( 
						$product_data[$active_product_id]['total_product_profit'], 
						$product_data[$active_product_id]['total_product_revenue']
					);
					$product_data[$active_product_id]['average_margin'] = $average_margin;

					$average_discount = wpd_ai_calculate_percentage( 
						$product_data[$active_product_id]['total_product_discounts_applied'], 
						$product_data[$active_product_id]['total_product_revenue_value_rrp']
					);
					$product_data[$active_product_id]['average_discount'] = $average_discount;

				}

			}

		}

		/**
		 *
		 *	Setup Totals
		 *
		 */
		$totals = array (

			'total_revenue' 				=> $total_revenue,
			'total_cost' 					=> $total_cost,
			'total_profit' 					=> $total_profit,
			'average_margin'				=> $margin_sum / $total_order_count,
			'total_records' 				=> $total_order_count,
			'total_shipping_charged' 		=> $total_shipping_charged,
			'total_shipping_cost' 			=> $total_shipping_cost,
			'total_product_cost' 			=> $total_product_cost,
			'total_refunds' 				=> $total_refunds,
			'total_payment_gateway_costs' 	=> $total_payment_gateway_costs,
			'total_tax_paid' 				=> $total_tax_paid,
			'total_discounts_applied' 		=> $total_discounts_applied,
			'payment_gateway' 				=> $payment_gateway_array,
			'total_product_revenue' 		=> $total_product_revenue,
			'total_product_revenue_at_rrp' 	=> $total_product_revenue_at_rrp,
			'total_qty_sold' 				=> $total_qty_sold,
			'total_skus_sold' 				=> $total_skus_sold,
			'highest_revenue' 				=> $highest_revenue,
			'highest_cost' 					=> $highest_cost,
			'highest_profit' 				=> $highest_profit,

		);

		/**
		 *
		 *	Store my data in properties
		 *
		 */
		$this->data 			= $results;
		$this->data_totals 		= $totals;
		$this->product_data 	= $product_data;

		// Return results if required
		return $results;

	}

	/**
	 *
	 *	Create date data
	 *
	 */
	public function data_by_date() {

		// Orders
		$orders 		= $this->data;
		$max_date 		= $this->selected_date_range('end'); 	// date in the past
        $min_date 		= $this->selected_date_range('start'); 	// current date
		$date_range 	= $this->date_range($min_date, $max_date, '+1 day', 'Y-m-d' ); // Test changing from Y-m-d to U

		$revenue_by_day = $profit_by_day = array(
			'Mon' => 0,
			'Tue' => 0,
			'Wed' => 0,
			'Thu' => 0,
			'Fri' => 0,
			'Sat' => 0,
			'Sun' => 0,
		);
		$revenue_by_time = $profit_by_time = array(
			'12am' => 0,
			'1am' => 0,
			'2am' => 0,
			'3am' => 0,
			'4am' => 0,
			'5am' => 0,
			'6am' => 0,
			'7am' => 0,
			'8am' => 0,
			'9am' => 0,
			'10am' => 0,
			'11am' => 0,
			'12pm' => 0,
			'1pm' => 0,
			'2pm' => 0,
			'3pm' => 0,
			'4pm' => 0,
			'5pm' => 0,
			'6pm' => 0,
			'7pm' => 0,
			'8pm' => 0,
			'9pm' => 0,
			'10pm' => 0,
			'11pm' => 0,
		);

		/**
		 *
		 *	Setup 
		 *
		 */
		foreach ( $date_range as $date_array_val ) {

			$revenue_array[$date_array_val] = array(
				'x'	=>	$date_array_val,
				'y' =>	0			
			);

			$profit_array[$date_array_val] = array(
				'x'	=>	$date_array_val,
				'y' =>	0
			);

			$expense_array[$date_array_val] = array(
				'x'	=>	$date_array_val,
				'y' =>	0
			);

			$total_profit_array[$date_array_val] = array(
				'x'	=>	$date_array_val,
				'y' =>	0
			);

		}

		/**
		 *
		 *	Store revenue and profit as summed array against date
		 *
		 */
		foreach ( $orders as $order ) {

	        $date 						=  $order['standard_date'];
			$revenue_array[$date]['y'] 	+= $order['unformatted_revenue'];
			$profit_array[$date]['y'] 	+= $order['unformatted_profit'];

			$day 						=  date('D', strtotime($date));
			$revenue_by_day[$day] 		+= $order['unformatted_revenue'];
			$profit_by_day[$day] 		+= $order['unformatted_profit'];

			$time 						=  $order['time_hour'];
			$revenue_by_time[$time]		+= $order['unformatted_revenue'];
			$profit_by_time[$time]		+= $order['unformatted_profit'];

		}

		/**
		 *
		 *	Expense Data
		 *
		 */
		$expense_data 			= $this->expense_data;
		foreach( $expense_data as $expense ) {

			// Date Paid
			$date = $expense['raw_date'];
			$expense_array[$date]['y'] += $expense['amount_paid_converted'];

		}

		/**
		 *
		 *	Loop again to get total profit (Order profit minus other expenses)
		 *
		 */
		foreach ( $date_range as $date_array_val ) {

			$total_profit_array[$date_array_val]['y'] = $profit_array[$date_array_val]['y'] - $expense_array[$date_array_val]['y'];

		}

		$this->data_totals_by_date = array(

			'revenue_by_time' 			=> $revenue_by_time,
			'profit_by_time'  			=> $profit_by_time,
			'revenue_by_day' 			=> $revenue_by_day,
			'profit_by_day'  			=> $profit_by_day,
			'order_revenue_by_date' 	=> $revenue_array,
			'order_profit_by_date' 		=> $profit_array,
			'total_expenses_by_date' 	=> $expense_array,
			'total_profit_by_date' 		=> $total_profit_array,

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
	        <div class="wpd-row" style="margin-bottom: 40px;">
	        	<div class="wpd-col-6">
	        		<div class="wpd-section-heading"><h3 style="margin: 0px;">Hi <?php echo esc_attr( wpd_ai_user_greeting() ); ?>, Welcome To Your Dashboard!</h3></div>
	        		<div class="wpd-meta">Showing results for the past <?php echo esc_html( $this->x_days_range() ); ?> days</div>
	        	</div>
	        	<div class="wpd-col-6 pull-right" style="text-align:right;">
		        	<?php echo esc_html( $this->date_selector_html() ); ?>
		        	<?php submit_button('Filter', 'wpd-input primary', 'submit', false); ?>
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

    	// Default
    	$days = 30;

    	if ( isset($_GET['wpd-report-from-date']) && isset($_GET['wpd-report-from-date']) ) {

			$start 	= new DateTime( $_GET['wpd-report-from-date'] );
			$end 	= new DateTime( $_GET['wpd-report-to-date'] );

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

    		if ( isset($_GET['wpd-report-from-date']) && ! empty($_GET['wpd-report-from-date']) ) {
    			$start = date( $format, strtotime($_GET['wpd-report-from-date']) );
    		}

        	return $start;

        } elseif ( $result == 'end' ) {

        	$end = current_time( $format ); 

        	if ( isset($_GET['wpd-report-to-date']) && ! empty($_GET['wpd-report-to-date']) ) {
    			$end = date( $format, strtotime($_GET['wpd-report-to-date']));
    		}

        	return $end;

        }

    }

	/**
	 *
	 *	@return date range || array[]
	 *
	 */
	public function date_range($first, $last, $step = '+1 day', $output_format = 'Y-m-d' ) {

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
		<?php echo wpd_ai_date_picker( $start, 'wpd-report-from-date' ); ?>
		<?php echo wpd_ai_date_picker( $end, 'wpd-report-to-date' ); ?>
		<?php

	}

		/**
	 *
	 *	Order reporting dashboard
	 *
	 */
	public function output_navigation() {

		?>
		<table class="wpd-table-wrap wpd-overview-grid fixed wpd-nav-table">
			<tbody>
				<tr><th colspan="3"><div class="wpd-section-heading">Gain Deeper Insights Into Your Business</div></th></tr>
				<tr>
					<td>
						<a class="wpd-key-insight wpd-nav-orders" href="<?php echo wpd_ai_admin_page_url('reports-orders'); ?>">
							<span class="wpd-nav-icon"><span class="dashicons dashicons-chart-bar"></span> Analytics</span>
							<div class="wpd-insight-wrapper">
								<div class="wpd-icon"><span class="dashicons dashicons-text-page"></span></div>
								<p>Profit By Orders</p>
							</div>
						</a>
					</td>
					<td>
						<a class="wpd-key-insight wpd-nav-products" href="<?php echo wpd_ai_admin_page_url('reports-products'); ?>">
							<span class="wpd-nav-icon"><span class="dashicons dashicons-chart-bar"></span> Analytics</span>
							<div class="wpd-insight-wrapper">
								<div class="wpd-icon"><span class="dashicons dashicons-cart"></span></div>
								<p>Profit By Product & Category</p>
							</div>
						</a>
					</td>
					<td>
						<a class="wpd-key-insight wpd-nav-customers" href="<?php echo wpd_ai_admin_page_url('reports-customers'); ?>">
							<span class="wpd-nav-icon"><span class="dashicons dashicons-chart-bar"></span> Analytics</span>
							<div class="wpd-insight-wrapper">
								<div class="wpd-icon"><span class="dashicons dashicons-admin-users"></span></div>
								<p>Profit By Customer</p>
							</div>
						</a>
					</td>
				</tr>
				<tr>
					<td>
						<a class="wpd-key-insight wpd-nav-expense" href="<?php echo wpd_ai_admin_page_url('reports-expenses'); ?>">
							<span class="wpd-nav-icon"><span class="dashicons dashicons-format-aside"></span> Report</span>
							<div class="wpd-insight-wrapper">
								<div class="wpd-icon"><span class="dashicons dashicons-media-spreadsheet"></span></div>
								<p>Expense Report</p>
							</div>
						</a>
					</td>
					<td>
						<a class="wpd-key-insight wpd-nav-inventory" href="<?php echo wpd_ai_admin_page_url('inventory-management'); ?>">
							<span class="wpd-nav-icon"><span class="dashicons dashicons-format-aside"></span> Report</span>
							<div class="wpd-insight-wrapper">
								<div class="wpd-icon"><span class="dashicons dashicons-list-view"></span></div>
								<p>Inventory Report</p>
							</div>
						</a>
					</td>
					<td>
						<a class="wpd-key-insight wpd-nav-profit-loss" href="<?php echo wpd_ai_admin_page_url('pl-statement'); ?>">
							<span class="wpd-nav-icon"><span class="dashicons dashicons-format-aside"></span> Report</span>
							<div class="wpd-insight-wrapper">
								<div class="wpd-icon"><span class="dashicons dashicons-chart-area"></span></div>
								<p>Profit & Loss Statement</p>
							</div>
						</a>
					</td>
				</tr>
			</tbody>
		</table>
		<?php

	}

	/**
	 *
	 *	Order reporting dashboard
	 *
	 */
	public function output_insights() {

		// Total order data
		$total_order_data 					= $this->data_totals;
		$total_expense_data 				= $this->expense_data_totals;
		$order_data_by_date 				= $this->data_totals_by_date;
		$product_data 						= $this->product_data;
		$n_days_period 						= $this->x_days_range();
    	$start_date  						= $this->selected_date_range('start', 'F j, Y');
		$end_date  							= $this->selected_date_range('end', 'F j, Y');
		$order_count 						= $total_order_data['total_records'];
		$total_revenue 						= $total_order_data['total_revenue'];
		$total_cost 						= $total_order_data['total_cost'];
		$total_profit 						= $total_order_data['total_profit'];
		$average_margin 					= round($total_order_data['average_margin'], 2);
		$average_revenue 					= $total_revenue / $order_count;
		$average_cost 						= $total_cost / $order_count;
		$average_profit 					= $total_profit / $order_count;
		$total_other_expenses 				= $total_expense_data['total_amount'];
		$total_adjusted_profit 				= $total_profit - $total_other_expenses;
		$average_other_expenses_per_order 	= $total_other_expenses / $order_count;
		$average_adjusted_profit_per_order 	= $average_profit - $average_other_expenses_per_order;
		$daily_average_order_revenue 		= $total_revenue / $n_days_period;
		$daily_average_order_cost 			= $total_cost / $n_days_period;
		$daily_average_order_profit 		= $total_profit / $n_days_period;
		$daily_average_order_margin 		= $average_margin;
		$daily_average_other_expenses 		= $total_other_expenses / $n_days_period;
		$daily_average_adjusted_profit 		= $total_adjusted_profit / $n_days_period;
		$pre_discount_revenue 				= $total_order_data['total_discounts_applied'] + $total_revenue;
		$total_product_discounts 			= $total_order_data['total_product_revenue_at_rrp'] - $total_order_data['total_product_revenue'];
		$product_data_by_profit_desc 		= array_slice( wpd_ai_sort_multi_level_array( $product_data, 'total_product_profit' ), 0, 5);
		$product_data_by_qty_desc 			= array_slice( wpd_ai_sort_multi_level_array( $product_data, 'total_quantity_sold' ), 0, 5);
		$to_do_list 						= wpd_ai_to_do_list();
		?>
		<table class="wpd-table-wrap wpd-overview-grid fixed">
			<tbody>
				<tr>
					<td class="wpd-key-insight centred">
						<div class="wpd-insight-wrapper">
							<p>Total Order Revenue<?php wpd_ai_tooltip('Total revenue (including tax) with refunds subtracted.'); ?></p>
							<div class="wpd-statistic"><?php echo wc_price( $total_revenue )?></div>
							<div class="wpd-meta"><?php echo wc_price( $average_revenue ) ?> AOV / <?php echo wc_price( $total_revenue / $n_days_period ) . ' Per Day' ?></div>
						</div>
					</td>
					<td class="wpd-key-insight centred">
						<div class="wpd-insight-wrapper">
							<p>Total Order Profit<?php wpd_ai_tooltip('Total order revenue minus total order cost.'); ?></p>
							<div class="wpd-statistic"><?php echo wc_price( $total_profit ) ?></div>
							<div class="wpd-meta"><?php echo wpd_ai_calculate_percentage( $total_profit, $total_revenue ); ?>% Of Order Revenue</div>
						</div>
					</td>
					<td class="wpd-key-insight">
						<div class="wpd-insight-wrapper centred">
							<p>Total No. Orders<?php wpd_ai_tooltip('Number of orders found in the given period.'); ?></p>
							<div class="wpd-statistic"><?php echo esc_attr( $total_order_data['total_records'] ); ?></div>
							<div class="wpd-meta"><?php echo esc_attr( round($total_order_data['total_records'] / $n_days_period, 2) ) . ' Orders Per Day' ?></div>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
		<div class="wpd-row wpd-white-block">
			<div class="wpd-section-heading">Overview<?php wpd_ai_tooltip('An overview of sales for the period.'); ?></div>
			<p><?php echo 'Profit Analysis From ' . esc_attr( $start_date ) . ' to ' . esc_attr( $end_date ) . ' (' . esc_attr( $n_days_period ) . ' days)'; ?>	</p>
			<div class="canvas-container" style="position: relative; height: <?php echo esc_attr( $this->chart_height ); ?>; width:100%;">
				<canvas id="order-reporting-chart"></canvas>
			</div>
		</div>
		<?php if ( ! empty( $to_do_list ) ) : ?>
			<div class="wpd-wrapper" id="wpd-to-do-list-wrapper">
				<table class="wpd-table widefat wpd-extra-insights">
					<thead>
						<tr>
							<td><div class="wpd-section-heading">To Do List</div></td>
							<td><a id="dismiss-to-do-list" class="button button-secondary pull-right" href="#">Dismiss To Do List</a></td>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $to_do_list as $key => $value ) : ?>
							<tr>
								<th><span class="wpd-to-do"><span class="dashicons dashicons-yes"></span></span></th>
								<td><?php echo $value; ?></td>
							</tr>
						<?php endforeach; ?>

					</tbody>
				</table>
			</div>
			<?php wpd_ai_javascript_ajax_action( '#dismiss-to-do-list', 'wpd_dismiss_to_do_list' ); ?>
		<?php endif; ?>
		<div class="wpd-wrapper">
			<div class="wpd-section-heading">Your Bottomline<?php wpd_ai_tooltip('An overview of sales for the period.'); ?></div>
			<div class="wpd-col-6">
				<table class="wpd-table widefat fixed" style="margin-bottom: 20px;">
					<tbody>
						<tr>
							<td>
								<div class="wpd-section-heading">Breakdown Of Order Costs<?php wpd_ai_tooltip('Breakdown of your order costs, If you haven\'t entered any shipping costs we\'ll use the shipping amount you\'ve charged.');?></div>
								<div class="canvas-container" style="position: relative; width:100%;">
									<canvas id="order-cost-breakdown-reporting-chart"></canvas>
								</div>
							</td>
						</tr>
					</tbody>
				</table>
				<table class="wpd-table widefat fixed">
					<tbody>
						<tr>
							<td>
								<div class="wpd-section-heading">Breakdown Of Other Expenses (Premium Content)</div>
								<div class="canvas-container" style="position: relative; width:100%;">
									<canvas id="other-expenses-reporting-chart"></canvas>
								</div>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="wpd-col-6 pull-right">
				<div class="wpd-key-insight flat">
					<?php 
						if ( $total_adjusted_profit > 0 ) {
 							echo '<p class="wpd-subtitle">Congratulations! You\'ve been operating at a ' . wpd_ai_calculate_percentage( $total_adjusted_profit, $total_revenue ) . '% profit margin over the past ' . esc_attr( $n_days_period ) . ' days, that\'s awesome!';
						} else {
 							echo '<p class="wpd-subtitle">Looks like you haven\'t quite been profitable over the past ' . esc_attr( $n_days_period ) . ' days, chin up keep pluggin\' away!';
						}
					?>
				</div>
				<table class="wpd-table-wrap wpd-overview-grid" style="margin-bottom: 20px;">
					<tbody>
						<tr>
							<td class="wpd-key-insight flat">
								<div class="wpd-insight-wrapper">
									<p>Bottomline Profit<?php wpd_ai_tooltip('Total order profit minus additional expenses for the given period.'); ?></p>
									<div class="wpd-statistic"><?php echo wc_price( $total_adjusted_profit ) ?></div>
								</div>
							</td>
							<td class="wpd-key-insight flat">
								<div class="wpd-insight-wrapper">
									<p>Average Margin<?php wpd_ai_tooltip('Your average bottom line margin, all things accounted.'); ?></p>
									<div class="wpd-statistic"><?php echo wpd_ai_calculate_percentage( $total_adjusted_profit, $total_revenue ); ?>%</div>
								</div>
							</td>
						</tr>
					</tbody>
				</table>
				<div class="wpd-wrapper">
					<table class="wpd-table widefat wpd-extra-insights fixed">
						<thead>
							<tr><td colspan="2"><div class="wpd-section-heading">Your Profit Summary</div></td></tr>
						</thead>
						<tbody>
							<tr>
								<th>Order Revenue</th>
								<td><?php echo wc_price( $total_revenue )?></td>
							</tr>
							<tr>
								<th>Order Cost</th>
								<td><?php echo wc_price( $total_cost )?></td>
							</tr>
							<tr>
								<th>Other Expenses</th>
								<td><?php echo wc_price( $total_other_expenses ) ?></td>
							</tr>
							<tr class="emphasis">
								<th>Total Profit</th>
								<td><?php echo wc_price( $total_adjusted_profit ) ?></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<!-- Best sellers -->
		<div class="wpd-wrapper">
			<div class="wpd-col-6">
				<table class="wpd-table widefat fixed">
					<thead>
						<th colspan="2"><div class="wpd-section-heading">Top 5 Sellers By Profit</div></th>
						<th>Profit</th>
						<th>Margin</th>
					</thead>
					<tbody>
						<?php foreach( $product_data_by_profit_desc as $key => $value ): ?>
							<tr>
								<th><?php echo wp_kses_post( $value['product_image'] ) ?></th>
								<th><?php echo ucfirst( esc_attr( $value['product_name']) ); ?></th>
								<td><?php echo wc_price( esc_attr( $value['total_product_profit']) ); ?></td>
								<td><?php echo esc_attr( $value['average_margin'] ); ?>%</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<div class="wpd-col-6 pull-right">
				<table class="wpd-table widefat fixed">
					<thead>
						<th colspan="2"><div class="wpd-section-heading">Top 5 Sellers By Quantity</div></th>
						<th>Quantity Sold</th>
						<th>Times Sold</th>
					</thead>
					<tbody>
						<?php foreach( $product_data_by_qty_desc as $key => $value ): ?>
							<tr>
								<th><?php echo wp_kses_post( $value['product_image'] ) ?></th>
								<th><?php echo ucfirst( esc_attr( $value['product_name'] ) ); ?></th>
								<td><?php echo esc_attr( $value['total_quantity_sold'] ); ?></td>
								<td><?php echo esc_attr( $value['total_number_of_sales'] ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
		<?php wpd_ai_chart_defaults(); ?>
		<?php $this->javascript_output(); ?>
		<?php

	}

	/**
	 *
	 *	Bundle all JS together
	 *
	 */
	public function javascript_output() {

		$max_date 				= $this->selected_date_range('end'); 	// date in the past
        $min_date 				= $this->selected_date_range('start'); 	// current date
		$date_range 			= $this->date_range($min_date, $max_date, '+1 day', 'Y-m-d' ); // Changed from Y-m-d to U in order to second the microtime
		$totals_by_date 		= $this->data_totals_by_date;
		$total_order_data 		= $this->data_totals;
		$total_expense_data 	= $this->expense_data_totals;
		$order_data_by_date 	= $this->data_totals_by_date;
		$n_days_period 			= $this->x_days_range();
		$expense_count 			= 0;
		$expense_labels 		= array();
		$expense_values 		= array();

		// Main chart
		$order_revenue 	= json_encode(array_values($totals_by_date['order_revenue_by_date']));
		$order_profit 	= json_encode(array_values($totals_by_date['order_profit_by_date']));
		$total_expenses = json_encode(array_values($totals_by_date['total_expenses_by_date']));
		$total_profit 	= json_encode(array_values($totals_by_date['total_profit_by_date']));

		foreach($total_expense_data['parent_expenses'] as $expense_type) {
			$expense_labels[] = $expense_type['type'];
			$expense_values[] = round($expense_type['total'],2);
			$expense_count++;
		}

		$order_cost_breakdown = array(
			round($total_order_data['total_product_cost'],2), 
			($total_order_data['total_shipping_cost'] == 0) ? round($total_order_data['total_shipping_charged'],2) : round($total_order_data['total_shipping_cost'],2),
			round($total_order_data['total_payment_gateway_costs'],2), 
			round($total_order_data['total_tax_paid'],2),
			round($total_order_data['total_refunds'],2),
		);

		?>
		<script type="text/javascript">
			//Main Sales Report
			jQuery(document).ready(function($) {
				var orderDataProfit 			= <?php echo $order_profit; ?>;
				var orderDataRevenue 			= <?php echo $order_revenue; ?>;
				var expenseData 				= <?php echo $total_expenses; ?>;
				var totalProfitData 			= <?php echo $total_profit; ?>;
            	var sales_report_line_chart 	= document.getElementById("order-reporting-chart");
				var lineChart = new Chart(sales_report_line_chart, {  
	                type: 'line',  
	                data: {  
	                    datasets: [{
	                        label: "Order Revenue",  
	                        backgroundColor: "rgb(132,103,214,0.5)",  
	                        borderColor: "rgb(132,103,214)", 
	                        pointBorderColor: "rgb(132,103,214)",  
	                        pointBackgroundColor: "rgb(132,103,214)",  
	                        pointHoverBackgroundColor: "rgb(132,103,214)",  
	                        pointHoverBorderColor: "rgb(132,103,214,0.5)",  
	                        hidden: false,
	                        data: orderDataRevenue,
	                    }, {  
	                        label: "Order Profit",  
	                        backgroundColor: "rgb(3, 170, 237, 0.5)",  
	                        borderColor: "rgb(3, 170, 237)", 
	                        pointBorderColor: "rgb(3, 170, 237)",  
	                        pointBackgroundColor: "rgb(3, 170, 237)",  
	                        pointHoverBackgroundColor: "rgb(3, 170, 237)",  
	                        pointHoverBorderColor: "rgb(3, 170, 237, 0.5)",  
	                        hidden: false,
	                        data: orderDataProfit
	                    }]  
	                },
	                options: {
	                	responsive: true,
	                	maintainAspectRatio: false,
	                	scales: {
			                x: {
			                    type: "time",
			                    time: {
			                    	unit: 'day',
			                        displayFormats: {
			                        	day: 'd-MMM', // <- display labels for X axis
			                        },
			                        tooltipFormat:'d MMM, Y', // e.g. 5th Jan
			                    },
			                },
			                y: {
			                    title: { // scaleLabel
			                        display: true,
			                        stacked: true,
			                        text: '(<?php echo esc_attr( $this->wc_currency ) ?>)' // labelString
			                    }
			                }
			            },
			            plugins: {
			            	tooltip: {
			            		mode: 'index', // Change to nearest to only show one point
	   							intersect: false,
								callbacks: {
									label: function(tooltipItem, index, tooltipItems, data) {
										var label = tooltipItem.dataset.label || '';
										var value = tooltipItem.formattedValue;
										return label + ' (<?php echo $this->wc_currency ?>) ' + Math.round(value * 100) / 100;

									}
								}
							}, 
			            },
						hover: {
						    mode: 'index', // Change to nearest to only show one point
						    intersect: false,
						},
	                }
	            });
	            jQuery('.change-time-display').on('change', function() {
				    lineChart.options.scales.xAxes[0].time.unit = this.value;
				    lineChart.update();
				});
			});
			// Order Cost Composition Doughnut Chart
			jQuery(document).ready(function() {
				var other_expenses_colour_array = wpdColourArray( 'rgb(132,103,214)', 'rgb(3, 170, 237)', <?php echo esc_attr( $expense_count ) ?> );
				var other_expenses_doughnut_chart = document.getElementById("other-expenses-reporting-chart");
				var other_expenses_doughnut_chart_graph = new Chart(other_expenses_doughnut_chart, {
					type: 'doughnut',
					data: {
						labels: <?php echo json_encode( $expense_labels ); ?>,
						datasets: [{
							data: <?php echo json_encode( $expense_values ); ?>,
							backgroundColor: other_expenses_colour_array,
						}]
					},
					options: {
						plugins: {
							legend: {
								position: 'bottom',
							}
						},
						responsive: true,
						maintainAspectRatio: false,
					}
				});
			});
			// Order Cost Composition Doughnut Chart
			jQuery(document).ready(function() {
				var order_cost_doughnut_chart = document.getElementById("order-cost-breakdown-reporting-chart");
				var order_cost_doughnut_chart_graph = new Chart(order_cost_doughnut_chart, {
					type: 'bar',
					data: {
						labels: ['Cost Of Goods', 'Shipping Costs', 'Payment Gateway Fees', 'Tax Collected', 'Refunds'],
						datasets: [{
							label: 'Order Cost Breakdown',
							data: <?php echo json_encode($order_cost_breakdown); ?>,
							backgroundColor: ["rgb(132,103,214)", "rgb(19,143,221)", "rgb(48, 193, 241)", "rgb(48, 229, 241)", "rgb(48,241,191)"],
						}]
					},
					options : {
						legend: {
							position: 'top',
						},
					}
				});
			});
		</script>
		<?php

	}

}

