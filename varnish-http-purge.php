<?php
/*
Plugin Name: Varnish HTTP Purge
Plugin URI: http://wordpress.org/extend/plugins/varnish-http-purge/ 
Description: Sends HTTP PURGE requests to URLs of changed posts/pages when they are modified. Works with Varnish 3.
Version: 1.2.0
Author: Leon Weidauer
Author URI: http:/sevenmil.es/
License: Apache License 2.0 http://www.apache.org/licenses/LICENSE-2.0
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
        $c = curl_init($url);
        curl_setopt($c, CURLOPT_CUSTOMREQUEST, 'PURGE');
        curl_exec($c);
        curl_close($c);    
    }

    public function purgePost($postId)
    {
        array_push($this->purgeUrls, get_permalink($postId));
    }
}

$purger = new VarnishPurger();

