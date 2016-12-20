<?php

require_once('framework/mysql.php');

class Model {// extends MySQL {
    private static $connection;
    protected static $isNewRecord = false;
    public $attributes = [];
    private static $instance;
    
    public function isNewRecord() {
        return self::$isNewRecord;
    }
    
    public static function getInstance() {
        return self::$instance;
    }
    
    public function getClassName() {
        return static::class;
    }
    
    public function __construct($isNewRecord = true)
    {
        self::$instance = $this;
        self::$isNewRecord = $isNewRecord;
        //$this -> isNewRecord = true;
        //self::$isNewRecord = true;
        return $this -> getConnection();
    }
    
    public static function getConnection() {
        if(!self::$connection) {
            self::$connection = new MySQL();
            self::$connection -> setTableName(static::$tableName);
            self::$connection -> model = static::getInstance();
        }
        
        return self::$connection;
    }
    
    public static function find() {
        self::$isNewRecord = false;
        return self::getConnection();
    }

    public function __get($key) {
        //return self::$connection -> getAttribute($key);
        return $this -> attributes[$key];
    }
    
    public function __set($key, $value) {
        //self::$connection -> setAttribute($key, $value);
        $this -> attributes[$key] = $value;
    }
    
    public function save() {
        if($this -> isNewRecord()) {
            //var_dump(self::$connection);
            return self::$connection -> insert();
        } else {
            return self::$connection -> update();
        }
    }
}