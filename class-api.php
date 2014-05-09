<?php
class CxenseAPI {

    const EXCEPTION_USER_NOT_DEFINED = 0;
    const EXCEPTION_UNAUTHORIZED = 1;
    const EXCEPTION_TIME_OUT = 2;


    /**
     * @param string|int $post_id Either post ID or permalink
     * @return array|null|object
     */
    static function pingCrawler($post_id)
    {
        if( !is_numeric($post_id) || (!wp_is_post_revision($post_id) && !wp_is_post_autosave($post_id)) ) {
            $url = is_numeric($post_id) ? get_permalink($post_id) : $post_id;
            try {
                return self::request('/profile/content/push', array('url'=> $url));
            } catch(Exception $e) {
                if( $e->getCode() == self::EXCEPTION_USER_NOT_DEFINED ) {
                    error_log('PHP Warning: To use CXense push you must define constants CXENSE_USER_NAME and CXENSE_API_KEY');
                } elseif( $e->getCode() == self::EXCEPTION_UNAUTHORIZED ) {
                    error_log('PHP Warning: Could not authorize with defined CXENSE_USER_NAME and CXENSE_API_KEY');
                }
            }
        }
        return null;
    }

    /**
     * @param string $path
     * @param array|string $args
     * @param int $timeout Seconds until timeout
     * @return array|null|object
     */
    static function request($path, $args, $timeout=5)
    {
        $cx_user = cxense_get_opt('cxense_user_name');
        if( !$cx_user ) {
            throw new Exception('You must define constants CXENSE_USER_NAME and CXENSE_API_KEY', self::EXCEPTION_USER_NOT_DEFINED);
        }

        $date = date("o-m-d\TH:i:s.000O");
        $signature = hash_hmac("sha256", $date, cxense_get_opt('cxense_api_key'));

        $request_opts = array(
            'method' => 'POST', // the api seems only to allow POST?
            'body' =>  is_array($args) ? json_encode($args):$args,
            'timeout' => $timeout,
            'headers' => array(
                'X-cXense-Authentication' => 'username='.cxense_get_opt('cxense_user_name').' date='.$date.' hmac-sha256-hex='.$signature
            )
        );

        $http = new WP_Http();
        $url = 'https://api.cxense.com/'.trim($path, '/');
        $resp = $http->request($url, $request_opts);

        if( is_wp_error($resp) ) {
            /* @var WP_Error $resp */
            $message = $resp->get_error_message();
            if( strpos($message, 'timed out') !== false ) {
                throw new Exception($message, self::EXCEPTION_TIME_OUT);
            } else {
                throw new Exception($message, -1);
            }
        }

        if( $resp['response']['code'] == 401 )
            throw new Exception('Authorization required', self::EXCEPTION_UNAUTHORIZED);
        elseif( $resp['response']['code'] < 200 || $resp['response']['code'] >= 300 )
            throw new Exception('Unexpected response, code: '.$resp['response']['code'].' message: '.$resp['response']['message'], -1);
        return json_decode($resp['body']);
    }

    static function getPosts(){
        global $wpdb;
        $query=    "SELECT ID FROM $wpdb->posts";
        $fivesdrafts = $wpdb->get_results($query);
        $return = '';
        foreach ( $fivesdrafts as $post_item){
            $return .= $post_item->ID.',';
        }
        return $return;
    }

}