<?php

require_once('framework/controller.php');

class Test extends Controller  {
    public function index() {
        return $this -> render('../index/test');
    }
}