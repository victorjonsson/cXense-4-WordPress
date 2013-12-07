# cXense 4 WordPress

WordPress plugin that integrates your website with [cXense](http://www.cXense.com/).

**In short:**

 - Pings cXense crawler when a post is added/updated/removed
 - Adds content profiling meta tags (open graph) to the header of the HTML document
 - Adds cXense Analytics javascript to wp_footer
 - *(wip)* Replaces WordPress public search with cXense site search

## Installation

Clone this project in the plugin directory of WordPress. Add the constant `CXENSE_SITE_ID` with your website
ID in wp-config.php or in the file functions.php located in your theme.


## Optional configuration

The following constants can be used to modify the behaviour of this plugin. Define them in wp-config.php or in the file functions.php located in your theme directory.

`CXENSE_USER_NAME` and `CXENSE_API_KEY` API user credentials at cXense, used to ping the cXense crawler when a post is created/updated/removed. These constants will speed up the re-indexing of your website when content is changed.
              
`CXENSE_GENERATE_OG_TAGS` Boolean, whether or not this plugin should generate open-graph tags (default true).

`CXENSE_DEFAULT_SITE_DESC` A description of your website that will be used as og:description when no other description is available.

`CXSENSE_ANALYTICS` Boolean, whether or not to include the analytics script (default true).

`CXENSE_DEV_SITE_ID` Will be used instead of CXENSE_SITE_ID if defined.

`CXENSE_REPORT_LOCATION` Used to override current URL (that will get a page view registered at cXense).


## Actions and filters

*cxense_is_recommendable* — Whether or not current URL should be recommendable (strings 'true' or 'false'). Only posts is recommended by default. Use
this filter if you don't wont certain posts to be recommended by the recommendations widgets provided by cXense.

*cxense_og_url* — Filters the current url that is used as og:url.

*cxense_og_image* — Fallback open-graph image used when no other image is available.
