<?php

class Request {
    public $controller;
    public $action;
    public $params = [];
    
    public function __construct() {
        
        $request = $_REQUEST;
        
        if($controller = @$request['controller'] ? : 'index') {
            if(@file_exists(PATH.'/controllers/'.$controller.'.php')) {
                require_once(PATH.'/controllers/'.$controller.'.php');
                $this -> controller = new $controller();
            } else {
                throw new Exception('Не найден файл контроллера');
            }
        }
        
        if($this ->action = @$request['action']) {
            if(method_exists($controller, $this ->action)) {
                $method = new ReflectionMethod($controller, $this -> action);
                if(!$method -> isPublic()) {
                    throw new Exception('Данный метод не является действием');
                }

                foreach($method -> getParameters() as $arg) {
                    //var_dump($arg -> getDefaultValue());
                    
                    //try {
                        if(isset($request[strtolower($arg -> name)])) {
                            $this -> params[] = $request[strtolower($arg -> name)];
                        } else {
                            try {
                                $this -> params[] = $arg -> getDefaultValue();
                            } catch (Exception $e) {
                                throw new Exception('Не передан обязательный аргумент: $'.$arg -> name);
                            }
                        }
                        /*
                    } catch (Exception $e) {
                        //$params[] = $_GET[strtolower($arg -> name)];
                        die('!!');
                    }
                        */
                }
                
                //call_user_func_array([$controller, $action], $params);
                /*
                return [
                    'controller' => $controller,
                    'action' => $action,
                    'params' => $params
                ];
                */
            } else {
                throw new Exception('Нет такого действия');
            }
        }
        
        /*
        
        if(isset($_GET['action'])) {
            $action = $_GET['action'];

            if(method_exists($this, $action)) {
                $method = new ReflectionMethod($this, $action);
                if(!$method -> isPublic()) {
                    throw new Exception('Данный метод не является действием');
                }
                
                $params = [];

                foreach($method -> getParameters() as $arg) {
                    try {
                        $arg -> getDefaultValue();
                        if(isset($_GET[strtolower($arg -> name)])) {
                            $params[] = $_GET[strtolower($arg -> name)];
                        }
                    } catch (Exception $e) {
                        $params[] = $_GET[strtolower($arg -> name)];
                    }
                    //if(!@$arg -> getDefaultValue()) {

                    //}
                }

                call_user_func_array([$this, $action], $params);
            } else {
                throw new Exception('Нет такого действия');
            }
        }
        */
    }
    
    public function get($param = null) {
        if(!$param) {
            return $_GET;
        }
        else {
            return $_GET[$param];
        }
    }
    
    public function post($param = null) {
        if(!$param) {
            return $_POST;
        }
        else {
            return $_POST[$param];
        }
    }
}