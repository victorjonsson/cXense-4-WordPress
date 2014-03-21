<?php
/*
Plugin Name: cXense 4 WordPress
Description: Integrates the website with cXense Analytics and cXense Site Search (require php v >= 5.3)
Version: 1.0.2
Author: Victor Jonsson <http://victorjonsson.se/>, Tom Brännström
*/

// Define initial constants
define('CXENSE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CXENSE_PLUGIN_PATH', __DIR__);
define('CXENSE_PLUGIN_VERSION', '1.0.3');

// Load the function library of the plugin
require __DIR__.'/functions.php';

// Add recommendable post types when activated
register_activation_hook(__FILE__, function() {
    if( !cxense_get_opt('cxense_recommendable_post_types') ) {
        update_option('cxense_recommendable_post_types', 'post');
    }
});

// Remove all settings that might have been created when plugin gets uninstalled
register_uninstall_hook(__FILE__, 'cxense_remove_all_settings');

// Load wp widget
add_action('widgets_init', function () {
    require_once __DIR__.'/widget.php';
    register_widget('Cxense_Widget');
});


if( is_admin() ) {


    /* * * * * Plugin admin stuff * * * */


    // Settings page for the plugin
    add_action('admin_menu', function() {
        $js_hook = add_options_page(
            'cXense',
            'cXense',
            'manage_options',
            'cxense-settings',
            function() {
                require_once CXENSE_PLUGIN_PATH.'/templates/admin/settings-page.php';
            }
        );
        wp_enqueue_script('admin-'.$js_hook, CXENSE_PLUGIN_URL.'templates/admin/admin-ui.js', array('jquery'), CXENSE_PLUGIN_VERSION);
    });

    add_action('admin_init', function() {

        // Ping crawler when post is changed
        add_action('save_post', 'cxense_ping_crawler');
        add_action('delete_post', 'cxense_ping_crawler');

        // Register our settings
        cxense_register_settings();
    });



} else {


    /* * * * * Manually triggering page view  * * * */

    add_filter('request', function($req) {
        if( strpos($_SERVER['REQUEST_URI'], '/cxense-event/') !== false )  {
            require __DIR__.'/templates/cx-event.php';
            die;
        }
        return $req;
    });


    /* * * * * Plugin theme stuff * * * */

    add_action('template_redirect', function() {

        if( !is_preview() ) {

            // Add content profiling tags
            // https://wiki.cxense.com/display/cust/Cxense+Content+-+Review+and+Refinement
            if( cxense_get_opt('cxense_generate_og_tags') != 'no' ) {
                add_action('wp_head', 'cxense_output_meta_tags');
            }

            // Add analytics script
            if( cxense_get_opt('cxense_add_analytics_script') != 'no' ) {
                add_action('wp_footer', 'cxense_analytics_script');
            }
        }

        // the analytics script requires jQuery
        wp_enqueue_script('jquery');

        // Add short code for ajax-search
        require __DIR__.'/ajax-search/ajax-search-shortcode.php';

    });

}
