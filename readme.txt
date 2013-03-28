=== Varnish HTTP Purge ===
Contributors: techpriester, Ipstenu, DH-Shredder
Tags: varnish, purge, cache
Requires at least: 3.0
Tested up to: 3.5
Stable tag: 2.0

== Description ==
Plugin for invalidating Wordpress items on a Varnish 3 Cache.

Varnish HTTP Purge sends a PURGE request to the URL of a page or post every time it it modified. This occurs when editing, publishing, commenting or deleting an item.

== Requirements ==
In order to work, the varnish cache meeds to accept PURGE request from the host of the wordpress web server.

Tested with Varnish 3.x

== Installation ==
No configuration needed.

== Changelog ==

= 2.0 =
* Commit access handed to Ipstenu
* Changed CURL to wp_remote_request (thank you <a href="http://wordpress.org/support/topic/incompatability-with-editorial-calendar-plugin?replies=1">Kenn Wilson</a>) so we don't have to do <a href="http://wordpress.org/support/topic/plugin-varnish-http-purge-incompatibility-with-woocommerce?replies=6">CURLOPT_RETURNTRANSFER</a> Remember kids, CURL is okay, but wp_remote_request is more portable :)

= 1.2.0 =
* Moved actual request execution to "shutdown" event
* Removed GET request due to bad performance impact