<?php
/**
 *
 * Settings Page - GEneral Settings
 *
 * @package Alpha Insights
 * @version 1.0.0
 * @author WPDavies
 * @link https://wpdavies.dev/
 *
 */
defined( 'ABSPATH' ) || exit;

$cost_defaults 			= get_option( 'wpd_ai_cost_defaults' );
$order_status 			= get_option( 'wpd_ai_order_status' );
$admin_style_override 	= get_option( 'wpd_ai_admin_style_override' );
$prevent_notices 		= get_option( 'wpd_ai_prevent_wp_notices' );

?>
<div class="wpd-wrapper">
	<div class="wpd-section-heading wpd-inline">
		General Settings
		<?php submit_button('Save Changes', 'primary pull-right', 'submit', false); ?>
	</div>
</div>
<div class="wpd-wrapper">
	<table class="wpd-table fixed widefat">
		<thead>
			<tr>
				<th colspan="2">Default Prices</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<label>Default Cost Price (%)</label>
					<div class="wpd-meta">This will be a fallback setting for products in which you haven't entered a cost price. This is calculated as a percentage of the given product's retail price. If you set a cost price for a variable product, that will take precedence over this for that variable product's children.</div>
				</td>
				<td>
					<input class="wpd-input" type="number" name="wpd_ai_cost_defaults[default_product_cost_percent]" value="<?php echo esc_attr( $cost_defaults['default_product_cost_percent'] ) ?>" step="0.01" placeholder="Percent of RRP">
					<label for="wpd_ai_cost_defaults[default_product_cost_percent]" class="wpd-meta wpd-block-label">Percent Of RRP</label>
				</td>
			</tr>
			<tr>
				<td>
					<label for="wpd_ai_general_settings">Default Payment Gateway Cost</label>
					<div class="wpd-meta">This will be a fallback setting payment gateway fees. Your orders will start with this cost, but you can override it as the fee is finalised.</div>
				</td>
				<td>
					<span style="display:inline-block">
						<input class="wpd-input" type="number" name="wpd_ai_cost_defaults[default_payment_cost_percent]" value="<?php echo esc_attr( $cost_defaults['default_payment_cost_percent'] ) ?>" step="0.01" placeholder="Percent Of Order Value">
						<label for="wpd_ai_cost_defaults[default_payment_cost_percent]" class="wpd-meta wpd-block-label">Percent Of Order Value</label>
					</span>
					<span style="display:inline-block">
						<input class="wpd-input" type="number" name="wpd_ai_cost_defaults[default_payment_cost_fee]" value="<?php echo esc_attr( $cost_defaults['default_payment_cost_fee'] ) ?>" step="0.01" placeholder="Static Fee (0.30)">
						<label for="wpd_ai_cost_defaults[default_payment_cost_fee]" class="wpd-meta wpd-block-label">Static Fee</label>
					</span>
				</td>
			</tr>
			<tr>
				<td>
					<label for="wpd_ai_general_settings">Default Shipping Cost</label>
					<div class="wpd-meta">This will be a fallback setting for the shipping fees you pay to your carrier. Your orders will start with this cost, but you can override it as the fee is finalised.</div>
				</td>
				<td>
					<span style="display:inline-block">
						<input class="wpd-input" type="number" name="wpd_ai_cost_defaults[default_shipping_cost_percent]" value="<?php echo esc_attr( $cost_defaults['default_shipping_cost_percent'] ) ?>" step="0.01" placeholder="Percent Of Order Value">
						<label for="wpd_ai_cost_defaults[default_shipping_cost_percent]" class="wpd-meta wpd-block-label">Percent Of Order Value</label>
					</span>
					<span style="display:inline-block">
						<input class="wpd-input" type="number" name="wpd_ai_cost_defaults[default_shipping_cost_fee]" value="<?php echo esc_attr( $cost_defaults['default_shipping_cost_fee'] ) ?>" step="0.01" placeholder="Static Fee (6.00)">
						<label for="wpd_ai_cost_defaults[default_shipping_cost_fee]" class="wpd-meta wpd-block-label">Static Fee</label>
					</span>
				</td>
			</tr>
			<tr>
				<td>
					<label for="wpd_ai_general_settings">Tax Settings</label>
					<div class="wpd-meta">Would you like to include Tax as a cost component in your profit calculations?</div>
				</td>
				<td>
					<select class="wpd-input" name="wpd_ai_cost_defaults[tax_settings]">
						<option value="include" <?php echo wpd_ai_selected_option( 'include', $cost_defaults['tax_settings'] ) ?> >Include Tax</option>
						<option value="exclude" <?php echo wpd_ai_selected_option( 'exclude', $cost_defaults['tax_settings'] ) ?> >Exclude Tax</option>
					</select>
				</td>
			</tr>
		</tbody>
	</table>
</div>
<div class="wpd-wrapper">
	<table class="wpd-table fixed widefat">
		<thead>
			<tr>
				<th colspan="2">Report Settings</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<label>Paid Order Status For Reporting</label>
					<div class="wpd-meta">These are the order status' that we will look at when reviewing your profitability. These status' are the ones that are considered paid for.</div>
				</td>
				<td>
					<select class="wpd-input wpd-combo-select" name="wpd_ai_order_status[]" value="" multiple="multiple">
						<?php 
							$chosen_status 	= get_option( 'wpd_ai_order_status' );
							$order_status 	= wc_get_order_statuses();
							foreach( $order_status as $key => $value ) {
								$selected = '';
								if ( in_array( $key, $chosen_status ) ) {
									$selected = 'selected="selected"';
								}
								echo '<option value="' . esc_attr( $key ) . '" ' . esc_attr( $selected ) . '>' . esc_attr( $value ) . '</option>';
							}

						?>
					</select>
				</td>
			</tr>
		</tbody>
	</table>
</div>
<div class="wpd-wrapper">
	<table class="wpd-table fixed widefat">
		<thead>
			<tr>
				<th colspan="2">Appearance Settings</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<label>Load Modern WP Admin Skin</label>
					<div class="wpd-meta">Load our custom stylesheet which will override core admin appearance settings to help modernize your admin.</div>
				</td>
				<td>
					<select class="wpd-input" name="wpd_ai_admin_style_override">
						<option value="0" <?php echo wpd_ai_selected_option( '0', $admin_style_override ) ?> >False</option>
						<option value="1" <?php echo wpd_ai_selected_option( '1', $admin_style_override ) ?> >True</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>
					<label>Prevent annoying WordPress notices</label>
					<div class="wpd-meta">This will prevent the annoying update notices, license notices and whatever else rubbish people like to clutter your screen with.</div>
				</td>
				<td>
					<select class="wpd-input" name="wpd_ai_prevent_wp_notices">
						<option value="0" <?php echo wpd_ai_selected_option( '0', $prevent_notices ) ?> >False</option>
						<option value="1" <?php echo wpd_ai_selected_option( '1', $prevent_notices ) ?> >True</option>
					</select>
				</td>
			</tr>
		</tbody>
	</table>
</div>
<div class="wpd-inline">
	<?php submit_button('Save Changes', 'primary pull-right', 'submit', false); ?>
</div>