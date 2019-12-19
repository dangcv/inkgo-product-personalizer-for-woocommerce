<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/*
Add js, css in page edit product
 */
function inkgo_add_admin_scripts($hook)
{
	global $post;
    if ( $hook == 'post-new.php' || $hook == 'post.php' )
    {
        if ( 'product' === $post->post_type )
        {
            wp_enqueue_script( 'inkgo_admin-js', INKGO_PLUGIN_URI.'assets/js/admin.js', array(), INKGO_VERSION, true );		
			wp_enqueue_style( 'inkgo_admin-css', INKGO_PLUGIN_URI.'assets/css/admin.css', array(), INKGO_VERSION );	
        }
    }
    elseif ($hook == 'toplevel_page_inkgo')
    {
        wp_enqueue_script( 'inkgo_admin-js', INKGO_PLUGIN_URI.'assets/js/connect.js', array(), INKGO_VERSION, true );
        wp_enqueue_style( 'inkgo_admin-css', INKGO_PLUGIN_URI.'assets/css/admin.css', array(), INKGO_VERSION );
    }
    wp_register_style( 'inkgo_admin_menu_css' );
    wp_enqueue_style( 'inkgo_admin_menu_css' );
    wp_add_inline_style( 'inkgo_admin_menu_css', '#toplevel_page_inkgo .wp-menu-image img{padding:5px 0 0 0;width:16px;}' );
}
add_action( 'admin_enqueue_scripts', 'inkgo_add_admin_scripts', 10, 1 );

/* show thumb of variation in page edit product */
add_action( 'woocommerce_variation_options', 'inkgo_variation_thumb', 10, 3 );
function inkgo_variation_thumb($loop, $variation_data, $variation)
{
	$thumbs = get_post_meta($variation->ID, 'inkgo_variation_img', true);
	if(is_array($thumbs) && count($thumbs))
	{
		$html 	= '<div class="inkgo-thumb">';
		for($i=0; $i<count($thumbs); $i++)
		{
			$image = array();
			if(is_string($thumbs[$i]))
				$image = json_decode($thumbs[$i], true);
			if(isset($image['src']))
				$html .= '<a href="javascript:void(0);" class="inkgo-thumb"><img width="64" src="'.esc_url($image['src']).'" alt=""></a>';
		}
		$html .= '</div>';
		echo $html;
	}
}

/* show link download from page order detail */
add_action( 'woocommerce_before_order_itemmeta', 'inkgo_download_oder_item', 99, 3);
function inkgo_download_oder_item($item_id, $item, $product)
{
	$url 	= wc_get_order_item_meta( $item_id, 'inkgo_file_output', true );
	if(!$url)
	{
		$url 	= inkgo_get_file_output($item, $item['order_id'], $item_id);
	}
	if( is_array($url) && count($url) > 0 )
	{
		$html = '<p><b>Download</b>: ';
		foreach($url as $view => $data)
		{
			$html .= '<a class="button" href="'.esc_url($data['url']).'" target="_blank">'.$data['name'].'</a> ';
		}
		$html .= '</p>';

		echo $html;
	}
}
?>