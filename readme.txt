=== AWStats Script ===
Contributors: jgbustos
Tags: awstats
Requires at least: 2.2
Tested up to: 2.5.1
Stable tag: 0.3

Adds the HTML script tag and JS code that AWStats requires to enable collection of 
browser data like screen size and browser capabilities. 

== Description ==

[AWStats](http://awstats.sourceforge.net/ "AWstats") is a free log file analysis
tool for web servers. It consists of a collection of Perl scripts that analyse
Apache-style access logs and produce graphical web pages with extended information
about the visits.

= Browser Data Collection =

AWStats can collect information about the browser capabilities and screen size,
but that requires embedding a `<script>` HTML tag in all the pages. This calls
a JavaScript function contained in the file `awstats_misc_tracker.js` that will 
report the extra data to the web server in a specific HTTP GET request. An 
extended explanation is provided in the [AWStats FAQ](http://awstats.sourceforge.net/docs/awstats_faq.html#SCREENSIZE "AWStats FAQ")

This plugin simplifies the job of adding the required `<script>` tag and provides
the latest stable version of the `awstats_misc_tracker.js` file, both in extended
and "minified" version using the [Yahoo YUI Compressor](http://developer.yahoo.com/yui/compressor/ "Yahoo YUI Compressor").

= Placing the Script =

To speed-up the page rendering, the `<script>` tag is best placed at the bottom
of the page. This is the preferred option for WordPress themes that have a footer.
If the theme doesn't have a footer, the tag is added to the page header. This
behaviour can be controlled using the plugin's settings page. This has been 
borrowed from the [Google Analyticator](http://cavemonkey50.com/code/google-analyticator
"Google Analyticator") plugin by Ronald Heft Jr.

= No Logging for Administrators =

The plugin also allows administrators to remove themselves and their visits from 
the AWStats log. There is a field in the settings page where we can enter the user
level (a number from 0 to 10) above which the plugin will omit the `<script>` tag.

== Installation ==

1. Upload the `awstats-script` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Configure and enable the plugin in the 'AWStats Script' Options page

== Frequently Asked Questions ==

== Screenshots ==

1. Settings page with default values, right after activation.
2. Most typical configuration, adding the `<script>` tag at the page footer and 
disabling the AWStats logging for blog admins (level 8 or greater).

== Changelog ==

= Version 0.3 (2008-07-01) =

* BUGFIX: Added `alt` attribute for `<img>` inside `<noscript>`
* BUGFIX: Closed the `<img>` tag properly
* BUGFIX: Removed a superfluous double quote that broke XHTML validation

= Version 0.2 (2008-06-29) =

* NEW: Screenshots of settings page
* NEW: Support for i18n

= Version 0.1 (2008-06-29) =

* First release
