<?php
namespace SpiritWP\Connect\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class API_Client {
    private string $base_url;
    private string $app_key;
    private int $cache_ttl;

    public function __construct() {
        $this->base_url  = rtrim( get_option( 'spwp_ce_base_url', '' ), '/' );
        $this->app_key   = get_option( 'spwp_ce_app_key', '' );
        $this->cache_ttl = (int) get_option( 'spwp_ce_cache_ttl', 300 );
    }

    public function request( $method, $endpoint, $data = [], $cache = true ) {
        if ( empty( $this->base_url ) || empty( $this->app_key ) ) {
            return new \WP_Error( 'ce_api_error', __( 'Clientexec API base URL or Application Key is missing.', 'spiritwp-connect' ) );
        }

        // BUG-001 Fix: Append /api/
        $url = $this->base_url . '/api/' . ltrim( $endpoint, '/' );
        $url = add_query_arg( 'apikey', $this->app_key, $url );

        $cache_key = 'ce_api_' . md5( $method . $endpoint . serialize( $data ) );

        // BUG-010 Fix: Transient caching for GET requests
        if ( 'GET' === strtoupper( $method ) ) {
            if ( ! empty( $data ) ) {
                $url = add_query_arg( $data, $url );
            }
            if ( $cache ) {
                $cached = get_transient( $cache_key );
                if ( false !== $cached ) {
                    return $cached;
                }
            }
        }

        $args = [
            'method'  => strtoupper( $method ),
            'timeout' => 30,
            'headers' => [
                'Content-Type' => 'application/json',
                'X-API-Key'    => $this->app_key,
            ],
        ];

        if ( 'GET' !== strtoupper( $method ) && ! empty( $data ) ) {
            $args['body'] = wp_json_encode( $data );
        }

        $retries     = 0;
        $max_retries = 3;
        $response    = null;
        $start       = microtime( true );

        // BUG-009 Fix: 429 rate-limit retry logic
        while ( $retries < $max_retries ) {
            $response = wp_remote_request( $url, $args );
            
            if ( is_wp_error( $response ) ) {
                Logger::log( $method, $endpoint, 0, microtime( true ) - $start, $response->get_error_message() );
                return $response;
            }

            $status_code = wp_remote_retrieve_response_code( $response );

            if ( 429 === $status_code ) {
                $retry_after = wp_remote_retrieve_header( $response, 'retry-after' );
                $sleep_time  = $retry_after ? (int) $retry_after : 1;
                sleep( min( $sleep_time, 5 ) );
                $retries++;
                continue;
            }

            break;
        }

        $response_time = microtime( true ) - $start;
        $body          = wp_remote_retrieve_body( $response );
        $decoded       = json_decode( $body, true );
        $status_code   = wp_remote_retrieve_response_code( $response );

        if ( $status_code >= 400 ) {
            $error_msg = isset( $decoded['message'] ) ? $decoded['message'] : 'API Error ' . $status_code;
            Logger::log( $method, $endpoint, $status_code, $response_time, $error_msg );
            return new \WP_Error( 'ce_api_error', $error_msg, $decoded );
        }

        if ( is_null( $decoded ) && ! empty( $body ) ) {
             Logger::log( $method, $endpoint, $status_code, $response_time, 'Invalid JSON returned' );
             return new \WP_Error( 'ce_api_error', 'Invalid JSON returned from API' );
        }

        Logger::log( $method, $endpoint, $status_code, $response_time );

        if ( 'GET' === strtoupper( $method ) && $cache && ! is_wp_error( $decoded ) ) {
            set_transient( $cache_key, $decoded, $this->cache_ttl );
        }

        return $decoded;
    }

    public function get_user_by_email( $email ) {
        return $this->request( 'GET', 'accounts/user/getuser', [ 'email' => $email ], false );
    }

    public function create_user( $data ) {
        return $this->request( 'POST', 'accounts/user/add', $data, false );
    }

    public function update_user( $userid, $data ) {
        $data['userid'] = $userid;
        return $this->request( 'PUT', 'accounts/user/updateuser', $data, false );
    }

    public function validate_user( $email, $password ) {
        return $this->request( 'GET', 'accounts/user/validateuser', [ 'email' => $email, 'password' => $password ], false );
    }

    public function get_users( $page = 1, $limit = 25 ) {
        return $this->request( 'GET', 'accounts/users/getusers', [ 'page' => $page, 'limit' => $limit ], false );
    }

    // BUG-002 Fix: Add get_packages and stub unsupported endpoints
    public function get_packages( $userid, $type = 'all' ) {
        return $this->request( 'GET', 'accounts/packages/getpackages', [
            'userid' => $userid,
            'type'   => $type,
        ] );
    }

    public function add_package( $userid, $productid, $status = 1, $custom_fields = [] ) {
        $data = array_merge( [
            'userid'    => $userid,
            'productid' => $productid,
            'status'    => $status,
        ], $custom_fields );
        return $this->request( 'POST', 'accounts/packages/addpackage', $data, false );
    }

    public function update_package( $packageid, $data ) {
        $data['packageid'] = $packageid;
        return $this->request( 'PUT', 'accounts/packages/updatepackage', $data, false );
    }

    // Unavailable via CE REST - Return WP_Error to enforce graceful fallback to SSO
    public function get_invoices( $userid ) {
        return new \WP_Error( 'ce_endpoint_unavailable', __( 'Not available via CE API', 'spiritwp-connect' ) );
    }

    public function get_tickets( $userid, $status = 'open' ) {
        return new \WP_Error( 'ce_endpoint_unavailable', __( 'Not available via CE API', 'spiritwp-connect' ) );
    }

    public function get_ticket( $ticket_id ) {
        return new \WP_Error( 'ce_endpoint_unavailable', __( 'Not available via CE API', 'spiritwp-connect' ) );
    }

    public function get_kb_articles( $tag = null, $search = null ) {
        return new \WP_Error( 'ce_endpoint_unavailable', __( 'Not available via CE API', 'spiritwp-connect' ) );
    }

    public function get_kb_tags() {
        return new \WP_Error( 'ce_endpoint_unavailable', __( 'Not available via CE API', 'spiritwp-connect' ) );
    }

    // BUG-011 Fix: Double SHA1 HMAC
    public function generate_sso_url( $email, $goto = '' ) {
        if ( empty( $this->base_url ) || empty( $this->app_key ) ) {
            return '';
        }
        $timestamp = time();
        $hash      = sha1( sha1( $this->app_key ) . $email . $timestamp );
        
        $url       = $this->base_url . '/index.php?fuse=admin&action=autologin';
        $params    = [
            'email'     => $email,
            'timestamp' => $timestamp,
            'hash'      => $hash,
        ];
        if ( ! empty( $goto ) ) {
            $params['goto'] = $goto;
        }
        
        return add_query_arg( $params, $url );
    }
}
