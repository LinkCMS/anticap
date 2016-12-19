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
        
        if($this -> action = @$request['action'] ? : 'index') {
            //var_dump($this -> action);die();
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
    }
    
    public function get($param = null) {
        if(!$param) {
            return $_GET;
        }
        else {
            return @$_GET[$param] ? : null;
        }
    }
    
    public function post($param = null) {
        if(!$param) {
            return $_POST;
        }
        else {
            return @$_POST[$param] ? : null;
        }
    }
}