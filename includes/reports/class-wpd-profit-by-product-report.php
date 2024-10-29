<?php 
/**
 *
 * Product Report
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

class WPD_AI_Profit_Reports_Products extends WP_List_Table {

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

        // wpd_ai_debug( $this->data_totals );

    }

    /**
	 *
	 *	Get the data we want and return to table
	 *	@todo change to https://github.com/woocommerce/woocommerce/wiki/wc_get_orders-and-WC_Order_Query#date
	 *
	 */
	public function raw_data() {

		$start 									= $this->selected_date_range('start'); 	// date in the past
        $end 									= $this->selected_date_range('end'); 	// current date
        $number_of_days 						= $this->x_days_range();
        $filter 								= $this->filter;
        $totals_data 							= array();
		$product_data 							= array();
		$product_type_data 						= array();
		$product_cat_data 						= array();
		$product_tag_data 						= array();
		$date_data 								= array();
        $total_order_count 						= 0;
        $total_product_count 					= 0;
        $highest_product_count 					= 0;
        $highest_product_quantity 				= 0;
		$status 								= wpd_ai_paid_order_status();
		$total_product_revenue_value_rrp 		= 0;
		$total_product_revenue_pre_discount 	= 0;
		$total_product_revenue 					= 0;
		$total_product_coupons_applied 			= 0;
		$total_product_discounts_applied 		= 0;
		$total_product_profit 					= 0;
		$total_product_cost 					= 0;
		$total_quantity_sold 					= 0;
		$total_products_refunded 				= 0;
		$total_amount_refunded 					= 0;
		$total_products_sold 					= 0;
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
				wpd_ai_admin_notice( 'You\'ve exhausted your memory usage. Increase your PHP memory limit or reduce the date range. Your current PHP memory limit is ' . $memory_limit . '.' );

				break; // Break the entire process if were hitting the memory limits

			}

			// Used for some date calculations
			$order_date = $order->get_date_created()->getOffsetTimestamp();
			$order_date = date( 'Y-m-d', $order_date );
			$products_per_order = 0;
			$quantity_per_order = 0;

			/**
		     *
		     *	Get order count
		     *
		     */
		    $total_order_count++;
		    if ( ! isset( $date_data[$order_date]['orders_per_day'] ) ) $date_data[$order_date]['orders_per_day'] = 0;
		    $date_data[$order_date]['orders_per_day']++;
			$date_data[$order_date]['date'] = $order_date;

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
					$product_id 	= $item->get_product_id();
					$variation_id 	= $item->get_variation_id();
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

					if ( is_object($product) ) {
					
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
						if ( $filter['combine-variations'] == 'true' ) {

							$active_product_id = $parent_id; // This will determine if it's for variations or variable

						}

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
					 *	Data by Date
					 *
					 */
					if ( ! isset($date_data[$order_date]['total_qty_sold']) ) $date_data[$order_date]['total_qty_sold'] = 0;
					if ( ! isset($date_data[$order_date]['total_qty_refund']) ) $date_data[$order_date]['total_qty_refund'] = 0;
					if ( ! isset($date_data[$order_date]['total_skus_sold']) ) $date_data[$order_date]['total_skus_sold'] = 0;
					
					$date_data[$order_date]['total_qty_sold'] 		+= $line_item_product_quantity_sold;
					$date_data[$order_date]['total_qty_refund'] 	+= $line_item_qty_refunded;
					$date_data[$order_date]['total_skus_sold']++;

					/**
					 *
					 *	Skip free items if we want to
					 *
					 */
					if ( $filter['exclude-free-items'] == 'true' && $line_item_product_revenue == 0 ) {

						continue;

					}

					/**
					 *
					 *	Return unformatted results
					 *	@todo handle category data seperately
					 *
					 */
					$product_data[$active_product_id]['product_image'] 		= '<img src="'.$product_image.'" class="wpd-product-thumbnail">';
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
					$product_revenue_value_rrp = $product_rrp * $line_item_product_quantity_sold;
					$product_discounts_applied = $product_revenue_value_rrp - $line_item_product_revenue;

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
						count($orders)
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

					/**
					 *
					 *	Add a few formatted results
					 *
					 */
					// Revenue
					// Profit
					$product_data[$active_product_id]['formatted_total_product_revenue'] = wc_price($product_data[$active_product_id]['total_product_revenue']);
					$product_data[$active_product_id]['formatted_total_product_profit'] = wc_price($product_data[$active_product_id]['total_product_profit']);


					/**
					 *
					 *	Build Overall Totals
					 *
					 */
					$total_product_revenue_value_rrp 		+= $product_revenue_value_rrp;
					$total_product_revenue_pre_discount 	+= $line_item_product_revenue_pre_discount;
					$total_product_revenue 					+= $line_item_product_revenue;
					$total_product_coupons_applied 			+= $line_item_product_coupons_applied;
					$total_product_discounts_applied 		+= $product_discounts_applied;
					$total_product_profit 					+= $line_item_profit;
					$total_product_cost 					+= $line_item_product_cost;
					$total_quantity_sold 					+= $line_item_product_quantity_sold;
					$total_products_refunded 				+= $line_item_qty_refunded;
					$total_amount_refunded 					+= $line_item_amount_refunded;
					$total_products_sold++;

					/**
					 *
					 *	Product Type Data $product_type_data
					 *	
					 */
					if ( ! isset($product_type_data[$product_type]['revenue'] ) ) $product_type_data[$product_type]['revenue'] = 0;
					if ( ! isset($product_type_data[$product_type]['profit'] ) ) $product_type_data[$product_type]['profit'] = 0;
					if ( ! isset($product_type_data[$product_type]['cost'] ) ) $product_type_data[$product_type]['cost'] = 0;
					if ( ! isset($product_type_data[$product_type]['qty_sold'] ) ) $product_type_data[$product_type]['qty_sold'] = 0;
					if ( ! isset($product_type_data[$product_type]['products_sold'] ) ) $product_type_data[$product_type]['products_sold'] = 0;
					$product_type_data[$product_type]['revenue'] 			+= round($line_item_product_revenue,2);
					$product_type_data[$product_type]['profit']				+= round($line_item_profit,2);
					$product_type_data[$product_type]['cost']				+= round($line_item_product_cost,2);
					$product_type_data[$product_type]['qty_sold']			+= $line_item_product_quantity_sold;
					$product_type_data[$product_type]['products_sold']++;

					/**
					 *
					 *	Product Category Data
					 *
					 */
					if ( is_array($product_category) || is_object($product_category)  ) {
						foreach( $product_category as $cat ) {
							if ( ! isset($product_cat_data[$cat->term_id]['revenue'] ) ) $product_cat_data[$cat->term_id]['revenue'] = 0;
							if ( ! isset($product_cat_data[$cat->term_id]['profit'] ) ) $product_cat_data[$cat->term_id]['profit'] = 0;
							if ( ! isset($product_cat_data[$cat->term_id]['qty_sold'] ) ) $product_cat_data[$cat->term_id]['qty_sold'] = 0;
							if ( ! isset($product_cat_data[$cat->term_id]['products_sold'] ) ) $product_cat_data[$cat->term_id]['products_sold'] = 0;
							$product_cat_data[$cat->term_id]['name'] 				= $cat->name;
							$product_cat_data[$cat->term_id]['revenue'] 			+= round($line_item_product_revenue,2);
							$product_cat_data[$cat->term_id]['profit'] 				+= round($line_item_profit,2);
							$product_cat_data[$cat->term_id]['qty_sold'] 			+= $line_item_product_quantity_sold;
							$product_cat_data[$cat->term_id]['parent_id'] 			= $cat->parent;
							$product_cat_data[$cat->term_id]['top_level_cat'] 		= ( $cat->parent === 0 ) ? 'true' : 'false';
							$product_cat_data[$cat->term_id]['products_sold']++;
						}
					}

					/**
					 *
					 *	Product Tag Data
					 *
					 */
					if ( is_array($product_tags) || is_object($product_tags) ) {
						foreach( $product_tags as $tag ) {

							if ( ! isset($product_tag_data[$tag->term_id]['revenue'] ) ) $product_tag_data[$tag->term_id]['revenue'] = 0;
							if ( ! isset($product_tag_data[$tag->term_id]['profit'] ) ) $product_tag_data[$tag->term_id]['profit'] = 0;
							if ( ! isset($product_tag_data[$tag->term_id]['qty_sold'] ) ) $product_tag_data[$tag->term_id]['qty_sold'] = 0;
							if ( ! isset($product_tag_data[$tag->term_id]['products_sold'] ) ) $product_tag_data[$tag->term_id]['products_sold'] = 0;
							$product_tag_data[$tag->term_id]['name'] 				= $tag->name;
							$product_tag_data[$tag->term_id]['revenue'] 			+= round($line_item_product_revenue,2);
							$product_tag_data[$tag->term_id]['profit'] 				+= round($line_item_profit,2);
							$product_tag_data[$tag->term_id]['qty_sold'] 			+= $line_item_product_quantity_sold;
							$product_tag_data[$tag->term_id]['products_sold']++;
						}
					}

					/**
					 *
					 *	Build highest values
					 *
					 */
					$products_per_order++;
					$quantity_per_order += $line_item_product_quantity_sold;

		        }

		    } 

			/**
			 *
			 *	Store highest values
			 *
			 */
		    if ($highest_product_count < $products_per_order) {
		    	$highest_product_count = $products_per_order;
		    }
		    if ( $highest_product_quantity < $quantity_per_order ) {
		    	$highest_product_quantity = $quantity_per_order;
		    }

		}

		// Sort by revenue descending
		$product_cat_data_reordered = wpd_ai_sort_multi_level_array( $product_cat_data, 'revenue' );
		$product_tag_data_reordered = wpd_ai_sort_multi_level_array( $product_tag_data, 'revenue' );

		/**
		 *
		 *	Setup Totals
		 *
		 */
		$totals_data = array (

			'total_order_count' 					=> $total_order_count,
			'total_order_count_per_day' 			=> round( $total_order_count / $number_of_days, 2 ),
			'total_products_sold' 					=> $total_products_sold,
			'total_products_sold_per_day' 			=> round( $total_products_sold / $number_of_days, 2 ),
			'total_quantity_sold' 					=> $total_quantity_sold,
			'total_quantity_sold_per_day' 			=> round( $total_quantity_sold / $number_of_days, 2 ),
			'total_product_revenue_value_rrp' 		=> $total_product_revenue_value_rrp,
			'total_product_revenue_pre_discount' 	=> $total_product_revenue_pre_discount,
			'total_product_revenue' 				=> $total_product_revenue,
			'total_product_cost' 					=> $total_product_cost,
			'total_product_discounts_applied' 		=> $total_product_discounts_applied,
			'average_product_discount' 				=> wpd_ai_calculate_percentage( $total_product_discounts_applied, $total_product_revenue_value_rrp),
			'total_product_coupons_applied' 		=> $total_product_coupons_applied,
			'total_product_profit' 					=> $total_product_profit,
			'total_product_profit_at_rrp' 			=> $total_product_revenue_value_rrp - $total_product_cost,
			'average_product_profit_per_product' 	=> round($total_product_profit / $total_products_sold, 2 ),
			'average_profit_margin' 				=> wpd_ai_calculate_percentage( $total_product_profit, $total_product_revenue ),
			'average_profit_margin_at_rrp' 			=> wpd_ai_calculate_percentage( $total_product_revenue_value_rrp - $total_product_cost, $total_product_revenue ),
			'total_products_refunded' 				=> $total_products_refunded,
			'total_amount_refunded' 				=> $total_amount_refunded,
			'total_products_sold'					=> $total_products_sold,
			'highest_product_count' 				=> $highest_product_count,
			'highest_product_quantity' 				=> $highest_product_quantity,
			'date_data' 							=> $date_data,
			'product_type_data' 					=> $product_type_data,
			'product_cat_data' 						=> $product_cat_data_reordered,
			'product_tag_data' 						=> $product_tag_data_reordered,

		);

		/**
		 *
		 *	Store my data in properties
		 *
		 */
		$product_data 		= wpd_ai_sort_multi_level_array( $product_data, 'total_product_revenue' );
		$this->data 		= $product_data;
		$this->data_totals 	= $totals_data;

		// Return results if required
		return $product_data;

	}

	/**
	 *
	 *	Create date data
	 *
	 */
	public function data_by_date() {

		// Orders
		$data_totals 		= $this->data_totals;
		$product_date_data 	= $data_totals['date_data'];
		$max_date 			= $this->selected_date_range('end'); 	// date in the past
        $min_date 			= $this->selected_date_range('start'); 	// current date
		$date_range 		= $this->date_range( $min_date, $max_date, '+1 day', 'Y-m-d' );

		/**
		 *
		 *	Setup 
		 *
		 */
		foreach ( $date_range as $date_array_val ) {

			$skus_sold_array[$date_array_val] = array(
				'x'	=>	$date_array_val,
				'y' =>	0			
			);

			$items_sold_array[$date_array_val] = array(
				'x'	=>	$date_array_val,
				'y' =>	0
			);

			$no_of_orders_array[$date_array_val] = array(
				'x'	=>	$date_array_val,
				'y' =>	0
			);

			$no_of_refunds_array[$date_array_val] = array(
				'x'	=>	$date_array_val,
				'y' =>	0
			);

		}

		/**
		 *
		 *	Store revenue and profit as summed array against date
		 *
		 */
		foreach ( $product_date_data as $product_date ) {

	        $date 							=  $product_date['date'];
			$skus_sold_array[$date]['y'] 	+= $product_date['total_skus_sold'];
			$items_sold_array[$date]['y'] 	+= $product_date['total_qty_sold'];
			$no_of_orders_array[$date]['y'] =  $product_date['orders_per_day'];
			$no_of_refunds_array[$date]['y'] =  $product_date['total_qty_refund'];

		}

		$this->data_totals_by_date = array(

			'skus_sold_by_date' 		=> $skus_sold_array,
			'items_sold_by_date' 		=> $items_sold_array,
			'no_order_sold_by_date' 	=> $no_of_orders_array,
			'no_items_refunds_by_date' 	=> $no_of_refunds_array,

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

    		// This is deliberate
    		if ( is_array( $this->requesting_url ) ) {

    			if ( isset( $this->requesting_url['from_date'] ) & ! empty( $this->requesting_url['from_date'] ) ) {
	    			$this->filter['start_date'] = $this->requesting_url['from_date'];
    			}
    			if ( isset( $this->requesting_url['to_date'] ) & ! empty( $this->requesting_url['to_date'] ) ) {
	    			$this->filter['end_date'] = $this->requesting_url['to_date'];
    			}

    		} else {

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

	  	'product_image'						=> 'Image',
		'product_name'						=> 'Product',
		'formatted_total_product_revenue' 	=> 'Revenue',
		'formatted_total_product_profit' 	=> 'Profit',
		'total_number_of_sales' 			=> '# Sales', // How many orders has this been sold in
		'total_quantity_sold'				=> 'Qty Sold',
		'purchases_rate' 					=> 'Purchase Rate',
		'total_times_refunded'  			=> '# Refunds',

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
    public function column_default($item, $column_name) {

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
	public function output_filters() {

		$totals 			= $this->data_totals;
		$start  			= $this->selected_date_range('start', 'F j, Y');
		$end  				= $this->selected_date_range('end', 'F j, Y');

		$filter 			= $this->filter;
		$variations_filter 	= ( ! empty($filter['combine-variations']) ) ? $filter['combine-variations'] : 'false';
		$exclude_free_items_filter 	= ( ! empty($filter['exclude-free-items']) ) ? $filter['exclude-free-items'] : 'false';

		// wpd_ai_debug( $this->data );
		// $string = $totals['total_records'] . ' orders and '. count($this->expense_data) .' expenses were found during period ' . $start . ' to ' . $end;

		?>
			<div class="wpd-white-block wpd-filter">
		        <div class="wrapper">
	        		<div class="wpd-col-10">
		        		<div class="wpd-section-heading">Filter</div>
	        			<div class="wpd-filter-wrapper">
	        				<?php echo esc_html( $this->date_selector_html() ); ?>
	        			</div>
	        			<div class="wpd-filter-wrapper">
	        				<label for="wpd-filter[combine-variations]">Combine Variations <?php wpd_ai_tooltip('This will combine data for product variations into their parent product'); ?></label>
	        				<select class="wpd-input" name="wpd-filter[combine-variations]">
		    					<option value="false" <?php echo wpd_ai_selected_option( 'false', $variations_filter )?>>False</option>
		    					<option value="true" <?php echo wpd_ai_selected_option( 'true', $variations_filter )?>>True</option>
		    				</select>
	        			</div>
	        			<div class="wpd-filter-wrapper">
	        				<label for="wpd-filter[exclude-free-items]">Exclude Free Items<?php wpd_ai_tooltip('This will exclude free products from the calculations. They will however still show up on the overview graph as a product sold.'); ?></label>
	        				<select class="wpd-input" name="wpd-filter[exclude-free-items]">
		    					<option value="false" <?php echo wpd_ai_selected_option( 'false', $exclude_free_items_filter )?>>False</option>
		    					<option value="true" <?php echo wpd_ai_selected_option( 'true', $exclude_free_items_filter )?>>True</option>
		    				</select>
	        			</div>
	        			<div class="wpd-filter-wrapper">
	        				<?php submit_button('Filter', 'wpd-input primary', 'submit', false); ?>
	        			</div>
	        		</div>
		        	<div class="wpd-col-2" style="text-align:center;">
		        		<table class="fixed">
		        			<tr>
		        				<td>
									<?php wpd_ai_export_to_csv_icon( 'export-products-to-csv', 'Export Product Data To CSV' ); ?>
		        				</td>
		        				<td>
									<?php wpd_ai_export_to_csv_icon( 'export-product-totals-to-csv', 'Export Product Totals To CSV' ); ?>
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

