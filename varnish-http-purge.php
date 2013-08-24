<?php
/*
Plugin Name: Varnish HTTP Purge
Plugin URI: http://wordpress.org/extend/plugins/varnish-http-purge/ 
Description: Sends HTTP PURGE requests to URLs of changed posts/pages when they are modified. 
Version: 3.0b
Author: Mika Epstein
Author URI: http://halfelf.org/
License: http://www.apache.org/licenses/LICENSE-2.0

Original Author: Leon Weidauer ( http:/www.lnwdr.de/ )

Copyright 2013: Mika A. Epstein (email: ipstenu@ipstenu.org)

    This file is part of Varnish HTTP Purge, a plugin for WordPress.

    Varnish HTTP Purge is free software: you can redistribute it and/or modify
    it under the terms of the Apache License 2.0 license.

    Varnish HTTP Purge is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.

*/

//$GLOBALS['wp_rewrite']->using_permalinks()

class VarnishPurger {
    protected $purgeUrls = array();
    
    public function __construct() {
        add_action( 'init', array( &$this, 'init' ) );
        //add_action( 'admin_bar_menu', array( $this, 'varnish_links' ), 100 );
        add_action( 'rightnow_end', array( $this, 'varnish_rightnow' ) );
    }
    
    public function init() {
        foreach ($this->getRegisterEvents() as $event) {
            add_action( $event, array($this, 'purgePost') );
        }
        add_action( 'shutdown', array($this, 'executePurge') );

        if ( isset($_GET['vhp_flush_all']) && current_user_can('manage_options') && check_admin_referer('helf_vhp') ) {
            add_action( 'admin_notices' , array( $this, 'purgeMessage'));
        }
    }

    function purgeMessage() {
        echo "<div id='message' class='updated fade'><p><strong>".__('Varnish purge flushed!', helf_vhp)."</strong></p></div>";
    }

    function varnish_rightnow() {
        if (current_user_can('activate_plugins')) {
            $url = wp_nonce_url(admin_url('?vhp_flush_all'), 'helf_vhp');
            $intro = sprintf( __('<a href="%1$s">Varnish HTTP Purge</a> automatically purges your posts when published or updated. Sometimes you need a manual flush. Press the button below to force it to purge your entire cache.', helf_vhp ), 'http://wordpress.org/plugins/varnish-http-purge/' );
            $button = sprintf( __('<p class="button"><a href="%1$s"><strong>Purge Varnish Cache</strong></a></p>', helf_vhp ), $url );
            $text = $intro . '<br />' . $button;
            echo "<p class='varnish-rightnow'>$text</p>\n";
        }
    }

    // For the not being used at this moment admin bar
    function varnish_links() {
        global $wp_admin_bar;
          if ( !is_super_admin() || !is_admin_bar_showing() || !is_admin() )
        return;

        $url = wp_nonce_url(admin_url('?vhp_flush_all'), 'helf_vhp');
        $wp_admin_bar->add_menu( array( 'id' => 'varnish_text', 'title' => __( 'Purge Varnish', helf_vhp ), 'href' => $url ) );
    }

    protected function getRegisterEvents() {
        return array(
            'publish_post',
            'edit_post',
            'deleted_post',
            'switch_theme',
            'delete_attachment'
        );
    }

    public function executePurge() {
        $purgeUrls = array_unique($this->purgeUrls);

        foreach($purgeUrls as $url) {
            $this->purgeUrl($url);
        }
        
        if (!empty($purgeUrls)) {
            $this->purgeUrl(home_url());
        } else {
            if ( isset($_GET['vhp_flush_all']) && current_user_can('manage_options') && check_admin_referer('helf_vhp') ) { 
                $this->purgeUrl( home_url() .'/?vhp=regex' );
            }
        }      
    }

    protected function purgeUrl($url) {
        // Parse the URL for proxy proxies
        $p = parse_url($url);
        
        if ( $p['query'] == 'vhp=regex' ) {
            $pregex = '.*';
            $varnish_x_purge = 'regex';
        } else {
            $varnish_x_purge = 'direct';
        }

        // Build a varniship
        if ( !defined( 'VHP_VARNISH_IP' ) && VHP_VARNISH_IP ) {
            $varniship = get_option('vhp_varnish_ip');
        } else {
            $varniship = VHP_VARNISH_IP;
        }

        // If we made varniship, let it sail
        if ( isset($varniship) ) {
            $purgeme = $p['scheme'].'://'.$varniship.$p['path'].$pregex;
        } else {
            $purgeme = $p['scheme'].'://'.$p['host'].$p['path'].$pregex;
        }

        // Cleanup CURL functions to be wp_remote_request and thus better
        // http://wordpress.org/support/topic/incompatability-with-editorial-calendar-plugin
        wp_remote_request($purgeme, array('method' => 'PURGE', 'headers' => array( 'host' => $p['host'], 'X-Purge' => $varnish_x_purge ) ) );
    }

    public function purgePost($postId) {
    
        // Category & Tag purge based on Donnacha's work in WP Super Cache
        $categories = get_the_category($postId);
        if ( $categories ) {
            $category_base = get_option( 'category_base');
            if ( $category_base == '' )
                $category_base = '/category/';
            $category_base = trailingslashit( $category_base );
            foreach ($categories as $cat) {
                array_push($this->purgeUrls, home_url( $category_base . $cat->slug . '/' ) );
            }
        }
        
        $tags = get_the_tags($postId);
        if ( $tags ) {
            $tag_base = get_option( 'tag_base' );
            if ( $tag_base == '' )
                $tag_base = '/tag/';
            $tag_base = trailingslashit( str_replace( '..', '', $tag_base ) ); 
            foreach ($tags as $tag) {
                array_push($this->purgeUrls, home_url( $tag_base . $tag->slug . '/' ) );
            }
        }
    
        array_push($this->purgeUrls, get_permalink($postId));
    }

}

$purger = new VarnishPurger();