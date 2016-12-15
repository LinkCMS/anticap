<?php

require_once('view.php');

class Controller {
    public $name;
    
    public function render($view, $params = []) {
        //return new View(static::class, $view, $params);
        $this -> name = strtolower(static::class);
        extract($params);
        ob_start();
        require_once(PATH.'/views/'.$this -> name.'/'.$view.'.php');
        $view = ob_get_clean();
        echo $view;
        return $view;
    }
}