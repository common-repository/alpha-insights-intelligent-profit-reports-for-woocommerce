<?php 
/**
 *
 * Inventory Report
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

class WPD_AI_Inventory_Management extends WP_List_Table {

	/**
	 *
	 *	This is our base currency
	 *
	 */
	public $wc_currency;

	/**
	 *
	 *	I will store data results here
	 *
	 */
	public $data = array();

	/**
	 *
	 *	I will store data results here
	 *
	 */
	public $total_data;

	/**
	 *
	 *	I will store data results here
	 *	@default 25
	 *
	 */
	public $per_page = 25;

	/**
	 *
	 *	I will store data results here
	 *
	 */
	public $filter = array();

	/**
	 *
	 *	I will store data results here
	 *
	 */
	public $total_records_found;

	/**
	 *
	 *	Array of our product ID's
	 *
	 */
	public $product_ids;

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
    public function __construct( $url = null ) {
                
        /**
         *
         *	Set options for parent class
         *
         */
        parent::__construct( 
        	array(
            	'singular'  => 'product',	//singular name of the listed records
            	'plural'    => 'products',	//plural name of the listed records
            	'ajax'      => false        //does this table support ajax?
        	) 
        );

        if ( $url ) {

        	$this->requesting_url = $url;

        }

        /**
         *
         *	Setup vars / initialize
		 *
		 */
        $this->wc_currency = wpd_ai_get_base_currency();
		$this->load_filters();
        $this->collect_product_ids();
        $this->collect_totals();
    	$this->raw_data();

    }

    /*
     *
     *	Combine all output actions here
     *
     */
    public function output() {

		$this->output_filter();
       	$this->output_insights();
		$this->views();
		$this->prepare_items();
		$this->display();
		$this->javascript();

    }

    /**
	 *
	 *	Get the data we want and return to table
	 *
	 */
	public function raw_data( $all_results = false ) {

		$paged = ( isset($_GET['paged']) ) ? absint( $_GET['paged'] ) : 1;
		$total_count = 0;
		$totals = array(
			'stock_value_at_rrp' => 0,
			'stock_value_at_cost' => 0,
			'stock_value_potential_profit' => 0,
			'stock_value_total_count' => 0
		);

		/**
		 *
		 *	WP Query Args
		 *
		 *	@see https://developer.wordpress.org/reference/functions/get_posts/
		 *
		 */
	  	$args = array(

		    'post_type' 		=> array( 'product', 'product_variation' ),
		    'posts_per_page' 	=> $this->per_page,
		    'paged' 			=> $paged,
		    'post_status'    	=> 'publish',
		    'post__in' 			=> $this->product_ids,
		    'orderby' 			=> 'title',
		    'order' 			=> 'ASC',

		);

		if ( $all_results ) {

			$args['paged'] 			= null;
			$args['posts_per_page'] = -1;

		}

		$query = new WP_Query( $args );

		/**
		 *
		 *	Begin loop
		 *
		 */
		if ( $query->have_posts() ) {

			while ( $query->have_posts() ) {

				$query->the_post();

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

				/**
				 *
				 *	Load basic variables
				 *
				 */
				$product_id 				= get_the_ID();
       			$product 					= wc_get_product( $product_id );
       			if ( ! is_object($product) ) continue; 	// Safety Check
				$type 						= $product->get_type();
				$title 						= $product->get_name();
				$sku 						= $product->get_sku();
				$unformatted_rrp_price 		= $product->get_regular_price();
				$rrp_price 					= wc_price($unformatted_rrp_price);
				$unformatted_cost_price 	= wpd_ai_get_cost_price_by_product_id( $product_id );
				$cost_price 				= wc_price($unformatted_cost_price);
				$admin_edit_link 	 		= wpd_ai_admin_post_url( $product_id );
				$product_image 				= get_the_post_thumbnail_url( $product_id, 'thumbnail' );
				$stock_html	   				= wpd_ai_stock_status_html( $product );
				$stock_on_hand 				= $product->get_stock_quantity();
				$manage_stock 				= $product->get_manage_stock();
				$stock_status 				= $product->get_stock_status();
				$backorders 				= $product->get_backorders();

				if ( $type === 'variation' ) {
					$parent_id 			= $product->get_parent_id();
					$admin_edit_link 	= wpd_ai_admin_post_url( $parent_id);
				}

				if ( empty($sku) ) {
					$sku = 'SKU not set';
				}
				$product_image 				= '<a href="'.$admin_edit_link.'"><img src="'.$product_image.'" class="wpd-product-thumbnail"></a>';
				$product_name 				= '<span class="wpd-product-title">'.$title . '</span><br>' . '<span class="wpd-meta">'.$sku.'</span><br><div class="wpd-meta product-type '.$type.'">'.$type.' product</div>';
				$product_html = '<table><tbody><tr><td>'.$product_image.'</td><td>'.$product_name.'</td></tr></tbody></table>';

				// If it has no rrp price no point in having a cost price
				if ( $unformatted_rrp_price === 0 || empty( $unformatted_rrp_price ) ) {
					$unformatted_rrp_price 	= 0;
					$unformatted_cost_price = 0;
					$cost_price = wc_price( 0 );
				}

				/**
				 *
				 *	Any product but variable products
				 *
				 */
				if ( $product->get_manage_stock() && $unformatted_rrp_price > 0 ) {

					if ( empty($stock_on_hand) || is_null($stock_on_hand) || $stock_on_hand < 0 ) {

						$stock_on_hand = 0;

					}

					$unformatted_stock_value_at_cost 			= $stock_on_hand * $unformatted_cost_price;
					$unformatted_stock_value_at_rrp 			= $stock_on_hand * $unformatted_rrp_price;
					$unformatted_potential_profit				= $unformatted_stock_value_at_rrp - $unformatted_stock_value_at_cost;

					$stock_value_at_cost 						= wc_price($unformatted_stock_value_at_cost);
					$stock_value_at_rrp 						= wc_price($unformatted_stock_value_at_rrp);

					// DO totals
					$totals['stock_value_at_rrp'] 				+= $unformatted_stock_value_at_rrp;
					$totals['stock_value_at_cost'] 				+= $unformatted_stock_value_at_cost;
					$totals['stock_value_potential_profit'] 	+= $unformatted_potential_profit;
					$totals['stock_value_total_count'] 			+= $stock_on_hand;

				} else {

					$stock_on_hand						= 0;
					$unformatted_stock_value_at_cost 	= 0;
					$unformatted_stock_value_at_rrp 	= 0;
					$stock_value_at_cost 				= wc_price( 0 );
					$stock_value_at_rrp 				= wc_price( 0 );

				}

				/**
				 *
				 *	Store results
				 *
				 */
				$results[] = array(

					'raw_title' 				=> $title,
					'title' 					=> $product_name,
					'SKU'						=> $sku,
					'raw_price' 				=> $unformatted_rrp_price,
					'price' 					=> $rrp_price,
					'raw_cost_price' 			=> $unformatted_cost_price,
					'cost_price'				=> $cost_price,
					'stock_on_hand' 			=> $stock_on_hand,
					'stock_html' 				=> $stock_html,
					'raw_stock_value_at_cost' 	=> $unformatted_stock_value_at_cost,
					'stock_value_at_cost' 		=> $stock_value_at_cost,
					'raw_stock_value_at_rrp' 	=> $unformatted_stock_value_at_rrp,
					'stock_value_at_rrp'		=> $stock_value_at_rrp,
					'id' 						=> $product_id,
					'product_image' 			=> $product_image,
					'product_html' 				=> $product_html,
					'manage_stock' 				=> $manage_stock,
					'stock_status' 				=> $stock_status,
					'backorders'				=> $backorders,
					'product_type'				=> $type,

				);

				// Update count
				$total_count++;

			} 



		}
		
		wp_reset_query();

		$this->data = $results;

		return $results;

	}

	/**
	 *
	 *	Seperate lightweight query to collect totals
	 *
	 */
	public function collect_totals() {

		$totals 					= array();
		$total_stock_value_rrp 		= 0;
		$total_stock_value_cost 	= 0;
		$unrealised_profits 		= 0;
		$total_stock_on_hand 		= 0;
		$total_products_found 		= 0;
		$total_products_managed 	= 0;
		$total_products_not_managed = 0;
		$product_ids 				= $this->product_ids;
		$low_stock_threshold 		= get_option( 'woocommerce_notify_low_stock_amount' );
  		$total_low_stock_products 	= 0;
  		$total_out_of_stock_products = 0;
  		$total_backorder_products 	= 0;
  		$product_meta 				= get_post_meta( $product_ids[0] );


	    foreach( $product_ids as $product_id ) {

	    	if ( ! is_numeric($product_id) ) {

	    		continue;

	    	}

	    	$product_price_rrp 		= (float) get_post_meta( $product_id, '_regular_price', true );
	    	$product_price_cost 	= (float) wpd_ai_get_cost_price_by_product_id( $product_id );
	    	$product_profit 		= $product_price_rrp - $product_price_cost;
	    	$product_manage_stock 	= get_post_meta( $product_id, '_manage_stock', true );
	    	$product_stock_qty 		= get_post_meta( $product_id, '_stock', true );
	    	$products_backorders 	= get_post_meta( $product_id, '_backorders', true ); //_backorders || Yes | No

    		// If it has no rrp price no point in having a cost price
			if ( $product_price_rrp === 0 || empty( $product_price_rrp ) ) {
				$product_price_rrp 	= 0;
				$product_price_cost = 0;
			}

	    	if ( $product_manage_stock === 'yes' ) {

	    		// Total number of products
				$total_products_managed++;

	    		// No stock
				if ( $product_stock_qty <= 0 ) {
					$total_out_of_stock_products++;
					if ( $products_backorders === 'yes' ) {
  						$total_backorder_products++;
					}
				} elseif ( $product_stock_qty <= $low_stock_threshold ) {
					// Greater then 0 but lower than stock threshold
					$total_low_stock_products++;
				}


				// This corrects the number for stock on hand count
	    		if ( $product_stock_qty < 0 ) {
	    			$product_stock_qty = 0;
	    		}

				$total_stock_value_rrp 		+= $product_price_rrp * $product_stock_qty;
				$total_stock_value_cost 	+= $product_price_cost * $product_stock_qty;
				$total_stock_on_hand 		+= $product_stock_qty;


	    	} else {

	    		$total_products_not_managed++;

	    	}

	    	$total_products_found++;

	    }

	    $totals['products_found']					= $total_products_found;
	    $totals['products_stock_managed'] 			= $total_products_managed;
	    $totals['products_stock_not_managed'] 		= $total_products_not_managed;
	    $totals['product_stock_value_rrp'] 			= $total_stock_value_rrp;
	    $totals['product_stock_value_cost'] 		= $total_stock_value_cost;
	    $totals['product_stock_quantity'] 			= $total_stock_on_hand;
	    $totals['product_stock_unrealised_profit'] 	= $total_stock_value_rrp - $total_stock_value_cost;
	    $totals['low_stock_products'] 				= $total_low_stock_products;
	    $totals['out_of_stock_products'] 			= $total_out_of_stock_products;
	    $totals['products_on_backorder'] 			= $total_backorder_products;
	    $totals['product_meta'] 					= $product_meta;

		// Store totals
		$this->total_data = $totals;

	}

	/**
	 *
	 *	Return list of target product ID's
	 *	
	 *	This is done to return variations of any products that have been queried
	 *
	 */
	public function collect_product_ids() {

		// 1. Collect list of "product" id's
	  	$args = array(

		    'post_type' 		=> array( 'product', 'product_variation' ),
		    'post_status'    	=> 'publish',
		    'fields' 			=> 'ids',
		    'posts_per_page' 	=> -1,

		);
	  	$args 					= $this->apply_filters_wp_query( $args );
		$query 					= new WP_Query( $args );
		$original_product_ids 	= $query->posts;
		wp_reset_postdata();

		// 2. then collect list of "product_variation" id's
		if ( empty( $query->posts ) ) {

			$children_product_ids = array();

		} else {

			$args = array(
				'post_type'   		=> 'product_variation',
				'post_status' 		=> 'publish',
				'post_parent__in' 	=> $original_product_ids,
				'fields'      		=> 'ids',
				'posts_per_page' 	=> -1,
			);
			$query 					= new WP_Query( $args );
			$children_product_ids 	= $query->posts;
			wp_reset_postdata();	

		}


		// 3. Combine all off the product id's found
		$product_ids 				= array_unique( array_merge( $original_product_ids, $children_product_ids) );

		if ( empty($product_ids) ) {
			$product_ids = array( 'No Product IDs Found' );
		}

		$this->product_ids 			= $product_ids;
		$this->total_records_found 	= count( $this->product_ids );

		return $product_ids;

	}

	/**
	 *
	 *	Apply filter to WP_Query so I can make seperate queries with same filter
	 *
	 */
	public function apply_filters_wp_query( $args ) {

		// Load filter
		$filter 					= $this->filter;
		$category_filter 			= ( ! empty($filter['product-category']) ) ? $filter['product-category'] : array();
		$tag_filter 				= ( ! empty($filter['product-tag']) ) ? $filter['product-tag'] : array();
		$search_filter 				= ( ! empty($filter['search']) ) ? $filter['search'] : '';
		$sku_filter 				= ( ! empty($filter['sku']) ) ? $filter['sku'] : '';
		$manage_stock_filter 		= ( ! empty($filter['stock-management']) ) ? $filter['stock-management'] : '';
		$stock_status_filter 		= ( ! empty($filter['stock-status']) ) ? $filter['stock-status'] : '';
		$stock_quantity_filter 		= ( ! empty($filter['stock-qty-count']) ) ? $filter['stock-qty-count'] : '';
		$stock_comparison_filter 	= ( ! empty($filter['stock-qty-comparison']) ) ? $filter['stock-qty-comparison'] : '=';
		$search_filter 				= ( ! empty($filter['search']) ) ? $filter['search'] : '';

	  	if ( ! empty( $search_filter ) ) {

	  		$args['s'] = $search_filter;

	    }
		if ( ! empty( $sku_filter ) ) {

		    $meta_query = array(

	            'key'           => '_sku',
	            'value'         => $sku_filter,
	            'compare' 		=> 'LIKE',

		    );

		    $args['meta_query'][] = $meta_query;

	    }
	    if ( ! empty( $manage_stock_filter ) ) {

		    $meta_query = array(

	            'key'           => '_manage_stock',
	            'value'         => $manage_stock_filter,
	            'compare' 		=> '=',

		    );

		    $args['meta_query'][] = $meta_query;

	    }
	    if ( ! empty( $stock_status_filter ) ) {

		    $meta_query = array(

	            'key'           => '_stock_status',
	            'value'         => $stock_status_filter,
	            'compare' 		=> '=',

		    );

		    $args['meta_query'][] = $meta_query;

	    }
	   	if ( ! empty( $stock_quantity_filter ) ) {

		    $meta_query = array(

	            'key'           => '_stock',
	            'value'         => $stock_quantity_filter,
	            'compare'       => $stock_comparison_filter,
	            'type' 			=> 'numeric'

		    );

		    $args['meta_query'][] = $meta_query;

	    }
		if ( ! empty( $category_filter ) ) {

			$tax_query = array(

	            'taxonomy'      => 'product_cat',
	            'field' 		=> 'slug',
	            'terms'         => array( $category_filter ),
	            'operator'      => 'IN'

	        );

			$args['tax_query'][] = $tax_query;

	    }
	    if ( ! empty( $tag_filter ) ) {

			$tax_query = array(

	            'taxonomy'      => 'product_tag',
	            'field' 		=> 'slug',
	            'terms'         => array( $tag_filter ),
	            'operator'      => 'IN'

	        );

			$args['tax_query'][] = $tax_query;

	    }

	    return $args;

	}

    /**
     *
     *	Load filters
     *
     */
    public function load_filters() {

    	if ( isset($_GET['wpd-per-page']) && ! empty($_GET['wpd-per-page']) ) {

    		$this->per_page = absint( $_GET['wpd-per-page'] );

    	} else {

    		$this->per_page = 25; // default 25

    	}

    	if (isset($_GET['wpd-filter'])) $this->filter = wc_clean( $_GET['wpd-filter'] );

    	/**
    	 *
    	 *	For AJAX calls we have to allow a URL to be parsed
    	 *	
    	 */
    	if ( ! empty( $this->requesting_url ) ) {

    		// Build filter
    		$parsed_url = parse_url( $this->requesting_url );
    		parse_str( $parsed_url['query'], $new_filter );
    		$this->filter = $new_filter['wpd-filter'];

    	}

    	// Set default operator to = for stock count
    	if ( empty( $this->filter['stock-qty-comparison'] ) ) {
    		$this->filter['stock-qty-comparison'] = '=';
    	}

    }

	/**
	 *
	 *	Define columns to be used
	 *
	 */
	public function get_columns() {

		$columns = array(

			'product_image' 		=> '',
			'title' 				=> 'Product',
			'price' 				=> 'RRP Price',
			'cost_price'			=> 'Cost Price',
			'stock_html' 			=> 'Inventory', // number or status
			'stock_value_at_rrp'	=> 'Value (rrp)',
			'stock_value_at_cost' 	=> 'Value (cost)',
			'id' 					=> 'ID',

		);

		return $columns;

	}

	/**
	 *
	 *	Setup table
	 *
	 */
	function prepare_items() {

		$_SERVER['REQUEST_URI'] = remove_query_arg( '_wp_http_referer', $_SERVER['REQUEST_URI'] );

		// Settings
        $columns 		= $this->get_columns();
        $hidden 		= $this->get_hidden_columns();
        $sortable 		= $this->get_sortable_columns();

        // Load table data
        $data 			= $this->data;
        $per_page 		= $this->per_page;
        $current_page 	= $this->get_pagenum();

        $this->set_pagination_args( 

        	array(

	            'total_items' => $this->total_records_found,
	            'per_page'    => $this->per_page,

        	)

        );

        $this->_column_headers 	= array( $columns, $hidden, $sortable );
        $this->items 			= $data;

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

	    echo '<tr class="wpd-table-row">';

	    $this->single_row_columns( $item );

	    echo '</tr>';

	}

	/**
	 *
	 *	Custom Filter
	 *	@see https://developer.wordpress.org/reference/functions/wp_dropdown_categories/
	 *
	 */
	function extra_tablenav( $which ) {

		?>
			<div class="actions" style="float:left;">
		        <?php wpd_ai_per_page_selector( $this->per_page ) ?>
		        <?php submit_button('Filter', 'wpd-input primary', 'submit', false); ?>
			</div>
		<?php

	}

	/**
	 *
	 *	Output Filters
	 *
	 */
	public function output_filter() {

		$filter 							= $this->filter;
		$category_filter 					= ( ! empty($filter['product-category']) ) ?$filter['product-category'] : '';
		$tag_filter 						= ( ! empty($filter['product-tag']) ) ? $filter['product-tag'] : '';
		$sku_filter 						= ( ! empty($filter['sku']) ) ? $filter['sku'] : '';
		$stock_management_filter 			= ( ! empty($filter['stock-management']) ) ? $filter['stock-management'] : '';
		$stock_status_filter 				= ( ! empty($filter['stock-status']) ) ? $filter['stock-status'] : '';
		$stock_quantity_comparison_filter 	= ( ! empty($filter['stock-qty-comparison']) ) ? $filter['stock-qty-comparison'] : '=';
		$search_filter 						= ( ! empty($filter['search']) ) ? $filter['search'] : '';

		// Needs a bit more specialisation to handle the 0 here
		if ( isset($filter['stock-qty-count']) && $filter['stock-qty-count'] === 0 ) {
			$stock_quantity_count_filter = 0;
		} elseif ( ! isset( $filter['stock-qty-count'] ) ) {
			$stock_quantity_count_filter = null;
		} else {
			$stock_quantity_count_filter = $filter['stock-qty-count'];
		}

		?>
			<div class="wpd-white-block wpd-filter">
		        <div class="wrapper">
					<div class="wpd-col-10">
					<div class="wpd-section-heading">Filter</div>
			        	<div class="wpd-filter-wrapper">
			        		<label for="wpd-filter[product-category]">Product Category Is</label>
		    				<?php wc_product_dropdown_categories( array(
		    					'name' => 'wpd-filter[product-category]',
		    					'selected' => $category_filter,
		    					'class' => 'wpd-input'
		    					) ); ?>
			        	</div>
						<div class="wpd-filter-wrapper">
			        		<label for="wpd-filter[product-tag]">Product Tag Is</label></td>
		    				<?php wc_product_dropdown_categories( array(
		    					'taxonomy' 	=> 'product_tag',
		    					'name' 		=> 'wpd-filter[product-tag]',
		    					'class' 	=> 'wpd-input',
		    					'selected' 	=> $tag_filter
		    					) ); ?>
			        	</div>
			        	<div class="wpd-filter-wrapper">
			        		<label for="wpd-filter[search]">Title / Content Contains</label>
		    				<input class="wpd-input" type="text" name="wpd-filter[search]" value="<?php echo $search_filter ?>" placeholder="shirt">
		    			</div>
			        	<div class="wpd-filter-wrapper">
			        		<label for="wpd-filter[sku]">SKU Contains</label>
		    				<input class="wpd-input" type="text" name="wpd-filter[sku]" value="<?php echo $sku_filter ?>" placeholder="alpha-xl">
		    			</div>
		    			<div class="wpd-filter-wrapper">
			        		<label for="wpd-filter[stock-status]">Stock Status</label>
		    				<select class="wpd-input" name="wpd-filter[stock-status]">
		    					<option value="" <?php echo wpd_ai_selected_option( '', $stock_status_filter )?>>Show All</option>
		    					<option value="instock" <?php echo wpd_ai_selected_option( 'instock', $stock_status_filter )?>>In Stock</option>
		    					<option value="outofstock" <?php echo wpd_ai_selected_option( 'outofstock', $stock_status_filter )?>>Out Of Stock</option>
		    					<option value="available_on_backorder" <?php echo wpd_ai_selected_option( 'available_on_backorder', $stock_status_filter )?>>On Backorder</option>
		    				</select>
		    			</div>
		    			<div class="wpd-filter-wrapper">
			        		<label for="wpd-filter[stock-management]">Stock Management</label>
		    				<select class="wpd-input" name="wpd-filter[stock-management]">
		    					<option value="" <?php echo wpd_ai_selected_option( '', $stock_management_filter )?>>Show All</option>
		    					<option value="yes" <?php echo wpd_ai_selected_option( 'yes', $stock_management_filter )?>>Stock Is Managed</option>
		    					<option value="no" <?php echo wpd_ai_selected_option( 'no', $stock_management_filter )?>>Stock Is Not Managed</option>
		    				</select>
		    			</div>
		    			<div class="wpd-filter-wrapper">
			        		<label for="wpd-filter[stock-qty-comparison]">Stock Count</label>
			        		<select class="wpd-input" name="wpd-filter[stock-qty-comparison]">
		    					<option value="=" <?php echo wpd_ai_selected_option( '=', $stock_quantity_comparison_filter )?>>Equal To (=)</option>
		    					<option value=">" <?php echo wpd_ai_selected_option( '>', $stock_quantity_comparison_filter )?>>Greater Than (>)</option>
		    					<option value="<" <?php echo wpd_ai_selected_option( '<', $stock_quantity_comparison_filter )?>>Less Than (<)</option>
		    				</select>
		    				<input class="wpd-input" type="number" value="<?php echo esc_attr( $stock_quantity_count_filter ) ?>" name="wpd-filter[stock-qty-count]" step="1" placeholder="0" style="width: 100px;">
		    			</div>
		    			<div class="wpd-filter-wrapper">
		    				<?php submit_button('Filter', 'wpd-input primary', 'submit', false); ?>
		    				<a href="<?php echo wpd_ai_admin_page_url('inventory-management'); ?>" class="button button-secondary">Reset Filter</a>
		    			</div>
	    			</div>
	    			<div class="wpd-col-2" style="text-align:center;">
						<?php wpd_ai_export_to_csv_icon( 'export-inventory-to-csv' ); ?>
					</div>
			    </div>
			</div>
		<?php

	}

	/**
	 *
	 *	Get the data we want and return to table
	 *
	 */
	public function output_insights() {

		$data = $this->total_data;

/*		$stock_value_at_rrp 	= wc_price($data['stock_value_at_rrp']);
		$stock_value_at_cost 	= wc_price($data['stock_value_at_cost']);
		$potential_profit 		= wc_price($data['stock_value_potential_profit']);
		$total_stock_count 		= $data['stock_value_total_count'];*/

		?>
		<table class="wpd-table-wrap wpd-overview-grid">
			<tbody>
				<tr>
					<td class="wpd-key-insight">
						<div class="wpd-insight-wrapper">
							<p>Total Stock Value (RRP)<?php wpd_ai_tooltip('This is the sum total of all of your product\'s retail prices which are set to stock management and have a positive amount of stock.'); ?></p>
							<div class="wpd-statistic"><?php echo wc_price($data['product_stock_value_rrp']); ?></div>
						</div>
					</td>
					<td class="wpd-key-insight">
						<div class="wpd-insight-wrapper">
							<p>Total Stock Value (Cost)<?php wpd_ai_tooltip('This is the sum total of all of your product\'s cost prices which are set to stock management and have a positive amount of stock.'); ?></p>
							<div class="wpd-statistic"><?php echo wc_price($data['product_stock_value_cost']); ?></div>
						</div>
					</td>
					<td class="wpd-key-insight">
						<div class="wpd-insight-wrapper">
							<p>Unrealised Profits<?php wpd_ai_tooltip('This is the retail price minus the cost prices of all of your products which have stock management and a positive stock amount.'); ?></p>
							<div class="wpd-statistic"><?php echo wc_price($data['product_stock_unrealised_profit']); ?></div>
						</div>
					</td>
				</tr>
				<tr>
					<td class="wpd-key-insight">
						<div class="wpd-insight-wrapper">
							<p>Total Stock On Hand<?php wpd_ai_tooltip('Total quantity of stock on hand of products which have stock management & a positive stock amount..'); ?></p>
							<div class="wpd-statistic"><?php echo esc_attr( $data['product_stock_quantity'] ); ?></div>
						</div>
					</td>
					<td class="wpd-key-insight">
						<div class="wpd-insight-wrapper">
							<p>Total Records Found<?php wpd_ai_tooltip('Total number of products found (this includes both variables and variations).'); ?></p>
							<div class="wpd-statistic"><?php echo esc_attr( $data['products_found'] ); ?></div>
						</div>
					</td>
					<td class="wpd-key-insight">
						<div class="wpd-insight-wrapper">
							<p>Products With Stock Management<?php wpd_ai_tooltip('Total number of products which have been set to have their stock managed..'); ?></p>
							<div class="wpd-statistic"><?php echo esc_attr( $data['products_stock_managed'] ); ?></div>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
		<?php

	}

	/**
	 *
	 *	JS Used for AJAX
	 *
	 */
	public function javascript() {

		wpd_ai_javascript_ajax( '#export-inventory-to-csv', 'wpd_export_inventory_to_csv' );

	}

	/**
	 *
	 *	Prepare and return CSV data
	 *
	 */
	public function csv_data() {

		$data = $this->raw_data( true ); // set to true to collect all data

		// Shape data for CSV
		$target_fields = array( 

			'raw_title' 				=> 'Title',
			'SKU'						=> 'SKU',
			'product_type'				=> 'Product Type',
			'raw_price'					=> 'Price (RRP)',
			'raw_cost_price'			=> 'Cost Price',
			'stock_on_hand'	 			=> 'Stock On Hand',
			'raw_stock_value_at_rrp' 	=> 'Stock Value (RRP)',
			'raw_stock_value_at_cost' 	=> 'Stock Value (Cost Price)',
			'id' 						=> 'Product ID',
			'manage_stock' 				=> 'Stock Management',
			'stock_status' 				=> 'Stock Status',
			'backorders'				=> 'Backorders',

		);

		$csv_results = wpd_ai_prepare_csv_data( $data, $target_fields );

		return $csv_results;

	}

}