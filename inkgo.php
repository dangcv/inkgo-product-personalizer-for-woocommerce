<?php
/**
 * @package inkgo
 * @version 2.0.0
 */
/*
Plugin Name: InkGo Product Personalizer for WooCommerce
Plugin URI: https://wordpress.org/plugins/inkgo-product-personalizer-for-woocommerce/
Description: InkGo - solution for sell personalized print-on-demand products and automatic connect fulfillment.
Author: InkGo
Version: 2.0.0
Author URI: https://inkgo.io
Text Domain: inkgo
*/
define('INKGO_DEV', 0);
define('INKGO_VERSION', '2.0.0');
define('INKGO_PLUGIN_FILE', __FILE__);
define('INKGO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('INKGO_PLUGIN_URI', plugin_dir_url(__FILE__));

define('INKGO_CDN_URI', 'https://cdn.inkgo.io/');
define('INKGO_SELLER_URI', 'https://seller.inkgo.io');
define('INKGO_API_URI', 'https://api.inkgo.io/');
define('INKGO_JSON_URI', 'https://json.inkgo.io/');
define('INKGO_INSTALLER_PLUGIN_FILE', __FILE__);
define('INKGO_SINGLE_TRANSLATION_FILE', true);

require INKGO_PLUGIN_DIR . 'inc/init.php';
?>