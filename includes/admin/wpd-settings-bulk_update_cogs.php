<?php
/**
 *
 * Settings page - Bulk Update Cost of Goods
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
 *	Bulk Update Cost of Goods
 *
 */
$product_meta_keys 			= wpd_ai_get_product_meta_keys();
$csv_data					= wpd_ai_load_cogs_via_csv_upload();

if ( isset($csv_data['data']) && count($csv_data['data'] ) > 0 ) {

	$product_ids = $csv_data['data'];

} else {

	$product_ids = wpd_ai_collect_product_ids();

}

/**
 *
 *	Set the meta key we are going to load up
 *
 */
if ( isset($_POST['cogs_update_meta_key']) && ! empty($_POST['cogs_update_meta_key']) ) {

	$current_meta_key = sanitize_text_field( $_POST['cogs_update_meta_key'] );

} else {

	$current_meta_key = '_wpd_ai_product_cost';

}
/**
 *
 *	Check if we are doing a load
 *
 */
if ( isset($_POST['cogs_load_data']) && $_POST['cogs_load_data'] == 'true' ) {

	$performing_load = true;

} else {

	$performing_load = false;

}



?>
<div class="wpd-wrapper">
	<div class="wpd-col-10">
		<div class="wpd-section-heading wpd-inline">Import Or Bulk Update Cost Of Goods</div>
		<p>Use this page to setup your Cost of Goods. Here you can import your Cost Of Goods by CSV, a past meta key or write them in manually.</p>
	</div>
	<div class="wpd-col-2" style="text-align:center;">
		<table class="fixed">
			<tbody>
				<tr>
					<td><?php wpd_ai_export_to_csv_icon( 'export-cogs-to-csv', 'Download COGS' ) ?></td>
					<td><?php wpd_ai_export_to_csv_icon( 'import-cogs-to-csv', 'Import COGS' ) ?></td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
<div class="wpd-wrapper">
	<table class="wpd-table wpd-list-of-currencies fixed widefat">
		<thead>
			<tr>
				<th colspan="2">Load Data From Another Meta Key</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<label>Load data from a pre-existing meta key</label>
					<div class="wpd-meta">You can use this table to update values or load in values via CSV or another plugins previously set values.</div>
				</td>
				<td>
					<select name="cogs_update_meta_key" class="wpd-single-select" data-max="1">
						<?php foreach( $product_meta_keys as $key ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php echo wpd_ai_selected_option( $key, $current_meta_key ) ?>><?php echo esc_attr( $key ); ?></option>
						<?php endforeach; ?>
					</select>
					<input type="hidden" value="false" id="cogs_load_data" name="cogs_load_data">
					<input type="hidden" value="false" id="cogs_save_data" name="cogs_save_data">
				</td>
			</tr>
			<tr>
				<td></td>
				<td><input type="submit" class="button button-secondary wpd-input pull-right" value="Load Meta Key Data" id="load-data-button"></td>
			</tr>
		</tbody>
	</table>
</div>
<div class="wpd-inline">
	<input type="submit" class="button button-primary pull-right wpd-input" value="Save Table Data" id="save-data-button">
</div>
<div class="wpd-wrapper">
	<table class="widefat fixed wpd-table">
		<thead>
			<tr>
				<th>Name</th>
				<th>SKU</th>
				<th>ID</th>
				<th>RRP Price</th>
				<th>Cost Of Goods</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach( $product_ids as $product ) :

				/**
				 *
				 *	If we uploaded via CSV this will be an array
				 *
				 */
				if ( is_array( $product ) ) {

					$product_id = $product[0];

				} else {

					$product_id = $product;

				}

				/**
				 *
				 *	Quickly check to make sure its a post ID
				 *
				 */
				if ( ! in_array( get_post_type( $product_id ), array( 'product', 'product_variation' ) ) ) {

					?><tr><td colspan="5">We couldn't match ID <?php echo esc_attr( $product_id ) ?> to any product in your database.</td></tr><?php

					continue;

				}

				/**
				 *
				 *	Load basic variables
				 *
				 */
				$product_name 		= get_the_title( $product_id );
				$sku 				= get_post_meta( $product_id, '_sku', true ); // _sku
				$rrp_price 			= get_post_meta( $product_id, '_regular_price', true );

				/**
				 *
				 *	Set message if we are doing a load
				 *	Also, if we are loading data use the meta key as the cogs value
				 *
				 */
				if ( $performing_load ) {
					$cogs 	= get_post_meta( $product_id, $current_meta_key, true );
				} elseif( $csv_data ) {
					$cogs 	= $product[1];
				} else {
					$cogs 	= get_post_meta( $product_id, '_wpd_ai_product_cost', true );
				}

				/**
				 *
				 *	Output row for non variation products
				 *
				 */
				?>
				<tr>
					<td><?php echo esc_attr( $product_name ); ?></td>
					<td><?php echo esc_attr( $sku ); ?></td>
					<td><?php echo esc_attr( $product_id ); ?></td>
					<td><?php echo esc_attr( $rrp_price ); ?></td>
					<td>
						<input type="number" name="<?php echo '_wpd_ai_product_cost[' . esc_attr( $product_id ) . ']' ?>" step="any" min="0" class="wpd-input" value="<?php echo esc_attr( $cogs ) ?>">
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
<div id="wpd-import-csv" style="display:none;">
	<strong>Format</strong>
	<p>Your CSV file must strictly have the product id in the first column and the cost of goods in the second column, no further columns.</p>
	<p>You should still include headings, although it won't matter how you label them.</p>
	<table class="wpd-table fixed widefat">
		<thead>
			<tr><th>product_id</th><th>cost_of_goods</th></tr>
		</thead>
		<tbody>
			<tr>
				<td>14356</td>
				<td>134.54</td>
			</tr>
			<tr>
				<td>14546</td>
				<td>3.54</td>
			</tr>
			<tr>
				<td colspan="2">
					<div class="wpd-form-wrap">
						<div class="wpd-section-heading">Upload Via CSV</div>
						<label for="csv_file" class="wpd-file-upload">
							<p>Click Here To Upload Your CSV File</p>
			    			<input type="file" name="csv_file" id="csv_file" accept=".csv">
			    		</label>
			    	</div>
				</td>
			</tr>
		</tbody>
	</table>
</div>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		jQuery('#load-data-button').click(function() {
			jQuery('#cogs_load_data').val('true');
		});
		jQuery('#save-data-button').click(function() {
			jQuery('#cogs_save_data').val('true');
		});
		$('#import-cogs-to-csv').on("click", function (e) {
        	e.preventDefault(); ///first, prevent the action
		    $('#wpd-import-csv').dialog("open");
		});
		///construct the dialog
		var width = $(window).width() * .5; // 80%
        $("#wpd-import-csv").dialog({
        	dialogClass: 'wpd-dialog',
            autoOpen: false,
            title: 'Load Cost Of Goods Via CSV',
            modal: true,
            height: 'auto',
            width: width,
            show: { duration: 300 },
            hide: { duration: 300 },
            maxHeight: false,
            maxWidth: false,
            resizable: false,
        });
       	$('.wpd-form-wrap').wrap('<form action="" method="post" enctype="multipart/form-data" id="csv-upload"></form>');
	    $('#csv-upload').on('change', "input#csv_file", function (e) {
	        e.preventDefault();
	        $("#csv-upload").submit();
	        $('label.wpd-file-upload p').text('Loading your CSV file...');
	    });
	});
</script>
<?php wpd_ai_javascript_ajax( '#export-cogs-to-csv', 'wpd_export_cogs_to_csv' ); ?>