<?php

/**
 * Class InkGo_Client
 */
class InkGo_Client
{
    private $userAgent = 'InkGo_WooCommerce_Plugin';
    private $apiUrl;
    private $key;

    /**
     * InkGo_Client constructor.
     *
     * @param $key
     * @param bool $disable_ssl
     * @throws InkGoException
     */
    public function __construct($key, $disable_ssl = false) {
        $key = (string) $key;

        $this->userAgent .= ' ' . INKGO_VERSION . ' (WP ' . get_bloginfo( 'version' ) . ' + WC ' . WC()->version . ')';

        if ( ! function_exists( 'json_decode' ) || ! function_exists( 'json_encode' ) ) {
            throw new InkGoException( 'PHP JSON extension is required for the InkGo API library to work!' );
        }

        $this->key = $key;

        $this->apiUrl = InkGo_Common::get_inkgo_seller_uri();

        if ( $disable_ssl ) {
            $this->apiUrl = str_replace( 'https://', 'http://', $this->apiUrl );
        }
    }

    /**
     * Make an GET Request
     *
     * @param $path
     * @param array $params
     * @return mixed
     * @throws InkGoHttpRequestException
     */
    public function get( $path, $params = array() ) {
        return $this->request( 'GET', $path, $params );
    }

    /**
     * Make an POST Request
     *
     * @param $path
     * @param array $data
     * @param array $params
     * @return mixed
     * @throws InkGoHttpRequestException
     */
    public function post( $path, $data = array(), $params = array() ) {
        return $this->request( 'POST', $path, $params, $data );
    }

    /**
     * Make an PUT Request
     *
     * @param $path
     * @param array $data
     * @param array $params
     * @return mixed
     * @throws InkGoHttpRequestException
     */
    public function put( $path, $data = array(), $params = array() ) {
        return $this->request( 'PUT', $path, $params, $data );
    }

    /**
     * Make an DELETE Request
     *
     * @param $path
     * @param array $params
     * @return mixed
     * @throws InkGoHttpRequestException
     */
    public function delete( $path, $params = array() ) {
        return $this->request( 'DELETE', $path, $params );
    }

    /**
     * Make an HTTP Request to InkGo
     *
     * @param $method
     * @param $path
     * @param array $params
     * @param null $data
     * @return mixed
     * @throws InkGoHttpRequestException
     */
    protected function request( $method, $path, array $params = array(), $data = null ) {
        $path = trim( $path, '/' );

        if ( ! empty( $params ) ) {
            $path .= '?' . http_build_query( $params );
        }

        $request = array(
            'timeout'    => 10,
            'user-agent' => $this->userAgent,
            'method'     => $method,
            'headers'    => array( 'Authorization' => 'Basic ' . base64_encode( $this->key ) ),
            'body'       => $data !== null ? json_encode( $data ) : null,
        );

        $result = wp_remote_get( $this->apiUrl.'/'. $path, $request );

        $result = apply_filters( 'inkgo_api_result', $result, $method, $this->apiUrl . $path, $request );

        if ( is_wp_error( $result ) ) {
            throw new InkGoHttpRequestException( "API request failed - " . $result->get_error_message() );
        }

        $response = json_decode( $result['body'], true );

        if ( ! isset( $response['code'], $response['data'] ) ) {
            throw new InkGoHttpRequestException( 'Invalid API response' );
        }
        $status = (int) $response['code'];
        if ( $status < 200 || $status >= 300 ) {
            throw new InkGoHttpRequestException( (string) $response['data'], $status );
        }

        return $response['data'];
    }
}

/**
 * Class InkGoException
 */
class InkGoException extends Exception {}

/**
 * Class InkGoHttpRequestException
 */
class InkGoHttpRequestException extends InkGoException {}