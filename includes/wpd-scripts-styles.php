<?php
/**
 *
 * Handle Scripts and Styles
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
 *
 *	Load admin css stylesheet
 *
 **/
add_action('admin_enqueue_scripts', 'wpd_ai_admin_enqueue');
function wpd_ai_admin_enqueue() {

	/**
	 *
	 *	Register Styles
	 *
	 */
	wp_register_style( 'wpd-ai-admin', plugins_url( 'assets/css/wpd-alpha-insights-admin.css' , dirname(__FILE__)) );
	wp_register_style( 'wpd-ai-core-style-override', plugins_url( 'assets/css/wpd-style-override-admin.css' , dirname(__FILE__)) );
	wp_register_style( 'wpd-ai-jquery-ui', plugins_url( 'assets/css/jquery-ui.css' , dirname(__FILE__)) );
	wp_register_style( 'wpd-ai-fonts', 'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap');
	wp_register_style( 'wpd-ai-easy-select', WPD_AI_FREE_URL_PATH . 'assets/css/js-easy-select-style.css' );

	/**
	 *
	 *	Register Scripts
	 *
	 */
	wp_register_script( 'wpd-ai-admin', WPD_AI_FREE_URL_PATH . 'assets/js/wpd-alpha-insights-admin.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker', 'jquery-ui-dialog' ) );
	wp_register_script( 'wpd-ai-chartjs-3', WPD_AI_FREE_URL_PATH . 'assets/js/chartjs3/chart.min.js', array( 'jquery' ), false, false ); // 3.0.xx
	wp_register_script( 'wpd-ai-chartjs-date-fns-2', WPD_AI_FREE_URL_PATH . 'assets/js/chartjs3/date-fns-2.js', array( 'jquery' ), false, false ); // 2.0
	wp_register_script( 'wpd-ai-chartjs-date-fns-2-adapter', WPD_AI_FREE_URL_PATH . 'assets/js/chartjs3/chartjs-adapter-date-fns-2.js', array( 'jquery' ), false, false ); // 2.0
	wp_register_script( 'wpd-ai-easy-select', WPD_AI_FREE_URL_PATH . 'assets/js/js-easy-select.js', array( 'jquery' ), false, true ); // 2.9.xx

	/**
	 *
	 *	Add vars if I need them
	 *
	 */
	// Localize the script with new data
	$wpd_ai_vars = array(
	    'processing' => wpd_ai_preloader( 40, true, true ),
	    'success' 	=> wpd_ai_success( 40, true, true ),
	    'failure' 	=> wpd_ai_failure( 40, true, true ),
	);
	wp_localize_script( 'wpd-ai-admin', 'wpdAlphaInsights', $wpd_ai_vars );

	/**
	 *
	 *	Enqueue Styles
	 *
	 */
	// Only load my styles on my pages
	if ( is_wpd_page() ) {
		wp_enqueue_style( 'wpd-ai-admin' );
	}
	
	wp_enqueue_style( 'wpd-ai-jquery-ui' );
	wp_enqueue_style( 'wpd-ai-fonts' );
	wp_enqueue_style( 'wpd-ai-easy-select' );

	// Conditional check for override
	if ( get_option('wpd_ai_admin_style_override') == 1 ) {
		wp_enqueue_style( 'wpd-ai-core-style-override' );
	}

	/**
	 *
	 *	Enqueue Scripts
	 *
	 */
	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_enqueue_script( 'jquery-ui-dialog' );
	wp_enqueue_script( 'wpd-ai-admin' );
	wp_enqueue_script( 'wpd-ai-chartjs-3' );
	wp_enqueue_script( 'wpd-ai-chartjs-date-fns-2' );
	wp_enqueue_script( 'wpd-ai-chartjs-date-fns-2-adapter' );
	wp_enqueue_script( 'wpd-ai-easy-select' );

}

/**
 *
 *	Front end enqueue
 *
 */
add_action( 'wp_enqueue_scripts', 'wpd_ai_frontend_scripts_styles' ); 
function wpd_ai_frontend_scripts_styles() {

	wp_register_script( 'wpd-ai-frontend', WPD_AI_FREE_URL_PATH . 'assets/js/wpd-alpha-insights-frontend.js', array('jquery'), false, true );
	wp_register_script( 'wpd-ai-sessions', WPD_AI_FREE_URL_PATH . 'assets/js/interactor.js', array(), false, true );

 	wp_enqueue_script( 'wpd-ai-frontend' );
	wp_enqueue_script( 'wpd-ai-sessions' );

	// Pass PHP vars
	$page_id	= get_the_ID();
	$user_id 	= get_current_user_id();

	wp_localize_script( 'wpd-ai-sessions', 'wpd_ai_session_vars', 
		array( 
			'page_id' => $page_id,
			'user_id' => $user_id
		) 
	);

}