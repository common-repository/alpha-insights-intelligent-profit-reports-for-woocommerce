<?php
/**
 *
 * Profit and Loss Statement Page
 *
 * @package Alpha Insights
 * @version 1.0.0
 * @author WPDavies
 * @link https://wpdavies.dev/
 *
 */
defined( 'ABSPATH' ) || exit;

// Main Class
class WPD_AI_Profit_Loss_Statement {

	/**
	 *
	 *	Data
	 *
	 */
	public $data;

	/**
	 *
	 *	Data columns number, used to build table
	 *
	 */
	public $columns = 2;

	/**
	 *
	 *	Store currency
	 *
	 */
	public $store_currency;

	/**
	 *
	 *	Requesting URL for overriding filters
	 *
	 */
	public $requesting_url;

	/**
	 *
	 *	Filter, in case I want to filter the results
	 *
	 */
	public $filter = array();

	/**
	 *
	 *	Constructor
	 *
	 */
	public function __construct( $requesting_url = null ) {

		// Update currency
		$this->store_currency = wpd_ai_get_base_currency();

		if ( $requesting_url ) {

        	$this->requesting_url = $requesting_url;

        }

        $this->load_filters();
        $this->get_data();

	}

	/**
	 *
	 *	Get data
	 *
	 */
	public function get_data() {

		/**
		 *
		 *	Load variables
		 *
		 */
		$results 									= array();
		$start 										= $this->selected_date_range('start');
        $end 										= $this->selected_date_range('end');
        $results['meta']['start_date'] 				= $start;
        $results['meta']['end_date'] 				= $end;
		$results['meta']['store_currency'] 			= $this->store_currency;
        $results['meta']['no_of_days'] 				= $this->x_days_range();
		$no_orders_found 							= 0;
		$no_expenses_found 							= 0;
		$results['meta']['no_of_orders_found'] 		= 0;
		$results['meta']['no_of_expenses_found'] 	= 0;
		$status 									= wpd_ai_paid_order_status();
		$converted_value 							= 0;


		/**
		 *
		 *	Order Query Args
		 *
		 */
		$order_args = array(

		    'limit' 			=> -1,
		    'orderby' 			=> 'date',
		    'order' 			=> 'DESC',
		    'date_created' 		=> $start . "..." . $end, //'2018-02-01...2018-02-28',
		    'type' 				=> 'shop_order', // shop_order_refund <- do this seperately to handle refund data
		    'status' 			=> $status,

		);
		$orders = wc_get_orders( $order_args );

		/**
		 *
		 *	Loop through orders
		 *
		 */
		foreach ( $orders as $order ) {	

			if ( ! is_object($order) ) return false;

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

			$order_id 	= $order->get_id();
			$order_data = wpd_ai_calculate_cost_profit_by_order( $order_id, false );

			if ( ! isset($results['revenue']['total_order_revenue']) ) $results['revenue']['total_order_revenue'] = 0;
			if ( ! isset($results['revenue']['total_order_refunds']) ) $results['revenue']['total_order_refunds'] = 0;
			if ( ! isset($results['revenue']['total_net_revenue']) ) $results['revenue']['total_net_revenue'] = 0;
			if ( ! isset($results['revenue']['cost_of_goods_sold']) ) $results['revenue']['cost_of_goods_sold'] = 0;
			if ( ! isset($results['revenue']['shipping_expenses']) ) $results['revenue']['shipping_expenses'] = 0;
			if ( ! isset($results['revenue']['payment_gateway_expenses']) ) $results['revenue']['payment_gateway_expenses'] = 0;
			if ( ! isset($results['revenue']['sales_tax_owed']) ) $results['revenue']['sales_tax_owed'] = 0;
			if ( ! isset($results['revenue']['gross_profit']) ) $results['revenue']['gross_profit'] = 0;
			if ( ! isset($results['revenue']['total_order_revenue_excluding_taxes']) ) $results['revenue']['total_order_revenue_excluding_taxes'] = 0;
			if ( ! isset($results['revenue']['total_order_profit_after_tax_deduction']) ) $results['revenue']['total_order_profit_after_tax_deduction'] = 0;
			//

			// 
			$results['revenue']['total_order_revenue'] 		+= $order_data['total_order_revenue_before_refunds'];
			$results['revenue']['total_order_revenue_excluding_taxes'] 	+= $order_data['total_order_revenue_excluding_taxes'];
			$results['revenue']['total_order_profit_after_tax_deduction'] 	+= $order_data['total_order_profit_after_tax_deduction'];
			$results['revenue']['total_order_refunds'] 		+= $order_data['total_refund_amount'];
			$results['revenue']['total_net_revenue'] 		+= $order_data['total_order_revenue'];
			$results['revenue']['cost_of_goods_sold'] 		+= $order_data['total_product_cost']; // was total_order_cost
			$results['revenue']['shipping_expenses'] 		+= $order_data['total_shipping_cost'];
			$results['revenue']['payment_gateway_expenses'] += $order_data['payment_gateway_cost'];
			$results['revenue']['sales_tax_owed'] 			+= $order_data['order_tax_paid'];
			$results['revenue']['gross_profit'] 			+= $order_data['total_order_profit'];

			// Increase counter
			$no_orders_found++;
			$results['meta']['no_of_orders_found'] = $no_orders_found++;

		}

		/**
		 *
		 *	Now lets deal with expenses
		 *
		 */
		$data_store 	= array(); // two levels deep
		$expense_args 	= array(

		    'post_type' 		=> 'expense',
		    'post_status' 		=> 'publish',
		    'posts_per_page' 	=> -1,
		    'meta_query' => array(
		        array(
		            'key' 		=> '_wpd_date_paid',
		            'value' 	=> array($start, $end),
		            'compare' 	=> 'BETWEEN',
		            'type' 		=> 'DATE'
		        )
		    ),
		    'orderby' 			=> 'meta_value',
		    'meta_key' 			=> '_wpd_date_paid',
		    'order' 			=> 'DESC',
		);

		$expenses = new WP_Query( $expense_args );

		/**
		 *
		 *	Loop through expenses
		 *
		 */
		$expense_debug_array 			= array();

    	while ( $expenses->have_posts() ) : $expenses->the_post();

	    	$post_id 					= get_the_ID();
	    	$wpd_amount_paid 			= get_post_meta( $post_id, '_wpd_amount_paid', true );
			$wpd_amount_paid_currency 	= get_post_meta( $post_id, '_wpd_amount_paid_currency', true );
			$expense_type 				= get_the_terms( $post_id, 'expense_category' )[0];

			// Debugging
			$expense_debug_array[] 		= $expense_type;

			// Creating name / hierarchy structure
			$expense_type_name 			= $expense_type->name;
			$expense_type_parent_id 	= $expense_type->parent;


			/** 
			 *
			 *	Convert value if required
			 *
			 */
			if ( $wpd_amount_paid_currency != $this->store_currency ) {
				$converted_value = wpd_ai_convert_currency( $wpd_amount_paid_currency, $this->store_currency, $wpd_amount_paid );
			} else {
				$converted_value = $wpd_amount_paid; 
			}

			/**
			 *
			 *	Format into array key/value pair
			 *	If there is a parent/child relationship lets handle that
			 *
			 */
			if ( $expense_type_parent_id > 0 ) {

				// 0. Get parent
				// 1. set hierarchy
				// 2. add the total to sub-category AND parent
				$parent_term 									= get_term( $expense_type_parent_id, 'expense_category' );
				$parent_name 									= $parent_term->name;

				// Store hierarchy
				if ( ! isset($data_store[$parent_name][$expense_type_name]) ) $data_store[$parent_name][$expense_type_name] = 0; 
				$data_store[$parent_name][$expense_type_name] 	+= $converted_value;

				// Store child data
				if ( ! isset($data_store[$parent_name]['total']) ) $data_store[$parent_name]['total'] = 0;
				$data_store[$parent_name]['total'] 				+= $converted_value;

			} else {

				// If there's no parent, store it as is
				if ( ! isset($data_store[$expense_type_name]['total']) ) $data_store[$expense_type_name]['total'] = 0;
				$data_store[$expense_type_name]['total'] 		+= $converted_value;

			}


			// Store results
			if ( ! isset($data_store['total']) ) $data_store['total'] = 0;
			$data_store['total'] 						+= $converted_value;
			$results['expenses'] 						= $data_store;
			$results['meta']['no_of_expenses_found'] 	= $no_expenses_found++;

		endwhile;

		if ( ! isset($results['expenses']['total']) ) {
			$results['expenses']['total'] = 0;
		}

		// Reset
		wp_reset_query();

		// Return all results
		return $this->data = $results;

	}

	/**
     *
     *	Load filters
     *
     */
    public function load_filters() {

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
     *	Filter HTML
     *
     */
    public function output_filters() {

		$data = $this->data;

		// Start Date
        $start_date = DateTime::createFromFormat( 'Y-m-d', $this->selected_date_range('start') );
		$output_start_date = $start_date->format('F j, Y');

		// End Date
        $end_date = DateTime::createFromFormat('Y-m-d', $this->selected_date_range('end'));
		$output_end_date = $end_date->format('F j, Y');

		?>
			<div class="wpd-white-block wpd-filter wpd-premium-content">
				<?php wpd_ai_premium_content_overlay(); ?>
		        <div class="wrapper">
	        		<div class="wpd-col-10">
		        		<div class="wpd-section-heading">Filter</div>
	        			<div class="wpd-filter-wrapper">
	        				<?php echo esc_html( $this->date_selector_html() ); ?>
	        			</div>
	        			<div class="wpd-filter-wrapper">
	        				<?php submit_button('Filter', 'wpd-input primary', 'submit', false); ?>
	        			</div>
	        			<p>Found <?php echo esc_attr( $data['meta']['no_of_orders_found'] ) ?> orders & <?php echo esc_attr( $data['meta']['no_of_expenses_found'] ) ?> expenses between <?php echo esc_attr( $output_start_date ) ?> and <?php echo esc_attr( $output_end_date ) ?> (<?php echo esc_attr( $data['meta']['no_of_days'] ); ?> days).</p>
	        		</div>
		        	<div class="wpd-col-2" style="text-align:center;">
		        		<table class="fixed">
		        			<tr>
		        				<td>
		        					<table class="fixed">
		        						<tr>
		        							<td style="vertical-align: top;"><?php wpd_ai_export_to_pdf_icon( 'export-pl-statement-to-pdf', 'Export P&L To PDF' ); ?></td>
		        							<td style="vertical-align: top;"><?php wpd_ai_export_to_csv_icon( 'export-pl-statement-to-csv', 'Export P&L To CSV' ); ?></td>
		        						</tr>
		        					</table>
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
	 *	Display report
	 *	@link https://corporatefinanceinstitute.com/resources/templates/excel-modeling/pl-profit-and-loss-template/
	 *
	 */
	public function display_report( ) {

		$this->output_filters();
		echo $this->return_report();
		$this->javascript_output();

	}

	/**
	 *
	 *	Build Report
	 *
	 */
	public function return_report() {

		// Sorry mate, not in the free version :)
		return false;

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
     *	Javascript
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

}