<?php

require_once('view.php');

class Controller {
    public $name;
    public $layout = 'main';
    public $title = 'Anticap Nano Framework';
    
    public function render($view, $params = []) {
        //var_dump($this -> layout);
        //$layout = new View()
        $this -> name = strtolower(static::class);
        
        $content = new View($this, $view, $params);
        //return $view -> render();
        
        $layout = new View($this, $this -> layout, [
            'title' => $this -> title,
            'content' => $content -> getContent(),
        ], true);
        
        return $layout -> render();
        //return $view -> render();
        /*
        $this -> name = strtolower(static::class);
        extract($params);
        ob_start();
        require_once(PATH.'/views/'.$this -> name.'/'.$view.'.php');
        $view = ob_get_clean();
        echo $view;
        return $view;
        */
    }
}