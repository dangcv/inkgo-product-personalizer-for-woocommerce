<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/* get token of design when load page product detail */
add_action( 'wp_ajax_inkgo_token', 'inkgo_token');
add_action( 'wp_ajax_nopriv_inkgo_token', 'inkgo_token');
function inkgo_token()
{
	$result 			= array();
	if(!function_exists('WC')) return;
	
	$result['token'] 	= '';
	if( WC()->session->get('inkgo_campaign_id', '') || isset($_SESSION['inkgo_campaign_id']) || isset($_POST['inkgo_campaign_id']) )
	{
		$settings 		= inkgo_get_settings();
		$timezone 		= '-0700';
		if( isset($_POST['timezone']) )
		{
			$timezone	= sanitize_text_field($_POST['timezone']);
		}

		$design_id 		= '';
		if(WC()->session->get('inkgo_campaign_id', '')) $design_id = WC()->session->get('inkgo_campaign_id', ''); 
		elseif( isset($_SESSION['inkgo_campaign_id']) ) $design_id = $_SESSION['inkgo_campaign_id'];
		elseif( isset($_POST['inkgo_campaign_id']) ) $design_id = sanitize_text_field($_POST['inkgo_campaign_id']);
		$options = array(
			'design_id' 	=> $design_id,
			'timezone' 		=> $timezone,
			'api' 			=> $settings['api_key'],
		);

		if(isset($_POST['layers']))
		{
			$options['layers'] = array_map( 'esc_attr', $_POST['layers'] );
		}

		$response 		= inkgo_api_post('upload/token', $options);
		if( !$response )
		{
			$url 		= INKGO_API_URI .'upload/token?api='.$settings['api_key'].'&design_id='.$design_id.'&layers='.implode('-', $options['layers']);
			$content 	= wp_remote_get( $url );
			if( !is_wp_error($content) )
			{
				$response = $content['body'];
			}
		}
		if( $response )
		{
			$options 	= json_decode($response, true);
			if( isset($options['data']) && isset($options['data']['token']) && $options['data']['token'] != '' )
			{
				$result['token'] 			= $options['data']['token'];
				WC()->session->set('inkgo_token_id', $result['token']);

				if( isset($options['data']['base_image_url']) )
				{
					$result['s3_base_url'] 	= $options['data']['base_image_url'];
				}

				if( isset($options['data']['urls']) )
				{
					$result['s3_upload_urls'] 	= $options['data']['urls'];
				}
				if( isset($options['data']['s3_domain']) )
				{
					$result['s3_domain'] 	= $options['data']['s3_domain'];
				}
			}
		}
		else
		{
			$result['error'] = $response;
		}
	}
	wp_send_json($result);
}
?>