<?php

class View {
    private $view;
    public function __construct($controller, $view, $params = [], $isLayout = false) {
        //var_dump($controller);die();
        extract($params);
        ob_start();
        
        if($isLayout) {
            require_once(PATH.'/views/layouts/'.$view.'.php');
        } else {
            require_once(PATH.'/views/'.$controller -> name.'/'.$view.'.php');
        }
        $this -> view = ob_get_clean();
        //$this -> view = ob_get_contents();
        if(@$this -> title) {
            $controller -> title = $this -> title;
        }
        return $this -> view;
    }
    
    public function render() {
        echo $this -> view;
    }
    
    public function getContent() {
        return $this -> view;
    }
}