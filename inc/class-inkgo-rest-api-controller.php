<?php

/**
 * API class
 */
class Inkgo_REST_API_Controller extends WC_REST_Controller
{
    /**
     * Endpoint namespace
     */
    protected $namespace = 'wc/v3';

    /**
     * Route base
     */
    protected $rest_base = 'inkgo';

    /**
     * Register the route api
     */
    public function register_routes()
    {
        register_rest_route($this->namespace, '/' . $this->rest_base . '/access', array(
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array($this, 'set_inkgo_access'),
                'permission_callback' => array($this, 'get_items_permissions_check'),
                'show_in_index' => false,
                'args' => array(
                    'access_key' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => __('Inkgo access key', 'inkgo'),
                    ),
                    'store_id' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => __('Store Identifier', 'inkgo'),
                    ),
                ),
            )
        ));

        register_rest_route($this->namespace, '/' . $this->rest_base . '/categories', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_categories'),
                'show_in_index' => false,
            )
        ));

        register_rest_route($this->namespace, '/' . $this->rest_base . '/complete-sync-products', array(
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array($this, 'update_sku_and_image_for_products'),
                'permission_callback' => array($this, 'update_item_permissions_check'),
                'show_in_index' => false,
                'args' => array(
                    'data' => array(
                        'required' => true,
                        'type' => 'json',
                        'description' => __('List data', 'inkgo'),
                    )
                ),
            )
        ));
    }

    public function get_categories()
    {
        $orderby = 'id';
        $order = 'asc';
        $hide_empty = false;
        $cat_args = array(
            'orderby' => $orderby,
            'order' => $order,
            'hide_empty' => $hide_empty,
        );

        $data = get_terms('product_cat', $cat_args);

        return compact('data');
    }

    /**
     * Handle inkgo access
     *
     * @param WP_REST_Request $request
     * @return array
     */
    public function set_inkgo_access($request)
    {
        $error = false;

        $options = get_option('woocommerce_inkgo_settings', array());

        $api_key = $request->get_param('access_key');
        $store_id = $request->get_param('store_id');
        $store_id = intval($store_id);

        if (!is_string($api_key) || strlen($api_key) == 0 || $store_id == 0) {
            $error = 'Failed to update access data';
        }

        $options['api_key'] = $api_key;
        $options['store_id'] = $store_id;

        update_option('inkgo', $options);

        return compact('error');
    }

    public function update_sku_and_image_for_products($request)
    {
        $data = $request->get_param('data');

        foreach ($data as $productId => $item) {
            $product = wc_get_product($productId);

            if (!$product) {
                continue;
            }

            update_post_meta($productId, '_sku', $item['sku']);

            if (isset($item['image'])) {

                $upload = wc_rest_upload_image_from_url(esc_url_raw($item['image']['src']));

                if (!is_wp_error($upload)) {
                    $attachment_id = wc_rest_set_uploaded_image_as_attachment($upload, $product->get_id());

                    update_post_meta($productId, '_thumbnail_id', $attachment_id);
                }
            }
        }

        return [];
    }

    /**
     * Check whether a given request has permission to read inkgo endpoints.
     *
     * @param WP_REST_Request $request Full details about the request.
     * @return WP_Error|boolean
     */
    public function get_items_permissions_check($request)
    {
        if (!wc_rest_check_user_permissions()) {
            return new WP_Error('woocommerce_rest_cannot_view', __('Sorry, you cannot list resources.', 'woocommerce'), array('status' => rest_authorization_required_code()));
        }

        return true;
    }

    public function update_item_permissions_check($request)
    {
        if (!wc_rest_check_post_permissions('product', 'create')) {
            return new WP_Error('woocommerce_rest_cannot_edit', __('Sorry, you are not allowed to edit this resource.', 'woocommerce'), array('status' => rest_authorization_required_code()));
        }

        return true;
    }
}
