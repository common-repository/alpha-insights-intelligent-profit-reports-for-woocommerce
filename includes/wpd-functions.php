<?php
/**
 *
 * Core functions for Alpha Insights
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
 *	Setup default cost price meta keys when new order is created
 *	
 *	@todo set default shipping cost & payment gateway according to settings
 *	@todo need to skip some actions with data collection param
 *	
 *	@param $order_id
 *	@param $update_values 				(Default) True to save the values to database
 *	@param $default_product_cost 		(default) False, use stored meta calculation if we have it
 *	@param $data_collection 			(default) True, collects heaps of data, heavy function
 *
 */
if ( ! function_exists( 'wpd_ai_calculate_cost_profit_by_order' ) ) {

	function wpd_ai_calculate_cost_profit_by_order( $order_id = null, $update_values = true, $default_product_cost = false, $data_collection = true ) {

		/**
		 *
		 *	If we dont have an order id lets try get one
		 *
		 */
		if ( ! $order_id ) {

			global $woocommerce, $post;
			$order = new WC_Order($post->ID);
			if ( ! is_object($order) ) return false;
			$order_id = $order->get_id();

		}
		
		/**
		 *
		 *	Load variables
		 *
		 */
		$order 								= wc_get_order( $order_id );
		$total_product_cost 				= 0;
		$total_cost_price_refund_exemption	= 0;

		// Single Values
		$meta_total_shipping_cost 			= get_post_meta( $order_id, '_wpd_ai_total_shipping_cost', true );
		$meta_payment_gateway_cost 			= get_post_meta( $order_id, '_wpd_ai_total_payment_gateway_cost', true );
		$meta_total_product_cost  			= get_post_meta( $order_id, '_wpd_ai_total_product_cost', true );
		$meta_total_product_cost_refund  	= get_post_meta( $order_id, '_wpd_ai_total_product_cost_refund', true );
		$refund_amount 						= $order->get_total_refunded(); // Refund amount
		$order_revenue 						= $order->get_total(); 			// Includes tax paid
		$order_revenue_before_refunds 		= $order_revenue; 				// This wont change - I remove the refund amount from the order revenue later
	    $order_tax 							= $order->get_total_tax();		// Total Tax Paid
	    $order_revenue_excluding_taxes 		= $order_revenue - $order_tax;
	    $shipping_total 					= $order->get_shipping_total();	// Total paid in shipping
	    $total_discounts_applied 			= $order->get_discount_total();
	    $payment_gateway 					= $order->get_payment_method_title();
	   	$total_product_revenue 				= 0;
		$total_product_revenue_at_rrp 		= 0;
		$total_qty_sold 					= 0;
		$total_skus_sold					= 0;
		$total_quantity_refunded 			= 0;
		$product_data 						= array();
		$cost_defaults 						= get_option( 'wpd_ai_cost_defaults' );


		/**
		 *	@todo might need to factor this in for conversions at some stage
		 */
		// $currency = is_callable( array( $order, 'get_currency' ) ) ? $order->get_currency() : $order->order_currency;
		( $refund_amount > 0 ) ? $refund = true : $refund = false;

		// Default shipping cost
		if ( empty($meta_total_shipping_cost) ) {

			if ( $order_revenue > 0 ) {

				// Set with default option
				$shipping_cost_multiplier 	= $cost_defaults['default_shipping_cost_percent'] / 100;
				$shipping_cost_fee 			= $cost_defaults['default_shipping_cost_fee'];
				$meta_total_shipping_cost 	= ( $order_revenue * $shipping_cost_multiplier ) + $shipping_cost_fee;

			} else {

				$meta_total_shipping_cost = 0;

			}

		}

		// Default payment gateway cost
		if ( empty($meta_payment_gateway_cost) ) {

			if ( $order_revenue > 0 ) {

				// Set with default option
				$payment_gateway_cost_multiplier 	= $cost_defaults['default_payment_cost_percent'] / 100;
				$payment_gateway_cost_fee 			= $cost_defaults['default_payment_cost_fee'];
				$meta_payment_gateway_cost 			= ( $order_revenue * $payment_gateway_cost_multiplier ) + $payment_gateway_cost_fee;

			} else {

				$meta_payment_gateway_cost = 0;

			}	

		}

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



				$product_id 					= $item->get_product_id();
				$variation_id 					= $item->get_variation_id();
				$quantity 						= $item->get_quantity();
				if ( empty($quantity) || ! is_numeric($quantity) ) $quantity = 1; // Make sure quantity is a number
				$line_item_subtotal 			= $item->get_total(); // total without tax (includes discounts)
				$line_item_rrp     				= $item->get_subtotal(); // this is just pre-tax discount, not a "discounted" product
				$total_product_revenue 			+= $line_item_subtotal;
				$total_qty_sold 				+= $quantity;
				$total_skus_sold++;

				/**
				 *
				 *	Set correct ID
				 *
				 */
				if ( $variation_id == 0 || empty( $variation_id ) || ! $variation_id ) {

					$active_product_id = $product_id;

				} else {

					$active_product_id = $variation_id;

				}

				/**
				 *
				 *	Load up cost price, default to 0
				 *	Function will load up parent cost if none set on variation
				 *
				 */	
				$cost_price_per_unit = wpd_ai_get_cost_price_by_product_id( $active_product_id );

				/**
				 *
				 *	Remove cost price from Bundle parent
				 *	@todo check for plugin presence
				 *
				 */
				if ( class_exists('WC_Bundles') ) {

					$item_product_object = $item->get_product();

					if ( is_object( $item_product_object ) ) {

						if ( $item_product_object->is_type('bundle') ) {

							$cost_price_per_unit = 0;

						}

					}

				}


				/**
				 *
				 *	Calculate running cost for products
				 *
				 */
				$total_line_item_cost_price 	= $cost_price_per_unit * $quantity;
				$total_product_cost 			+= $total_line_item_cost_price;

				if ( $refund ) {

					$qty_refunded 						= $order->get_qty_refunded_for_item( $item_id );
					$total_quantity_refunded 			+= $qty_refunded;
					$cost_price_refund_exemption 		= $qty_refunded * $total_line_item_cost_price;
					$total_cost_price_refund_exemption	+=  $cost_price_refund_exemption;

				}

				/**
				 *
				 *	Alright lets capture heaps of extra info
				 *
				 */
				if ( $data_collection ) {

					$product_object = wc_get_product( $active_product_id );

					if ( is_object( $product_object ) ) {

						$rrp_price = $product_object->get_regular_price();

						if ( empty($rrp_price) ) {
							$rrp_price = 0;
						}

						$total_product_revenue_at_rrp += $rrp_price * $quantity;
						$product_data[] = array(

							'product_id' => $active_product_id,

						);

					}

				}

	        }

	    } else {

	    	/**
	    	 *
	    	 *	Didnt find any products in this order, product cost is 0
	    	 *
	    	 */
			$total_product_cost = 0;

	    }

		/**
		 *
		 *	Override the product cost if it has been set
		 *
		 */
		if ( $meta_total_product_cost && ! empty($meta_total_product_cost) && $meta_total_product_cost > 0 && $default_product_cost == false && $refund == false) {

			// If weve saved a product cost already use that because its been deliberately overriden
			$total_product_cost = $meta_total_product_cost;

		} elseif ( $meta_total_product_cost_refund > 0 && $refund == true ) {

			// If weve set a refund amount
			$total_product_cost = $meta_total_product_cost_refund;

		}

		// If products have been removed, remove their cost price from the calculation
		if ( $refund ) {

			$total_product_cost 	= $total_product_cost - abs( $total_cost_price_refund_exemption );
			$order_revenue 			= $order_revenue - $refund_amount;

		}



	    /**
	     *
	     *	Quick calculation to get total profit (simply order total minus product cost for now, we dont have shipping costs or taxes)
	     *
	     */
	    $total_order_cost = (float) $total_product_cost + (float) $meta_total_shipping_cost + (float) $meta_payment_gateway_cost + (float) $order_tax;

	    /**
		 *
		 *	Let's manipulate the cost prices
		 *
		 */
	    if ( $cost_defaults['tax_settings'] == 'exclude' ) $total_order_cost -= $order_tax;

	    /**
	     *
	     *	Continue calculations
	     *
	     */
	    $total_order_profit 		= $order_revenue - $total_order_cost;
	    $total_order_margin 		= (float) round( wpd_ai_divide($total_order_profit, $order_revenue) * 100, 2 );

	    /**
	     *
	     *	Store Values
	     *	@see https://developer.wordpress.org/reference/functions/add_post_meta/
	     *	@param $bool, true to update || false to not
	     *
	     */
	    if ( $update_values ) {

			// Update product cost
			update_post_meta( $order_id, '_wpd_ai_total_product_cost', $total_product_cost );

			// Update order cost
			update_post_meta( $order_id, '_wpd_ai_calculated_order_cost', $total_order_cost );

			// Update order profit
			update_post_meta( $order_id, '_wpd_ai_calculated_order_profit', $total_order_profit );

	    }



		/**
		 *
		 *	Lets return our new values in case they are needed
		 *
		 */
		$results = array(

			'total_order_revenue_before_refunds' 	=> $order_revenue_before_refunds,
			'total_order_revenue' 					=> $order_revenue,
			'total_order_revenue_excluding_taxes' 	=> $order_revenue_before_refunds - $order_tax,
			'total_refund_amount' 					=> $refund_amount,
			'total_refund_quantity' 				=> $total_quantity_refunded,
			'total_order_cost' 						=> $total_order_cost,
			'total_order_profit'					=> $total_order_profit,
			'total_order_profit_after_tax_deduction' => $total_order_profit - $order_tax,
			'total_product_cost' 					=> $total_product_cost,
			'total_shipping_charged' 				=> $shipping_total,
			'total_shipping_cost' 					=> $meta_total_shipping_cost,
			'payment_gateway_cost' 					=> $meta_payment_gateway_cost,
			'order_tax_paid'						=> $order_tax,
			'total_order_margin' 					=> $total_order_margin,
			'total_discounts_applied' 				=> $total_discounts_applied, // This is redundant, start to phase this out
			'total_coupon_discounts_applied' 		=> $total_discounts_applied,
			'payment_gateway' 						=> $payment_gateway,
			'total_product_revenue' 				=> $total_product_revenue,
			'total_product_revenue_at_rrp' 			=> $total_product_revenue_at_rrp,
			'total_product_discounts' 				=> round( ($total_product_revenue_at_rrp - $total_product_revenue), 2 ),
			'total_qty_sold' 						=> $total_qty_sold,
			'total_product_profit' 					=> $total_product_revenue - $total_product_cost,
			'total_skus_sold' 						=> $total_skus_sold,
			'product_data' 							=> $product_data
			// add total product discounts applied
			// add coupon discounts

		);

		// Finally
		return $results;

	}

}

/**
 *
 *	Get cost price
 *
 */
if ( ! function_exists( 'wpd_ai_get_cost_price_by_product_id' ) ) {

	function wpd_ai_get_cost_price_by_product_id( $product_id ) {

		$cost_price_meta = get_post_meta( $product_id, '_wpd_ai_product_cost', true );

		if ( ! empty( $cost_price_meta ) && ! is_null( $cost_price_meta ) ) {

			$cost_price_per_unit = $cost_price_meta;

		} else {

			$cost_price_per_unit = 0;

			/**
			 *
			 *	If variation doesnt have a cost price set, lets check the parent
			 *
			 */
			if ( get_post_type( $product_id ) == 'product_variation' ) {

				// get parent product ID
				$parent_id 			 = wp_get_post_parent_id( $product_id );
				$cost_price_per_unit = get_post_meta( $parent_id, '_wpd_ai_product_cost', true );
				$cost_price_per_unit = ( $cost_price_per_unit ) ? $cost_price_per_unit : 0;

				/**
				 *
				 *	If cost price doesnt exist, use our default setting
				 *
				 */
				if ( $cost_price_per_unit === 0 ) {

					$cost_price_per_unit = wpd_ai_default_cost_price( $product_id );

				}

			}

			/**
			 *
			 *	If cost price doesnt exist, use our default setting
			 *
			 */
			if ( $cost_price_per_unit === 0 ) {

				$cost_price_per_unit = wpd_ai_default_cost_price( $product_id );

			}

		}

		return $cost_price_per_unit;

	}

}

/**
 *
 *	Get default cost price
 *
 */
if ( ! function_exists( 'wpd_ai_default_cost_price' ) ) {

	function wpd_ai_default_cost_price( $product_id ) {

		$default_cost_prices 	= get_option( 'wpd_ai_cost_defaults' );
		$price 					= get_post_meta( $product_id, '_regular_price', true );
		$cost_price_per_unit 	= (float) $price * ( $default_cost_prices['default_product_cost_percent'] / 100 );

		if ( $cost_price_per_unit > 0 ) {

			return $cost_price_per_unit;

		} else {

			return 0;

		}

	}

}

/**
 *
 *	Calculate profit and cost by product
 *
 */
if ( ! function_exists( 'wpd_ai_default_cost_price' ) ) {

	function wpd_ai_default_cost_price( $product_id = 0 ) {

		// Silence is golden

	}

}

/**
 *
 *	Calculate profit and cost by line item
 *
 */
if ( ! function_exists( 'wpd_ai_calculate_cost_profit_by_line_item' ) ) {

	function wpd_ai_calculate_cost_profit_by_line_item() {

		// Silence is golden

	}

}

/**
 *
 *	Date picker
 *
 */
if ( ! function_exists( 'wpd_ai_date_picker' ) ) {

	function wpd_ai_date_picker( $selected_date = null, $name = '_wpd_date_paid', $classes = '', $placeholder = 'yyyy-mm-dd' ) {

		return '<input type="text" placeholder="' . $placeholder . '" class="wpd-input wpd-jquery-datepicker ' . $classes . '" name="'.$name.'" value="' . $selected_date . '">';

	}

}

/**
 *
 *	Check if it's a WPD page
 *
 */
if ( ! function_exists( 'is_wpd_page' ) ) {

	function is_wpd_page() {

		$screen 	= get_current_screen();
		$page 		= ( isset($_GET['page']) ) ? sanitize_text_field( $_GET['page'] ) : null;
		$post_type 	= ( isset($_GET['post_type']) ) ? sanitize_text_field( $_GET['post_type'] ) : null;
		$taxonomy 	= ( isset($_GET['taxonomy']) ) ? sanitize_text_field( $_GET['taxonomy'] ) : null;
		$bool 		= false;

		if ( 
				$screen->parent_base == 'wpd-alpha-insights' 
				|| $screen->post_type == 'expense' 
				|| $page == 'wpd-bulk-add-expense' 
				|| $page == 'wpd-alpha-insights' 
				|| $page == 'wpd-profit-reports'
				|| $page == 'wpd-expense-reports'
				|| $page == 'wpd-inventory-management'
				|| $page == 'wpd-analytics'
				|| $page == 'wpd-pl-statement'
				|| $page == 'wpd-ai-settings'
				|| $post_type == 'expense'
				|| $taxonomy == 'expense_category' 
			) {

			$bool = true;

		}



		return $bool;

	}

}

/**
 *
 *	Currency Selector
 *
 */
if ( ! function_exists( 'wpd_ai_currency_list' ) ) {

	function wpd_ai_currency_list() {

		// https://openexchangerates.org/api/latest.json?app_id=YOUR_APP_ID
		$oxr_url = "https://openexchangerates.org/api/currencies.json";

		// Open CURL session:
		$json 		= wp_remote_get( $oxr_url );
		$body 		= wp_remote_retrieve_body( $json );
		$currencies = (array) json_decode( $body );

		$currencies = array(); // deliberately empty

		// Fallback
		if ( empty( $currencies ) ) {

			// Fallback
			$currencies = array(

			    /*'AFA' => array('Afghan Afghani', '971'),*/
			    'AWG' => array('Aruban Florin', '533'),
			    'AUD' => array('Australian Dollars', '036'),
			    'ARS' => array('Argentine Pes', '032'),
			    'AZN' => array('Azerbaijanian Manat', '944'),
			    'BSD' => array('Bahamian Dollar', '044'),
			    'BDT' => array('Bangladeshi Taka', '050'),
			    'BBD' => array('Barbados Dollar', '052'),
			    /*'BYR' => array('Belarussian Rouble', '974'),*/
			    'BOB' => array('Bolivian Boliviano', '068'),
			    'BRL' => array('Brazilian Real', '986'),
			    'GBP' => array('British Pounds Sterling', '826'),
			    'BGN' => array('Bulgarian Lev', '975'),
			    'KHR' => array('Cambodia Riel', '116'),
			    'CAD' => array('Canadian Dollars', '124'),
			    'KYD' => array('Cayman Islands Dollar', '136'),
			    'CLP' => array('Chilean Peso', '152'),
			    'CNY' => array('Chinese Renminbi Yuan', '156'),
			    'COP' => array('Colombian Peso', '170'),
			    'CRC' => array('Costa Rican Colon', '188'),
			    'HRK' => array('Croatia Kuna', '191'),
			    /*'CPY' => array('Cypriot Pounds', '196'),*/
			    'CZK' => array('Czech Koruna', '203'),
			    'DKK' => array('Danish Krone', '208'),
			    'DOP' => array('Dominican Republic Peso', '214'),
			    'XCD' => array('East Caribbean Dollar', '951'),
			    'EGP' => array('Egyptian Pound', '818'),
			    'ERN' => array('Eritrean Nakfa', '232'),
			    /*'EEK' => array('Estonia Kroon', '233'),*/
			    'EUR' => array('Euro', '978'),
			    'GEL' => array('Georgian Lari', '981'),
			    /*'GHC' => array('Ghana Cedi', '288'),*/
			    'GIP' => array('Gibraltar Pound', '292'),
			    'GTQ' => array('Guatemala Quetzal', '320'),
			    'HNL' => array('Honduras Lempira', '340'),
			    'HKD' => array('Hong Kong Dollars', '344'),
			    'HUF' => array('Hungary Forint', '348'),
			    'ISK' => array('Icelandic Krona', '352'),
			    'INR' => array('Indian Rupee', '356'),
			    'IDR' => array('Indonesia Rupiah', '360'),
			    'ILS' => array('Israel Shekel', '376'),
			    'JMD' => array('Jamaican Dollar', '388'),
			    'JPY' => array('Japanese yen', '392'),
			    'KZT' => array('Kazakhstan Tenge', '368'),
			    'KES' => array('Kenyan Shilling', '404'),
			    'KWD' => array('Kuwaiti Dinar', '414'),
			    /*'LVL' => array('Latvia Lat', '428'),*/
			    'LBP' => array('Lebanese Pound', '422'),
			    /*'LTL' => array('Lithuania Litas', '440'),*/
			    'MOP' => array('Macau Pataca', '446'),
			    'MKD' => array('Macedonian Denar', '807'),
			    'MGA' => array('Malagascy Ariary', '969'),
			    'MYR' => array('Malaysian Ringgit', '458'),
			    /*'MTL' => array('Maltese Lira', '470'),*/
			    'BAM' => array('Marka', '977'),
			    'MUR' => array('Mauritius Rupee', '480'),
			    'MXN' => array('Mexican Pesos', '484'),
			    /*'MZM' => array('Mozambique Metical', '508'),*/
			    'NPR' => array('Nepalese Rupee', '524'),
			    'ANG' => array('Netherlands Antilles Guilder', '532'),
			    'TWD' => array('New Taiwanese Dollars', '901'),
			    'NZD' => array('New Zealand Dollars', '554'),
			    'NIO' => array('Nicaragua Cordoba', '558'),
			    'NGN' => array('Nigeria Naira', '566'),
			    'KPW' => array('North Korean Won', '408'),
			    'NOK' => array('Norwegian Krone', '578'),
			    'OMR' => array('Omani Riyal', '512'),
			    'PKR' => array('Pakistani Rupee', '586'),
			    'PYG' => array('Paraguay Guarani', '600'),
			    'PEN' => array('Peru New Sol', '604'),
			    'PHP' => array('Philippine Pesos', '608'),
			    'QAR' => array('Qatari Riyal', '634'),
			    'RON' => array('Romanian New Leu', '946'),
			    'RUB' => array('Russian Federation Ruble', '643'),
			    'SAR' => array('Saudi Riyal', '682'),
			    /*'CSD' => array('Serbian Dinar', '891'),*/
			    'SCR' => array('Seychelles Rupee', '690'),
			    'SGD' => array('Singapore Dollars', '702'),
			    /*'SKK' => array('Slovak Koruna', '703'),*/
			    /*'SIT' => array('Slovenia Tolar', '705'),*/
			    'ZAR' => array('South African Rand', '710'),
			    'KRW' => array('South Korean Won', '410'),
			    'LKR' => array('Sri Lankan Rupee', '144'),
			    'SRD' => array('Surinam Dollar', '968'),
			    'SEK' => array('Swedish Krona', '752'),
			    'CHF' => array('Swiss Francs', '756'),
			    'TZS' => array('Tanzanian Shilling', '834'),
			    'THB' => array('Thai Baht', '764'),
			    'TTD' => array('Trinidad and Tobago Dollar', '780'),
			    'TRY' => array('Turkish New Lira', '949'),
			    'AED' => array('UAE Dirham', '784'),
			    'USD' => array('US Dollars', '840'),
			    'UGX' => array('Ugandian Shilling', '800'),
			    'UAH' => array('Ukraine Hryvna', '980'),
			    'UYU' => array('Uruguayan Peso', '858'),
			    'UZS' => array('Uzbekistani Som', '860'),
			    /*'VEB' => array('Venezuela Bolivar', '862'),*/
			    'VND' => array('Vietnam Dong', '704'),
			    /*'AMK' => array('Zambian Kwacha', '894'),*/
			    /*'ZWD' => array('Zimbabwe Dollar', '716'),*/
			);

		}

		return $currencies;

	}

}

/**
 *
 *	Currency list in selection option
 *
 */
if ( ! function_exists( 'wpd_ai_currency_list_select' ) ) {

	function wpd_ai_currency_list_select( $selected = null ) {

		$currencies = wpd_ai_currency_list();
		$html = '';

		if ( $selected == null ) {

			$woocommerce_currency = get_option('woocommerce_currency');

			if ( isset($woocommerce_currency) && ! empty($woocommerce_currency) ) {

				$selected = $woocommerce_currency;

			}

		}

		foreach( $currencies as $key => $pair ) {

			if ( $selected == $key ) {

				$select = 'selected="selected"';

			} else {

				$select = null;

			}

			$html .= '<option value="' . $key . '" ' . $select . '>' . $key . '</option>';

		}

		return $html;

	}

}

/**
 *
 *	Cleanly display print_r debug container
 *
 */
if ( ! function_exists( 'wpd_ai_debug' ) ) {

	function wpd_ai_debug( $data, $title = null ) {

		if ( $title ) {

			$heading = 'Debug Container - ' . $title;

		} else {

			$heading = 'Debug Container';

		}

		echo '<div style="background:white; padding: 10px; border: solid 1px #eaeaea; margin: 20px 0px; border-radius: 5px;">';
		echo '<h2 style="text-align:center;">' . $heading . '</h2>';
		echo '<pre style="max-height: 300px; overflow-y: auto; background: #f7f7f7; padding: 20px;">';
		print_r($data);
		echo '</pre>';
		echo '</div>';

	}

}


/**
 *
 *	Get base currency - Currency to display everything in
 *
 */
if ( ! function_exists( 'wpd_ai_get_base_currency' ) ) {

	function wpd_ai_get_base_currency() {

		return get_option('woocommerce_currency');

	}

}

/**
 *
 *	Currency COnversion
 *
 */
if ( ! function_exists( 'wpd_ai_convert_currency' ) ) {

	function wpd_ai_convert_currency( $from, $to, $amount ) {

		// Eg I have 35AUD, should become 24.87USD
		// AUD  = 1.41

		$total = 0;	
		
		$currency_conversion_table = wpd_ai_get_list_of_currency_conversion_rates();

		$rate = $currency_conversion_table[$to] / $currency_conversion_table[$from];

		$total = $amount * $rate;

	    // Return results.
	    return $total;

	}

}

/**
 *
 *	Get currency converions list
 *
 */
if ( ! function_exists( 'wpd_ai_get_list_of_currency_conversion_rates' ) ) {

	function wpd_ai_get_list_of_currency_conversion_rates() {

		$options = get_option( 'wpd_ai_currency_table' );

		return $options;

	}

}

/** 
 *
 *	List of default conversion rates
 *
 */
if ( ! function_exists( 'wpd_ai_default_currency_conversion' ) ) {

	function wpd_ai_default_currency_conversion() {

		$array = array(

		    'AFA' => 77.05,
		    'AWG' => 1.8,
		    'AUD' => 1.41,
		    'ARS' => 71.84,
		    'AZN' => 1.70,
		    'BSD' => 1,
		    'BDT' => 84.77,
		    'BBD' => 6.90,
		    'BYR' => 2.39,
		    'BOB' => 6.90,
		    'BRL' => 5.23,
		    'GBP' => .78,
		    'BGN' => 1.68,
		    'KHR' => 4100.00,
		    'CAD' => 1.34,
		    'KYD' => .83,
		    'CLP' => 774.30,
		    'CNY' => 7.02,
		    'COP' => 3691.20,
		    'CRC' => 581.36,
		    'HRK' => 6.45,
		    'CPY' => 0.51,
		    'CZK' => 22.53,
		    'DKK' => 6.38,
		    'DOP' => 58.59,
		    'XCD' => 2.70,
		    'EGP' => 15.99,
		    'ERN' => 15,
		    'EEK' => 13.42,
		    'EUR' => 0.86,
		    'GEL' => 3.08,
		    'GHC' => 5.78,
		    'GIP' => 0.78,
		    'GTQ' => 7.69,
		    'HNL' => 24.91,
		    'HKD' => 7.75,
		    'HUF' => 296.82,
		    'ISK' => 135.59,
		    'INR' => 74.72,
		    'IDR' => 14602.75,
		    'ILS' => 3.41,
		    'JMD' => 145.32,
		    'JPY' => 106.13,
		    'KZT' => 414.08,
		    'KES' => 107.85,
		    'KWD' => 0.31,
		    'LVL' => 0.6,
		    'LBP' => 1512.93,
		    'LTL' => 2.96,
		    'MOP' => 7.98,
		    'MKD' => 52.96,
		    'MGA' => 3820.00,
		    'MYR' => 4.26,
		    'MTL' => 0.37,
		    'BAM' => 1.68,
		    'MUR' => 40.15,
		    'MXN' => 22.28,
		    'MZM' => 70.64,
		    'NPR' => 119.68,
		    'ANG' => 1.79,
		    'TWD' => 29.46,
		    'NZD' => 1.50,
		    'NIO' => 34.60,
		    'NGN' => 387.50,
		    'KPW' => 900.01,
		    'NOK' => 9.15,
		    'OMR' => 0.39,
		    'PKR' => 167.45,
		    'PYG' => 6966.50,
		    'PEN' => 3.53,
		    'PHP' => 49.33,
		    'QAR' => 3.64,
		    'RON' => 4.14,
		    'RUB' => 71.67,
		    'SAR' => 3.75,
		    'CSD' => 100.89,
		    'SCR' => 17.64,
		    'SGD' => 1.38,
		    'SKK' => 25.84,
		    'SIT' => 205.58,
		    'ZAR' => 16.67,
		    'KRW' => 1201.59,
		    'LKR' => 185.68,
		    'SRD' => 7.46,
		    'SEK' => 8.84,
		    'CHF' => 0.92,
		    'TZS' => 2325.00,
		    'THB' => 31.63,
		    'TTD' => 6.76,
		    'TRY' => 6.85,
		    'AED' => 3.67,
		    'USD' => 1,
		    'UGX' => 6.76,
		    'UAH' => 27.80,
		    'UYU' => 42.62,
		    'UZS' => 10215.00,
		    'VEB' => 9987.5,
		    'VND' => 23180.00,
		    'AMK' => 18.13,
		    'ZWD' => 361.9,
		);

		return $array;

	}

}

/**
 *
 *	Class to determine traffic type
 *
 */
class WPD_AI_Traffic_Type {

    /*
     * Organic sources
     */
    protected $organic_sources = array(

    	'www.google' => array('q='),
       'daum.net/' => array('q='),
       'eniro.se/' => array('search_word=', 'hitta:'),
       'naver.com/' => array('query='),
       'yahoo.com/' => array('p='),
       'msn.com/' => array('q='),
       'bing.com/' => array('q='),
       'aol.com/' => array('query=', 'encquery='),
       'lycos.com/' => array('query='),
       'ask.com/' => array('q='),
       'altavista.com/' => array('q='),
       'search.netscape.com/' => array('query='),
       'cnn.com/SEARCH/' => array('query='),
       'about.com/' => array('terms='),
       'mamma.com/' => array('query='),
       'alltheweb.com/' => array('q='),
       'voila.fr/' => array('rdata='),
       'search.virgilio.it/' => array('qs='),
       'baidu.com/' => array('wd='),
       'alice.com/' => array('qs='),
       'yandex.com/' => array('text='),
       'najdi.org.mk/' => array('q='),
       'aol.com/' => array('q='),
       'mamma.com/' => array('query='),
       'seznam.cz/' => array('q='),
       'search.com/' => array('q='),
       'wp.pl/' => array('szukai='),
       'online.onetcenter.org/' => array('qt='),
       'szukacz.pl/' => array('q='),
       'yam.com/' => array('k='),
       'pchome.com/' => array('q='),
       'kvasir.no/' => array('q='),
       'sesam.no/' => array('q='),
       'ozu.es/' => array('q='),
       'terra.com/' => array('query='),
       'mynet.com/' => array('q='),
       'ekolay.net/' => array('q='),
       'rambler.ru/' => array('words='),

 	);

 	protected $social_sources = array(

 		'http://m.facebook.com' 		=> 'facebook',
 		'https://m.facebook.com/' 		=> 'facebook',
 		'https://l.facebook.com/' 		=> 'facebook',
 		'facebook.com' 					=> 'facebook',
 		'fb.com' 						=> 'facebook',
 		'ig.com' 						=> 'instagram',
 		'https://l.instagram.com/' 		=> 'instagram',
 		'instagram.com' 				=> 'instagram',

 	);

    /**
     *
     *	Referral URL
     *
     */
 	public $referrer;

 	/**
 	 *
 	 *	Contructor
 	 *
 	 */
	public function __construct( $referrer ) {

		// Setup our referral URL
        $this->referrer = $referrer;

	}

	/**
	 *
	 *	Lets start the process here
	 *
	 */
	public function determine_traffic_source() {

		$referral_url = $this->referrer;

		if ( $this->is_traffic_organic( $referral_url ) ) {

			$result = 'organic';

		} elseif ( $this->is_traffic_direct( $referral_url ) ) {

			$result = 'direct';

		} elseif ( $this->is_traffic_paid_google( $referral_url ) ) {

			$result = 'paid (google)';

		} elseif ( $this->is_traffic_mail( $referral_url ) ) {

			$result = 'email';

		} elseif ( $this->is_traffic_social( $referral_url ) ) {

			$result = 'social';

		} else {

			$result = 'unknown';

		} 

		return $result;

	}

    /*
     * Check if source is organic
     * 
     * @param string $referrer The referrer page
     * 
     * @return true if organic, false if not
     */
    public function is_traffic_organic( $referrer ) {

        //Go through the organic sources
        foreach( $this->organic_sources as $searchEngine => $queries ) {
            //If referrer is part of the search engine...
            if (strpos($referrer, $searchEngine) !== false) {
                    //Check if query is also there
                    foreach ($queries as $query) {
                            if (strpos($referrer, $query) !== false) {
                                    //If there, traffic is organic
                                    return true;
                            }
                    }
            }
        }

        return false;
    }

    /*
     * Check if source is organic
     * 
     * @param string $referrer The referrer page
     * 
     * @return true if organic, false if not
     */
    public function is_traffic_direct( $referrer ) {

    	if ( empty($referrer) || is_null($referrer) ) {

    		return true;

    	} else {

    		return false;

    	}

    }

        /*
     * Check if source is organic
     * 
     * @param string $referrer The referrer page
     * 
     * @return true if organic, false if not
     */
    public function is_traffic_paid_google( $referrer ) {

    	return false;

    }

        /*
     * Check if source is organic
     * 
     * @param string $referrer The referrer page
     * 
     * @return true if organic, false if not
     */
    public function is_traffic_mail( $referrer ) {

    	return false;

    }
        
     /*
     * Check if source is organic
     * 
     * @param string $referrer The referrer page
     * 
     * @return true if organic, false if not
     */
    public function is_traffic_social( $referrer ) {

        //Go through the organic sources
        foreach( $this->social_sources as $social_source => $social_platform ) {

            //If referrer is part of the search engine...
            if ( strpos( $referrer, $social_source ) !== false) {

            	return true;

            }
        }

    	return false;

    }

}

/**
 *
 *	Set defaults for line chart
 *	rgb(132,103,214) Purple
 *	rgb(19,143,221) Dark Blue
 *	rgb(48, 193, 241) Blue
 *	rgb(48, 229, 241) Light Blue
 *	
 *
 */
if ( ! function_exists( 'wpd_ai_chart_defaults' ) ) {

	function wpd_ai_chart_defaults() {
		?>
			<script type="text/javascript">
				jQuery(document).ready(function() {

					// console.log('Printing ChartJS defaults');
					// console.log(Chart.defaults);

					// Doughnut, pie
					Chart.defaults.elements.arc = {
						backgroundColor: ["rgb(132,103,214,0.75)", "rgb(19,143,221,0.75)", "rgb(48, 193, 241,0.75)", "rgb(48, 229, 241,0.75)", "rgb(48,241,191,0.75)"],
						borderAlign: "center",
						borderColor: "#fff",
						borderWidth: 2,
					};
					Chart.defaults.elements.line = {
						backgroundColor: ["rgb(132,103,214,0.75)", "rgb(19,143,221,0.75)", "rgb(48, 193, 241,0.75)", "rgb(48, 229, 241,0.75)", "rgb(48,241,191,0.75)"],
						hoverBackgroundColor: ["rgb(132,103,214)", "rgb(19,143,221)", "rgb(48, 193, 241)", "rgb(48, 229, 241)", "rgb(48,241,191)"],
						borderCapStyle: "butt",
						borderColor: ["rgb(132,103,214)", "rgb(19,143,221)", "rgb(48, 193, 241)", "rgb(48, 229, 241)", "rgb(48,241,191)"],
						borderDash: [],
						borderDashOffset: 0,
						borderJoinStyle: "round",
						borderWidth: 2, // was 2
						capBezierPoints: true,
						fill: true,
						tension: 0.25, // Lower value is more linear (0-1), 0.5 is okay
					};
					Chart.defaults.elements.point = {
						backgroundColor: "rgba(0,0,0,0.1)",
						borderColor: "rgba(0,0,0,0.1)",
						borderWidth: 4,
						hitRadius: 5,
						hoverBorderWidth: 15,
						hoverRadius: 5,
						pointStyle: "circle",
						radius: 0, // 0 to get rid of point
					};
					Chart.defaults.elements.square = {
						backgroundColor: "rgba(0,0,0,0.1)",
						borderColor: "rgba(0,0,0,0.1)",
						borderSkipped: "bottom",
						borderWidth: 20,
					};
					Chart.defaults.hover.mode = 'index';
					Chart.defaults.hover.intersect = false;	
					Chart.defaults.interaction.mode = 'index';
					Chart.defaults.interaction.intersect = false;
					Chart.defaults.defaultFontFamily = "'Poppins', 'Helvetica Neue', 'Helvetica', 'Arial', sans-serif";
					Chart.defaults.scale.ticks.beginAtZero = true;
					Chart.defaults.plugins.tooltip.bodySpacing = 5;
					Chart.defaults.plugins.tooltip.padding = 12;

				});
			</script>
		<?php
	}

}

/**
 *
 *	Per page selector
 *
 */
if ( ! function_exists( 'wpd_ai_per_page_selector' ) ) {

	function wpd_ai_per_page_selector( $per_page = 25 ) {

		?>
		    <span class="wpd-per-page-wrapper">
		        <label for="wpd-per-page">Per Page</label>
			    <select name="wpd-per-page" class="wpd-input">
					<?php 
						$per_page_array = array( '25', '50', '100', '250', '500', 'all' );	
						foreach( $per_page_array as $per_page_array_value ) {
							( $per_page_array_value == $per_page ) ? $selected = 'selected="selected"' : $selected = '';
							echo '<option value="' . $per_page_array_value . '" ' . $selected . '>' . $per_page_array_value . '</option>';
						}
					?>
		    	</select>
			</span>
		<?php

	}

}

/**
 *
 *	Margin calculation
 *
 */
if ( ! function_exists( 'wpd_ai_calculate_percentage' ) ) {

	function wpd_ai_calculate_percentage( $original, $total, $round = 2 ) {

		// Definitely going to be 0 if any value is 0
		if ( ! $original || ! $total ) {

			return 0;

		}

		if ( ! $round ) {

			$result = ($original / $total) * 100;

		} else {

			$result = round( ($original / $total) * 100, $round);

		}

		return $result;

	}

}

/**
 *
 *	Divide calculation (prevents NaN)
 *
 */
if ( ! function_exists( 'wpd_ai_divide' ) ) {

	function wpd_ai_divide( $n1, $n2, $round = false ) {

		if ( ! $n1 || ! $n2 ) {

			return 0;

		} else {

			if ( $round && is_numeric($round) ) {

				return round( $n1 / $n2, $round );

			} else {

				return $n1 / $n2;

			}

		}

	}

}

/**
 *
 *	Data Tip ToolTip
 *
 */
if ( ! function_exists( 'wpd_ai_tooltip' ) ) {

	function wpd_ai_tooltip( $string = null, $primary = true ) {

		( $primary ) ? $class = 'primary' : $class = 'secondary';

		?>
		<span class="wpd-tooltip <?php echo $class ?>">
			<span class="dashicons dashicons-info"></span>
			<span class="tooltiptext"><?php echo $string; ?></span>
		</span>
		<?php

	}

}

/**
 *
 *	Sort multi level associative array
 *	
 *	@param $array (array) The array
 *	@param $key (string) 'Key to sort by'
 *
 */
if ( ! function_exists( 'wpd_ai_sort_multi_level_array' ) ) {

	function wpd_ai_sort_multi_level_array( $array, $key, $desc = true ) {

		if ( ! is_array($array) ) {

			return $array;
		
		}

		( $desc === true ) ? $order = SORT_DESC : $order = SORT_ASC;
		
		array_multisort( array_column( $array, $key ), $order, $array );

		return $array;

	}

}

/**
 *
 *	Add HTML container for dialogs
 *
 */
add_action('admin_footer', 'wpd_ai_footer_dialog_html');
function wpd_ai_footer_dialog_html() {

	if ( is_wpd_page() ) {

		?><div class="wpd-dialog" id="wpd-dialog">
			<p>Alpha Insights by WP Davies allows you to track your profitability with razor sharp precision. Focus on the one metric that matters - profitability and your business can do nothing but flourish.</p>
			<strong>Documentation</strong>
			<p>Not sure about something? Check out our <a href="https://wpdavies.dev/docs/alpha-insights/" style="color:rgb(3, 170, 237);" target="_blank">documentation</a> to learn more about Alpha Insights.</p>
			<strong>Bug Report</strong>
			<p>If you've found a bug we would encourage you to <a href="https://wpdavies.dev/report-a-bug/" style="color:rgb(3, 170, 237);" target="_blank">report a bug here</a>. We are generally very quick at patching up issues when we know about them.</p>
			<strong>Support</strong>
			<p>Need to talk to someone? <a href="mailto:support@wpdavies.dev" style="color:rgb(3, 170, 237);">support@wpdavies.dev</a></p>
			<strong>Upgrade To Pro Version</strong>
			<p>Unlock the full capability of Alpha Insights with a $1 trial. <a target="_blank" href="https://wpdavies.dev/plugins/alpha-insights/?utm_source=alpha-insights-free&utm_medium=main_cta&utm_content=alpha-insights_page_wpd-profit-reports" style="color:rgb(3, 170, 237);">Click Here</a> to get started.</p>
		</div><?php
		
	}

}

/**
 *
 *	Convert device category into icon
 *
 */
if ( ! function_exists( 'wpd_ai_device_category_icon' ) ) {

	function wpd_ai_device_category_icon( $device_category ) {

		$device_category = strtolower($device_category);

		if ( $device_category === 'mobile' ) {

			$result = '<span class="dashicons dashicons-smartphone"></span>';

		} elseif ( $device_category === 'tablet' ) {

			$result = '<span class="dashicons dashicons-tablet"></span>';


		} elseif ( $device_category === 'desktop' ) {

			$result = '<span class="dashicons dashicons-desktop"></span>';

		} else {

			return false;

		}

		return '<span class="wpd-device-category-icon wpd-icon">' . $result . '</span>';

	}

}

/**
 *
 *	Name for greetings
 *
 */
if ( ! function_exists( 'wpd_ai_user_greeting' ) ) {

	function wpd_ai_user_greeting( $user_id = null ) {

		$user_info = $user_id ? new WP_User( $user_id ) : wp_get_current_user();

		if ( $user_info->first_name ) {

			return $user_info->first_name;
		}

		return $user_info->display_name;

	}

}

/**
 *
 *	Post edit link for admin
 *
 */
if ( ! function_exists( 'wpd_ai_admin_post_url' ) ) {

	function wpd_ai_admin_post_url( $post_id ) {

		return admin_url( 'post.php?post=' . $post_id ) . '&action=edit';

	}

}

/**
 *
 *	Stock Status Message
 *
 */
if ( ! function_exists( 'wpd_ai_stock_status_html' ) ) {

	function wpd_ai_stock_status_html( $product_object ) {

		if ( ! is_object( $product_object ) ) return false;

		$result 				= null;
		$manage_stock 			= $product_object->get_manage_stock();
		$stock_quantity 		= $product_object->get_stock_quantity();
		$stock_status 			= $product_object->get_stock_status();
		$backorders 			= $product_object->get_backorders();

		if ( $manage_stock ) {

			if ( $stock_quantity < 1 ) {

				// Out of stock
				$result = 'Out Stock ('.$stock_quantity.')';
				$result	.= '<div class="wpd-meta">Backorders: '.$backorders.'</div>';

			} else {

				// In stock
				$result = 'In Stock ('.$stock_quantity.')';

			}

		} else {

			$result = 'In Stock (N/A)' . '<div class="wpd-meta">Stock Not Managed</div>';

		}

		return $result;

	}

}

/**
 *
 *	Check if this option is selected and return html
 *
 */
if ( ! function_exists( 'wpd_ai_selected_option' ) ) {

	function wpd_ai_selected_option( $current_option, $current_value ) {

		if ( $current_option === $current_value ) {

			return 'selected="selected"';

		} else {

			return '';

		}

	}

}

/**
 *
 *	Get Admin Menu Item URL
 *
 */
if ( ! function_exists( 'wpd_ai_admin_page_url' ) ) {

	function wpd_ai_admin_page_url( $target ) {

		if ( $target === 'inventory-management' ) {

			return admin_url( 'admin.php?page=wpd-inventory-management' );

		} elseif( $target === 'settings-emails' ) {

			return admin_url( 'admin.php?page=wpd-ai-settings&subpage=email' );

		} elseif( $target === 'settings-emails-preview-profit-report' ) {

			return admin_url( '/admin.php?page=wpd-ai-settings&subpage=email&email_preview=profit-report' );

		} elseif( $target === 'settings-emails-preview-expense-report' ) {

			return admin_url( '/admin.php?page=wpd-ai-settings&subpage=email&email_preview=expense-report' );

		} elseif( $target === 'settings-emails-preview-inventory-report' ) {

			return admin_url( '/admin.php?page=wpd-ai-settings&subpage=email&email_preview=inventory-report' );

		} elseif( $target === 'settings' ) {

			return admin_url( '/admin.php?page=wpd-ai-settings' );

		} elseif( $target === 'settings-bulk-import' ) {

			return admin_url( '/admin.php?page=wpd-ai-settings&subpage=import' );

		} elseif( $target === 'settings-currency' ) {

			return admin_url( '/admin.php?page=wpd-ai-settings&subpage=currency' );

		} elseif( $target === 'settings-license' ) {

			return admin_url( '/admin.php?page=wpd-ai-settings&subpage=license' );

		} elseif( $target === 'reports-orders' ) {

			return admin_url( '/admin.php?page=wpd-profit-reports' );

		} elseif( $target === 'reports-products' ) {

			return admin_url( '/admin.php?page=wpd-profit-reports&subpage=products' );

		} elseif( $target === 'reports-customers' ) {

			return admin_url( '/admin.php?page=wpd-profit-reports&subpage=customers' );

		} elseif( $target === 'reports-expenses' ) {

			return admin_url( '/admin.php?page=wpd-expense-reports' );

		} elseif( $target === 'pl-statement' ) {

			return admin_url( '/admin.php?page=wpd-pl-statement' );

		} else {

			return '#';

		}

	}

}

/**
 *
 *	CSV Icon
 *
 */
if ( ! function_exists( 'wpd_ai_export_to_csv_icon' ) ) {

	function wpd_ai_export_to_csv_icon( $id = null, $text = 'Export To CSV' ) {

	?>
		<div class="wpd-download-csv wpd-download" id="<?php echo $id; ?>">
			<div class="wpd-icon">
				<span class="dashicons dashicons-media-spreadsheet"></span>
			</div>
			<p><?php echo $text ?></p>
		</div>
	<?php

	}

}

/**
 *
 *	CSV Icon
 *
 */
if ( ! function_exists( 'wpd_ai_export_to_pdf_icon' ) ) {

	function wpd_ai_export_to_pdf_icon( $id = null, $text = 'Export To PDF' ) {

	?>
		<div class="wpd-download-pdf wpd-download" id="<?php echo $id; ?>">
			<div class="wpd-icon">
				<span class="dashicons dashicons-media-text"></span>
			</div>
			<p><?php echo $text ?></p>
		</div>
	<?php

	}

}

/**
 *
 *	Prepare CSV Data
 *
 */
if ( ! function_exists( 'wpd_ai_prepare_csv_data' ) ) {

	function wpd_ai_prepare_csv_data( $data, $target_fields ) {

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

}

/**
 *
 *	create csv
 *
 */
if ( ! function_exists( 'wpd_ai_create_csv_file' ) ) {

	function wpd_ai_create_csv_file( $file_name, $data ) {

		$start 						= microtime( true );
		$response 					= array();
		$system_path 				= WPD_AI_FREE_CSV_SYSTEM_PATH;
	    $output 					= fopen( $system_path . $file_name, "w");
	    $i 							= 0;
	    $success 					= true;
		$response['file_type'] 		= 'CSV';
		$error_messages 			= null;

		wpd_ai_write_log( 'Output set as: ' . $system_path . $file_name );

	    if ( ! $output ) {

			$error_message['file-creation-failure'] = 'Failed to create the CSV file, check to make sure folder permissions are okay.';
			$success = false;

	    }

	    /**
	     *
	     *	Set headers
	     *
	     */
	    // header('Content-type: text/csv');
	    // header("Content-Encoding: UTF-8");
	    // header('Content-Disposition: attachment; filename="' . $file_name . '"');
	    // header('Pragma: no-cache');
	    // header('Expires: 0');

	    /**
	     *
	     *	Fill in rows with data
	     *
	     */
	    foreach( $data as $row ) {

		   	$write_csv = fputcsv( $output, $row );  //output the user info line to the csv file

		     if ( ! $write_csv ) {

		    	$error_message['write-failure'] = 'Failed to write CSV, check to make sure folder permissions are okay.';
				$success = false;

		    } else {

		    	$i++;

		    }

	    }

	    /**
	     *
	     *	Send fail if 0 or 1 rows are written
	     *
	     */
	    if ( $i === 0 || $i === 1 ) {

	    	$error_message['data-failure'] = 'We couldn\'t find any data for the given range. Please check your filter and try again.';
			$success = false;

	    }

	 	/**
	 	 *
	 	 *	Close output
	 	 *
	 	 */
	    fclose( $output ); 

	    /**
	     *
	     *	Store results
	     *
	     */
	    $download_link 				= WPD_AI_FREE_CSV_PATHE . $file_name;  //make a link to the file so the user can download.
		$finish 					= microtime( true );
		$execution_time 			= $finish - $start;
		$response = array(

			'execution_time' => $execution_time,
			'download_link'  => $download_link,
			'error_messages' => $error_message,
			'rows_found' 	 => $i,
			'success' 		 => $success,

		);

		return $response;

	}

}

/**
 *
 *	Create a PDF file
 *
 */
if ( ! function_exists( 'wpd_ai_create_pdf' ) ) {

	function wpd_ai_create_pdf( $file_name, $html, $action = 'F' ) {

		$response = array();

		try {

			require_once( WPD_AI_FREE_PATH . 'includes/helpers/mpdf/vendor/autoload.php');
		    $pdf_css 			= file_get_contents( WPD_AI_FREE_PATH . 'assets/css/pdf.css' );
		    $wpd_css 			= file_get_contents( WPD_AI_FREE_PATH . 'assets/css/wpd-alpha-insights-admin.css' );

		    /**
		     *
		     *	Initialize
		     *
		     */
			$mpdf 				= new \Mpdf\Mpdf( 
				array(
					'tempDir' 		=> WPD_AI_FREE_PATH . 'assets/tmp',
					'mode' 			=> 'utf-8',
					'margin_left' 	=> 0,
					'margin_right' 	=> 0,
					'margin_top' 	=> 0,
					'margin_bottom' => 0,
				) 
			);

			/**
			*
			*	@link https://mpdf.github.io/css-stylesheets/introduction.html
			*
			*/
		    $mpdf->WriteHTML( $wpd_css, 1 );
		    $mpdf->WriteHTML( $pdf_css, 1 );
			$mpdf->WriteHTML( $html, 2 );

			/**
			 *
			 *	@see https://mpdf.github.io/reference/mpdf-functions/output.html <- params for output
			 *	@param $filename... directory and file name
			 *	@param 2 - 
			 *	'I' = Send the file inline to the browser. 
			 *	'D' = Send to the browser and force a download (requires param one filename)
			 *	'F' = Save to server (requires param one filename)
			 *	'S' = Return the document as a string?
			 *
			 */
			$server_directory 			= WPD_AI_FREE_PATH . 'assets/pdf/';
			$public_directory 			= WPD_AI_FREE_URL_PATH . 'assets/pdf/';
			$server_pdf_file 			= $server_directory . $file_name;
			$public_pdf_file 			= $public_directory . $file_name;
			// $response['server_file'] 	= $server_pdf_file;
			// $response['download_link'] 	= $public_pdf_file;
			// $response['file_name'] 		= $file_name;
			$success 		= true;
			/**
			 *
			 *	Final Output
			 *
			 */
			$mpdf->Output( $server_pdf_file, $action );

		} catch (\Throwable $e) {

			$error_message['file-creation-failure'] = $e->getMessage();
			$success 		= false;

		}

		$response = array(

			'download_link'  => $public_pdf_file,
			'server_file'  	 => $server_pdf_file,
			'error_messages' => $error_message,
			'file_name' 	 => $file_name,
			'success' 		 => $success,
			'file_type' 	=> 'PDF',

		);

		return $response;

	}

}

/**
 *
 *	Preloaer
 *
 */
if ( ! function_exists( 'wpd_ai_preloader' ) ) {

	function wpd_ai_preloader( $width = 30, $visible = true, $return = false ) {

		$style = 'width: ' . $width . 'px;';
		$style .= 'height: ' . $width . 'px;';
		
		if ( ! $visible ) {

			$style .= 'display:none;';

		}

		$result = '<div class="wpd-preloader" style="' . $style . '"><img src="' . WPD_AI_FREE_URL_PATH . '/assets/img/wpd-preloader.svg"></div>';

		if ( $return ) {
			return $result;
		} else {
			echo $result;
		}

	}

}

/**
 *
 *	Success
 *
 */
if ( ! function_exists( 'wpd_ai_success' ) ) {

	function wpd_ai_success( $width = 30, $visible =  true, $return = false ) {

		$style = 'width: ' . $width . 'px;';
		$style .= 'height: ' . $width . 'px;';

		if ( ! $visible ) {

			$style .= 'display:none;';

		}

		$result = '<div class="wpd-success" style="' . $style . '"><span class="dashicons dashicons-yes" style="line-height: ' . $width . 'px; font-size: ' . $width / 2 . 'px;"></span></div>';

		if ( $return ) {
			return $result;
		} else {
			echo $result;
		}

	}

}

/**
 *
 *	Failure
 *
 */
if ( ! function_exists( 'wpd_ai_failure' ) ) {

	function wpd_ai_failure( $width = 30, $visible =  true, $return = false  ) {

		$style = 'width: ' . $width . 'px;';
		$style .= 'height: ' . $width . 'px;';

		if ( ! $visible ) {

			$style .= 'display:none;';

		}

		$result = '<div class="wpd-failure" style="' . $style . '"><span class="dashicons dashicons-no" style="line-height: ' . $width . 'px; font-size: ' . $width / 2 . 'px;"></span></div>';

		if ( $return ) {
			return $result;
		} else {
			echo $result;
		}

	}

}

/**
 *
 *	Turn a underscore_key into a Nice Key
 *
 */
if ( ! function_exists( 'wpd_ai_clean_string' ) ) {

	function wpd_ai_clean_string( $string ) {

		return ucwords( str_replace( '_', ' ', $string ) );

	}

}

/**
 *
 *	Paid Order Status
 *
 */
if ( ! function_exists( 'wpd_ai_paid_order_status' ) ) {

	function wpd_ai_paid_order_status() {

		$status = get_option( 'wpd_ai_order_status' );

		if ( ! $status || empty($status) ) {

			$status = array( 'wc-completed', 'wc-processing' );

		}

		return $status;

	}

}

/**
 *
 *	Function to register a WP Davies Notice
 *
 */
if ( ! function_exists( 'wpd_ai_notice' ) ) {

	function wpd_ai_notice( $string ) {

		$_POST['wpd-notice'][] = $string;

	}

}

/**
 *
 *	Order hooks correctly for admin notice
 *	You must add your hooks before the do_action
 *
 */
add_action( 'admin_init', 'wpd_ai_setup_notice_hook' );
function wpd_ai_setup_notice_hook() {

	add_action( 'wpd_before_content', 'wpd_ai_output_notices' );

}

/**
 *
 *	Loop & Output notices
 *
 */
if ( ! function_exists( 'wpd_ai_output_notices' ) ) {

	function wpd_ai_output_notices() {

		if ( isset( $_POST['wpd-notice'] ) && ! empty( $_POST['wpd-notice'] ) ) {

			if ( is_array($_POST['wpd-notice']) ) {

				foreach( $_POST['wpd-notice'] as $message ) {

					wpd_ai_admin_notice( sanitize_text_field( $message ) );

				}

			} else {

				wpd_ai_admin_notice( sanitize_text_field( $_POST['wpd-notice'] ) );

			}

		}

	}

}

/**
 *
 *	Output admin notice as function
 *	To be hooked onto @hook wpd_before_content
 *	@todo move this to core functions
 *
 */
if ( ! function_exists( 'wpd_ai_admin_notice' ) ) {

	function wpd_ai_admin_notice( $string ) {

		echo '<div class="wpd-notice notice notice-success is-dismissible"><p>' . $string . '</p></div>';

	}

}

/**
 *
 *	Get all post meta keys
 *
 */
if ( ! function_exists( 'wpd_ai_product_meta_keys' ) ) {

	function wpd_ai_product_meta_keys() {

	    global $wpdb;
	    $query = "
	        SELECT DISTINCT($wpdb->postmeta.meta_key) 
	        FROM $wpdb->posts 
	        LEFT JOIN $wpdb->postmeta 
	        ON $wpdb->posts.ID = $wpdb->postmeta.post_id 
	        WHERE $wpdb->posts.post_type IN ('product', 'product_variation')
	    ";
	    $meta_keys = $wpdb->get_col( $wpdb->prepare( $query ) );
	    set_transient('product_meta_keys', $meta_keys, 60*60*24); # create 1 Day Expiration
	    return $meta_keys;

	}

}

/**
 *
 *	Collect product IDS from SQL Query
 *
 */
if ( ! function_exists( 'wpd_ai_get_product_meta_keys' ) ) {

	function wpd_ai_get_product_meta_keys() {

	    $cache = null; // get_transient('product_meta_keys');
	    $meta_keys = $cache ? $cache : wpd_ai_product_meta_keys();
	    return $meta_keys;

	}

}

/**
 *
 *	Collect Product IDS
 *
 */
if ( ! function_exists( 'wpd_ai_collect_product_ids' ) ) {

	function wpd_ai_collect_product_ids() {

	  	$args = array(

		    'post_type' 		=> array( 'product', 'product_variation' ),
		    'post_status'    	=> 'publish',
		    'fields' 			=> 'ids',
		    'posts_per_page' 	=> -1,

		);
		$query 					= new WP_Query( $args );
		$product_ids 			= $query->posts;
		wp_reset_postdata();
		return $product_ids;

	}

}

/**
 *
 *	Checkbox
 *
 */
if ( ! function_exists( 'wpd_ai_checkbox' ) ) {

	function wpd_ai_checkbox( $name, $value = null, $label = null ) {

		if ( $value == true || $value == 1 ) {
			$checked = 'checked="checked"';
		} else {
			$checked = null;
		}

		?>
		    <div class="wpd-checkbox-container">
				<label for="<?php echo $name; ?>" class="wpd-checkbox-label">
					<input type="checkbox" name="<?php echo $name; ?>" value="1" id="<?php echo $name; ?>" class="wpd-input wpd-checkbox" <?php echo $checked ?>>
					<span class="checkbox-custom rectangular"></span>
				</label>
				<span class="wpd-checkbox-text"><?php echo $label; ?></span>
			</div>
		<?php

	}

}

/**
 *
 *	Image URL
 *	wp-content/plugins/wpdavies-alpha-insights/assets/img/
 *
 */
if ( ! function_exists( 'wpd_ai_img_url' ) ) {

	function wpd_ai_img_url( $image ) {

		//
		return WPD_AI_FREE_URL_PATH . 'assets/img/' . $image;

	}

}

/**
 *
 *	Return the current website's time
 *	@default = 2020-09-24 10:50:22
 *	@link https://www.php.net/manual/en/datetime.formats.relative.php relative formats
 *
 */
if ( ! function_exists( 'wpd_ai_site_date_time' ) ) {

	function wpd_ai_site_date_time( $format = 'Y-m-d H:i:s', $modify = false ) {

		// $date = new DateTime( current_time( 'Y-m-d H:i:s' ) );
		$date = date_create( current_time( 'Y-m-d H:i:s' ) );

		if ( $modify ) {
			$date = date_modify( $date, $modify );
		}

		$date = date_format( $date, $format );

		return $date;

	}

}

/**
 *
 *	To Do List
 *
 */
if ( ! function_exists( 'wpd_ai_to_do_list' ) ) {

	function wpd_ai_to_do_list() {

		$response = array();
		$to_do_list = get_option( 'wpd_ai_to_do_list' );
		$to_do_list_dismiss = get_option( 'wpd_ai_dismiss_to_do_list' );

		if ( $to_do_list_dismiss ) {

			return array();	

		}


		foreach( $to_do_list as $to_do => $value ) {

			if ( ! $value ) {

				if ( $to_do === 'default_cost_prices' ) {

					$response['default_cost_prices'] = '<strong>Setup your default cost prices</strong><br>These are used as fallback values for products that you have not entered product costs directly. You can set these up <a href="'.wpd_ai_admin_page_url( 'settings' ).'">here</a>.';

				} elseif ( $to_do === 'import_cost_prices' ) {

					$response['import_cost_prices'] = '<strong>Import your cost prices</strong><br>You can edit in bulk, import cost of goods by CSV or import by another plugin\'s values <a href="'.wpd_ai_admin_page_url( 'settings-bulk-import' ).'">here</a>.';

				} elseif ( $to_do === 'email_preferences' ) {

					$response['email_preferences'] = '<strong>Set your email preferences</strong><br>We can automatically send you reports daily, weekly & monthly - checkout the settings <a href="'.wpd_ai_admin_page_url( 'settings-emails' ).'">here</a>.';

				} elseif ( $to_do === 'currency_conversions' ) {

					$response['currency_conversions'] = '<strong>Setup automatic currency conversions</strong><br>You will need to create an account to get an API key which will allow us to automatically fetch up to date currency rates on your behalf <a href="'.wpd_ai_admin_page_url( 'settings-currency' ).'">here</a>.';

				}

			}

		}

		return $response;

	}

}

/**
 *
 *	If we dont have our menu
 *	
 */
add_action( 'admin_footer', 'wpd_ai_no_menu_found' );
function wpd_ai_no_menu_found() {

	if ( ! is_wpd_page() ) {
		return false;
	}
	?>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			var wpd_ai_notice_string = 'PLEASE NOTE: The Alpha Insights menu could not be loaded, this may be due to a plugin preventing WordPress notices from showing. Please remove any plugins which may effect WordPress notices, we have a setting to remove notices if you would like to maintain that functionality.';
			var wpd_ai_menu_notice = '<div class="wpd-notice notice notice-success is-dismissible"><p>' + wpd_ai_notice_string + '</p></div>';
			if ( ! $('#wpd-ai-menu').length ) {
		        $('.wrap').prepend(wpd_ai_menu_notice);
		    }
		});
	</script>
	<?php

}

/**
 *
 *	PHP Compatability
 *
 */
if ( ! function_exists('wpd_ai_array_key_first') ) {

    function wpd_ai_array_key_first( array $arr ) {

        foreach($arr as $key => $unused) {

            return $key;

        }

        return NULL;

    }

}

/**
 *
 *	Prevent undefined notice
 *
 */
if ( ! function_exists( 'wpd_ai_increment' ) ) {

	function wpd_ai_increment( $var ) {

		if ( ! empty($var) && numeric($Var) ) {

			return $var++;

		} else {

			return 1; // This is our first increment so it can be one

		}

	}

}

/**
 *
 *	Prevent undefined notice
 *
 */
if ( ! function_exists( 'wpd_ai_increment_by_val' ) ) {

	function wpd_ai_increment_by_val( $var1, $var2, $operator = '+=' ) {

		if ( ! empty($var) && numeric($Var) ) {

			return $var1 + $var2;

		} else {

			return $var2; // if var1 doesnt exist, were just at var2

		}

	}

}

/**
 *
 *	Write log
 *
 */
if ( ! function_exists( 'wpd_ai_write_log' ) ) {

	function wpd_ai_write_log( $data ) {

		$filepath 	= WPD_AI_FREE_PATH . 'wpd_log.txt';

		file_put_contents( $filepath, trim( $data ) . PHP_EOL, FILE_APPEND );
		
	}

}

/**
 *
 *	Parse URL to check for Query params
 *	
 *	@return array() key|value pair of query params
 *
 */
if ( ! function_exists( 'wpd_ai_parse_query_params' ) ) {

	function wpd_ai_parse_query_params( $url ) {

		parse_str( parse_url( $url, PHP_URL_QUERY ), $query_params );

		return $query_params;

	}

}

/**
 *
 *	Let's defer the emails until after checkout to help with speed
 *
 */
add_filter( 'woocommerce_defer_transactional_emails', '__return_true' );

/**
 *
 *	WPD Templates
 *
 */
if ( ! function_exists( 'wpd_ai_template_locate' ) ) {

	function wpd_ai_template_locate( $template_category, $template_name ) {

		$template_file = WPD_AI_FREE_PATH . 'templates/' . $template_category . '/' . $template_name . '.php';

		if ( file_exists( $template_file ) ) {

			return $template_file;

		} else {

			echo 'Template not found (' . $template_file . ')';

		}

	}

}

/**
 *
 *	Check memory loop 
 *	@return $peak_memory_usage if true, otherwise returns false
 *
 */
if ( ! function_exists( 'wpd_ai_is_memory_usage_greater_than' ) ) {

	function wpd_ai_is_memory_usage_greater_than( $percent = 90 ) {

		$peak_memory_usage 	= memory_get_peak_usage( true );
		$wp_memory_limit 	= intval( ini_get('memory_limit') ) * 1024 * 1024;
		$memory_usage 		= ($peak_memory_usage / $wp_memory_limit) * 100;

		if ( $memory_usage > $percent ) {

			return $peak_memory_usage;

		} else {

			return false;

		}

	}

}

/**
 *
 *	Premium content overlay
 *
 */
if ( ! function_exists( 'wpd_ai_premium_content_overlay' ) ) {

	function wpd_ai_premium_content_overlay() {

		?><div class="wpd-premium-content-overlay"></div><?php

	}

}

/**
 *
 *	Redirect expense page
 *
 */
if ( ! function_exists('wpd_ai_pro_version_only') ) {

	function wpd_ai_pro_version_only() {

    	$current_screen = get_current_screen()->id;
    	$link = 'https://wpdavies.dev/plugins/alpha-insights/?utm_source=alpha-insights-free&utm_medium=pro_version_text&utm_content=' . $current_screen;

		?>
			<h3 class="wpd-premium-content-text">
				<a href="<?php echo $link; ?>" target="_blank">This Is Only Available In The Pro Version - Click To Learn More</a>
			</h3>
		<?php

	}

}


/**
 *
 *	Functions for Demo Sales Page (demo.wpdavies.dev)
 *
 */
add_action( 'admin_head', 'wpd_ai_upgrade_notice' );
function wpd_ai_upgrade_notice() {

	if ( is_wpd_page() ) :

		$current_screen = get_current_screen()->id;
		$link = 'https://wpdavies.dev/plugins/alpha-insights/?utm_source=alpha-insights-free&utm_medium=main_cta&utm_content=' . $current_screen;

		?>
		<div class="demo-notice">
			<div class="container">
				<div class="wpd-col-6"><h3>Alpha Insights (Free)</h3></div>
				<div class="wpd-col-6">
					<span class="sell-section pull-right">
						<a href="<?php echo $link; ?>" target="_blank" class="button wpd-input wpd-demo-cta">Unlock All Features For $1</a>
					</span>
				</div>
			</div>
		</div>
		<?php

	endif;

}

/**
 *
 *	Check that this is a number
 *
 */
if ( ! function_exists('wpd_ai_numbers_only') ) {

	function wpd_ai_numbers_only( $number, $return_on_fail = false ) {

		if ( is_numeric($number) ) {

			return $number;

		} else {

			return $return_on_fail;

		}

	}

}


/**
 *
 *	Sanitize URL
 * 	Might consider using sanitize_text_field() instead
 *
 */
function wpd_ai_sanitize_url( $url ) {

	return strip_tags( stripslashes( filter_var($url, FILTER_VALIDATE_URL) ) );

}