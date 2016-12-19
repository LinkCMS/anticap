<?php

require_once('framework/mysql.php');

class Model extends MySQL {
    /*
    public function __construct()
    {
        $this -> model = static::class;
    }
    */
    public static function find() {
        $mysql = new MySQL();
        $mysql -> setTableName(static::$tableName);
        $mysql -> model = static::class;
        //$mysql -> setTableName(static::table());
        return $mysql;
    }
}