<?php 
/**
 *
 * Expense Report
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

class WPD_AI_Expense_Reports extends WP_List_Table {

	/**
	 *
	 *	This is our base currency
	 *
	 */
	public $wc_currency;

	/**
	 *
	 *	This is our base currency
	 *
	 */
	public $raw_data = array();

	/**
	 *
	 *	This is our base currency
	 *
	 */
	public $data_totals = array();

	/**
	 *
	 *	Chart Height
	 *
	 */
	public $chart_height = '400px';

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
                
        /**
         *
         *	Set options for parent class
         *
         */
        parent::__construct( array(

            'singular'  => 'expense',     //singular name of the listed records
            'plural'    => 'expenses',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?

        ) );

		if ( $requesting_url ) {

	    	$this->requesting_url = $requesting_url;

	    }

        $this->wc_currency = wpd_ai_get_base_currency();
        $this->load_filters(); // stores $_GET values
       	$this->raw_data();
       	
    }

    /**
	 *
	 *	Get the data we want and return to table
	 *	@todo sort by date paid in reverse
	 *
	 */
	public function raw_data() {

		$start 					= $this->selected_date_range('start'); 	// date in the past
        $end 					= $this->selected_date_range('end'); 	// current date
        $filter 				= $this->filter;
        $totals 				= array();
        $expense_by_type 		= array();
        $count 					= 0;
        $total_amount_paid 		= 0;
		$parent_expense_by_type = array();
		$child_expense_by_type 	= array();
		$results 				= array();

        /**
         *
         *	Setup filter
         *
         */
        if ( isset( $filter['expense_type' ]) && ! empty( $filter['expense_type'] ) ) {
        	$tax_args = array(
		        array (
		            'taxonomy' 	=> 'expense_category',
		            'field' 	=> 'slug',
		            'terms' 	=> $filter['expense_type'],
		        )
		    );
        } else {

        	$tax_args = array();

        }

		$args = array(

		    'post_type' 		=> 'expense',
		    'post_status' 		=> 'publish',
		    'posts_per_page' 	=> -1,
		    'meta_query' 		=> array(
		        array(
		            'key' 		=> '_wpd_date_paid',
		            'value' 	=> array($start, $end),
		            'compare' 	=> 'BETWEEN',
		            'type' 		=> 'DATE'
		        )
		    ),
		   	'tax_query' 		=> $tax_args,
		    'orderby' 			=> 'meta_value',
		    'meta_key' 			=> '_wpd_date_paid',
		    'order' 			=> 'DESC',
		);

		$loop = new WP_Query( $args );

    	while ( $loop->have_posts() ) : $loop->the_post();

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

	    	$post_id 					= get_the_ID();
			$child_category_name 		= null;
			$child_expense_slug 		= null;
			$category_name 				= null;
			$parent_category_name 		= null;
			$expense_type 				= null;
			$parent_expense_slug 		= null;
	    	$wpd_amount_paid 			= get_post_meta( $post_id, '_wpd_amount_paid', true );
			$wpd_amount_paid_currency 	= get_post_meta( $post_id, '_wpd_amount_paid_currency', true );
			$wpd_date_paid 				= get_post_meta( $post_id, '_wpd_date_paid', true );
			$wpd_expense_reference 		= get_post_meta( $post_id, '_wpd_expense_reference', true );
			$expense_type 				= get_the_terms( $post_id, 'expense_category' );
			$converted_value 			= 0;


			/** 
			 *
			 *	Store a converted value
			 *
			 */
			if ( $wpd_amount_paid_currency != $this->wc_currency ) {
				$converted_value = wpd_ai_convert_currency( $wpd_amount_paid_currency, $this->wc_currency, $wpd_amount_paid );
			} else {
				$converted_value = $wpd_amount_paid; 
			}

			/**
			 *
			 *	Build Totals
			 *
			 */
			$total_amount_paid += $converted_value;
			$count++;

			// Make sure we have expenses
			if ( is_array($expense_type) ) {
				
				/**
				 *
				 *	Build / store expense type tax
				 *
				 */
				foreach( $expense_type as $expense ) {

					if ( $expense->parent === 0 ) {

						// This is a parent expense
						$parent_category_name 	= $expense->name;
						$expense_type 			= $parent_category_name;
						$parent_expense_slug 	= $expense->slug;

					} else {

						// Child expense taxonomy
						$child_category_name 	= $expense->name;
						$child_expense_slug 	= $expense->slug;
						// Parent storage
						$parent_id 				= $expense->parent;
						$parent_category 		= get_term_by( 'id', $parent_id, 'expense_category' );
						$parent_category_name 	= $parent_category->name;
						$expense_type 			= $child_category_name . '<div class="wpd-meta">'.$parent_category_name.'</div>';
						$parent_expense_slug 	= $parent_category->slug;

						// If not set
						if ( ! isset($child_expense_by_type[$child_expense_slug]['count']) ) $child_expense_by_type[$child_expense_slug]['count'] = 0;
						if ( ! isset($child_expense_by_type[$child_expense_slug]['total']) ) $child_expense_by_type[$child_expense_slug]['total'] = 0;
						if ( ! isset($child_expense_by_type[$child_expense_slug]['type']) ) $child_expense_by_type[$child_expense_slug]['type'] = '';

						// Only build child expense data when its been set
						$child_expense_by_type[$child_expense_slug]['count']++;
						$child_expense_by_type[$child_expense_slug]['total'] += $converted_value;
						$child_expense_by_type[$child_expense_slug]['type']  =  $child_category_name;

					}

				}

			}

			/**
			 *
			 *	How much expenses per category
			 *
			 */
			if ( empty($parent_expense_slug) ) {
				$parent_category_name = 'Not Set';
				$parent_expense_slug = 'not-set';
			}
			// If not set
			if ( ! isset($parent_expense_by_type[$parent_expense_slug]['count']) ) $parent_expense_by_type[$parent_expense_slug]['count'] = 0;
			if ( ! isset($parent_expense_by_type[$parent_expense_slug]['total']) ) $parent_expense_by_type[$parent_expense_slug]['total'] = 0;
			if ( ! isset($parent_expense_by_type[$parent_expense_slug]['type']) ) $parent_expense_by_type[$parent_expense_slug]['type'] = '';

			$parent_expense_by_type[$parent_expense_slug]['count']++;
			$parent_expense_by_type[$parent_expense_slug]['total'] += $converted_value;
			$parent_expense_by_type[$parent_expense_slug]['type'] 	= $parent_category_name;

			/**
			 *
			 *	Format and clean up
			 *
			 */
			$formatted_amount_paid = '<div>' . wc_price( $converted_value ) . '</div>';
			$formatted_amount_paid .= '<div class="wpd-meta">' . wc_price( $wpd_amount_paid, array( 'currency' => $wpd_amount_paid_currency ) ) . ' ('.$wpd_amount_paid_currency.')</div>';

			/**
			 *
			 *	Store loop results
			 *
			 */
			$results[] = array(

				'title' 				=> '<a href="'.wpd_ai_admin_post_url( $post_id ).'">' . get_the_title() . '</a><div class="wpd-meta">(ID: '.$post_id.')</div>',
				'raw_date' 				=> $wpd_date_paid,
				'date'					=> date( 'd-M', strtotime($wpd_date_paid) ), //$date_created->format('d-M') , F j, Y
				'column_date'			=> date( 'F j, Y', strtotime($wpd_date_paid) ), //$date_created->format('d-M') , F j, Y
				'reference' 			=> $wpd_expense_reference,
				'amount_paid'			=> $wpd_amount_paid,
				'amount_paid_currency' 	=> $wpd_amount_paid_currency,
				'formatted_amount_paid' => $formatted_amount_paid,
				'amount_paid_converted' => $converted_value,
				'expense_type' 			=> $expense_type,
				'id'					=> $post_id,

			);


		/**
		 *
		 *	End loop
		 *
		 */
		endwhile;
		wp_reset_query();

		/**
		 *
		 *	Begin building and storing data
		 *
		 */
		$parent_expense_by_type = wpd_ai_sort_multi_level_array( $parent_expense_by_type, 'total' );
		$child_expense_by_type = wpd_ai_sort_multi_level_array( $child_expense_by_type, 'total' );

		$totals = array(

			'total_amount'  			=> $total_amount_paid,
			'count' 					=> $count,
			'average_expenses_per_day' 	=> wpd_ai_divide( $total_amount_paid, $this->x_days_range() ),
			'parent_expenses' 			=> $parent_expense_by_type,
			'child_expenses' 			=> $child_expense_by_type,

		);

		$this->data_totals 	= $totals;
		$this->raw_data 	= $results;
		return $results;

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
	public function date_range($first, $last, $step = '+1 day', $output_format = 'd/m/Y' ) {

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
	 *	Get the data we want and return to table
	 *
	 */
	public function output_insights() {

		// Sorry mate, not in the free version :)
		return false;

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
	 *	Define columns to be used
	 *
	 */
	public function get_columns() {

	  $columns = array (

	  	'title' 				=> 'Title',
		'column_date'			=> 'Date Paid',
		'formatted_amount_paid'	=> 'Amount Paid',
		'expense_type' 			=> 'Expense Type',
		'reference' 			=> 'Reference Number',
		'id' 					=> 'ID',

	  );

	  return $columns;

	}

	/**
	 *
	 *	Setup table
	 *
	 */
	public function prepare_items() {

		// Prevent URL string getting too long
		$_SERVER['REQUEST_URI'] = remove_query_arg( '_wp_http_referer', $_SERVER['REQUEST_URI'] );

		// Settings
        $columns 		= $this->get_columns();
        $hidden 		= $this->get_hidden_columns();
        $sortable 		= $this->get_sortable_columns();
        $total_items 	= $this->data_totals['count'];
        $per_page 		= $this->per_page;
        $current_page 	= $this->get_pagenum();

        $this->set_pagination_args( 
        	array(
	            'total_items' => $total_items,
	            'per_page'    => $per_page
        	) 
        );

        $data 					= $this->raw_data;
        $data 					= array_slice( $data, (( $current_page - 1 ) * $per_page), $per_page );
        $this->_column_headers 	= array( $columns, $hidden, $sortable );
        $this->items 			= $data;

	}

	/**
	 *
	 *	Filters
	 *
	 */
	public function output_filters() {

		$totals 		= $this->data_totals;
		$start  		= $this->selected_date_range('start', 'F j, Y');
		$end  			= $this->selected_date_range('end', 'F j, Y');
		$active_filters = $this->filter;
		$active_expense = ( isset( $active_filters['expense_type'] ) ) ? $active_filters['expense_type'] : null;

		?>
			<div class="wpd-white-block wpd-filter wpd-premium-content">
				<?php wpd_ai_premium_content_overlay(); ?>
		        <div class="wrapper">
	        		<div class="wpd-col-10">
		        		<div class="wpd-section-heading">Filter</div>
					    <div class="wpd-filter-wrapper">
        					<label for="wpd-filter[expense_type]" style="display:block;">Filter By Expense Type</label>
        					<?php 
				    			wp_dropdown_categories( 
				    				array(
					    				'taxonomy'			 	=> 'expense_category',
					    				'show_option_none'		=> 'All Expense Types',
					    				'option_none_value'		=> '',
					    				'name' 					=> 'wpd-filter[expense_type]',
					    				'id' 					=> 'filter-by-expense',
					    				'hide_empty' 			=> 0,
					    				'class' 				=> 'wpd-input',
					    				'selected' 				=> $active_expense,
					    				'value_field' 			=> 'slug'
				    				)
				    			); 
			    			?>
		    			</div>
		    			<div class="wpd-filter-wrapper">
		    				<?php echo esc_html( $this->date_selector_html() ); ?>
		    			</div>
		    			<div class="wpd-filter-wrapper">
		    				<?php submit_button('Filter', 'wpd-input primary', 'submit', false); ?>
		    			</div>
	        		</div>
	        		<div class="wpd-col-2">
	        			<table class="fixed">
		        			<tr>
		        				<td>
									<?php wpd_ai_export_to_csv_icon( 'export-expenses-to-csv', 'Export Expense Data To CSV' ); ?>
		        				</td>
		        				<td>
									<?php wpd_ai_export_to_csv_icon( 'export-expense-totals-to-csv', 'Export Expense Totals To CSV' ); ?>
		        				</td>
		        			</tr>
		        		</table>
	        		</div>
			    </div>
			</div>
		<?php

	}

	/**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns() {

        return array('id');

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

	    echo '<tr class="wpd-profit-tracking-report-row wpd-row-order-id-' . $item['ID'] . '" data-order-id="' . $item['ID'] . '">';

	    $this->single_row_columns( $item );

	    echo '</tr>';

	}

	/**
	 *
	 *	Custom Filter
	 *
	 */
	public function extra_tablenav( $which ) {

	    global $wpdb, $testiURL, $tablename, $tablet;

	    if ( $which == "top" ) : ?>

			<div class="actions" style="float:left;">
		        <?php wpd_ai_per_page_selector( $this->per_page ) ?>
		        <?php submit_button('Filter', 'wpd-input primary', 'submit', false); ?>
			</div>

	    <?php endif; 

	    if ( $which == "bottom" ) {

	        //The code that goes after the table is there

	    }

	}

	/**
	 *
	 *	Output javascript
	 *
	 */
	public function output_javascript() {

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