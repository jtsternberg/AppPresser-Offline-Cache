# AppPresser Offline Cache #
**Contributors:**      jtsternberg  
**Donate link:**       http://dsgnwrks.pro  
**Tags:**              REST API, WP-API, AppPresser, Offline  
**Requires at least:** 4.4  
**Tested up to:**      4.4  
**Stable tag:**        0.0.0  
**License:**           GPLv2  
**License URI:**       http://www.gnu.org/licenses/gpl-2.0.html  

## Description ##

Adds WordPress REST API endpoints to allow AppPresser to download and use curated offline content.

This project is a work in progress, so in order for it to "do" anything, a few steps are required.

First, the plugin adds a new REST API endpoint. As such, this plugin requires at least WordPress 4.4 and for the [REST API plugin](https://wordpress.org/plugins/rest-api/) to be installed.

When you go to that endpoint (`/wp-json/appp-offline/v1/static-pages/`), you will not see any results. This endpoint shows all pages Added to the 'offline-whitelist' AppPresser setting. There is not yet a UI built into the AppPresser settings to add these pages (consider this a [todo](https://github.com/jtsternberg/AppPresser-Offline-Cache/issues/9)). As a workaround, [you can use the filter](https://github.com/jtsternberg/AppPresser-Offline-Cache/issues/9).

This endpoint will display a listing of [all](https://github.com/jtsternberg/AppPresser-Offline-Cache/issues/10) pages designated for offline.

The indiviual page endpoint (`/wp-json/appp-offline/v1/static-pages/120`) will show a bit more detail. This endpoint will include some of the normal data from a post endpoint, but more importantly contains a few new properties:

* `'flush'` - A data when a specific post requested a cache flush.
* `'global_flush'` - A date when a cache flush was requested for all pages.
* `'html'` - **Important:** This is the key to this endpoint. It contains a static html version of the entire page output.
* `'scripts'` - A listing of the linked .js files in the static html.
* `'stylesheets'` - A listing of the linked .css files in the static html.
* `'images'` - A listing of the linked images in the static html.

Along with the new properties, the endpoint takes a few key query arguments:

* `remove_scripts` - This argument will cause all externally linked scripts to be stripped from the `'html'` output.
* `remove_stylesheets` - This argument will cause all externally linked stylesheets to be stripped from the `'html'` output.
* `base64_images` - This argument will cause all images to be converted to base64-encoded images in the `'html'` output.

### Offline App Demo

Within this plugin there is a proof of concept offline app. To test with it, first open your browser's javascript console (as there will not be much to "see" when you get there), then go to this URL in your browser, `<SITE-WITH-PLUGIN>.com?offline-demo`. This will redirect you to the offline app demo, and if your browser console is open, you'll [see some output](http://b.ustin.co/E3zA). This is indicating that your browser is caching the contents of the pages from the `/wp-json/appp-offline/v1/static-pages/` endpoint.

With your console still open, replace the `?resturl=<SITE-WITH-PLUGIN>.com/wp-json/` in your address bar with `offline/offline.html#<page-slug>`. `<page-slug>` should represent one of the slugs listed on `/wp-json/appp-offline/v1/static-pages/` endpoint.

When you navigate to that new hash (you may actually need to refresh this once or twice.. Still a WIP), you should again see some [output in your console](http://b.ustin.co/QtFI). That's it! You're now viewing the offline-cached version of your page.

### WordPress Filters

There are 3 filters around the static html generation:

* `'apppresser_offline_cache_static_html_request_args'` - The `$args` array sent to `wp_remote_get()`.
* `'apppresser_offline_cache_static_html_request_url'` - The `$url` sent to `wp_remote_get()`. Uses the `'link'` value from the `WP_REST_Response` object.
* `'apppresser_offline_cache_static_html_output'` - The html output from the `wp_remote_get()` response body.

To have the static html requests be "logged-in" requests, you can use the `'apppresser_offline_cache_static_html_request_args'` filter. Keep in mind, this will only work if you are making an authenticated request to this endpoint.

```php
add_filter( 'apppresser_offline_cache_static_html_request_args', function ( $args ) {
​
	$cookies = array();
​
	foreach ( $_COOKIE as $name => $value ) {
		$cookies[] = new WP_Http_Cookie( array( 'name' => $name, 'value' => $value ) );
	}
​
	$args = array( 'cookies' => $cookies );
​
	return $args;
} );
```
_(credit [@scottbasgaard](https://github.com/scottbasgaard))_

## Installation ##

### Manual Installation ###

1. Upload the entire `/apppresser-offline-cache` directory to the `/wp-content/plugins/` directory.
2. Activate AppPresser Offline Cache through the 'Plugins' menu in WordPress.

## Frequently Asked Questions ##


## Screenshots ##


## Changelog ##

### 0.0.0 ###
* First release

## Upgrade Notice ##

### 0.0.0 ###
First Release
