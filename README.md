# cXense 4 WordPress

WordPress plugin that integrates your website with [cXense](http://www.cXense.com/).

**In short:**

 - Pings cXense crawler when a post is added/updated/removed
 - Adds content profiling meta tags (open graph) to the header of the HTML document
 - Adds cXense Analytics javascript to wp_footer
 - *(wip)* Replaces WordPress public search with cXense site search
 - *(wip)* Makes it possible to display recommendation widgets provided by cXense

## Installation

1. Clone this project in the plugin directory of WordPress.
2. Install the plugin in wp-admin. 
3. Let the plugin know your site id at cXense. You can do this either by setting a constant with the name `CXENSE_SITE_ID` in wp-config, or use the settings page for the plugin that can be found in wp-admin, below Settings.


## Optional configuration

The following constants can be used to modify the behaviour of this plugin. Define them in wp-config.php or in the file functions.php located in your theme directory.

**Notice!** None of these constants are mandatory. You can choose to define them in the cXense settings page that can be found in wp-admin. If you choose to define these settings in form of constants you can override them using the settings page.

`CXENSE_USER_NAME` and `CXENSE_API_KEY` API user credentials at cXense, used to ping the cXense crawler when a post is created/updated/removed. These constants will speed up the re-indexing of your website when content is changed.
              
`CXENSE_GENERATE_OG_TAGS` Boolean, whether or not this plugin should generate open-graph tags (default true).

`CXENSE_DEFAULT_SITE_DESC` A description of your website that will be used as og:description when no other description is available.

`CXENSE_DEFAULT_OG_IMAGE` URL to the image that should be used as og:image when no other image is available.

`CXSENSE_ANALYTICS` Boolean, whether or not to include the analytics script (default true).

`CXENSE_USER_PRODUCTS` Only necessary if you're using the WordPress plugin *paygate*. This constant makes it possible to send which type
of product your visitor has to cXense as a custom parameter. The constants should contain a comma separated string with product names.

`CXENSE_RECOMMENDABLE_POST_TYPES` By default only posts with post type "post" will get recommended by the recommendations widgets provided by cXense.
Use this constant if you want to define which post types that should be recommendable.


## Actions and filters

*cxense_is_recommendable* — Whether or not current URL should be recommendable (strings 'true' or 'false'). Use this filter if you don't wont certain posts to be recommended by the recommendations widgets provided by cXense.

*cxense_og_url* — Filters the current url that is used as og:url.
