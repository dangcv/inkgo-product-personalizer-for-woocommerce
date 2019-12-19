<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/* Saved info of customize when add to cart */
add_action( 'woocommerce_add_to_cart', 'inkgo_save_custom_design', 1, 5 );
function inkgo_save_custom_design($cart_item_key, $product_id = null, $quantity= null, $variation_id= null, $variation= null)
{
	if( isset($_POST['inkgo_design_info']) )
	{
		$inkgo_campaign_id 			= get_post_meta( $product_id, 'inkgo_campaign_id', true );
		$design 					= array();

		$inkgo_token_id 			= WC()->session->get('inkgo_token_id', '');
		if($inkgo_token_id == '')
		{
			if(isset($_SESSION['inkgo_token_id']) )
			{
				$inkgo_token_id 	= $_SESSION['inkgo_token_id'];
			}
			elseif(isset($_POST['inkgo_token_id']) )
			{
				$inkgo_token_id 	= sanitize_text_field($_POST['inkgo_token_id']);
			}
			else
			{
				$inkgo_token_id 		= 'none';
			}
		}
		if( isset($_POST['inkgo_design_thumb']) )
		{
			$design['thumbs'] 	= array();
			$imgs				= array_map( 'esc_attr', $_POST['inkgo_design_thumb'] );
			$dir 				= wp_upload_dir();
			$path 				= $dir['path'];
			foreach($imgs as $id => $img)
			{
				if( $img != '')
				{
					$temp 		= explode(';base64,', $img);
					$buffer		= base64_decode($temp[1]);
					$filename 	= 'inkgo-'.$inkgo_campaign_id.'-'.$inkgo_token_id.'-'.$id.'.jpeg';
					$file 		= $path .'/'. $filename;

					$savefile 	= fopen($file, 'w');
					fwrite($savefile, $buffer);
					fclose($savefile);

					$design['thumbs'][$id] = $dir['url'] .'/'. $filename;
				}
			}
		}
			
		$design['campaign_id'] 		= $inkgo_campaign_id;
		$design['inkgo_token_id'] 	= $inkgo_token_id;
		$design['customize'] 		= array_map( 'esc_attr', $_POST['inkgo_design_info'] );
		WC()->session->set( $cart_item_key.'_inkgo_design', $design );
	}
}

function inkgo_cart_unique_key($cart_item_data, $product_id)
{
	if( isset($_POST['inkgo_design_info']) && $_POST['inkgo_design_info'] != '' )
	{
		$cart_item_data['unique_key'] = md5( microtime().rand() );
		return $cart_item_data;
	}
}
add_filter( 'woocommerce_add_cart_item_data','inkgo_cart_unique_key', 10, 2 );

/* save design of each item in page checkout */
add_action( 'woocommerce_add_order_item_meta', 'inkgo_save_item_design', 1, 3 );
function inkgo_save_item_design($item_id, $values, $cart_item_key)
{
	$inkgo_fulfillment 		= get_post_meta( $values['variation_id'], 'inkgo_fulfillment', true );
	if($inkgo_fulfillment)
	{
		wc_add_order_item_meta( $item_id, "inkgo_fulfillment",  $inkgo_fulfillment);
	}
	if( WC()->session->__isset( $cart_item_key.'_inkgo_design' ) )
	{
		$design 				= WC()->session->get( $cart_item_key.'_inkgo_design');
		$design['item_id']		= $item_id;

		if(isset($design['inkgo_token_id']))
		{
			wc_add_order_item_meta( $item_id, "inkgo_custom_id",  $design['inkgo_token_id']);
		}
		wc_add_order_item_meta( $item_id, "inkgo_custom_designer",  $design);

		$settings 	= inkgo_get_settings();
		inkgo_api_post('order/'.$settings['api_key'], $design);
	}
}

/*
* show thumb of product customize in page cart and order
 */
function inkgo_cart_item_thumbnail($thumb, $cart_item, $cart_item_key)
{
	if( WC()->session->__isset( $cart_item_key.'_inkgo_design' ) )
	{
		$data = WC()->session->get( $cart_item_key.'_inkgo_design');
		if( isset($data['thumbs']) && count($data['thumbs']) > 0 )
		{
			$thumb 	= '';
			foreach($data['thumbs'] as $src)
			{
				$thumb 	.= '<img src="'.esc_url($src).'" alt="" class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail">';
			}
		}
		elseif( isset($cart_item['variation_id']) &&  isset($data['campaign_id']) && $data['campaign_id'] != '' )
		{
			$inkgo_thumbs = get_post_meta( $cart_item['variation_id'], 'inkgo_variation_img', true );
			if($inkgo_thumbs)
			{
				$thumbs = json_decode($inkgo_thumbs[0], true);
				$thumb = '<img src="'.esc_url($thumbs['src']).'" alt="" class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail">';
			}
		}
	}
	elseif(isset($cart_item['variation_id']) && $cart_item['variation_id'] > 0)
	{
		$inkgo_thumbs = get_post_meta( $cart_item['variation_id'], 'inkgo_variation_img', true );
		if($inkgo_thumbs)
		{
			$thumbs = json_decode($inkgo_thumbs[0], true);
			$thumb = '<img src="'.esc_url($thumbs['src']).'" alt="" class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail">';
		}
	}
	return $thumb;
}
add_filter( 'woocommerce_cart_item_thumbnail', 'inkgo_cart_item_thumbnail', 999, 3);


/* create file output of order */
add_action( 'wp_ajax_inkgo_ajax_output', 'inkgo_ajax_output');
add_action( 'wp_ajax_nopriv_inkgo_ajax_output', 'inkgo_ajax_output');
function inkgo_ajax_output()
{
	if(isset($_GET['order_id']))
	{
		$order_id 		= absint($_GET['order_id']);
		$order 			= new WC_Order( $order_id );
		if(count($order))
		{
			$order_items 	= $order->get_items();
			$urls 			= array();
			foreach ($order_items as $item_id => $item_data)
			{
				$output = wc_get_order_item_meta( $item_id, 'inkgo_file_output', true );
				if(!$output)
				{
					$urls 	= inkgo_get_file_output($item_data, $order_id, $item_id);
					if(count($urls)){
						wc_add_order_item_meta( $item_id, 'inkgo_file_output', $urls);
					}
				}
			}
		}
	}
	echo 'ok';
	exit;
}

/* get file output with each item_id of order */
function inkgo_get_file_output($item, $order_id, $item_id)
{
	$urls 				= array();
	$inkgo_campaign_id 	= get_post_meta( $item['product_id'], 'inkgo_campaign_id', true );
	$products 			= get_post_meta( $item['product_id'], 'inkgo_campaign_mockups', true );
	$inkgo_product_id 	= get_post_meta( $item['variation_id'], 'inkgo_product_id', true );
	if($inkgo_campaign_id && $products && $inkgo_product_id && isset($products[$inkgo_product_id]))
	{
		$data 			= wc_get_order_item_meta( $item_id, "inkgo_custom_designer", true );
		if(empty($data['inkgo_token_id']))
		{
			$data['inkgo_token_id'] = 'none';

			$inkgo_custom_id = wc_get_order_item_meta( $item_id, "inkgo_custom_id", true );
			if($inkgo_custom_id)
			{
				$data['inkgo_token_id'] = $inkgo_custom_id;
			}
		}
		else
		{
			wc_delete_order_item_meta($item_id, 'inkgo_custom_designer');
		}
		$sku 			= get_post_meta( $item['variation_id'], '_sku', true );
		if($sku != '') $sku = '/'.$sku;
		foreach($products[$inkgo_product_id] as $view => $name)
		{
			$url = INKGO_SELLER_URI.'/download/index/'.$inkgo_campaign_id.'/'.$data['inkgo_token_id'].'/'.$inkgo_product_id.'/'.$view.'/'.$order_id.'/'.$item_id.$sku;
			$urls[$view] = array(
				'view' => $view,
				'inkgo_product_id' => $inkgo_product_id,
				'name' => $name,
				'url' => $url,
			);
			$response = wp_remote_get( $url );
		}
	}
	return $urls;
}
?>