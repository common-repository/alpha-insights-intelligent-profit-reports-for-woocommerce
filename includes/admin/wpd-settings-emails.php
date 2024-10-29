<?php
/**
 *
 * Settings Page - Email
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
 *	Email Settings
 *
 */
$header_styles 				= 'background: rgb(3, 170, 237);color: white;border-top-right-radius: 7px;border-top-left-radius: 7px;';
$admin_email 				= get_option( 'admin_email' );
$email_settings 			= get_option( 'wpd_ai_email_settings' );
$appearance_settings 		= $email_settings['appearance'];
$profit_report_settings 	= (array) $email_settings['profit-report'];
$expense_report_settings 	= (array) $email_settings['expense-report'];
$inventory_report_settings 	= (array) $email_settings['inventory-report'];

if ( ! isset($profit_report_settings['frequency']['daily']) ) $profit_report_settings['frequency']['daily'] = null;
if ( ! isset($profit_report_settings['frequency']['weekly']) ) $profit_report_settings['frequency']['weekly'] = null;
if ( ! isset($profit_report_settings['frequency']['monthly']) ) $profit_report_settings['frequency']['monthly'] = null;
if ( ! isset($expense_report_settings['frequency']['daily']) ) $expense_report_settings['frequency']['daily'] = null;
if ( ! isset($expense_report_settings['frequency']['weekly']) ) $expense_report_settings['frequency']['weekly'] = null;
if ( ! isset($expense_report_settings['frequency']['monthly']) ) $expense_report_settings['frequency']['monthly'] = null;
if ( ! isset($inventory_report_settings['frequency']['daily']) ) $inventory_report_settings['frequency']['daily'] = null;
if ( ! isset($inventory_report_settings['frequency']['weekly']) ) $inventory_report_settings['frequency']['weekly'] = null;
if ( ! isset($inventory_report_settings['frequency']['monthly']) ) $inventory_report_settings['frequency']['monthly'] = null;
?>
<?php wpd_ai_pro_version_only(); ?>
<div class="wpd-wrapper wpd-premium-content">
	<?php wpd_ai_premium_content_overlay(); ?>
	<div class="wpd-section-heading wpd-inline">
		Email Settings
		<?php submit_button('Save Changes', 'primary pull-right wpd-input', 'submit', false); ?>
	</div>
</div>
<div class="wpd-wrapper wpd-premium-content">
	<?php wpd_ai_premium_content_overlay(); ?>
	<table class="wpd-table fixed widefat">
		<thead>
			<th colspan="2">Appearance Settings</th>
		</thead>
		<tbody>
			<tr>
				<th>
					<label>Would you like to include our header and footer?<div class="wpd-meta">Sometimes this helps with formatting if you've got other html email templates already adding headers and footers.</div></label>
				</th>
				<td>
					<?php wpd_ai_checkbox( 'wpd-email[appearance][header]', $appearance_settings['header'], 'Header' ); ?>
					<?php wpd_ai_checkbox( 'wpd-email[appearance][footer]', $appearance_settings['footer'], 'Footer' ); ?>
				</td>
			</tr>
		</tbody>
	</table>
</div>
<div class="wpd-wrapper wpd-premium-content">
<?php wpd_ai_premium_content_overlay(); ?>
	<table class="wpd-table fixed widefat">
		<thead>
			<th colspan="2" style="<?php echo $header_styles; ?>">Email #1 - Profit Report</th>
		</thead>
		<tbody>
			<tr>
				<th>
					<label>Comma Seperated List Of Recipient</label>
				</th>
				<td>
					<input type="text" name="wpd-email[profit-report][recipients]" class="wpd-input full-width" value="<?php echo esc_attr( $profit_report_settings['recipients'] ) ?>" placeholder="<?php echo esc_attr( $admin_email ) ?>">
				</td>
			</tr>
			<tr>
				<th>
					<label>How Often This Email Should Be Sent?</label>
				</th>
				<td>
					<?php wpd_ai_checkbox( 'wpd-email[profit-report][frequency][daily]', $profit_report_settings['frequency']['daily'], 'Daily' ); ?>
					<?php wpd_ai_checkbox( 'wpd-email[profit-report][frequency][weekly]', $profit_report_settings['frequency']['weekly'], 'Weekly' ); ?>
					<?php wpd_ai_checkbox( 'wpd-email[profit-report][frequency][monthly]', $profit_report_settings['frequency']['monthly'], 'Monthly' ); ?>
				</td>
			</tr>
			<tr>
				<th>What would you like to include?</th>
				<td>
					<?php wpd_ai_checkbox( 'wpd-email[profit-report][details][order_revenue]', $profit_report_settings['details']['order_revenue'], 'Order Revenue' ); ?>
					<?php wpd_ai_checkbox( 'wpd-email[profit-report][details][order_cost]', $profit_report_settings['details']['order_revenue'], 'Order Cost' ); ?>
					<?php wpd_ai_checkbox( 'wpd-email[profit-report][details][order_profit]', $profit_report_settings['details']['order_revenue'], 'Order Profit' ); ?>
					<?php wpd_ai_checkbox( 'wpd-email[profit-report][details][order_count]', $profit_report_settings['details']['order_revenue'], 'Order Count' ); ?>
					<?php wpd_ai_checkbox( 'wpd-email[profit-report][details][average_order_value]', $profit_report_settings['details']['order_revenue'], 'Average Order Value' ); ?>
					<?php wpd_ai_checkbox( 'wpd-email[profit-report][details][average_profit_per_order]', $profit_report_settings['details']['order_revenue'], 'Average Profit Per Order' ); ?>
					<?php wpd_ai_checkbox( 'wpd-email[profit-report][details][total_products_sold]', $profit_report_settings['details']['order_revenue'], 'Total Products Sold' ); ?>
					<?php wpd_ai_checkbox( 'wpd-email[profit-report][details][total_product_discounts]', $profit_report_settings['details']['order_revenue'], 'Total Product Discounts' ); ?>
					<?php wpd_ai_checkbox( 'wpd-email[profit-report][details][total_refunds]', $profit_report_settings['details']['order_revenue'], 'Total Refunds' ); ?>
					<?php wpd_ai_checkbox( 'wpd-email[profit-report][details][additional_expenses]', $profit_report_settings['details']['order_revenue'], 'Additional Expenses' ); ?>
					<?php wpd_ai_checkbox( 'wpd-email[profit-report][details][net_profit]', $profit_report_settings['details']['order_revenue'], 'Net Profit' ); ?>
				</td>
			</tr>
			<tr>
				<th>
					<label>Would you like to attach a document?</label>
				</th>
				<td>
					<?php wpd_ai_checkbox( 'wpd-email[profit-report][attachment][pl-statement]', $profit_report_settings['attachment']['pl-statement'], 'Profit & Loss Statement (PDF)' ); ?>
				</td>
			</tr>
			<tr>
				<td colspan="2" style="background-color: #fbfbfb;">
					<a href="<?php echo wpd_ai_admin_page_url( 'settings-emails-preview-profit-report' ); ?>" class="wpd-input button secondary-button pull-right" target="_blank">Preview Email</a>
					<a href="#" class="wpd-input button secondary-button pull-right" id="send-email-profit-report">Send Test Email</a>
				</td>
			</tr>
		</tbody>
	</table>
</div>
<div class="wpd-wrapper wpd-premium-content">
	<?php wpd_ai_premium_content_overlay(); ?>
	<table class="wpd-table fixed widefat">
		<thead>
			<th colspan="2" style="<?php echo $header_styles; ?>">Email #2 - Expense Report</th>
		</thead>
		<tbody>
			<tr>
				<th>
					<label>Comma Seperated List Of Recipient</label>
				</th>
				<td>
					<input type="text" name="wpd-email[expense-report][recipients]" class="wpd-input full-width" value="<?php echo esc_attr( $expense_report_settings['recipients'] ) ?>" placeholder="<?php echo esc_attr( $admin_email ) ?>">
				</td>
			</tr>
			<tr>
				<th>
					<label>How Often This Email Should Be Sent?</label>
				</th>
				<td>
					<?php wpd_ai_checkbox( 'wpd-email[expense-report][frequency][daily]', $expense_report_settings['frequency']['daily'], 'Daily' ); ?>
					<?php wpd_ai_checkbox( 'wpd-email[expense-report][frequency][weekly]', $expense_report_settings['frequency']['weekly'], 'Weekly' ); ?>
					<?php wpd_ai_checkbox( 'wpd-email[expense-report][frequency][monthly]', $expense_report_settings['frequency']['monthly'], 'Monthly' ); ?>
				</td>
			</tr>
			<tr>
				<th>What would you like to include?</th>
				<td>
					<?php wpd_ai_checkbox( 'wpd-email[expense-report][details][total_expenses_paid]', $expense_report_settings['details']['total_expenses_paid'], 'Total Expenses Paid' ); ?>
					<?php wpd_ai_checkbox( 'wpd-email[expense-report][details][total_no_expenses]', $expense_report_settings['details']['total_no_expenses'], 'Total No. Expenses' ); ?>
					<?php wpd_ai_checkbox( 'wpd-email[expense-report][details][average_expenses_per_day]', $expense_report_settings['details']['average_expenses_per_day'], 'Average Expenses Per Day' ); ?>
					<?php wpd_ai_checkbox( 'wpd-email[expense-report][details][parent_expenses]', $expense_report_settings['details']['parent_expenses'], 'All Parent Category Expenses' ); ?>
					<?php wpd_ai_checkbox( 'wpd-email[expense-report][details][child_expenses]', $expense_report_settings['details']['child_expenses'], 'All Child Category Expenses' ); ?>
				</td>
			</tr>
			<tr>
				<th>
					<label>Would you like to attach a document?</label>
				</th>
				<td>
					<?php wpd_ai_checkbox( 'wpd-email[expense-report][attachment][expense-report]', $expense_report_settings['attachment']['expense-report'], 'List Of Expenses (CSV)' ); ?>
				</td>
			</tr>
			<tr>
				<td colspan="2" style="background-color: #fbfbfb;">
					<a href="<?php echo wpd_ai_admin_page_url( 'settings-emails-preview-expense-report' ); ?>" class="wpd-input button secondary-button pull-right" target="_blank">Preview Email</a>
					<a href="#" class="wpd-input button secondary-button pull-right" id="send-email-expense-report">Send Test Email</a>
				</td>
			</tr>
		</tbody>
	</table>
</div>
<div class="wpd-wrapper wpd-premium-content">
	<?php wpd_ai_premium_content_overlay(); ?>
	<table class="wpd-table fixed widefat">
		<thead>
			<th colspan="2" style="<?php echo $header_styles; ?>">Email #3 - Inventory Report</th>
		</thead>
		<tbody>
			<tr>
				<th>
					<label>Comma Seperated List Of Recipient</label>
				</th>
				<td>
					<input type="text" name="wpd-email[inventory-report][recipients]" class="wpd-input full-width" value="<?php echo esc_attr( $inventory_report_settings['recipients'] ) ?>" placeholder="<?php echo esc_attr( $admin_email ) ?>">
				</td>
			</tr>
			<tr>
				<th>
					<label>How Often This Email Should Be Sent?</label>
				</th>
				<td>
					<?php wpd_ai_checkbox( 'wpd-email[inventory-report][frequency][daily]', $inventory_report_settings['frequency']['daily'], 'Daily' ); ?>
					<?php wpd_ai_checkbox( 'wpd-email[inventory-report][frequency][weekly]', $inventory_report_settings['frequency']['weekly'], 'Weekly' ); ?>
					<?php wpd_ai_checkbox( 'wpd-email[inventory-report][frequency][monthly]', $inventory_report_settings['frequency']['monthly'], 'Monthly' ); ?>
				</td>
			</tr>
			<tr>
				<th>What would you like to include?</th>
				<td>
					<?php wpd_ai_checkbox( 'wpd-email[inventory-report][details][total_stock_value_rrp]', $inventory_report_settings['details']['total_stock_value_rrp'], 'Total Stock Value (RRP)' ); ?>
					<?php wpd_ai_checkbox( 'wpd-email[inventory-report][details][total_stock_value_cost]', $inventory_report_settings['details']['total_stock_value_cost'], 'Total Stock Value (Cost)' ); ?>
					<?php wpd_ai_checkbox( 'wpd-email[inventory-report][details][unrealised_profits]', $inventory_report_settings['details']['unrealised_profits'], 'Unrealised Profits' ); ?>
					<?php wpd_ai_checkbox( 'wpd-email[inventory-report][details][total_stock_on_hand]', $inventory_report_settings['details']['total_stock_on_hand'], 'Total Stock On Hand' ); ?>
					<?php wpd_ai_checkbox( 'wpd-email[inventory-report][details][total_records_found]', $inventory_report_settings['details']['total_records_found'], 'Total Records Found' ); ?>
					<?php wpd_ai_checkbox( 'wpd-email[inventory-report][details][number_products_stock_management]', $inventory_report_settings['details']['number_products_stock_management'], 'No. Products With Stock Management' ); ?>
					<?php wpd_ai_checkbox( 'wpd-email[inventory-report][details][number_out_of_stock_products]', $inventory_report_settings['details']['number_out_of_stock_products'], 'No. Out Of Stock Products' ); ?>
					<?php wpd_ai_checkbox( 'wpd-email[inventory-report][details][number_low_stock_products]', $inventory_report_settings['details']['number_low_stock_products'], 'No. Low Stock Products' ); ?>
					<?php wpd_ai_checkbox( 'wpd-email[inventory-report][details][number_backorder_products]', $inventory_report_settings['details']['number_backorder_products'], 'No. Backorder Products' ); ?>
				</td>
			</tr>
			<tr>
				<th>
					<label>Would you like to attach a document?</label>
				</th>
				<td>
					<?php wpd_ai_checkbox( 'wpd-email[inventory-report][attachment][inventory-report]', $inventory_report_settings['attachment']['inventory-report'], 'Inventory Report (CSV)' ); ?>
				</td>
			</tr>
			<tr>
				<td colspan="2" style="background-color: #fbfbfb;">
					<a href="<?php echo wpd_ai_admin_page_url( 'settings-emails-preview-inventory-report' ); ?>" class="wpd-input button secondary-button pull-right" target="_blank">Preview Email</a>
					<a href="#" class="wpd-input button secondary-button pull-right" id="send-email-inventory-report">Send Test Email</a>
				</td>
			</tr>
		</tbody>
	</table>
</div>
<div class="wpd-inline wpd-premium-content">
	<?php wpd_ai_premium_content_overlay(); ?>
	<?php submit_button('Save Changes', 'primary pull-right wpd-input', 'submit', false); ?>
</div>
<?php wpd_ai_javascript_email_ajax( '#send-email-profit-report', 'wpd_profit_report' ); ?>
<?php wpd_ai_javascript_email_ajax( '#send-email-expense-report', 'wpd_expense_report' ); ?>
<?php wpd_ai_javascript_email_ajax( '#send-email-inventory-report', 'wpd_inventory_report' ); ?>
