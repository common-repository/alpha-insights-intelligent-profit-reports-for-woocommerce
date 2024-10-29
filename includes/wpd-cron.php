<?php
/**
 *
 * Cron related functions
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
 *	Register Cron Event
 *
 */
add_action( 'init', 'wpd_ai_setup_cron_schedules' );
function wpd_ai_setup_cron_schedules() {
    if ( ! wp_next_scheduled( 'wpd_schedule_exchange_rate_update' ) ) {
        wp_schedule_event( time(), 'daily', 'wpd_schedule_exchange_rate_update' );
    }
}

/**
 *
 *  Fetch new exchange rates
 *
 */
add_action( 'wpd_schedule_exchange_rate_update', 'wpd_ai_schedule_exchange_rate_update_function' );
function wpd_ai_schedule_exchange_rate_update_function() {

    $oxr                    = wpd_ai_collect_data_oxr();
    $options                = get_option( 'wpd_ai_currency_table' );
    $merged_data            = array_merge( $options, $oxr );
    $update_option          = update_option( 'wpd_ai_currency_table', $merged_data ); 

    if ( $update_option ) {

        $update_option_date = update_option( 'wpd_ai_currency_table_update', current_time( 'timestamp' ) ); 

    }

}
