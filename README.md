# cXense 4 WordPress

WordPress plugin that integrates your website with [cXense](http://www.cXense.com/).

**In short:**

 - Pings cXense crawler when post is added/updated/removed
 - Adds content profiling meta tags (open graph) in header of the HTML document
 - Adds cXense Analytics javascript to wp_footer
 - *(wip)* Replaces WordPress public search with cXense site search

## Installation

Clone this project in the plugin directory of WordPress. Add the constant `CXENSE_SITE_ID` with your website
ID in wp-config.php or in the file functions.php located in your theme.


## Optional configuration

The following constants can be used to modify the behaviour of this plugin. Define them in wp-config.php or in the file functions.php located in your theme directory.

`CXENSE_USER_NAME` and `CXENSE_API_KEY` API user credentials at cXense, used to ping the cXense crawler when a post is created/updated/removed. Theses constants will speed up the re-indexing of your website when content is changed.
              
`CXENSE_GENERATE_OG_TAGS` Boolean, whether or not this plugin should generate open-graph tags (default true).

`CXENSE_DEFAULT_SITE_DESC` A description of your website that will be used as og:description when no other description is available.

`CXSENSE_ANALYTICS` Boolean, whether or not to include analytics script (default true).

`CXENSE_DEV_SITE_ID` Will be used instead of CXENSE_SITE_ID if defined.

`CXENSE_REPORT_LOCATION` Can be used to override current URL registered for a page view at cXense 


## Actions and filters

*cxense_is_recommendable* — Whether or not current URL should be recommendable (strings 'true' or 'false').

*cxense_og_url* — Filters the current url.

*cxense_og_image* — Fallback open-graph image used when no other image is available.


## Registering page views using AJAX

All you have to do to register a page view using AJAX is to call `$(window).trigger('pageSwipe')` when you want the 
page view to registered at cXense.
