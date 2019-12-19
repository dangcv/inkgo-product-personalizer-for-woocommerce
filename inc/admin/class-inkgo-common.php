<?php

class InkGo_Common
{
    public static $_instance;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function __construct() {
        self::$_instance = $this;
    }

    public function is_connected() {
        $settings = get_option('inkgo');

        if (! isset($settings['api_key']) || (!$settings['api_key'])) {
            return false;
        }

        return true;
    }

    public function ajax_inkgo_check_connect_status() {
        if (self::is_connected()) {
            die ('OK');
        }

        die('FAIL');
    }

    public static function get_inkgo_api_uri() {
        if ( defined( 'INKGO_DEV_API_URI' ) ) {
            return INKGO_DEV_API_URI;
        }

        return INKGO_API_URI;
    }

    public static function get_inkgo_seller_uri() {
        if ( defined( 'INKGO_DEV_SELLER_URI' ) ) {
            return INKGO_DEV_SELLER_URI;
        }

        return INKGO_SELLER_URI;
    }

    public static function ping_to_inkgo() {
        try {
            $client = new InkGo_Client('');
            $pong = $client->get('integration/ping');

            if ($pong == 'ok') {
                return true;
            }
        } catch (InkGoException $e) {
            //
        }

        return false;
    }
}