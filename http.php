<?php
require_once('simple_html_dom.php');

class Http {
    private $connection;
    private $defaultOptions = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
    ];

    public function __construct($options = [])
    {
        $this -> connection = curl_init();
        curl_setopt_array($this -> connection, $this -> defaultOptions);

        curl_setopt($this -> connection, CURLOPT_COOKIEFILE, '/home/link/www/gibdd/www/cookies.txt');
        curl_setopt($this -> connection, CURLOPT_COOKIEJAR, '/home/link/www/gibdd/www/cookies.txt');
    }

    public function get($query)
    {
        curl_setopt($this -> connection, CURLOPT_URL, $query);
        curl_setopt($this -> connection, CURLOPT_RETURNTRANSFER, true);
        return new Response(curl_exec($this -> connection), $this -> getInfo());
    }

    public function getInfo()
    {
        return curl_getinfo($this -> connection);
    }

    public function setOpt($opt, $value)
    {
        curl_setopt($this -> connection, $opt, $value);
    }

    public function post($url, $data = [])
    {
        curl_setopt($this -> connection, CURLOPT_URL, $url);
        curl_setopt($this -> connection, CURLOPT_POST, true);
        curl_setopt($this -> connection, CURLOPT_POSTFIELDS, $data);
        return new Response(curl_exec($this -> connection), $this -> getInfo());
    }
}

class Response {
    public $headers;
    public $body;
    public $html;

    public function __construct($response, $pageInfo)
    {
        $this -> headers = substr($response, 0, $pageInfo['header_size']);
        $this -> body = substr($response, $pageInfo['header_size']);

        $this -> html = str_get_html($this -> body);
        /*
        $this -> html = new DOMDocument();
        @$this -> html -> loadHTML($this -> body);
        */
    }
}