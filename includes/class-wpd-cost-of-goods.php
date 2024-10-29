<?php
/**
 *
 * Core profit tracking functionality
 *
 * Add meta boxes to orders and products, save data, make calculations
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
 *	Main Class
 *
 */
class WPD_AI_Profit_Tracking {

	public function __construct() {

		/**
		 *
		 *	Manage order data
		 *
		 */
		// Admin Meta Boxes
		add_action( 'add_meta_boxes', 								array( $this, 'register_order_admin_meta_box_cost_profit' ) );
		add_action( 'add_meta_boxes', 								array( $this, 'register_order_admin_meta_box_ad_campaign' ) );
		add_action( 'woocommerce_process_shop_order_meta',			array( $this, 'save_order_details' ) );

		// Admin Order Columns
		add_filter( 'manage_edit-shop_order_columns', 				array( $this, 'actual_order_profit' ) );
		add_action( 'manage_shop_order_posts_custom_column', 		array( $this, 'add_order_profit_column_content' ) );

		// Save landing page
		add_action( 'woocommerce_checkout_update_order_meta', 		array( $this, 'save_landing_page_to_order_meta') );

		// Admin order meta
		add_action( 'woocommerce_admin_order_item_headers', 		array( $this, 'add_cost_price_to_admin_order_meta_line_item_heading' ) );
		add_action( 'woocommerce_admin_order_item_values', 			array( $this, 'add_cost_price_to_admin_order_meta_line_item' ), 10, 3 );
		add_action( 'woocommerce_admin_order_totals_after_tax', 	array( $this, 'show_profit_in_order_summary'), 10, 1 );
		add_action( 'woocommerce_before_order_itemmeta',		 	array( $this, 'shipping_cost_price_input_admin_order_page' ), PHP_INT_MAX, 3 );

		/**
		 *
		 *	Product inputs & data processing
		 *
		 */
		// Product purchasing information tab
		add_action( 'woocommerce_product_data_panels', 				array( $this, 'purchasing_info_data_tab_content' ) );
		add_filter( 'woocommerce_product_data_tabs', 				array( $this, 'product_editor_cost_of_goods_data_tab') );

		// Save and process data inputs other than variations
		add_action( 'save_post_product',                            array( $this, 'save_product_cog_data' ), PHP_INT_MAX, 2 );

		// Handle variation data
		add_action( 'woocommerce_variation_options_pricing',        array( $this, 'cost_price_input_html_for_variations' ), 10, 3 );
		add_action( 'woocommerce_save_product_variation',           array( $this, 'save_cost_price_for_variations' ), PHP_INT_MAX, 2 );

		// Set default values when an order is created
		add_action( 'woocommerce_new_order', 						array( $this, 'calculate_cost_profit' ), 10, 1  );

	}

	/**
	 *
	 *	Add input to shipping field
	 *
	 */
	public function shipping_cost_price_input_admin_order_page( $item_id, $item, $product ) {

		if ( 'WC_Order_Item_Shipping' === get_class( $item ) ) {

			global $woocommerce, $post;
			$order 					= new WC_Order($post->ID);
			if ( ! is_object($product) ) return false;
			$total_shipping_cost 	= get_post_meta( $order->get_id(), '_total_shipping_cost', true );

			if ( ! empty($total_shipping_cost) ) {
				?>
					<table cellspacing="0" class="display_meta">
						<tbody>
							<tr>
								<th>Shipping Cost:</th>
								<td><p><?php echo wc_price( esc_attr($total_shipping_cost) ); ?></p></td>
							</tr>
						</tbody>
					</table>
				<?php
			}

		}
	}

	/**
	 *
	 *	Show profit in order summary on edit order page
	 *	@todo need to make sure this factors in any manual override
	 *
	 */
	public function show_profit_in_order_summary( $order_id ) {

	    // Here set your data and calculations
	    $label 				= __( 'Profit', 'woocommerce' );
	    $calculate_total 	= $this->calculate_cost_profit( $order_id, false );
	    $profit 			= $calculate_total['total_order_profit'];
		  
		if ( $profit && ! empty($profit) ) : ?>
	        <tr>
	            <td class="label"><?php echo $label; ?>:</td>
	            <td width="1%"></td>
	            <td class="custom-total"><?php echo wc_price( esc_attr($profit) ); ?></td>
	        </tr>

		<?php endif;

	}

	/**
	 *
	 *	Add heading for order line item data
	 *
	 */
	public function add_cost_price_to_admin_order_meta_line_item_heading( $order ){
		?>
			<th class="wpd_cogs sortable" data-sort="your-sort-option">Alpha Insights COGS</th>
		<?php
	}

	/**
	 *
	 *	Add data to order line item
	 *
	 */
	public function add_cost_price_to_admin_order_meta_line_item( $product, $item, $item_id = null ) {

		// Only continue if product is object and item is of type product
		if ( is_a( $item, 'WC_Order_Item_Product' ) ) :

			// Get product
			if ( ! is_object($product) ) return false;
			$product_id 	= $product->get_id();

			// Get cost price
			$cost_price_per_unit = $this->get_cost_price( $product_id );

			// Total cost price per line item
			$total_cost_price 		= $cost_price_per_unit * $item->get_quantity();

			// Calculate profit per unit
			$line_item_subtotal 	= $item->get_total();
			$total_profit 			= $line_item_subtotal - $total_cost_price;

			// Can check refunds here
			$line_subtotal     		= $item->get_subtotal();

			// Margin
			$margin = wpd_ai_calculate_percentage( $total_profit, $line_item_subtotal );

/*			// Globals
			global $woocommerce, $post;

			// Begin initialization
			$order = new WC_Order($post->ID);*/

			// 
			$cost_string = $total_cost_price;
			$profit_string = $total_profit;
			$margin_string = $margin . '%';

			/**
			 *
			 *	If we use product bundles
			 *
			 */
			if ( class_exists('WC_Bundles') ) {

				if ( function_exists('wc_pb_is_bundled_order_item') && function_exists('wc_pb_get_bundled_order_items') ) {

					// Child
					if ( wc_pb_is_bundled_order_item($item, $order) ) {

						$cost_string = 'N/A';
						$profit_string = 'N/A';
						$margin_string = 'N/A';

					}

					// Parent
					if ( $product->is_type('bundle') ) {

						$bundle_items = wc_pb_get_bundled_order_items( $item, $order );
						$total_bundle_cost = 0;

						foreach( $bundle_items as $item_id => $item ) {

							if ( ! is_a( $item, 'WC_Order_Item_Product' ) ) continue;
							if ( ! is_object($item) ) continue;
							$product_id 					= $item->get_product_id();
							$variation_id 					= $item->get_variation_id();
							if ( $variation_id == 0 || empty( $variation_id ) || ! $variation_id ) {

								$active_product_id = $product_id;

							} else {

								$active_product_id = $variation_id;

							}
							$cost_price_per_unit = wpd_ai_get_cost_price_by_product_id( $active_product_id );
							$total_bundle_cost += $cost_price_per_unit;

						}

						// Calculations
						$total_bundle_cost = $total_bundle_cost * $item->get_quantity();
						$total_profit = $line_item_subtotal - $total_bundle_cost;
						$margin = wpd_ai_calculate_percentage( $total_profit, $line_item_subtotal );

						// Formatting
						$cost_string = $total_bundle_cost; // Needs to  be total of all child products
						$profit_string = $total_profit; // calculate
						$margin_string = $margin . '%'; // calculate

					}

				}

			}

			?>
				<td class="wpd_cogs" width="20%" data-sort-value="<?php echo esc_attr( $cost_price ); ?>">
					<div class="view">
						<div class="wpd-line-item-summary">
							<table class="display_meta">
								<tr>
									<th>Cost</th>
									<td><?php echo wc_price( esc_attr($cost_string) ); ?></td>
								</tr>
								<tr>
									<th>Profit</th>
									<td><?php echo wc_price( esc_attr($profit_string) ); ?></td>
								</tr>
								<tr>
									<th>Margin</th>
									<td><?php echo esc_attr( $margin_string ); ?></td>
								</tr>
							</table>
						</div>
					</div>
				</td>
			<?php
		else : ?>

			<td class="wpd_cogs" width="20%" data-sort-value="<?php echo $cost_price; ?>">
				<div class="view">
					<div class="wpd-line-item-summary">

					</div>
				</div>
			</td>

		<?php endif;

	}

	/**
	 *
	 *	Add meta to admin order page
	 *
	 */
	public function register_order_admin_meta_box_cost_profit() {

	    add_meta_box ( 
	    	'wpd-ai-insights-summary', 
	    	'Alpha Insights Summary', 
	    	array( $this, 'order_admin_meta_box_cost_profit' ), 
	    	'shop_order', 
	    	'side', 
	    	'default' 
	    );

	}

	/**
	 *
	 *	HTML output for admin order page meta box
	 *
	 */
	public function order_admin_meta_box_cost_profit( $order ) { 

		// Globals
		global $woocommerce, $post;

		// Begin initialization
		$order 						= new WC_Order($post->ID);
		if ( ! is_object($order) ) return false;
		$order_id 					= $order->get_id();
	    $calculate_total 			= $this->calculate_cost_profit( $order_id, false );

		// Single Values
		$total_product_cost 		= $calculate_total[ 'total_product_cost' ];
		$total_shipping_cost 		= $calculate_total[ 'total_shipping_cost' ];
		$payment_gateway_cost 		= $calculate_total[ 'payment_gateway_cost' ];
		$tax_paid 					= $calculate_total[ 'order_tax_paid' ];
		$calculated_profit 			= $calculate_total[ 'total_order_profit' ];
		$calculated_cost 			= $calculate_total[ 'total_order_cost' ];
		$revenue 					= $calculate_total[ 'total_order_revenue' ];
		$margin  					= wpd_ai_calculate_percentage( $calculated_profit, $revenue );

	?>
	<!-- Inline so we dont need to load another stylesheet -->
	<style type="text/css">
		.wpd-toggle-trigger {
	    	cursor: pointer;
		}
		.wpd-order-summary .wpd-toggle th, .wpd-order-summary .wpd-toggle td {
		    padding-left: 20px;
		    opacity: .65;
		    vertical-align: top;
		    line-height: initial;
		}
		.wpd-toggle-trigger .dashicons {
		    font-size: 90% !important;
		    vertical-align: initial;
		}
	</style>
	<p>The inputs below will override any default calculations.</p>
	<table class="widefat striped fixed wpd-order-summary">
		<tbody>
			<tr>
				<th>Order Revenue:</th>
				<td><?php echo wc_price( esc_attr($revenue) ); ?></td>
			</tr>
			<tr class="wpd-toggle-trigger">
				<th>Order Costs:</th>
				<td>
					<?php echo wc_price( esc_attr($calculated_cost) ); ?>
					<span class="dashicons dashicons-arrow-down-alt2"></span>
				</td>
			</tr>
			<tr class="wpd-toggle" style="display:none;">
				<th>Product Cost:</th>
				<td><?php echo wc_price( esc_attr($total_product_cost) ); ?></td>
			</tr>
			<tr class="wpd-toggle" style="display:none;">
				<th>Shipping Cost:</th>
				<td><?php echo wc_price( esc_attr($total_shipping_cost) ); ?></td>
			</tr>
			<tr class="wpd-toggle" style="display:none;">
				<th>Payment Gateway Cost:</th>
				<td><?php echo wc_price( esc_attr($payment_gateway_cost) ); ?></td>
			</tr>
			<tr>
				<th>Total Profit:</th>
				<td><?php echo wc_price( esc_attr($calculated_profit) ); ?></td>
			</tr>
			<tr>
				<th>Margin:</th>
				<td><?php echo esc_attr( $margin ); ?>%</td>
			</tr>
		</tbody>
	</table>
	<div class="edit_address"><?php

		woocommerce_wp_text_input( array(
			'id' => 'total_product_cost',
			'label' => 'Total Product Cost:',
			'value' => esc_attr( $total_product_cost ),
			'wrapper_class' => 'form-field-wide'
		) );

		woocommerce_wp_text_input( array(
			'id' => 'total_shipping_cost',
			'label' => 'Total Shipping Cost:',
			'value' => esc_attr( $total_shipping_cost ),
			'wrapper_class' => 'form-field-wide'
		) );

			woocommerce_wp_text_input( array(
			'id' => 'payment_gateway_cost',
			'label' => 'Payment Gateway Cost:',
			'value' => esc_attr( $payment_gateway_cost ),
			'wrapper_class' => 'form-field-wide'
		) );

		echo '<p style="text-align:right;">';
		submit_button('Update', 'primary wpd-ai-submit', 'submit', false );
		echo '</p>';

	?></div>
	<script type="text/javascript">
		jQuery(document).ready(function() {
			jQuery('.wpd-toggle-trigger').click(function() {
				jQuery('.wpd-toggle').slideToggle( 0, 'linear' );
				jQuery('.wpd-toggle-trigger').toggleClass('active');
			});
		});
	</script>
	<?php 

	}
	
	/**
	 *
	 *	Save our order details
	 *
	 */
	public function save_order_details( $ord_id ) {

		$product_cost 			= ( isset($_POST[ 'total_product_cost' ]) ) ? wc_clean( $_POST[ 'total_product_cost' ] ) : null;
		$shipping_cost 			= ( isset($_POST[ 'total_shipping_cost' ]) ) ? wc_clean( $_POST[ 'total_shipping_cost' ] ) : null;
		$payment_gateway_cost 	= ( isset($_POST[ 'payment_gateway_cost' ]) ) ? wc_clean( $_POST[ 'payment_gateway_cost' ] ) : null;
		$ad_campaign 			= ( isset($_POST[ 'ad_campaigns' ]) ) ? wc_sanitize_textarea( $_POST[ 'ad_campaigns' ] ) : null;

		// _ad_campaigns

		// Update all values
		update_post_meta( $ord_id, '_wpd_ai_total_shipping_cost', $shipping_cost );
		update_post_meta( $ord_id, '_wpd_ai_total_payment_gateway_cost', $payment_gateway_cost );
		update_post_meta( $ord_id, '_wpd_ai_order_campaign', $ad_campaign );

		// Set refund cost price override 
		// If we do it this way it will only override once they save it
		$order 					= new WC_Order( $ord_id  );
		$refund_amount 			= $order->get_total_refunded();
		if ( $refund_amount > 0 ) {
			update_post_meta( $ord_id, '_wpd_ai_total_product_cost_refund', $product_cost );
		} else {
			update_post_meta( $ord_id, '_wpd_ai_total_product_cost', $product_cost );
		}

		$recalculate_totals = $this->calculate_cost_profit( $ord_id, true );

	}

	/**
	 *
	 *	Add meta to admin order page
	 *
	 */
	public function register_order_admin_meta_box_ad_campaign() {

	    add_meta_box (
	    	'wpd-advertising-campaigns', 
	    	'Alpha Insights Attribution',
	    	array( $this, 'order_admin_meta_box_ad_campaign' ), 
	    	'shop_order', 
	    	'side', 
	    	'high' 
	    );

	}

	/**
	 *
	 *	HTML Output for admin order page meta box (ad campaigns)
	 *
	 */
	public function order_admin_meta_box_ad_campaign() {

		global $woocommerce, $post;

		$order 			= new WC_Order( $post->ID );
		if ( ! is_object($order) ) return false;
		$ad_campaigns 	= get_post_meta( $order->get_id(), '_wpd_ai_order_campaign', true );
		$landing_page 	= get_post_meta( $order->get_id(), '_wpd_ai_landing_page', true );
		$referral 		= get_post_meta( $order->get_id(), '_wpd_ai_referral_source', true );

		// Move this to another meta
		woocommerce_wp_text_input( array(
			'id' 			=> 'ad_campaigns',
			'label' 		=> 'Ad Campaign(s):',
			'value' 		=> esc_attr($ad_campaigns),
			'wrapper_class' => 'form-field-wide'
		));

		?><p><strong>Landing Page</strong> <br><?php echo $landing_page; ?></p><?php

		if ( ! empty( $landing_page ) ) {

			parse_str( parse_url( $landing_page, PHP_URL_QUERY ), $query_params );

			echo '<strong>Query Parameters</strong>';

			echo '<ul>';

			foreach ( $query_params as $key => $value ) {

				echo '<li>' . ucfirst( esc_attr($key) ) . ': ' . ucfirst( esc_attr($value) ) . '</li>';

			}

			echo '</ul>';

		}

		?><p><strong>Referral Source</strong> <br><?php echo esc_url($referral); ?></p><?php

	}

	/**
	 * Adds 'Profit' column header to 'Orders' page immediately after 'Total' column.
	 *
	 * @param string[] $columns
	 * @return string[] $new_columns
	 */
	public function actual_order_profit( $columns ) {

		$reordered_columns = array();

	    // Inserting columns to a specific location
	    foreach( $columns as $key => $column) {

	        $reordered_columns[$key] = $column;

	        if( $key ==  'order_status' ){
	            // Inserting after "Status" column
	            $reordered_columns['order_profit'] = __( 'Profit', 'my-textdomain' );
	            $reordered_columns['order_margin'] = __( 'Margin', 'my-textdomain' );
	        }
	    }

	    return $reordered_columns;


	}

	/**
	 * Adds 'Profit' column content to 'Orders' page immediately after 'Total' column.
	 *
	 * @param string[] $column name of column being displayed
	 *	@todo revisit this with calculations function
	 */
	public function add_order_profit_column_content( $column ) {

		if ( 'order_profit' == $column || 'order_margin' == $column ) {

		    global $post;

	    	// Order object
	        $order    				= wc_get_order( $post->ID );
	        if ( ! is_object($order) ) return false;
	        $order_id 				= $order->get_id();
	        $calculate_totals 		= $this->calculate_cost_profit( $order_id, false, false, false ); // false = dont save values

			// Single Values
			$total_product_cost 	= $calculate_totals['total_product_cost'];
			$total_shipping_cost 	= $calculate_totals['total_shipping_cost'];
			$payment_gateway_cost 	= $calculate_totals['payment_gateway_cost'];
			$calculated_profit 		= $calculate_totals['total_order_profit'];
			$calculated_cost 		= $calculate_totals['total_order_cost'];
			$revenue 				= $calculate_totals['total_order_revenue'];
			$margin 				= round( (($revenue - $calculated_cost) / $revenue) * 100, 2 ) . '%';

		    if ( 'order_profit' == $column ) {

				if ( empty( $calculated_profit ) ) {

					echo 'N/A';

				} else {

					echo wc_price( esc_attr($calculated_profit) );

				}

		    } elseif ( 'order_margin' == $column ) {
		    	
				if ( '' == $calculated_cost || false == $calculated_cost ) {

					echo 'N/A';

				} else {

		    		echo esc_attr( $margin );

		    	}

		    }

		}

	}

	/**
	 *
	 *	Set landing page cookie
	 *
	 */
	public function set_landing_page_cookie() {

		if ( ! isset( $_COOKIE['wpd-ai-landing'] ) ) {

			global $wp;

			// Landing page URL
			$URL = home_url( add_query_arg( array($_GET), $wp->request ) );
			$days = 3;

			// Set the cookie
			setcookie( 'wpd-ai-landing', $URL, time() + ( 86400 * $days ), '/' );

		}

		if ( ! isset( $_COOKIE['wpd-ai-referral'] ) ) {

			global $wp;

			// Landing page URL
			$referral = $_SERVER["HTTP_REFERER"];
			$days = 3;

			// Set the cookie
			setcookie( 'wpd-ai-referral', $referral, time() + ( 86400 * $days ), '/' );

		}

	}

	/**
	 *
	 *	Add this to order meta if it exists
	 *
	 */
	public function save_landing_page_to_order_meta( $order_id ) {

	    if ( ! empty( $_COOKIE['wpd-ai-landing'] ) ) {

	        update_post_meta( $order_id, '_wpd_ai_landing_page', esc_url_raw( $_COOKIE['wpd-ai-landing'] ) );

	    }      

	    if ( ! empty( $_COOKIE['wpd-ai-referral'] ) ) {

	        update_post_meta( $order_id, '_wpd_ai_referral_source', esc_url_raw( $_COOKIE['wpd-ai-referral'] ) );

	    }  

	    // Lets set default    

	}

	/**
	 *
	 *	Cost Price input html as a function so we can reuse
	 *
	 */
	public function cost_price_input_html() {

		$product_id = get_the_ID();
		$cost_tooltip_description = 'This will be the default for variable products, you can set the cost price per variation within the variations tab.';

		woocommerce_wp_text_input (

			array (

				'id'          => '_wpd_ai_product_cost',
				'value'       => wc_format_localized_price( get_post_meta( $product_id, '_wpd_ai_product_cost', true ) ),
				'data_type'   => 'price',
				'label'       => 'Alpha Insights Cost Of Goods (' . get_woocommerce_currency_symbol() . ')',
				'description' => '<img class="help_tip" data-tip="' . $cost_tooltip_description .'" src="' . esc_url( WC()->plugin_url() ) . '/assets/images/help.png" height="16" width="16">',

			)

		);

	}

	/**
	 *
	 *	Add cost of goods input to product page - New Tab
	 *
	 */
	public function product_editor_cost_of_goods_data_tab( $product_data_tabs ) {

		$product_data_tabs['wpd-ai-cost-of-goods'] = array(

			'label' 	=> __( 'COGS & Purchasing Info', 'my_text_domain' ),
			'target' 	=> 'wpd-ai-cost-of-goods',
			'priority' 	=> 10,
			'class' 	=> array( 'wpd-ai-cost-of-goods' ),

		);

		return $product_data_tabs;

	}

	/**
	 *
	 *	Content for product data tab
	 *
	 */
	public function purchasing_info_data_tab_content() {

		global $woocommerce, $post; // use these if I want
		$product_id = get_the_ID();

		?>
		<div id="wpd-ai-cost-of-goods" class="panel woocommerce_options_panel">
			<?php

			/**
			 *
			 *	Cost Price Input
			 *
			 */
			$this->cost_price_input_html();

			/**
			 *
			 *	Supplier Info Input
			 *
			 */
			woocommerce_wp_text_input (

				array (

					'id'          => '_wpd_ai_product_supplier',
					'value'       => get_post_meta( $product_id, '_wpd_ai_product_supplier', true ),
					'data_type'   => 'text',
					'label'       => 'Supplier',

				)

			);

			/**
			 *
			 *	Supplier Product Ref No
			 *
			 */
			$suppler_ref_tooltip_description = 'If your supplier has a reference number for this product it can be handy to store here.';
			woocommerce_wp_text_input (

				array (

					'id'          => '_wpd_ai_product_supplier_ref_no',
					'value'       => get_post_meta( $product_id, '_wpd_ai_product_supplier_ref_no', true ),
					'data_type'   => 'text',
					'label'       => 'Supplier Reference Number',
					'description' => '<img class="help_tip" data-tip="' . $suppler_ref_tooltip_description .'" src="' . esc_url( WC()->plugin_url() ) . '/assets/images/help.png" height="16" width="16">',

				)

			);

			/**
			 *
			 *	Supplier Product Ref No
			 *
			 */
			$suppler_ref_tooltip_description = 'If your supplier has a reference number for this product it can be handy to store here.';
			woocommerce_wp_textarea_input (

				array (

					'id'          => '_wpd_ai_product_purchasing_info',
					'value'       => get_post_meta( $product_id, '_wpd_ai_product_purchasing_info', true ),
					'data_type'   => 'text',
					'label'       => 'Any extra purchasing info',

				)

			); ?>
		</div>
		<?php

	}

	/**
	 *
	 *	Save all of my posted product data
	 *
	 */
	function save_product_cog_data( $product_id, $__post ) {

		if ( isset( $_POST['_wpd_ai_product_cost'] ) ) {

			update_post_meta( $product_id, '_wpd_ai_product_cost', wc_clean( $_POST['_wpd_ai_product_cost'] ) );

		}

		if ( isset( $_POST['_wpd_ai_product_supplier'] ) ) {

			update_post_meta( $product_id, '_wpd_ai_product_supplier', wc_clean( $_POST['_wpd_ai_product_supplier'] ) );

		}

		if ( isset( $_POST['_wpd_ai_product_supplier_ref_no'] ) ) {

			update_post_meta( $product_id, '_wpd_ai_product_supplier_ref_no', wc_clean( $_POST['_wpd_ai_product_supplier_ref_no'] ) );

		}

		if ( isset( $_POST['_wpd_ai_product_purchasing_info'] ) ) {

			update_post_meta( $product_id, '_wpd_ai_product_purchasing_info', wc_clean( $_POST['_wpd_ai_product_purchasing_info'] ) );

		}

	}

	/**
	 *
	 *	Cost Price input HTML for variations
	 *
	 */
	function cost_price_input_html_for_variations( $loop, $variation_data, $variation ) {

		woocommerce_wp_text_input( 

			array (

				'id'            => "wpd_ai_variation_product_cost_{$loop}",
				'name'          => "wpd_ai_variation_product_cost[{$loop}]",
				'value'         => wc_format_localized_price( isset( $variation_data['_wpd_ai_product_cost'][0] ) ? $variation_data['_wpd_ai_product_cost'][0] : '' ),
				'label'         => 'Alpha Insights Cost Of Goods (' . get_woocommerce_currency_symbol() . ')',
				'data_type'     => 'price',
				'wrapper_class' => 'form-row form-row-full',
				'description'   => '',
				'placeholder' 	=> 'Default: ' . wp_strip_all_tags( wc_price( $this->get_cost_price( $variation->ID ) ) ),

			) 

		);

	}

	/**
	 *
	 *	Saved variation data
	 *
	 */
	function save_cost_price_for_variations( $variation_id, $i ) {

		if ( isset( $_POST['wpd_ai_variation_product_cost'][ $i ] ) ) {

			update_post_meta( $variation_id, '_wpd_ai_product_cost', wc_clean( $_POST['wpd_ai_variation_product_cost'][ $i ] ) );

		}

	}

	/**
	 *
	 *	Setup default cost price meta keys when new order is created
	 *	
	 *	@todo set default shipping cost & payment gateway according to settings
	 *	
	 *	@param $order_id
	 *	@param $update_values 				(Default) True to save the values to database
	 *	@param $default_product_cost 		(default) False, use stored meta calculation if we have it
	 *
	 */
	function calculate_cost_profit( $order_id = null, $update_values = true, $default_product_cost = false, $data_collection = false ) {

		return wpd_ai_calculate_cost_profit_by_order( $order_id, $update_values, $default_product_cost, $data_collection );

	}

	/**
	 *
	 *	Retrieve cost price
	 *
	 */
	public static function get_cost_price( $product_id = 0 ) {

		return wpd_ai_get_cost_price_by_product_id( $product_id );

	}

}

// Init
$WPD_AI_Profit_Tracking = new WPD_AI_Profit_Tracking();