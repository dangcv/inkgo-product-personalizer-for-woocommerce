<?php
/**
 * Main init file for admin
 *
 * @package inkgo_plugin
 *
 * @copyright 2019 inkgo.io
 * @version 1.0.0
 * @author inkgo
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
require INKGO_PLUGIN_DIR . 'inc/woocommerce/init.php';

/**
 * Load plugin textdomain.
 */
add_action('plugins_loaded', 'inkgo_load_textdomain');
function inkgo_load_textdomain()
{
    load_plugin_textdomain('inkgo', false, 'inkgo/languages');
}

/*
* Check website install pluign or No
 */
function inkgo_register_session()
{
	if( !session_id() ){
		session_start();
	}

	if(isset($_GET['inkgo_installed']))
	{
		$result 			= array();
		$result['error'] 	= 0;
        $result['site_name']  = get_bloginfo();
		$permalinks 	= get_option( 'permalink_structure', false );
		if($permalinks != '')
		{
			$result['permalinks'] = 1;
		}
		else
		{
			$result['permalinks'] = 0;
			$result['error'] 	= 1;
		}
		if(function_exists('WC'))
		{
			$result['woo'] = WC()->version;
		}
		$result['inkgo_version'] = INKGO_VERSION;
		wp_send_json($result);
	}
}
add_action('init', 'inkgo_register_session');


/**
 * Load setting of plugin
 */
function inkgo_get_settings()
{
    $settings = get_option('inkgo');

    return $settings;
}

/* post data to API of inkgo */
function inkgo_api_post($type, $options)
{
	$url 			= INKGO_API_URI.$type;
	
	$response 		= wp_remote_post($url, array(
		'body' => $options,
	));

	if( !is_wp_error($response) )
	{
		return $response['body'];
	}
	
	return '';
}

if ( ! class_exists( 'WP_REST_Server' ) ) {
    return;
}

// Init REST API routes.
add_action( 'rest_api_init', 'register_rest_routes', 20);
add_action( 'wp_ajax_ajax_inkgo_check_connect_status', array( 'InkGo_Common', 'ajax_inkgo_check_connect_status' ) );
function register_rest_routes()
{
    require_once INKGO_PLUGIN_DIR .'inc/class-inkgo-rest-api-controller.php';
    $inkgoRestAPIController = new Inkgo_REST_API_Controller();
    $inkgoRestAPIController->register_routes();
}
?>
