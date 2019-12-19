<?php
/**
 * All function of admin settings
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
include(INKGO_PLUGIN_DIR . 'inc/admin/product.php');
include(INKGO_PLUGIN_DIR . 'inc/admin/class-inkgo-common.php');
include(INKGO_PLUGIN_DIR . 'inc/admin/class-inkgo-client.php');


add_action('plugins_loaded', 'inkgo_check_woocommerce_active');
function inkgo_check_woocommerce_active()
{
	$check 	= inkgo_is_woocommerce_active();
	if($check == false)
	{
		add_action( 'admin_notices', 'inkgo_install_woo' );
	}
}

/* 
* show ask install Woocommerce 
*/
function inkgo_install_woo()
{
	$class 		= 'notice notice-error';
	$message 	= __( 'WooCommerce plugin is required, please install plugin WooCommerce before use this plugin', 'inkgo' );

	printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) ); 
}

add_action('admin_menu', 'inkgo_admin_menu', 30);
function inkgo_admin_menu()
{
	add_menu_page( esc_attr(__('InkGo', 'inkgo')), esc_attr(__('InkGo', 'inkgo')), 'administrator', 'inkgo', 'inkgo_settings', INKGO_PLUGIN_URI.'/assets/images/inkgo.svg');
    add_submenu_page( 'inkgo', esc_attr(__('InkGo Settings', 'inkgo')), esc_attr(__('Settings', 'inkgo')), 'administrator', 'inkgo', 'inkgo_settings');
    add_submenu_page( 'inkgo', esc_attr(__('About InkGo', 'inkgo')), esc_attr(__('About InkGo', 'inkgo')), 'manage_options', 'inkgo-import', 'inkgo_add_product');
}
function inkgo_add_product() {
    wp_redirect(INKGO_SELLER_URI);
}

function inkgo_settings()
{
	if( isset($_POST['inkgo_settings_hidden']) && $_POST['inkgo_settings_hidden'] == 'Y' )
	{
		$settings 	= array_map( 'esc_attr', $_POST['inkgo'] );
		update_option( 'inkgo', $settings);
	}

    $settings = get_option('inkgo');

    $issues = array();

    $permalinks = get_option( 'permalink_structure', false );

    if ( $permalinks && strlen( $permalinks ) > 0 ) {
        // ok
    } else {
        $message      = __('WooCommerce API will not work unless your permalinks are set up correctly. Go to <a href="%s">Permalinks settings</a> and make sure that they are NOT set to "plain".');
        $settings_url = esc_url(admin_url( 'options-permalink.php' ));
        $issues[]     = sprintf( $message, $settings_url );
    }

    if ( strpos( get_site_url(), 'localhost' ) ) {
        $issues[] = esc_html("You can not connect to InkGo from localhost. InkGo needs to be able reach your site to establish a connection.");
    }

    if (! InkGo_Common::ping_to_inkgo()) {
        $issues[] = esc_html("Can not connect to InkGo Server. Please check your server network connection.");
    }

	require INKGO_PLUGIN_DIR . 'inc/admin/html/settings.php';
}
?>