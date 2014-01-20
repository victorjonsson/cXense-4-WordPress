<?php
/*
Plugin Name: cXense 4 WordPress
Description: Integrates the website with cXense Analytics and cXense Site Search
Version: 1.0.1
Author: Victor Jonsson <http://victorjonsson.se/>
*/
define('CXENSE_PLUGIN_URL', plugin_dir_url(__FILE__));


/**
 * Ping cXense crawler so that the crawler re-indexes the URL of the post
 * @param int|string $post_id Either post ID or an URL
 * @return array|null
 */
function cxense_ping_crawler($post_id) {

    if( !is_numeric($post_id) || (!wp_is_post_revision($post_id) && !wp_is_post_autosave($post_id)) ) {

        if( !defined('CXENSE_USER_NAME') ) {
            if( defined('WP_DEBUG') && WP_DEBUG )
                error_log('PHP Warning: To use CXense push you must define constants CXENSE_USER_NAME and CXENSE_API_KEY');
            return null;
        }

        $date = date("o-m-d\TH:i:s.000O");
        $signature = hash_hmac("sha256", $date, CXENSE_API_KEY);

        $url = is_numeric($post_id) ? get_permalink($post_id) : $post_id;

        $request_opts = array(
            'method' => 'POST',
            'body' => json_encode(array('url'=> $url)),
            'headers' => array(
                'X-cXense-Authentication' => 'username='.CXENSE_USER_NAME.' date='.$date.' hmac-sha256-hex='.$signature
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
        'og:description' => defined('CXENSE_DEFAULT_SITE_DESC') ? CXENSE_DEFAULT_SITE_DESC:''
    );

    if ( is_singular() || is_single() ) {
        global $post;

        $og_tags = array(
            'og:title' => get_the_title(),
            'og:url' => apply_filters('cxense_og_url', get_permalink())
        );

        $recommendable_types = 'post';
        if( defined('CXENSE_RECOMMENDABLE_POST_TYPES') ) {
            $recommendable_types = CXENSE_RECOMMENDABLE_POST_TYPES;
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
                $og_tags['cXenseParse:recs:image'] = $src;
                if( $width > 200 && $height > 200 ) {
                    $og_tags['og:image'] = $src;
                }
            } else {
                $og_tags['cXenseParse:recs:image'] = 'noimage';
            }

            $is_recommendable = 'true';

        } else {
            // Page of some kind
            $is_recommendable = 'false';
        }

        $og_tags['cXenseParse:recs:recommendable'] = apply_filters('cxense_is_recommendable', $is_recommendable);


        // Paywall
        if( defined('PAYGATE_PLUGIN_URL') ) {
            $og_tags['cXenseParse:paywall'] = is_paygate_protected($post) ? 'true':'false';
            $og_tags['cXenseParse:recs:paywall'] = $og_tags['cXenseParse:paywall'];
            if( $og_tags['cXenseParse:paywall'] == 'true' ) {
                // For content index search
                $og_tags['cXenseParse:recs:custom0'] = 'paywall';
            }
        }

        // Post id
        $og_tags['cXenseParse:recs:articleid'] = $post->ID;

    }
    else {
        // Tags/category/search etc....
        $og_tags['cXenseParse:recs:recommendable'] = 'false';
        $og_tags['og:url'] = get_site_url().$_SERVER['REQUEST_URI'];
    }


    if( empty($og_tags['og:image']) ) {
        $og_tags['og:image'] = apply_filters('cxense_og_image', '');
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

    if( !empty($_GET['no-cxense-og']) ) {
        unset($og_tags['og:article:author']);
        unset($og_tags['cXenseParse:recs:recommendable']);
        unset($og_tags['cXenseParse:paywall']);
        unset($og_tags['cXenseParse:recs:paywall']);
        unset($og_tags['cXenseParse:recs:custom0']);
        unset($og_tags['cXenseParse:recs:articleid']);
        $og_tags['og:url'] = add_query_arg('no-cxense-og', '1', $og_tags['og:url']);
    }

    foreach($og_tags as $name => $val) {
        echo '<meta property="'.$name.'" content="'.$val.'" />'.PHP_EOL;
    }
}

/**
 * Outputs a cxense widget
 * @param string $id
 * @param int $width
 * @param int $height
 * @param bool $template - Optional, will default to "default-widget.html" located in this plugin
 * @param bool $resize_content Optional, whether or not the widget should resize it self after loading
 */
function cxense_widget($id, $width, $height, $template=false, $resize_content=true) {
    static $num_widgets = 1;
    ?>
    <div id="cxense-widget-<?php echo $num_widgets ?>"></div>
    <script>
        var cX = cX || {}; cX.callQueue = cX.callQueue || [];
        cX.callQueue.push(['insertWidget',{
            widgetId: '<?php echo $id ?>',
            insertBeforeElementId: 'cxense-widget-<?php echo $num_widgets ?>',
            renderTemplateUrl: '<?php echo $template ? $template : CXENSE_PLUGIN_URL.'/default-widget.html' ?>',
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
    if( !defined('CXSENSE_ANALYTICS') || CXSENSE_ANALYTICS ) {
        require __DIR__.'/analytics-script.php';
    }
}

add_action('after_setup_theme', function() {

    if( is_admin() ) {

        // Ping crawler when post is changed
        add_action('save_post', 'cxense_ping_crawler');
        add_action('delete_post', 'cxense_ping_crawler');

    }
    else {

        // Add content profiling tags
        // https://wiki.cxense.com/display/cust/Cxense+Content+-+Review+and+Refinement
        if( !defined('CXENSE_GENERATE_OG_TAGS') || CXENSE_GENERATE_OG_TAGS ) {
            add_action('wp_head', 'cxense_output_meta_tags');
        }

        // Add analytics script
        add_action('wp_footer', 'cxense_analytics_script');

    }

});