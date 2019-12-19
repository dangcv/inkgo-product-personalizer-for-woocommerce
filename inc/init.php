<?php
/**
 * Main init file for the plugin.
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

require INKGO_PLUGIN_DIR . 'inc/functions.php';

if (is_admin())
{
	require INKGO_PLUGIN_DIR . 'inc/admin/functions.php';
}
?>