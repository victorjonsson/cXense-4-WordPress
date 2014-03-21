<?php

/**
 * Ping cXense crawler so that the crawler re-indexes the URL of the post
 * @param int|string $post_id Either post ID or an URL
 * @return array|null
 */
function cxense_ping_crawler($post_id) {

    if( !is_numeric($post_id) || (!wp_is_post_revision($post_id) && !wp_is_post_autosave($post_id)) ) {

        $cx_user = cxense_get_opt('cxense_user_name');
        if( !$cx_user ) {
            if( WP_DEBUG )
                error_log('PHP Warning: To use CXense push you must define constants CXENSE_USER_NAME and CXENSE_API_KEY');
            return null;
        }

        $date = date("o-m-d\TH:i:s.000O");
        $signature = hash_hmac("sha256", $date, cxense_get_opt('cxense_api_key'));

        $url = is_numeric($post_id) ? get_permalink($post_id) : $post_id;

        $request_opts = array(
            'method' => 'POST',
            'body' => json_encode(array('url'=> $url)),
            'headers' => array(
                'X-cXense-Authentication' => 'username='.cxense_get_opt('cxense_user_name').' date='.$date.' hmac-sha256-hex='.$signature
            )
        );

        $http = new WP_Http();
        $response = $http->post('https://api.cxense.com/profile/content/push', $request_opts);
        return $response;
    }
    return null;
}

/**
 * Output content profiling meta tags (open-graph and cXenseParse)
 * @param string|null $location Override current URL
 */
function cxense_output_meta_tags($location=null) {

    $og_tags = array(
        'og:site_name' => str_replace( 'http://', '',  get_site_url() ),
        'og:description' => cxense_get_opt('CXENSE_DEFAULT_SITE_DESC')
    );

    if ( is_singular() || is_single() ) {
        global $post;

        $recs_tags = array();
        $og_tags = array(
            'og:title' => get_the_title(),
            'og:url' => apply_filters('cxense_og_url', get_permalink())
        );

        $recommendable_types = cxense_get_opt('cxense_recommendable_post_type');
        if( !$recommendable_types ) {
            $recommendable_types = 'post';
        }
        
        if( strpos($recommendable_types, $post->post_type) !== false ) {
            $og_tags['og:type'] = 'article';
            $og_tags['og:article:published_time'] = date('c', strtotime($post->post_date));
            $og_tags['og:article:author'] = get_user_by('id', $post->post_author)->display_name;
            $og_tags['og:description'] = get_the_excerpt();
            if( empty($og_tags['og:description']) ) {
                $og_tags['og:description'] = str_replace("\n", ' ', strip_tags($post->post_content));
            }

            if( mb_strlen($og_tags['og:description'], 'UTF-8') > 75 ) {
                $og_tags['og:description'] = mb_substr($og_tags['og:description'], 0, 75, 'UTF-8').'...';
            }
            if( has_post_thumbnail() ) {
                list($src, $width, $height) = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' );
                $recs_tags['cXenseParse:recs:image'] = $src;
                if( $width > 200 && $height > 200 ) {
                    $og_tags['og:image'] = $src;
                }
            } else {
                $recs_tags['cXenseParse:recs:image'] = 'noimage';
            }

            $is_recommendable = 'true';

        } else {
            // Page of some kind
            $is_recommendable = 'false';
        }

        $recs_tags['cXenseParse:recs:recommendable'] = apply_filters('cxense_is_recommendable', $is_recommendable);


        // Paywall
        if( defined('PAYGATE_PLUGIN_URL') ) {
            $recs_tags['cXenseParse:paywall'] = is_paygate_protected($post) ? 'true':'false';
            $recs_tags['cXenseParse:recs:paywall'] = $recs_tags['cXenseParse:paywall'];
            if( $recs_tags['cXenseParse:paywall'] == 'true' ) {
                // For content index search
                $recs_tags['cXenseParse:recs:custom0'] = 'paywall';
            }
        }

        // Post id
        $recs_tags['cXenseParse:recs:articleid'] = $post->ID;

    }
    else {
        // Tags/category/search etc....
        $recs_tags['cXenseParse:recs:recommendable'] = 'false';
        $og_tags['og:url'] = get_site_url().$_SERVER['REQUEST_URI'];
        $og_tags['og:type'] = 'website';
    }


    if( empty($og_tags['og:image']) ) {
        $og_tags['og:image'] = cxense_get_opt('cxense_default_og_image');
    }

    if( !empty($location) ) {
        $og_tags['og:url'] = $location;
    }

    // Sanitize stuff
    foreach(array('og:title', 'og:description') as $tag => $val) {
        if( !empty($og_tags[$tag]) ) {
            $og_tags[$tag] = trim(str_replace('"','&quot;', $val));
        }
    }

    foreach($og_tags as $name => $val) {
        echo '<meta property="'.$name.'" content="'.$val.'" />'.PHP_EOL;
    }
    foreach($recs_tags as $name => $val) {
        echo '<meta name="'.$name.'" content="'.$val.'" />'.PHP_EOL;
    }
}

/**
 * Outputs a cxense recommend widget (EXPERIMENTAL !!)
 * @param string $id
 * @param int $width
 * @param int $height
 * @param bool $template - Optional, will default to "default-widget.html" located the template directory of this plugin
 * @param bool $resize_content Optional, whether or not the widget should resize it self after loading
 */
function cxense_recommend_widget($id, $width, $height, $template=false, $resize_content=true) {
    static $num_widgets = 1;
    ?>
    <div id="cxense-widget-<?php echo $num_widgets ?>"></div>
    <script>
        var cX = cX || {}; cX.callQueue = cX.callQueue || [];
        cX.callQueue.push(['insertWidget',{
            widgetId: '<?php echo $id ?>',
            insertBeforeElementId: 'cxense-widget-<?php echo $num_widgets ?>',
            renderTemplateUrl: '<?php echo $template ? $template : CXENSE_PLUGIN_URL.'/templates/recommend-widgets/default-widget.html' ?>',
            resizeToContentSize : <?php echo $resize_content ? 'true':'false' ?>,
            width: <?php echo $width ?>,
            height: <?php echo $height ?>
        }]);
    </script>
    <?php
    $num_widgets++;
}

/**
 * Outputs javascript that register a pageview at cXense
 */
function cxense_analytics_script() {
    require __DIR__ . '/analytics-script.php';
}

/**
 * Get a settings option used by this plugin. Will fall back on
 * constant if defined
 * @param string $name
 * @return mixed
 */
function cxense_get_opt($name) {
    if( $opt = get_option($name) ) {
        return $opt;
    } else {
        $name = strtoupper($name);
        return defined($name) ? constant($name) : false;
    }
}

/**
 * Do a search against cXense indexed pages. Will return false in case
 * of an error or a json result if all is fine (will be cached)
 * @param string $query
 * @param array $args
 * @return array|bool
 */
function cxense_search($query, $args) {
    $default_args = array(
        'columns' => 'title,description,body',
        'count' => 10,
        'pagination' => 0,
        'sort' => 'timestamp:desc',
        'cache_ttl' => HOUR_IN_SECONDS,
        'site_id' => cxense_get_opt('cxense_site_id'),
    );

    $args = array_merge($default_args, $args);

    $url = sprintf(
        'http://sitesearch.cxense.com/api/search/%s?p_aq=query(%s:"%s",token-op=and)&p_c=%d&p_s=%d&p_sm=%s',
        $args['site_id'],
        $args['columns'],
        urlencode($query),
        $args['count'],
        $args['pagination'],
        $args['sort']
    );

    $cache_key = md5($url);
    $result = get_transient($cache_key);

    if( !$result ) {
        $response = wp_remote_get($url);
        if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();
            error_log('PHP Warning: Something went wrong trying to get cxense search data: '.$error_message, E_USER_WARNING);
            return false;
        } else {
            $result = @json_decode($response['body'], true);
            if( !$result ) {
                error_log('PHP Warning: Unable to parse json from result ('.json_last_error().')', E_USER_WARNING);
                return false;
            }
            set_transient($cache_key, $result, $args['cache_ttl']);
        }
    }

    return $result;
}

/**
 * Get a list of all settings that this plugin has
 * @return array
 */
function cxense_get_settings() {
    return array(
        array('name'=>'cxense_site_id', 'title' => 'Site ID'),
        array('name'=>'cxense_user_name', 'title' => 'User name'),
        array('name'=>'cxense_api_key', 'title' => 'API Key'),
        array('name'=>'cxense_generate_og_tags', 'title' => 'Generate og-tags', 'select'=>array('yes' => 'Yes', 'no' => 'No')),
        array('name'=>'cxense_add_analytics_script', 'title' => 'Add analytics script', 'select'=>array('yes' => 'Yes', 'no' => 'No')),
        array('name'=>'cxense_recommendable_post_types', 'title' => 'Recommendable post types (comma separated)'),
        array('name'=>'cxense_default_site_desc', 'title' => 'The default website description used in og:description'),
        array('name'=>'cxense_default_og_image', 'title' => 'URL to default og:image'),
        array('name'=>'cxense_user_products', 'title' => 'Paywall user products (comma separated string)'),
        array('name'=>'cxense_widgets_options', 'title' => 'cxense_widgets_options', 'add_field' => false)
    );
}

/**
 * Register all settings used by this plugin
 */
function cxense_register_settings() {

    // Setup section on our options page
    add_settings_section('cxense-settings-section', 'cXense Settings', '__return_empty_string', 'cxense-settings');

    // Register our settings and create
    foreach(cxense_get_settings() as $setting) {

        // Register setting
        register_setting('cxense-settings', $setting['name']);

        // Add settings field if add_field isn't false
        if( !isset($setting['add_field']) || $setting['add_field'] !== false) {
            add_settings_field(
                $setting['name'],
                $setting['title'],
                function($args) {
                    $value = cxense_get_opt($args['name']);
                    if( !empty($args['select']) ) {
                        echo '<select name="'.$args['name'].'">';
                        foreach($args['select'] as $opt_val => $opt_name) {
                            echo '<option value="'.$opt_val.'"'.($opt_val == $value ? ' selected="selected"':'').'>'.$opt_name.'</option>';
                        }
                        echo '</select>';
                    } else {
                        echo '<input type="text" name="'.$args['name'].'" value="'.$value.'" />';
                    }
                },
                'cxense-settings',
                'cxense-settings-section',
                $setting
            );
        }
    }
}

/**
 * Remove all settings that might have been
 * saved to the database by the plugin
 */
function cxense_remove_all_settings() {
    foreach(cxense_get_settings() as $setting)
        delete_option($setting['name']);
}