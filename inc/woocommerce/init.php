<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
require INKGO_PLUGIN_DIR . 'inc/woocommerce/cart.php';
require INKGO_PLUGIN_DIR . 'inc/woocommerce/design.php';
require INKGO_PLUGIN_DIR . 'inc/woocommerce/product.php';
require INKGO_PLUGIN_DIR . 'inc/woocommerce/attributes.php';

/**
 * Returns if woocommerce is active.
 *
 * @return boolean If woocommerce plugins is active.
 */
function inkgo_is_woocommerce_active()
{
	$installed 	= true;
	if (!defined('WC_VERSION'))
	{
		$installed 	= flase;
	}

	return $installed;
}
?>