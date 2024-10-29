<?php 
/**
 *
 * Order Reports
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

class WPD_AI_Profit_Reports_Orders extends WP_List_Table {

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
	public $expense_data;

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
	 *	Expense Data
	 *
	 */
	public $expense_data_totals;

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
        $this->wc_currency 			= wpd_ai_get_base_currency(  );
        $this->chart_height 		= '400px';
       	$expense_data 				= new WPD_AI_Expense_Reports( $requesting_url );
		$this->expense_data 		= $expense_data->raw_data;
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

		$start 							= $this->selected_date_range('start'); 	// date in the past
        $end 							= $this->selected_date_range('end'); 	// current date
        $totals  						= array();
        $total_order_count 				= 0;
        $payment_gateway_array 			= array();
    	$highest_revenue 				= 0;
		$highest_cost 					= 0;
		$highest_profit					= 0;
		$status 						= wpd_ai_paid_order_status();
		$total_shipping_charged 		= 0;
		$total_shipping_cost 			= 0;
		$total_product_cost 			= 0;
		$total_product_discounts 		= 0;
		$total_refunds 					= 0;
		$total_payment_gateway_costs 	= 0;
		$total_tax_paid 				= 0;
		$total_coupon_discounts_applied = 0;
		$total_product_revenue 			= 0;
		$total_product_revenue_at_rrp 	= 0;
		$total_qty_sold 				= 0;
		$total_skus_sold 				= 0;
		$total_revenue 					= 0;
		$total_cost 					= 0;
		$total_profit 					= 0;
		$margin_sum 					= 0;

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
				wpd_ai_admin_notice( 'You\'ve exhausted your memory usage. Increase your PHP memory limit or reduce the date range. Your current PHP memory limit is ' . $memory_limit . '.' );

				break; // Break the entire process if were hitting the memory limits

			}

			// Collect order data
			$order_id 	= $order->get_id();
			$order_data = wpd_ai_calculate_cost_profit_by_order( $order_id, false );

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
			$total_product_discounts 		+= $order_data['total_product_discounts'];
			$total_refunds 					+= $order_data['total_refund_amount'];
			$total_payment_gateway_costs 	+= $order_data['payment_gateway_cost'];
			$total_tax_paid 				+= $order_data['order_tax_paid'];
			$total_coupon_discounts_applied += $order_data['total_coupon_discounts_applied'];
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

				'time_hour' 				=> date( 'ga', $date_created ),
				'standard_date' 			=> date( 'Y-m-d', $date_created ),
				'date'						=> date( 'd-M', $date_created ), //$date_created->format('d-M'),
				'column-date'				=> date( 'F j, Y', $date_created ), //$date_created->format('d-M'),
				'payment_gateway' 			=> $payment_gateway,
				'orderid' 					=> $order_id,
				'order-id' 					=> '<a href="'.$admin_order_link.'">#' . $order_id . '</a>',
				'status' 					=> $order_status,
				'status-nice-name' 			=> '<span class="wpd-order-status wpd-status-'.$order_status.'">' . wc_get_order_status_name( $order_status ) . '</span>',
				'unformatted_revenue' 		=> $order_revenue,
				'revenue' 					=> wc_price($order_revenue),
				'unformatted_cost'			=> $order_cost,
				'cost'						=> wc_price($order_cost),
				'unformatted_profit' 		=> $order_profit,
				'profit' 					=> wc_price($order_profit),
				'unformatted_margin' 		=> $order_margin,
				'margin' 					=> round($order_margin, 2) . '%',
				// 'currency'  				=> $currency,
				'ID' 						=> $order_id,
				'shipping_amount_charged' 	=> $order_data['total_shipping_charged'],
				'shipping_costs' 			=> $order_data['total_shipping_cost'],
				'product_cost' 				=> $order_data['total_product_cost'],
				'total_refund_amount' 		=> $order_data['total_refund_amount'],
				'payment_gateway_cost' 		=> $order_data['payment_gateway_cost'],
				'order_tax_paid' 			=> $order_data['order_tax_paid'],
				'total_coupon_discounts_applied' 	=> $order_data['total_coupon_discounts_applied'],
				'total_product_revenue' 	=> $order_data['total_product_revenue'],
				'total_product_revenue_at_rrp' => $order_data['total_product_revenue_at_rrp'],
				'total_quantity_sold' 		=> $order_data['total_qty_sold'],
				'total_skus_sold' 			=> $order_data['total_skus_sold'],
				'total_product_discounts'   => $order_data['total_product_discounts'],

			);

		}

		/**
		 *
		 *	Setup Totals
		 *
		 */
		$total_other_expenses 	= $this->expense_data_totals['total_amount'];
		$n_days_period 			= $this->x_days_range();
		$total_product_discounts = round($total_product_revenue_at_rrp,2) - round($total_product_revenue,2);
		$totals = array (

			'total_revenue' 					=> $total_revenue,
			'total_cost' 						=> $total_cost,
			'total_profit' 						=> $total_profit,
			'total_records' 					=> $total_order_count,
			'total_shipping_charged' 			=> $total_shipping_charged,
			'total_shipping_cost' 				=> $total_shipping_cost,
			'total_product_cost' 				=> $total_product_cost,
			'total_refunds' 					=> $total_refunds,
			'total_payment_gateway_costs' 		=> $total_payment_gateway_costs,
			'total_tax_paid' 					=> $total_tax_paid,
			'total_coupon_discounts_applied' 	=> $total_coupon_discounts_applied,
			'total_product_revenue' 			=> $total_product_revenue,
			'total_product_revenue_at_rrp' 		=> $total_product_revenue_at_rrp,
			'total_qty_sold' 					=> $total_qty_sold,
			'total_skus_sold' 					=> $total_skus_sold,
			'pre_discount_revenue' 				=> $total_coupon_discounts_applied + $total_revenue,
			'total_product_discounts' 			=> $total_product_discounts,
			'payment_gateway' 					=> $payment_gateway_array,
			'highest_revenue' 					=> $highest_revenue,
			'highest_cost' 						=> $highest_cost,
			'highest_profit' 					=> $highest_profit,
			'average_margin'					=> wpd_ai_divide( $margin_sum, $total_order_count, 2 ), // 34.445
			'average_revenue' 					=> wpd_ai_divide( $total_revenue, $total_order_count, 2 ),
			'average_cost'						=> wpd_ai_divide( $total_cost, $total_order_count, 2 ),
			'average_profit' 					=> wpd_ai_divide( $total_profit, $total_order_count, 2 ),
			'other_expenses_in_this_period' 	=> $total_other_expenses,
			'total_adjusted_profit' 			=> $total_profit - $total_other_expenses,
			'average_other_expenses_per_order' 	=> wpd_ai_divide( $total_other_expenses, $total_order_count ),
			'average_adjusted_profit_per_order' => wpd_ai_divide( $total_profit, $total_order_count ) - wpd_ai_divide( $total_other_expenses, $total_order_count ),
			'daily_average_order_revenue' 		=> wpd_ai_divide( $total_revenue, $n_days_period ),
			'daily_average_order_cost' 			=> wpd_ai_divide( $total_cost, $n_days_period ),
			'daily_average_order_profit' 		=> wpd_ai_divide( $total_profit, $n_days_period ),
			'daily_average_other_expenses' 		=> wpd_ai_divide( $total_other_expenses, $n_days_period ),
			'daily_average_adjusted_profit' 	=> ( $total_profit - $total_other_expenses ) / $n_days_period,

		);

		/**
		 *
		 *	Store my data in properties
		 *
		 */
		$this->data 			= $results;
		$this->data_totals 		= $totals;

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
		$date_range 	= $this->date_range($min_date, $max_date, '+1 day', 'Y-m-d' );
		$expense_data 	= $this->expense_data;

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

    	if ( isset( $_GET['wpd-report-from-date'] ) && isset( $_GET['wpd-report-to-date'] ) ) {

			$this->filter['start_date'] 	= preg_replace("([^0-9-])", "", $_GET['wpd-report-from-date']);
			$this->filter['end_date'] 		= preg_replace("([^0-9-])", "", $_GET['wpd-report-to-date']);

		}

    	/**
    	 *
    	 *	For AJAX calls & email args we have to allow a URL to be parsed
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
	    		if (isset($new_filter['wpd-filter'])) $this->filter = $new_filter['wpd-filter'];
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

	    'column-date' 	=> 'Date',
	    'date' 			=> 'Date',
	    'order-id' 		=> 'Order ID',
	    'status'    	=> 'Status',
	    'status-nice-name' => 'Status',
	    'revenue'      	=> 'Revenue',
	    'cost'      	=> 'Cost',
	    'profit'      	=> 'Profit',
	    'margin'      	=> 'Margin',
	    'ID' 			=> 'ID'

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
        $total_items 	= $this->data_totals['total_records'];
        $per_page 		= $this->per_page;
        $current_page 	= $this->get_pagenum();
        $data 			= $this->data;

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

	    echo '<tr class="wpd-table-row" data-order-id="' . $item['ID'] . '">';

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
		        				<td style="position: relative;" class="wpd-premium-content">
		        					<?php wpd_ai_premium_content_overlay(); ?>
									<?php wpd_ai_export_to_csv_icon( 'export-orders-to-csv', 'Export Order Data To CSV' ); ?>
		        				</td>
		        				<td style="position: relative;" class="wpd-premium-content">
		        					<?php wpd_ai_premium_content_overlay(); ?>
									<?php wpd_ai_export_to_csv_icon( 'export-order-totals-to-csv', 'Export Order Totals To CSV' ); ?>
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

		// Total order data
		$total_order_data 					= $this->data_totals;
		$total_expense_data 				= $this->expense_data_totals;
		$order_data_by_date 				= $this->data_totals_by_date;
		$n_days_period 						= $this->x_days_range();
    	$start_date  						= $this->selected_date_range('start', 'F j, Y');
		$end_date  							= $this->selected_date_range('end', 'F j, Y');
		$order_count 						= $total_order_data['total_records'];
		$total_revenue 						= $total_order_data['total_revenue'];
		$total_cost 						= $total_order_data['total_cost'];
		$total_profit 						= $total_order_data['total_profit'];
		$average_margin 					= round($total_order_data['average_margin'], 2);
		$average_revenue 					= $total_order_data['average_revenue'];
		$average_cost 						= $total_order_data['average_cost'];
		$average_profit 					= $total_order_data['average_profit'];
		$total_other_expenses 				= $total_expense_data['total_amount'];
		$total_adjusted_profit 				= $total_order_data['total_adjusted_profit'];
		$average_other_expenses_per_order 	= $total_order_data['average_other_expenses_per_order'];
		$average_adjusted_profit_per_order 	= $total_order_data['average_adjusted_profit_per_order'];
		$daily_average_order_revenue 		= $total_order_data['daily_average_order_revenue'];
		$daily_average_order_cost 			= $total_order_data['daily_average_order_cost'];
		$daily_average_order_profit 		= $total_order_data['daily_average_order_profit'];
		$daily_average_order_margin 		= $average_margin;
		$daily_average_other_expenses 		= $total_order_data['daily_average_other_expenses'];
		$daily_average_adjusted_profit 		= $total_order_data['daily_average_adjusted_profit'];
		$pre_discount_revenue 				= $total_order_data['pre_discount_revenue'];
		$total_product_discounts 			= $total_order_data['total_product_discounts'];

		?>
		<div class="wpd-row wpd-white-block">
			<div class="wpd-section-heading">Overview<?php wpd_ai_tooltip('An overview of sales for the period.'); ?></div>
			<p><?php echo 'Profit Analysis From ' . esc_attr( $start_date ) . ' to ' . esc_attr( $end_date ) . ' (' . esc_attr( $n_days_period ) . ' days)'; ?></p>
			<div class="canvas-container" style="position: relative; height: <?php echo esc_attr( $this->chart_height ); ?>; width:100%;">
				<canvas id="order-reporting-chart"></canvas>
			</div>
		</div>
		<table class="wpd-table-wrap wpd-overview-grid">
			<tbody>
				<tr>
					<td class="wpd-key-insight">
						<div class="wpd-insight-wrapper">
							<p>Total Order Revenue<?php wpd_ai_tooltip('Total revenue (including tax) with refunds subtracted.'); ?></p>
							<div class="wpd-statistic"><?php echo wc_price( $total_revenue )?></div>
							<div class="wpd-meta"><?php echo wc_price( $average_revenue ) ?> AOV / <?php echo wc_price( $total_revenue / $n_days_period ) . ' Per Day' ?></div>
						</div>
					</td>
					<td class="wpd-key-insight">
						<div class="wpd-insight-wrapper">
							<p>Total Order Cost<?php wpd_ai_tooltip('The sum of product cost, shipping cost, payment gateway fees & tax.'); ?></p>
							<div class="wpd-statistic"><?php echo wc_price( $total_cost )?></div>
							<div class="wpd-meta"><?php echo wpd_ai_calculate_percentage( $total_cost, $total_revenue ); ?>% Of Order Revenue</div>
						</div>
					</td>
					<td class="wpd-key-insight">
						<div class="wpd-insight-wrapper">
							<p>Total Order Profit<?php wpd_ai_tooltip('Total order revenue minus total order cost.'); ?></p>
							<div class="wpd-statistic"><?php echo wc_price( $total_profit ) ?></div>
							<div class="wpd-meta"><?php echo wpd_ai_calculate_percentage( $total_profit, $total_revenue ); ?>% Of Order Revenue</div>
						</div>
					</td>
					<td class="wpd-key-insight">
						<div class="wpd-insight-wrapper">
							<p>Total No. Orders<?php wpd_ai_tooltip('Number of orders found in the given period.'); ?></p>
							<div class="wpd-statistic"><?php echo esc_attr( $total_order_data['total_records'] ); ?></div>
							<div class="wpd-meta"><?php echo round($total_order_data['total_records'] / $n_days_period, 2) . ' Orders Per Day' ?></div>
						</div>
					</td>
				</tr>
				<tr>
					<td class="wpd-key-insight">
						<div class="wpd-insight-wrapper">
							<p>Total No. Expenses<?php wpd_ai_tooltip('Number of non order-related expenses found in the given period.'); ?></p>
							<div class="wpd-statistic"><?php echo esc_attr( $total_expense_data['count'] ); ?></div>
							<div class="wpd-meta"><?php echo round( $total_expense_data['count'] / $n_days_period, 2 ) . ' Per Day' ?></div>
						</div>
					</td>
					<td class="wpd-key-insight">
						<div class="wpd-insight-wrapper">
							<p>Additional Expenses<?php wpd_ai_tooltip('Total amount of additional expenses paid during this period.'); ?></p>
							<div class="wpd-statistic"><?php echo wc_price( $total_other_expenses ) ?></div>
							<div class="wpd-meta"><?php echo wpd_ai_calculate_percentage( $total_other_expenses, $total_profit); ?>% Of Total Profits</div>
						</div>
					</td>
					<td colspan="2" class="wpd-key-insight">
						<div class="wpd-insight-wrapper">
							<p>Total Profit<?php wpd_ai_tooltip('Total order profit minus additional expenses for the given period.'); ?></p>
							<div class="wpd-statistic"><?php echo wc_price( $total_adjusted_profit ) ?></div>
							<div class="wpd-meta"><?php echo wpd_ai_calculate_percentage( $total_adjusted_profit, $total_revenue ); ?>% Of Order Revenue</div>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
		<!-- Another section -->
		<div class="wpd-wrapper">
			<div class="wpd-col-6">
				<table class="wpd-table widefat fixed">
					<tbody>
						<tr>
							<td>
								<div class="wpd-section-heading">Revenue & Profit By Day</div>
								<div class="canvas-container" style="position: relative; width:100%;">
									<canvas id="sales-by-day-reporting-chart"></canvas>
								</div>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="wpd-col-6 pull-right">
				<table class="wpd-table widefat fixed">
					<tbody>
						<tr>
							<td>
								<div class="wpd-section-heading">Revenue & Profit By Time</div>
								<div class="canvas-container" style="position: relative; width:100%;">
									<canvas id="sales-by-time-reporting-chart"></canvas>
								</div>

							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<!-- Another section -->
		<div class="wpd-wrapper">
			<div class="wpd-col-6">
				<table class="wpd-table widefat fixed">
					<tbody>
						<tr>
							<td>
								<div class="wpd-section-heading">Breakdown Of Order Costs</div>
								<p><div class="wpd-meta">* If you haven't entered any shipping costs we'll use the shipping amount you've charged.</div></p>
								<div class="canvas-container" style="position: relative; width:100%;">
									<canvas id="order-cost-breakdown-reporting-chart"></canvas>
								</div>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="wpd-col-6 pull-right">
				<table class="wpd-table widefat fixed">
					<tbody>
						<tr>
							<td>
								<div class="wpd-section-heading">Most Popular Payment Gateways</div>
								<p><div class="wpd-meta"></div></p>
								<div class="canvas-container" style="position: relative; width:100%;">
									<canvas id="payment-gateway-reporting-chart"></canvas>
								</div>

							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<table class="wpd-table-wrap">
			<tbody>
				<tr>
					<td>
						<div class="wpd-insight-wrapper wpd-key-insight">
							<p>Average Order Value<?php wpd_ai_tooltip('Average order value for given period.'); ?></p>
							<div class="wpd-statistic"><?php echo wc_price( $average_revenue ) ?></div>
						</div>
					</td>
					<td>
						<div class="wpd-insight-wrapper wpd-key-insight">
							<p>Average Profit Per Order<?php wpd_ai_tooltip('Profit per order, useful for considering your your taget customer acquisition cost.'); ?></p>
							<div class="wpd-statistic"><?php echo wc_price( $average_profit )?></div>
						</div>
					</td>
					<td>
						<div class="wpd-insight-wrapper wpd-key-insight">
							<p>Average No. Items Sold Per Order<?php wpd_ai_tooltip('The average number of SKU\'s per order sold in this period.'); ?></p>
							<div class="wpd-statistic"><?php echo round( wpd_ai_divide($total_order_data['total_skus_sold'], $order_count), 2 ); ?></div>
						</div>
					</td>
					<td>
						<div class="wpd-insight-wrapper wpd-key-insight">
							<p>Your Largest Order<?php wpd_ai_tooltip('This is the biggest order we found in this period.'); ?></p>
							<div class="wpd-statistic"><?php echo wc_price( $total_order_data['highest_revenue'] ); ?></div>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
		<div class="wpd-wrapper">
			<table class="wpd-table widefat fixed">
				<thead>
					<tr>
						<td><div class="wpd-section-heading">Product Insights<?php wpd_ai_tooltip('These statistics are only related to product data within an order.'); ?></div></td>
						<th>Product Revenue</th>
						<th>Product Discounts</th>
						<th>Items Sold</th>
						<th>Quantity Sold</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<th>Total</th>
						<td>
							<?php echo wc_price($total_order_data['total_product_revenue']) ?>
							<br>
							<span class="wpd-meta"><?php echo wpd_ai_calculate_percentage( $total_order_data['total_product_revenue'], $total_revenue ); ?>% Of Total Revenue</span>
						</td>
						<td>
							<?php echo wc_price( $total_product_discounts ); ?>
							<br>
							<span class="wpd-meta"><?php echo wpd_ai_calculate_percentage( $total_product_discounts, $total_revenue ); ?>% Average Product Discount</span>
						</td>
						<td><?php echo esc_attr( $total_order_data['total_skus_sold'] ); ?></td>
						<td><?php echo esc_attr( $total_order_data['total_qty_sold'] ); ?></td>
					</tr>
					<tr>
						<th>Per Order</th>
						<td><?php echo wc_price( $total_order_data['total_product_revenue'] / $order_count ) ?></td>
						<td><?php echo wc_price( $total_product_discounts / $order_count ); ?></td>
						<td><?php echo round( $total_order_data['total_skus_sold'] / $order_count, 2 ); ?></td>
						<td><?php echo round( $total_order_data['total_qty_sold'] / $order_count, 2 ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>

		<div class="wpd-wrapper">
			<div class="wpd-col-6">
				<table class="wpd-table widefat">
					<thead>
						<tr>
							<td><div class="wpd-section-heading">Order Insights</div></td>
							<th>Revenue</th>
							<th>Cost</th>
							<th>Profit</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<th>Per Order<div class="wpd-meta">Average</div></th>
							<td><?php echo wc_price( $average_revenue ) ?></td>
							<td><?php echo wc_price( $average_cost ) ?></td>
							<td><?php echo wc_price( $average_profit ) ?></td>
						</tr>
						<tr>
							<th>Per Day<div class="wpd-meta">Average</div></th>
							<td><?php echo wc_price( $daily_average_order_revenue ) ?></td>
							<td><?php echo wc_price( $daily_average_order_cost ) ?></td>
							<td><?php echo wc_price( $daily_average_order_profit ) ?></td>
						</tr>
						<tr>
							<th>Per 30 Days<div class="wpd-meta">Projected Average</div></th>
							<td><?php echo wc_price( $daily_average_order_revenue * 30 ) ?></td>
							<td><?php echo wc_price( $daily_average_order_cost * 30 ) ?></td>
							<td><?php echo wc_price( $daily_average_order_profit * 30 ) ?></td>
						</tr>
						<tr>
							<th>Per 365 Days<div class="wpd-meta">Projected Average</div></th>
							<td><?php echo wc_price( $daily_average_order_revenue * 365 ) ?></td>
							<td><?php echo wc_price( $daily_average_order_cost * 365 ) ?></td>
							<td><?php echo wc_price( $daily_average_order_profit * 365 ) ?></td>
						</tr>
						<tr>
							<th>Maximum<div class="wpd-meta">Highest Recorded Values</div></th>
							<td><?php echo wc_price( $total_order_data['highest_revenue'] ); ?></td>
							<td><?php echo wc_price( $total_order_data['highest_cost'] ); ?></td>
							<td><?php echo wc_price( $total_order_data['highest_profit'] ); ?></td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="wpd-col-6 pull-right">
				<table class="wpd-table widefat wpd-extra-insights fixed">
					<thead>
						<tr><td colspan="2"><div class="wpd-section-heading">Extra Insights</div></td></tr>
					</thead>
					<tbody>
						<tr>
							<th>Shipping Collected<?php wpd_ai_tooltip('The amount you\'ve charged customers for shipping.'); ?></th>
							<td>
								<?php echo wc_price( $total_order_data['total_shipping_charged'] ); ?>
								<br><span class="wpd-meta"><?php echo wpd_ai_calculate_percentage($total_order_data['total_shipping_charged'], $total_revenue); ?>% Of Revenue</span>
							</td>
						</tr>
						<tr>
							<th>Shipping Costs<?php wpd_ai_tooltip('The amount you\'ve actually paid in shipping.'); ?></th>
							<td>
								<?php echo wc_price( $total_order_data['total_shipping_cost'] ); ?>
								<br><span class="wpd-meta"><?php echo wpd_ai_calculate_percentage( $total_order_data['total_shipping_cost'], $total_order_data['total_shipping_charged']); ?>% Of Shipping Charged</span>
							</td>
						</tr>
						<tr>
							<th>Payment Gateway Costs</th>
							<td>
								<?php echo wc_price( $total_order_data['total_payment_gateway_costs'] ); ?>
								<br><span class="wpd-meta"><?php echo wpd_ai_calculate_percentage( $total_order_data['total_payment_gateway_costs'], $total_revenue ); ?>% Of Revenue</span>
							</td>
						</tr>
						<tr>
							<th>Refunds Paid Out</th>
							<td>
								<?php echo wc_price( $total_order_data['total_refunds'] ); ?>
								<br><span class="wpd-meta"><?php echo wpd_ai_calculate_percentage( $total_order_data['total_refunds'], $total_revenue ); ?>% Of Revenue</span>
							</td>
						</tr>
						<tr>
							<th>Tax Collected</th>
							<td>
								<?php echo wc_price( $total_order_data['total_tax_paid'] ); ?>
								<br><span class="wpd-meta"><?php echo wpd_ai_calculate_percentage( $total_order_data['total_tax_paid'], $total_revenue ); ?>% Of Revenue</span>
							</td>
						</tr>
						<tr>
							<th>Coupon Discounts</th>
							<td>
								<?php echo wc_price( $total_order_data['total_coupon_discounts_applied'] ); ?>
								<br><span class="wpd-meta"><?php echo wpd_ai_calculate_percentage( $total_order_data['total_coupon_discounts_applied'], $pre_discount_revenue ); ?>% Average Order Discount</span>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
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
		$date_range 			= $this->date_range($min_date, $max_date, '+1 day', 'Y-m-d' );
		$totals_by_date 		= $this->data_totals_by_date;
		$total_order_data 		= $this->data_totals;
		$total_expense_data 	= $this->expense_data_totals;
		$order_data_by_date 	= $this->data_totals_by_date;
		$n_days_period 			= $this->x_days_range();

		// Main chart
		$order_revenue 	= json_encode(array_values($totals_by_date['order_revenue_by_date']));
		$order_profit 	= json_encode(array_values($totals_by_date['order_profit_by_date']));
		$total_expenses = json_encode(array_values($totals_by_date['total_expenses_by_date']));
		$total_profit 	= json_encode(array_values($totals_by_date['total_profit_by_date']));

		//Charts ->'Cost Of Goods', 'Shipping', 'Payment Gateway Fees', 'Tax Collected', 'Refunds'
		$order_cost_breakdown = array(
			round($total_order_data['total_product_cost'],2), 
			($total_order_data['total_shipping_cost'] == 0) ? round($total_order_data['total_shipping_charged'],2) : round($total_order_data['total_shipping_cost'],2),
			round($total_order_data['total_payment_gateway_costs'],2), 
			round($total_order_data['total_tax_paid'],2),
			round($total_order_data['total_refunds'],2),
		);

		// Payment Gateway
		$payment_gateway_labels = array_keys( $total_order_data['payment_gateway'] );
		$payment_gateway_values = array_values( $total_order_data['payment_gateway'] );

		// Revenue by day
		$revenue_day_labels 	= array_keys( $order_data_by_date['revenue_by_day'] );
		$revenue_day_values 	= array_values( $order_data_by_date['revenue_by_day'] );

		// Profit by day
		$profit_day_labels 		= array_keys( $order_data_by_date['profit_by_day'] );
		$profit_day_values 		= array_values( $order_data_by_date['profit_by_day'] );

		// Revenue by time
		$revenue_time_labels 	= array_keys( $order_data_by_date['revenue_by_time'] );
		$revenue_time_values 	= array_values( $order_data_by_date['revenue_by_time'] );

		// Profit by time
		$profit_time_labels 	= array_keys( $order_data_by_date['profit_by_time'] );
		$profit_time_values 	= array_values( $order_data_by_date['profit_by_time'] );

		?>
		<?php wpd_ai_chart_defaults(); ?>
		<?php wpd_ai_javascript_ajax( '#export-orders-to-csv', 'wpd_export_orders_to_csv' ); ?>
		<?php wpd_ai_javascript_ajax( '#export-order-totals-to-csv', 'wpd_export_order_totals_to_csv' ); ?>
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
	                    },	{  
	                        label: "Order Profit",  
	                        backgroundColor: "rgb(19,143,221,0.5)",  
	                        borderColor: "rgb(19,143,221)", 
	                        pointBorderColor: "rgb(19,143,221)",  
	                        pointBackgroundColor: "rgb(19,143,221)",  
	                        pointHoverBackgroundColor: "rgb(19,143,221)",  
	                        pointHoverBorderColor: "rgb(19,143,221,0.5)",  
	                        hidden: false,
	                        data: orderDataProfit
	                    },  {  
	                        label: "Other Expenses",  
	                        backgroundColor: "rgb(48, 193, 241, 0.5)",  
	                        borderColor: "rgb(48, 193, 241)", 
	                        pointBorderColor: "rgb(48, 193, 241)",  
	                        pointBackgroundColor: "rgb(48, 193, 241)",  
	                        pointHoverBackgroundColor: "rgb(48, 193, 241)",  
	                        pointHoverBorderColor: "rgb(48, 193, 241, 0.5)",  
	                        hidden: false,
	                        data: expenseData
	                    }, {  
	                        label: "Total Profit",  
	                        backgroundColor: "rgb(48, 229, 241, 0.5)",  
	                        borderColor: "rgb(48, 229, 241)", 
	                        pointBorderColor: "rgb(48, 229, 241)",  
	                        pointBackgroundColor: "rgb(48, 229, 241)",  
	                        pointHoverBackgroundColor: "rgb(48, 229, 241)",  
	                        pointHoverBorderColor: "rgb(48, 229, 241, 0.5)",  
	                        hidden: false,
	                        data: totalProfitData
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
			                        text: '(<?php echo esc_attr( $this->wc_currency ); ?>)' // labelString
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
			//Sales by Day Bar Chart
			jQuery(document).ready(function() {
				var sales_by_day_bar_chart = document.getElementById("sales-by-day-reporting-chart");
				var sales_by_day_bar_cart_graph = new Chart(sales_by_day_bar_chart, {
					type: 'bar',
					data: {
						labels: <?php echo json_encode($revenue_day_labels) ?>,
						datasets: [{
							label: "Revenue",
							data: <?php echo json_encode($revenue_day_values) ?>,
							backgroundColor: "rgb(19,143,221)",
						},{
							label: "Profit",
							data: <?php echo json_encode($profit_day_values) ?>,
							backgroundColor: "rgb(48, 193, 241)",
						}]
					}
				});
			});
			// Sales by Time Bar Chart
			jQuery(document).ready(function() {
				var sales_by_time_bar_chart = document.getElementById("sales-by-time-reporting-chart");
				var sales_by_time_bar_cart_graph = new Chart(sales_by_time_bar_chart, {
					type: 'bar',
					data: {
						labels: <?php echo json_encode($revenue_time_labels) ?>,
						datasets: [{
							label: "Revenue",
							data: <?php echo json_encode($revenue_time_values) ?>,
							backgroundColor: "rgb(19,143,221)",
						},{
							label: "Profit",
							data: <?php echo json_encode($profit_time_values) ?>,
							backgroundColor: "rgb(48, 193, 241)",
						}]
					}
				});
			});
			// Order Cost Composition Doughnut Chart
			jQuery(document).ready(function() {
				var order_cost_doughnut_chart = document.getElementById("order-cost-breakdown-reporting-chart");
				var order_cost_doughnut_chart_graph = new Chart(order_cost_doughnut_chart, {
					type: 'doughnut',
					data: {
						labels: ['Cost Of Goods', 'Shipping Costs', 'Payment Gateway Fees', 'Tax Collected', 'Refunds'],
						datasets: [{
							data: <?php echo json_encode($order_cost_breakdown); ?>,
							backgroundColor: ["rgb(132,103,214)", "rgb(19,143,221)", "rgb(48, 193, 241)", "rgb(48, 229, 241)", "rgb(48,241,191)"],
						}]
					},
					options : {
						plugins : {
							legend: {
								display: true,
								position: 'left',
							},
						},
						responsive: true,
						maintainAspectRatio: false,
					}
				});
			});
			//Payment Gateway Horizontal Bar
			jQuery(document).ready(function() {
				var payment_gateway_doughnut_chart = document.getElementById("payment-gateway-reporting-chart");
				var payment_gateway_doughnut_chart_graph = new Chart(payment_gateway_doughnut_chart, {
					type: 'bar',
					data: {
						labels: <?php echo json_encode($payment_gateway_labels); ?>,
						datasets: [{
							label: 'Order Count',
							data: <?php echo json_encode($payment_gateway_values); ?>,
							backgroundColor: ["rgb(132,103,214)", "rgb(19,143,221)", "rgb(48, 193, 241)", "rgb(48, 229, 241)", "rgb(48,241,191)"],
						}]
					},
					options : {
						plugins : {
							legend: {
								position: 'top',
							},
						},
						indexAxis: 'y',
						scales: {
						    yAxes: [{
						        afterFit: function(scaleInstance) {
						          // scaleInstance.width = 100; // sets the width to 100px
						        }
						    }]
						}
					}
				});
			});
		</script>
		<?php

	}

	/**
	 *
	 *	Prepare and return CSV data
	 *
	 */
	public function csv_data() {

		$data = $this->data;

		// Shape data for CSV
		$target_fields = array( 

			'orderid' 						=> 'Order ID',
			'standard_date' 				=> 'Order Date',
			'time_hour' 					=> 'Hour Of Order',
			'status' 						=> 'Order Status',
			'unformatted_revenue' 			=> 'Order Revenue',
			'unformatted_cost'				=> 'Order Cost',
			'unformatted_profit' 			=> 'Order Profit',
			'unformatted_margin' 			=> 'Margin (%)',
			'payment_gateway' 				=> 'Payment Gateway Used',
			'shipping_amount_charged' 		=> 'Shipped Amount Charged',
			'shipping_costs' 				=> 'Shipping Cost',
			'product_cost' 					=> 'Product Cost',
			'total_refund_amount' 			=> 'Total Refunds',
			'payment_gateway_cost' 			=> 'Payment Gateway Cost',
			'order_tax_paid' 				=> 'Tax Collected',
			'total_coupon_discounts_applied' => 'Total Coupon Discounts Applied',
			'total_product_discounts' 		=> 'Total Product Discounts',
			'total_product_revenue' 		=> 'Total Product Revenue',
			'total_product_revenue_at_rrp' 	=> 'Total Product Revenue (at rrp)',
			'total_quantity_sold' 			=> 'Total Quantity Sold',
			'total_skus_sold' 				=> 'Total SKUs Sold',

		);

		$row_number = 0;
		$csv_results = array();

		/**
		 *
		 *	Store Header Rows
		 *
		 */
		foreach( $target_fields as $key => $value ) {

			$csv_results[$row_number][] = $value;

		}

		/**
		 *
		 *	Store CSV Rows
		 *
		 */
		foreach( $data as $data_row ) {

			$row_number++;

			foreach( $target_fields as $key => $value ) {

				$csv_results[$row_number][] = $data_row[$key];

			}

		}

		return $csv_results;

	}

	/**
	 *
	 *	Prepare and return CSV data
	 *
	 */
	public function csv_data_totals() {

		$data = $this->data_totals;

		// Shape data for CSV
		$target_fields = array( 

			'field'					=> 'Field',
			'value'					=> 'Value',

		);

		$row_number = 0;
		$csv_results = array();

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
		 *	Store CSV Rows
		 *
		 */
		foreach( $data as $key => $value ) {

			if ( is_array($value) ) {

				// Lets go a level deeper
				foreach( $value as $sub_key => $sub_value ) {

					if ( is_array($sub_value) ) {

						// Lets go another level deep
						foreach( $sub_value as $sub_sub_key => $sub_sub_value ) {

							if ( is_array($sub_sub_value) ) {
								continue;
							}

							$csv_results[$row_number][] = wpd_ai_clean_string( $key ) . ' - ' . wpd_ai_clean_string( $sub_key ) . ' (' . wpd_ai_clean_string( $sub_sub_key ) . ')';
							$csv_results[$row_number][] = $sub_sub_value; 
							$row_number++;

						}

					} else {

						$csv_results[$row_number][] = wpd_ai_clean_string( $key ) . ' - ' . wpd_ai_clean_string( $sub_key );
						$csv_results[$row_number][] = $sub_value; 
						$row_number++;

					}

				}

			} else {

				$csv_results[$row_number][] = wpd_ai_clean_string( $key );
				$csv_results[$row_number][] = $value; 
				$row_number++;

			}

		}

		return array_values( $csv_results ); // returning array values fixes any fuck ups in row numbers
		
	}

}

