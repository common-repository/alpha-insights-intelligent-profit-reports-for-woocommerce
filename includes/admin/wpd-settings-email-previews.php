<?php
/**
 *
 * Settings - Email Previews
 *
 * @package Alpha Insights
 * @version 1.0.0
 * @author WPDavies
 * @link https://wpdavies.dev/
 *
 */
defined( 'ABSPATH' ) || exit;

if ( isset($_GET['email_preview']) && $_GET['email_preview'] === 'profit-report' ) {

	?>
	<div class="wpd-wrapper">
		<div class="pull-left wpd-section-heading">Profit Report Email</div>
		<div class="pull-right">
			<a href="<?php echo wpd_ai_admin_page_url( 'settings-emails' ); ?>" class="wpd-input button button-secondary">Return To Settings</a>
			<a href="#" id="send-email-profit-report" class="wpd-input button button-primary">Send Email</a>
		</div>
		<div class="wpd-inline">
			<span class="wpd-filter-wrapper">Profit Report</span>
			<a href="<?php echo wpd_ai_admin_page_url( 'settings-emails-preview-expense-report' ); ?>" class="wpd-filter-wrapper">Expense Report</a>
			<a href="<?php echo wpd_ai_admin_page_url( 'settings-emails-preview-inventory-report' ); ?>" class="wpd-filter-wrapper">Inventory Report</a>
		</div>
	</div>
	<?php 
	wpd_email( 'wpd_profit_report', true );
	wpd_ai_javascript_email_ajax( '#send-email-profit-report', 'wpd_profit_report' );

} elseif( isset($_GET['email_preview']) && $_GET['email_preview'] === 'expense-report' ) {

	?>
	<div class="wpd-wrapper">
		<div class="wpd-section-heading pull-left">Expense Report Email</div>
		<div class="pull-right">
			<a href="<?php echo wpd_ai_admin_page_url( 'settings-emails' ); ?>" class="wpd-input button button-secondary">Return To Settings</a>
			<a href="#" id="send-email-expense-report" class="wpd-input button button-primary">Send Email</a>
		</div>
		<div class="wpd-inline">
			<a href="<?php echo wpd_ai_admin_page_url( 'settings-emails-preview-profit-report' ); ?>" class="wpd-filter-wrapper">Profit Report</a>
			<span class="wpd-filter-wrapper">Expense Report</span>
			<a href="<?php echo wpd_ai_admin_page_url( 'settings-emails-preview-inventory-report' ); ?>" class="wpd-filter-wrapper">Inventory Report</a>
		</div>
	</div>

	<?php
	wpd_email( 'wpd_expense_report', true );
	wpd_ai_javascript_email_ajax( '#send-email-expense-report', 'wpd_expense_report' );

} elseif ( isset($_GET['email_preview']) && $_GET['email_preview'] === 'inventory-report' ) {

	?>
	<div class="wpd-wrapper">
		<div class="wpd-section-heading pull-left">Inventory Report Email</div>
		<div class="pull-right">
			<a href="<?php echo wpd_ai_admin_page_url( 'settings-emails' ); ?>" class="wpd-input button button-secondary">Return To Settings</a>
			<a href="#" id="send-email-inventory-report" class="wpd-input button button-primary">Send Email</a>
		</div>
		<div class="wpd-inline">
			<a href="<?php echo wpd_ai_admin_page_url( 'settings-emails-preview-profit-report' ); ?>" class="wpd-filter-wrapper">Profit Report</a>
			<a href="<?php echo wpd_ai_admin_page_url( 'settings-emails-preview-expense-report' ); ?>" class="wpd-filter-wrapper">Expense Report</a>
			<span class="wpd-filter-wrapper">Inventory Report</span>
		</div>
	</div>
	<?php 
	wpd_email( 'wpd_inventory_report', true );
	wpd_ai_javascript_email_ajax( '#send-email-inventory-report', 'wpd_inventory_report' );

} else {

	?>	
	<div class="wpd-wrapper">
		<div class="wpd-section-heading">Sorry, we couldn't find this email preview</div>
		<a href="<?php echo wpd_ai_admin_page_url( 'settings-emails' ); ?>" class="wpd-input button button-secondary pull-right">Return To Settings</a>
	</div>
	<?php

}

?>