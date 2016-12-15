<?php

require_once('framework/controller.php');
require_once('models/test.php');

class Index extends Controller  {
    public function test($asd = 'zxc') {
        $model = new Test();
        return $this -> render('index', ['model' => $model]);
    }
}