# cXense 4 WordPress

WordPress plugin that integrates your website with [cXense](http://www.cXense.com/)

**In short:**

 - Pings cXense crawler when post is added/updated/removed
 - Adds content profiling meta tags (open graph) in header of the HTML document
 - Adds cXense Analytics javascript to wp_footer
 - *WIP* Replaces WordPress public search with cXense site search

### Installation

Clone this project in the plugin directory of WordPress. Add the constant `CXENSE_SITE_ID` with your website
ID in wp-config.php or in the file functions.php located in your theme.


### Optional configuration

Constants: (defined in wp-config.php or theme)
---------------
CXENSE_SITE_ID (required)   Website ID at cXense
CXENSE_USER_NAME            API user at cXense (Required if wanting to ping crawler when post created)
CXENSE_API_KEY              API key at cXense (Required if wanting to ping crawler when post created)
CXENSE_GENERATE_OG_TAGS     Boolean, whether or not this plugin should generate open-graph tags (default true)
CXENSE_DEFAULT_SITE_DESC    Default website description
CXSENSE_ANALYTICS           Boolean, whether or not to include analytics script (default true)


### Actions and filters

CXENSE_is_recommendable Whether or not current URL should be recommendable (strings 'true' or 'false')
CXENSE_og_url           Filters the current url
CXENSE_og_image         Fallback open-graph image