<?php

namespace App;

class Navigationcurl
{
    public $url;


    public function setCurl($url)
    {
        $this->url = $url;
    }


    public  function getCurl($redirect =null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
     if($redirect)
     {
         curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
     }
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
//        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
//        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        $data = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $data;
    }


}