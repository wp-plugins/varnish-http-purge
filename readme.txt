=== Varnish HTTP Purge ===
Contributors: techpriester, Ipstenu, DH-Shredder
Tags: varnish, purge, cache
Requires at least: 3.0
Tested up to: 3.5
Stable tag: 2.1

Purge Varnish Cache when pages are modified.

== Description ==
Varnish HTTP Purge sends a PURGE request to the URL of a page or post every time it it modified. This occurs when editing, publishing, commenting or deleting an item.

<a href="https://www.varnish-cache.org/">Varnish</a> is a web application accelerator also known as a caching HTTP reverse proxy. You install it in front of any server that speaks HTTP and configure it to cache the contents.

== Installation ==
No configuration needed.

Varnish must be installed on your webserver. This is outside of WordPress, and is under the purview of your webhost.

== Frequently Asked Questions ==

= What version of Varnish is supported? =

This was built and tested on Varnish 3.x, however it is reported to work on 2.x. It is only supported on v3 at this time.

= Why don't my gzip'd pages flush? =

Make sure your Varnish VCL is configured correctly to purge all the right pages.

= Can I use this with a prodxy service like CloudFlare? =

Yes, but you'll need to make some additonal changes (see "Why aren't my changes showing when I use CloudFlare or another proxy?" below).

= How come my CSS changes aren't showing up? =

Because this plugin only flushes posts, pages, and comments. If you're editing CSS, you need to flush Varnish on your server as a whole.

If you use the Jetpack CSS editor, however, your changes will show up.

= Why is nothing caching when I use PageSpeed? =

Because PageSpeed likes to put in Caching headers to say not to cache. To fix this, you need to put this in your .htaccess:

`
<IfModule pagespeed_module>
    ModPagespeed on
    ModPagespeedModifyCachingHeaders off
</IfModule>
`

If you're using nginx, it's `pagespeed ModifyCachingHeaders off;`

= Why aren't my changes showing when I use CloudFlare or another proxy? =

When you use CloudFlare or any other similar servive, you've got a proxy in front of the Varnish proxy. In general this isn't a bad thing. The problem arises when the DNS shenanigans send the purge request to your domainname. When you've got an additional proxy like CloudFlare, you don't want the request to go to the proxy, you want it to go to Varnish server.

To fix this, add the following to your wp-config.php file:

`define('VHP_VARNISH_IP','123.45.67.89');`

Replace "123.45.67.89" with the IP of your <em>Varnish Server</em> (not CloudFlare, Varnish).
<em>DO NOT</em> put in http in this define.

= How do I find my Varnish IP? =

Your Varnish IP must be one of the IPs that Varnish is listening on. If you use multiple IPs, or if you've customized your ACLs, you'll need to pick on that doesn't conflict with your other settings. For example, if you have Varnish listening on a public and private IP, you'll want to pick the private. On the other hand, if you told Varnish to listen on 0.0.0.0 (i.e. "listen on every interface you can") you would need to check what IP you set your purge ACL to allow (commonly 127.0.0.1 aka localhost), and use that (i.e. 127.0.0.1).

If your webhost set up Varnish for you, you may need to ask them for the specifics if they don't have it documented. I've listed the ones I know about here, however you should still check with them if you're not sure.

<ul>
    <li><strong>DreamHost</strong> - If you're using DreamPress, go into the Panel and click on the DNS settings for the domain. The entry for <em>resolve-to.domain</em> is your varnish server: `resolve-to.www A 208.97.157.172`</li>
</ul>

== Changelog ==

= 2.2 =
* Added in workaround for Varnish purge reqs going AWOL when another proxy server is in place. (props to Shredder and Berler)

= 2.1 =
* Removed old code that had been commented out.
* Add in purge for gzip'd files too (props <a href="http://wordpress.org/support/topic/does-not-purge-compressed-objects-in-varnish-20">jumpzork</a>) - Varnish 2.x support.
* Header Image

= 2.0 =
* Commit access handed to Ipstenu
* Changed CURL to wp_remote_request (thank you <a href="http://wordpress.org/support/topic/incompatability-with-editorial-calendar-plugin?replies=1">Kenn Wilson</a>) so we don't have to do <a href="http://wordpress.org/support/topic/plugin-varnish-http-purge-incompatibility-with-woocommerce?replies=6">CURLOPT_RETURNTRANSFER</a> Remember kids, CURL is okay, but wp_remote_request is more better.

= 1.2.0 =
* Moved actual request execution to "shutdown" event
* Removed GET request due to bad performance impact