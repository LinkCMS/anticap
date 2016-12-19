<?php

require_once('request.php');
require_once('response.php');

class App {
    public $requset;
    public $response;
    public static $instance;
    public static $config;
    
    public function exception(Exception $e) {
        die($e -> getMessage());
    }

    public function __construct()
    {
        $this -> response = new Response();
        $this -> response -> setHeader('Content-type: text/html; charset=utf-8');
        
        self::$instance = $this;
        set_exception_handler([$this, 'exception']);
        $this -> requset = new Request();
    }
    
    public function run($config) {
        self::$config = $config;
        //$this -> response -> send();
        call_user_func_array([$this -> requset -> controller, $this -> requset -> action], $this -> requset -> params);
    }
}