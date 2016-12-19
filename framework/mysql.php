<?php

class MySQL {
    private static $instance;
    //private $template = 'SELECT :attributes FROM :table WHERE :where LIMIT :limit ORDER BY :order :direction';
    private $sql;
    public static $tableName;
    public $attributes = [];
    private $schema;
    public $model;
    
    //public function __construct($host, $username, $password, $database) {
    public function __construct() {
        $host = App::$config['db']['host'];
        $database = App::$config['db']['database'];
        $username = App::$config['db']['username'];
        $password = App::$config['db']['password'];
        self::$instance = new pdo("mysql:host={$host};dbname={$database}", $username, $password);
        //$this -> tableName = $this -> table();
        
        $this -> getSchema();
    }
    
    public static function getInstance() {
        return self::$instance;
    }
    
    public function setTableName($tableName) {
        self::$tableName = $tableName;
    }

    public function select($attibutes = ['*']) {
        $this -> sql = 'SELECT '.implode(', ', $attibutes).' FROM '.static::$tableName;
        return $this;
    }

    //public function insert($attributes) {
    public function insert() {
        $this -> sql = 'INSERT INTO `'.static::$tableName.'` ('.implode(', ', array_keys($this -> attributes)).') VALUES ('.implode(', ', array_values($this -> attributes)).')';
        return $this -> execute();
    }
    
    public function update() {
        return $this;
    }
    
    public function delete() {
        return $this;
    }
    
    public function orderBy($attribute, $direction = 'ASC') {
        $this -> sql .= ' ORDER BY '.$attribute.' '.$direction;
        return $this;
    }
    
    public function where($conditions) {
        //$this -> sql .= ' WHERE '.implode(' AND ', $conditions);
        $this -> sql .= ' WHERE '. $conditions;
        return $this;
    }

    public function andWhere($conditions) {
        $this -> sql .= ' AND '.$conditions;
        return $this;
    }

    public function orWhere($conditions) {
        $this -> sql .= ' OR '.$conditions;
        return $this;
    }
    
    public function limit($start = 0, $count = 10) {
        $this -> sql .= ' LIMIT '.$start.', '.$count;
        return $this;
    }
    
    public function fetch() {
        return $this;
    }

    public function fetchAll() {
        $statement = self::$instance -> prepare($this -> sql);
        $statement -> execute();
        
        return $statement -> fetchAll();
        //return $statement -> fetchAll(PDO::FETCH_ASSOC);
        //return $this;
    }
    
    public function sql($sql) {
        $this -> sql = $sql;
        return $this;
    }
    
    private function getSchema() {
        $schema = $this -> sql('SHOW COLUMNS FROM `'.static::$tableName.'`') -> fetchAll();

        foreach($schema as $attribute) {
            preg_match('/([a-zA-Z]+)(\(([0-9]+)\))?/', $attribute['Type'], $type);
            $this -> schema[$attribute['Field']] = [
                'type' => $type[1],
                'size' => @$type[3] ? : null,
            ];
        }
        
        return $schema;
    }
    
    public function execute() {
        return self::$instance -> exec($this -> sql);
    }
    
    public function getSql() {
        return $this -> sql;
    }
    
    public function __get($attribute) {
        
    }
    
    public function __set($key, $value) {
        if(!$attribute = @$this -> schema[$key]) {
            throw new Exception('У модели нет атрибута с таким именем: <b>'.$key.'</b>');
        }
        
        switch($attribute['type']) {
            case 'blob':
            case 'varchar': 
                $value = "'{$value}'";
                break;
        }
        
        $this -> attributes[$key] = $value;
    }
}