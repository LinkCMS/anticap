<?php

require_once('framework/controller.php');

class Test extends Controller  {
    public function index() {
        echo 'А это уже совсем другой контроллер';
    }
}