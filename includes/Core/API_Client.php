<?php
namespace SpiritWP\Connect\Core;

if ( ! defined( 'ABSPATH' ) ) { exit; }

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
        $url = $this->base_url . '/api/' . ltrim( $endpoint, '/' );
        $url = add_query_arg( 'apikey', $this->app_key, $url );
        $cache_key = 'ce_api_' . md5( $method . $endpoint . serialize( $data ) );
        if ( 'GET' === strtoupper( $method ) ) {
            if ( ! empty( $data ) ) { $url = add_query_arg( $data, $url ); }
            if ( $cache ) {
                $cached = get_transient( $cache_key );
                if ( false !== $cached ) { return $cached; }
            }
        }
        $args = ['method' => strtoupper( $method ),'timeout' => 30,'headers' => ['Content-Type' => 'application/json','X-API-Key' => $this->app_key]];
        if ( 'GET' !== strtoupper( $method ) && ! empty( $data ) ) { $args['body'] = json_encode( $data ); }
        $retries = 0; $max_retries = 3; $start = microtime(true);
        while ( $retries < $max_retries ) {
            $response = wp_remote_request( $url, $args );
            if ( is_wp_error( $response ) ) { Logger::log($method,$endpoint,0,microtime(true)-$start,$response->get_error_message()); return $response; }
            $status = wp_remote_retrieve_response_code( $response );
            if ( 429 === $status ) {
                $ra = wp_remote_retrieve_header( $response, 'retry-after' );
                sleep( min( $ra ? (int) $ra : 1, 5 ) );
                $retries++; continue;
            }
            break;
        }
        $rt = microtime(true)-$start;
        $body = wp_remote_retrieve_body( $response );
        $dec = json_decode( $body, true );
        $st = wp_remote_retrieve_response_code( $response );
        if ( $st >= 400 ) {
            $em = isset( $dec['message'] ) ? $dec['message'] : 'API Error '.$st;
            Logger::log($method,$endpoint,$st,$rt,$em);
            return new \WP_Error( 'ce_api_error', $em, $dec );
        }
        if ( is_null( $dec ) && ! empty( $body ) ) { Logger::log($method,$endpoint,$st,$rt,'Invalid JSON'); return new \WP_Error('ce_api_error','Invalid JSON returned'); }
        Logger::log($method,$endpoint,$st,$rt);
        if ( 'GET' === strtoupper($method) && $cache && ! is_wp_error($dec) ) { set_transient($cache_key,$dec,$this->cache_ttl); }
        return $dec;
    }

    public function get_user_by_email($e){return $this->request('GET','accounts/user/getuser',['email'=>$e],false);}
    public function create_user($data){return $this->request('POST','accounts/user/add',$data,false);}
    public function update_user($uid,$data){$data['userid']=$uid;return $this->request('PUT','accounts/user/updateuser',$data,false);}
    public function validate_user($e,$p){return $this->request('GET','accounts/user/validateuser',['email'=>$e,'password'=>$p],false);}
    public function get_users($p=1,$l=25){return $this->request('GET','accounts/users/getusers',['page'=>$p,'limit'=>$l],false);}
    public function get_packages($uid,$t='all'){return $this->request('GET','accounts/packages/getpackages',['userid'=>$uid,'type'=>$t]);}
    public function add_package($uid,$pid,$s=1,$cf=[]){return $this->request('POST','accounts/packages/addpackage',array_merge(['userid'=>$uid,'productid'=>$pid,'status'=>$s],$cf),false);}
    public function update_package($pid,$data){$data['packageid']=$pid;return $this->request('PUT','accounts/packages/updatepackage',$data,false);}
    public function get_invoices($uid){return new \WP_Error('ce_endpoint_unavailable','Invoice listing not available via CE API.');}
    public function get_tickets($uid,$s='open'){return new \WP_Error('ce_endpoint_unavailable','Ticket listing not available via CE API.');}
    public function get_ticket($tid){return new \WP_Error('ce_endpoint_unavailable','Individual ticket retrieval not available.');}
    public function get_kb_articles($tag=null,$search=null){return new \WP_Error('ce_endpoint_unavailable','Knowledge base not available via CE API.');}
    public function get_kb_tags(){return new \WP_Error('ce_endpoint_unavailable','KB tags not available via CE API.');}

    public function generate_sso_url( $email, $goto = '' ) {
        if ( empty( $this->base_url ) || empty( $this->app_key ) ) { return ''; }
        $ts = time();
        $hash = sha1( sha1( $this->app_key ) . $email . $ts );
        $url = $this->base_url . '/index.php?fuse=admin&action=autologin';
        $p = ['email' => $email,'timestamp' => $ts/'hash' => $hash];
        if ( ! empty( $goto ) ) { $p['goto'] = $goto; }
        return add_query_arg( $p, $url );
    }
}
