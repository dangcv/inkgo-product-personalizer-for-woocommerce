<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

$inkgo_product;

/* add js file to page product detail */
function inkgo_frontend_scripts()
{
	if ( is_product() )
	{
		global $inkgo_product;
		$product_id 			= get_the_ID();
		$inkgo_campaign_id 		= get_post_meta( $product_id, 'inkgo_campaign_id', true );
		if($inkgo_campaign_id)
		{
			/* add file js */
			$version 	= INKGO_VERSION;
			$file_name 	= 'frontend';
			if( INKGO_DEV == 1 )
			{
				$version 	= time();
				$file_name 	= 'frontend-dev';
				$file_js 	= INKGO_PLUGIN_URI.'assets/js/'.$file_name.'.js';
				$file_css 	= INKGO_PLUGIN_URI.'assets/css/'.$file_name.'.css';
			}
			else
			{
				$file_js 	= 'https://cdn.inkgo.io/assets/frontend.min.js';
				$file_css 	= 'https://cdn.inkgo.io/assets/frontend.min.css';
			}
			wp_enqueue_script( 'inkgo_app-js', $file_js, array('jquery', 'flexslider'), $version, true );		
			wp_enqueue_style( 'inkgo_app-css', $file_css, array(), $version);

			/* add language js */
			$inkgo_product->id 			= $product_id;
			$inkgo_product->campaign_id = $inkgo_campaign_id;

			$inkgo_product->is_custom 	= get_post_meta( $product_id, 'inkgo_campaign_custom', true );
			$settings 					= inkgo_get_settings();
			$inkgo_product->settings 	= $settings;

			if($inkgo_product->is_custom == 1 && isset($settings['api_key']) && $settings['api_key'] != '')
			{
				$button 		= esc_html(__('Personalize Design', 'inkgo'));
				if( isset($settings['button']) && $settings['button'] != '' )
					$button 	= esc_html($settings['button']);

				$lang 	= array();

				$lang['personalize'] 			= $button;
				$lang['preview'] 				= esc_html(__('Preview your design', 'inkgo'));
				$lang['mobile_help'] 			= esc_html(__('Click on text or image to edit', 'inkgo'));
				$lang['upload'] 				= esc_html(__('Please upload file JPG, PNG, JPEG, GIF', 'inkgo'));
				$lang['required'] 				= esc_html(__('Please enter all text fields and upload images', 'inkgo'));

				$lang['photo_title'] 			= esc_html(__('Photo', 'inkgo'));
				if( isset($settings['label']) && $settings['label'] != '' )
					$lang['photo_title'] 		= esc_html($settings['label']);

				$lang['save'] 					= esc_html(__('Confirm', 'inkgo'));
				if( isset($settings['confirm']) && $settings['confirm'] != '' )
					$lang['save'] 				= esc_html($settings['confirm']);

				$lang['change'] 				= esc_html(__('Change', 'inkgo'));
				if( isset($settings['change']) && $settings['change'] != '' )
					$lang['change'] 			= esc_html($settings['change']);

				$lang['rotate'] 				= esc_html(__('Rotate', 'inkgo'));
				if( isset($settings['rotate']) && $settings['rotate'] != '' )
					$lang['rotate'] 			= esc_html($settings['rotate']);

				wp_add_inline_script('inkgo_app-js', 'var inkgo_campaign_custom = 1; var inkgo_lang = '.json_encode($lang).'; var inkgo_dis_mobile = 1; var INKGO_JSON_URL = "'.INKGO_JSON_URI.'"; var inkgo_campaign_id = "'.$inkgo_campaign_id.'"; inkgo_woo_id = "'.$product_id.'"; var inkgo_ajax_url = "'.admin_url('admin-ajax.php').'";', 'before');
			}
			else
			{
				wp_add_inline_script('inkgo_app-js', 'var inkgo_campaign_custom = 0; var INKGO_JSON_URL = "'.INKGO_JSON_URI.'"; var inkgo_campaign_id = "'.$inkgo_campaign_id.'"; inkgo_woo_id = "'.$product_id.'"; var inkgo_ajax_url = "'.admin_url('admin-ajax.php').'"', 'before');
			}
		}
	}
	elseif ( is_checkout() && !empty( is_wc_endpoint_url('order-received') ) )
	{
		/* call to ajax create file output in page completed order */
		if ( ! wp_script_is( 'jquery', 'done' ) ) {
			wp_enqueue_script( 'jquery' );
		}
		$order_id = wc_get_order_id_by_order_key( sanitize_title($_GET['key']) );
		$url 	= admin_url('admin-ajax.php').'?action=inkgo_ajax_output&order_id='.$order_id;
   		wp_add_inline_script( 'jquery-migrate', 'jQuery.get("'.$url.'", function(data) {});');
	}
}
add_action( 'wp_enqueue_scripts', 'inkgo_frontend_scripts');

/* show input design in page product */
add_action( 'woocommerce_before_add_to_cart_button', 'inkgo_design_fields', 30 );
function inkgo_design_fields()
{
	global $inkgo_product;
	
	if( isset($inkgo_product->campaign_id) && $inkgo_product->campaign_id != '' )
	{
		WC()->session->set('inkgo_campaign_id', $inkgo_product->campaign_id);
		$_SESSION['inkgo_campaign_id'] = $inkgo_product->campaign_id;

		$settings 						= $inkgo_product->settings;
		if($inkgo_product->is_custom == 1 && isset($settings['api_key']) && $settings['api_key'] != '')
		{
			$text_head 		= esc_html(__('Personalize design', 'inkgo'));
			if( isset($settings['header']) && $settings['header'] != '' )
				$text_head 	= esc_html($settings['header']);

			$button 		= esc_html(__('Personalize Design', 'inkgo'));
			if( isset($settings['button']) && $settings['button'] != '' )
				$button 	= esc_html($settings['button']);
			
			$close_icon = '<a href="javascript:void(0);" class="inkgo-mobile-close inkgo-show-mobile"><svg viewBox="0 0 512 512" xml:space="preserve"><g><path d="M505.943,6.058c-8.077-8.077-21.172-8.077-29.249,0L6.058,476.693c-8.077,8.077-8.077,21.172,0,29.249    C10.096,509.982,15.39,512,20.683,512c5.293,0,10.586-2.019,14.625-6.059L505.943,35.306    C514.019,27.23,514.019,14.135,505.943,6.058z"/><path d="M505.942,476.694L35.306,6.059c-8.076-8.077-21.172-8.077-29.248,0c-8.077,8.076-8.077,21.171,0,29.248l470.636,470.636    c4.038,4.039,9.332,6.058,14.625,6.058c5.293,0,10.587-2.019,14.624-6.057C514.018,497.866,514.018,484.771,505.942,476.694z"/></g></svg></a>';
			
			echo '<div class="inkgo-design inkgo-hide-mobile" data-id="'.esc_attr($inkgo_product->campaign_id).'"><h4 class="inkgo-title">'.$text_head.' '.$close_icon.'</h4><div class="inkgo-design-items"></div></div>';
			echo '<button type="button" name="btn_inkgo_customize" class="inkgo-custom-mobile inkgo-hidden inkgo-show-mobile">'.$button.'</button>';
			echo '<input type="hidden" name="inkgo_design_info" class="inkgo_design_info">';
			echo '<div class="inkgo-thumbs"></div>';
		}
	}
}