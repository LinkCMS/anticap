<?php

class MySQL {
    private static $instance;
    private $pdo;
    //private $template = 'SELECT :attributes FROM :table WHERE :where LIMIT :limit ORDER BY :order :direction';
    private $sql;
    public $tableName;
    //public $attributes = [];
    public $schema;
    public $model;
    public $modelName;
    //public $resultSet;
    
    //public function __construct($host, $username, $password, $database) {
    public function __construct() {
        $host = App::$config['db']['host'];
        $database = App::$config['db']['database'];
        $username = App::$config['db']['username'];
        $password = App::$config['db']['password'];
        $this -> pdo = new pdo("mysql:host={$host};dbname={$database}", $username, $password);
        //$this -> tableName = $this -> table();
        //$this -> setTableName($this -> tableName);
    }
    
    public static function getInstance() {
        if(!self::$instance) {
            self::$instance = new self();
            //self::$instance -> tableName = static::tableName();
            self::$instance -> model = static::class;
        }

        return self::$instance;
    }
    
    public function setTableName($tableName) {
        $this -> tableName = $tableName;
        $this -> getSchema();
    }

    public function select($attibutes = ['*']) {
        $this -> sql = 'SELECT '.implode(', ', $attibutes).' FROM '.$this -> tableName;
        return $this;
    }

    //public function insert($attributes) {
    public function insert() {
        //var_dump()
        //var_dump($this -> model -> getClassName());
        $this -> sql = 'INSERT INTO `'.$this -> tableName.'` ('.implode(', ', array_keys($this -> model -> attributes)).') VALUES ('.implode(', ', array_values($this -> model -> attributes)).')';

        return $this -> execute();
        //var_dump($this -> sql);
        //var_dump(${$this -> model}::$attributes);
        
        /*
        $this -> sql = 'INSERT INTO `'.$this -> tableName.'` ('.implode(', ', array_keys($attributes)).') VALUES ('.implode(', ', array_values($attributes)).')';
        return $this -> execute();
        */
    }


    public function update($model) {
        $attributes = [];
        foreach ($model -> attributes as $key => $val) {
            $attributes[] = "`{$key}` = {$val}";
        }
        
        //var_dump($model -> oldAttributes);die();

        $this -> sql = 'UPDATE `'.$this -> tableName.'` SET '.implode(', ', $attributes).' WHERE `id` = '.$model -> id;
        //return $this -> execute();
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

    public function all() {
        $statement = $this -> pdo -> prepare($this -> sql);
        $statement -> execute();
        
        if($this -> model) { // Инстанс модели уже существует (создаётся новая запись)
            echo 'asd';
        } else {
            if($this -> modelName) {
                $items = [];
    
                foreach($statement -> fetchAll(PDO::FETCH_OBJ) as $i => $item) {
                    //var_dump($item);die();
    
                    //var_dump($ts);
                    
                    $items[$i] = new $this -> modelName(false);

                    $items[$i] -> load($item);
                    /*
                    foreach ($item as $key => $val) {
                        $items[$i] -> $key = $val;
                    }
                    */
                }
                
                return $items;
            } else {
                return $statement -> fetchAll(PDO::FETCH_ASSOC);
            }
        }
    }
    
    public function one() {
        
    }
    
    public function sql($sql) {
        $this -> sql = $sql;
        return $this;
    }
    
    private function getSchema() {
        $schema = $this -> sql('SHOW COLUMNS FROM `'.$this -> tableName.'`') -> all();
        
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
        try {
            //return $this -> pdo -> exec($this -> sql);
            return $this -> pdo -> exec($this -> sql);
        } catch (PDOException $e) {
            var_dump($e);die();
        }
    }
    
    public function getSql() {
        return $this -> sql;
    }
/*
    public function getAttribute($key) {
        return trim($this -> attributes[$key], "'");
    }

    public function setAttribute($key, $value) {
        if(!$attribute = @$this -> schema[$key]) {
            throw new Exception('У модели нет атрибута с таким именем: <b>'.$key.'</b>');
        }

        switch($attribute['type']) {
            case 'blob':
            case 'varchar':
                //$value = trim($value, "'");
                $value = "'{$value}'";
                break;
        }

        $this -> attributes[$key] = $value;
    }
    */
    /*
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
    */
}