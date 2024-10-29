<?php
/**
 *
 * Functions relating to AJAX requests
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
 *	HTML container for small processing notifications	
 *
 */
add_action( 'admin_footer', 'wpd_ai_admin_notification_pop' );
function wpd_ai_admin_notification_pop() {

	if ( ! is_wpd_page() ) return; ?>
	<div class="wpd-notification-pop" id="wpd-notification-pop">
		<div class="wpd-exit-notification-pop"><span class="dashicons dashicons-no-alt"></span></div>
		<table>
			<tbody>
				<tr>
					<td class="wpd-notification-pop-icon"><?php wpd_ai_preloader( 40 ); ?></td>
					<td>
						<div class="wpd-notification-pop-title">Processing...</div>
						<div class="wpd-meta wpd-notification-pop-subtitle">We're working on it!</div>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<?php

}

/**
 *
 *	JS Ajax Request TEmplate
 *
 */
if ( ! function_exists('wpd_ai_javascript_ajax') ) {

	function wpd_ai_javascript_ajax( $click_selector, $ajax_action ) {

		?>
		<div id="wpd-csv-export">
			<div class="wpd-loading" style="text-align: center;">
				<?php echo wpd_ai_preloader( 100 ); ?>
				<?php echo wpd_ai_success( 100, false ); ?>
				<p class="wpd-loading-message">Processing Your Data...</p>
				<div class="wpd-results"></div>
				<div class="wpd-cta"><a href="#" class="wpd-button" id="wpd-csv-download" style="display:none;" download>Download File</a></div>
				<p class="wpd-results-summary wpd-meta"></p>
			</div>
		</div>
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				jQuery('<?php echo $click_selector; ?>').click(function(e) {

					e.preventDefault();

					$('.wpd-preloader').show();
	            	$('.wpd-success').hide();
	            	$('.wpd-loading-message').text('Processing Your Data...');
	            	$('.wpd-results').text('');
	            	$('#wpd-csv-download').attr('href', '#');
	            	$('#wpd-csv-download').hide();
	            	$('.wpd-results-summary').text('');

					 ///open the dialog window
		       		$("#wpd-csv-export").dialog("open");
			        
		            var data = {

		                'action': '<?php echo $ajax_action; ?>',
		                'url'   : window.location.href,

		            };

		            var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
		            
		            $.post(ajaxurl, data, function( response ) {
	            		var response = JSON.parse( response );
		            	console.log( response );
		            	if ( response.success ) {
			            	var url = response.download_link;
			            	$('.wpd-preloader').hide();
			            	$('.wpd-success').show();
			            	$('.wpd-loading-message').text('Success!');
			            	$('.wpd-results').text('Your CSV was succesfully created, click the link to download.');
			            	$('#wpd-csv-download').attr('href', url);
			            	$('#wpd-csv-download').show();
	            			$('.wpd-results-summary').text( (response.rows_found - 1) + ' records were found.');

	            			if ( response.file_type == 'PDF' ) {

			            		$('.wpd-results').text('Your PDF was succesfully created, click the link to download.');
	            				$('.wpd-results-summary').text('');

	            			}

		            	} else {

		            		var error_string = '';
		            		if ( response.error_messages ) {
			            		for ( var key in response.error_messages ) {
								  	error_string += "<p><strong>Error:</strong> " + response.error_messages[key] + "</p>";
								}
		            		}
			            	$('.wpd-preloader').hide();
			            	$('.wpd-loading-message').text('Something went wrong');
			            	$('.wpd-results').html('<p>Hm, something went wrong. We were unable to create your CSV file.</p>' + error_string);

			            	if ( response.file_type == 'PDF' ) {
			            		$('.wpd-results').html('<p>Hm, something went wrong. We were unable to create your PDF file.</p>' + error_string);
	            			}

		            	}

		            }).fail(function() {

		            	$('.wpd-preloader').hide();
		            	$('.wpd-loading-message').text('Something went wrong');
		            	$('.wpd-results').text('Hm, something went wrong. We were unable to create your document.');

		            });

			    });
			    var width = $(window).width() * .5; // 50%
		        $("#wpd-csv-export").dialog({
		        	dialogClass: 'wpd-dialog',
		            autoOpen: false,
		            title: 'Alpha Insights Exporter',
		            modal: true,
		            height: 'auto',
		            width: width,
		            show: { duration: 300 },
		            hide: { duration: 300 },
		            maxHeight: false,
		            maxWidth: false,
		            resizable: false,
		        });
		    });
		</script>
		<?php

	}

}

/**
 *
 *	Send email via ajax
 *
 */
if ( ! function_exists('wpd_ai_javascript_email_ajax') ) {

	function wpd_ai_javascript_email_ajax( $click_selector, $email_to_send ) {

	?>
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				jQuery('<?php echo $click_selector; ?>').click(function(e) {
					e.preventDefault();
					wpdPopNotification('loading', 'Processing...', 'We\'re working on it!');
			        var data = {
			            'action': 'wpd_send_email',
			            'email' : '<?php echo $email_to_send ?>',
			            'url'   : window.location.href,
			        };
			        var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
			        $.post(ajaxurl, data, function( response ) {
			        	console.log(response);
			    		var response = JSON.parse( response );
			    		if ( response.email_sent ) {
							wpdPopNotification('success', 'Success!', 'Your email has been succesfully sent.');
			    		} else {
							wpdPopNotification('fail', 'Hm, Something\'s Not Quite Right', 'Your email was not sent. (' + response.message + ')');
			    		}
			        }).fail(function() {
						wpdPopNotification('fail', 'Hm, Something\'s Not Quite Right', 'Your email was not sent.');
			        });
			    });
			});
		</script>
	<?php

	}

}

/**
 *
 *	Send email via ajax
 *
 */
if ( ! function_exists('wpd_ai_javascript_ajax_action') ) {

	function wpd_ai_javascript_ajax_action( $click_selector, $action, $args = null ) {

	?>
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				jQuery('<?php echo $click_selector; ?>').click(function(e) {
					e.preventDefault();
					wpdPopNotification('loading', 'Processing...', 'We\'re working on it!');
			        var data = {
			            'action': '<?php echo $action; ?>',
			            'url'   : window.location.href,
			        };
			        var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
			        $.post(ajaxurl, data, function( response ) {
			        	console.log(response);
			    		var response = JSON.parse( response );
			    		if ( response.success ) {
			    			if (response.success_message) {
			    				wpdPopNotification('success', 'Success!', response.success_message);
			    			} else {
			    				wpdPopNotification('success', 'Success!', 'Your request has been succesfully completed.');
			    			}
			    		} else {
							wpdPopNotification('fail', 'Hm, Something\'s Not Quite Right', 'Your action could not be complete. (' + response.message + ')');
			    		}
			        }).fail(function() {
						wpdPopNotification('fail', 'Hm, Something\'s Not Quite Right', 'Your action could not be complete.');
			        });
			    });
			});
		</script>
	<?php

	}

}

/**
 *
 *	Ajax request for inventory
 *
 */
add_action('wp_ajax_wpd_export_inventory_to_csv', 'wpd_ai_export_inventory_to_csv' );
add_action('wp_ajax_nopriv_wpd_export_inventory_to_csv', 'wpd_ai_export_inventory_to_csv' );
function wpd_ai_export_inventory_to_csv() {

	$requesting_url 			= wpd_ai_sanitize_url( $_POST['url'] );
	$inventory_management 		= new WPD_AI_Inventory_Management( $requesting_url );
	$data 						= $inventory_management->csv_data();
	$date_time_stamp 			= current_time( 'Y-m-d-h:i:s' );
    $file_name 					= 'alpha-insights-inventory-report-' . $date_time_stamp . '.csv';
    $response 					= wpd_ai_create_csv_file( $file_name, $data );
    $response['filter'] 		= $inventory_management->filter;
    $response['product_ids'] 	= $inventory_management->product_ids;
    $response['headers'] 		= $data[0];
	wp_die( json_encode( $response ) );

}

/**
 *
 *	Ajax request for product totals
 *
 */
add_action('wp_ajax_wpd_export_cogs_to_csv', 'wpd_ai_export_cogs_to_csv' );
add_action('wp_ajax_nopriv_wpd_export_cogs_to_csv', 'wpd_ai_export_cogs_to_csv' );
function wpd_ai_export_cogs_to_csv() {

	$requesting_url 			= wpd_ai_sanitize_url( $_POST['url'] );
	$data 						= wpd_ai_download_product_cogs_by_csv();
	$date_time_stamp 			= current_time( 'Y-m-d-h:i:s' );
    $file_name 					= 'alpha-insights-product-cogs-' . $date_time_stamp . '.csv';
    $response 					= wpd_ai_create_csv_file( $file_name, $data );
    $response['headers'] 		= $data[0];
	wp_die( json_encode( $response ) );

}

/**
 *
 *	Ajax request for product totals
 *
 */
add_action('wp_ajax_wpd_dismiss_to_do_list', 'wpd_ai_dismiss_to_do_list_function' );
add_action('wp_ajax_nopriv_wpd_dismiss_to_do_list', 'wpd_ai_dismiss_to_do_list_function' );
function wpd_ai_dismiss_to_do_list_function() {

	$requesting_url 			= wpd_ai_sanitize_url( $_POST['url'] );
	$response['success'] 		= update_option( 'wpd_ai_dismiss_to_do_list', true );

	if ( ! $response['success'] ) {
		$response['message'] = 'Could not save the correct setting.';
	}

	wp_die( json_encode( $response ) );

}