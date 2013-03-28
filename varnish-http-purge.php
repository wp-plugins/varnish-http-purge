<?php
/*
Plugin Name: Varnish HTTP Purge
Plugin URI: http://wordpress.org/extend/plugins/varnish-http-purge/ 
Description: Sends HTTP PURGE requests to URLs of changed posts/pages when they are modified. 
Version: 2.0
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

class VarnishPurger
{
    protected $purgeUrls = array();
    
    public function __construct()
    {
        foreach ($this->getRegisterEvents() as $event)
        {
            add_action($event, array($this, 'purgePost'));
        }
        add_action('shutdown', array($this, 'executePurge'));
    }

    protected function getRegisterEvents()
    {
        return array(
            'publish_post',
            'edit_post',
            'deleted_post',
        );
    }

    public function executePurge()
    {
        $purgeUrls = array_unique($this->purgeUrls);

        foreach($purgeUrls as $url)
        {
            $this->purgeUrl($url);
        }
        
        if (!empty($purgeUrls))
        {
            $this->purgeUrl(home_url());
        }        
    }

    protected function purgeUrl($url)
    {
        //$c = curl_init($url);
        //curl_setopt($c, CURLOPT_CUSTOMREQUEST, 'PURGE');
        
        // http://wordpress.org/support/topic/plugin-varnish-http-purge-incompatibility-with-woocommerce?replies=6
        //curl_setopt($c, CURLOPT_RETURNTRANSFER, true); 
        
        //curl_exec($c);
        //curl_close($c);
        
        // http://wordpress.org/support/topic/incompatability-with-editorial-calendar-plugin?replies=1
        wp_remote_request($url, array('method' => 'PURGE'));
    }

    public function purgePost($postId)
    {
        array_push($this->purgeUrls, get_permalink($postId));
    }
}

$purger = new VarnishPurger();