<?php
/*
Plugin Name: Varnish HTTP Purge
Plugin URI: http://wordpress.org/extend/plugins/varnish-http-purge/ 
Description: Sends HTTP PURGE requests to URLs of changed posts/pages when they are modified. Works with Varnish 3.
Version: 1.1.0
Author: Leon Weidauer
Author URI: http:/sevenmil.es/
License: Apache License 2.0 http://www.apache.org/licenses/LICENSE-2.0
*/

class VarnishPurger
{
    protected $purgedUrls = array();
    
    public function __construct()
    {
        foreach ($this->getRegisterEvents() as $event)
        {
            add_action($event, array($this, 'purgePost'));
        }
    }

    protected function getRegisterEvents()
    {
        return array(
            'publish_post',
            'edit_post',
            'deleted_post',
        );
    }

    protected function purgeUrl($url)
    {
        if (in_array($url, $this->purgedUrls))
        {
            //Don't purge the same item more than once in one run.
            return;
        }

        $c = curl_init($url);
        curl_setopt($c, CURLOPT_CUSTOMREQUEST, 'PURGE');
        curl_exec($c);
        curl_close($c);    
        
        array_push($this->purgedUrls, $url);

        $this->callUrl($url);
    }

    protected function callUrl($url)
    {
        $c = curl_init($url);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        curl_exec($c);
        curl_close($c);    
    }
    
    public function purgePost($postId)
    {
        $this->purgeUrl(get_permalink($postId));
    }
}

$purger = new VarnishPurger();

