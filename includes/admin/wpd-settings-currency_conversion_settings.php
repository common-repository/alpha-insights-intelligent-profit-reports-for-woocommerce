<?php
/**
 *
 * Settings Page - Currency Conversion
 *
 * @package Alpha Insights
 * @version 1.0.0
 * @author WPDavies
 * @link https://wpdavies.dev/
 *
 */
defined( 'ABSPATH' ) || exit;

$options 			= get_option( 'wpd_ai_currency_table' );
$currencies 		= wpd_ai_currency_list();
$oer_api_key 		= get_option( 'wpd_profit_tracking_oer_api_key' );
$update_option_date = get_option( 'wpd_ai_currency_table_update');

?>
<div class="wpd-wrapper">
	<div class="wpd-section-heading wpd-inline">
		Automatic Exchange Rate
		<?php submit_button('Save Changes', 'primary pull-right', 'submit', false); ?>
	</div>
</div>
<div class="wpd-wrapper">
	<table class="wpd-table fixed widefat">
		<thead>
			<tr>
				<th colspan="2">Exchange Rate API</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<label>Open Exchange Rates API Key</label>
					<div class="wpd-meta">Create a free account to get your API Key here, this will be used to update the exchange rates. <a href="https://openexchangerates.org/signup/free" target="_blank">Create Open Exchange Rates Account</a>.</div>
				</td>
				<td>
					<input type="text" name="wpd_profit_tracking_oer_api_key" value="<?php echo esc_attr( $oer_api_key ); ?>" class="wpd-input">
					<?php if ( ! empty($oer_api_key) ) : ?>
						<a class="button btn wpd-input" id="wpd-update-rate">Update Exchange Rates</a>
						<input type="hidden" value="" id="wpd-update-rate-bool" name="wpd_update_rate_bool">
						<script type="text/javascript">
							jQuery(document).ready(function($) {
								$('#wpd-update-rate').click(function() {
									$('#wpd-update-rate-bool').val('true');
									$('#submit').click();
								});
							});
						</script>
					<?php endif; ?>
				</td>
			</tr>
			<?php if ( ! empty($update_option_date)) : ?>
				<tr>
					<td colspan="2">
						<p><strong>Exchange Rates last updated <?php echo date('l jS \of F Y h:i:s A', $update_option_date) ?></strong></p>
					</td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>
</div>
<div class="wpd-wrapper">
	<table class="wpd-table wpd-list-of-currencies fixed widefat">
		<thead>
			<tr>
				<td>Currency Code</td>
				<td>Conversion Rate against USD (USD = 1)</td>
			</tr>
		</thead>
		<tbody>
			<?php foreach( $currencies as $key => $value ) : ?>
				<tr>
					<td><?php echo esc_attr( $value ) ?> (<?php echo esc_attr( $key ) ?>)</td>
					<?php if ( ! isset($options[$key])) $options[$key] = null; ?>
					<td>
						<input name="wpd_ai_currency_table[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $options[$key] ) ?>" type="number" min="0" step="any" class="wpd-input code"/>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
<div class="wpd-inline">
	<?php submit_button('Save', 'primary pull-right', 'submit', false); ?>
</div>