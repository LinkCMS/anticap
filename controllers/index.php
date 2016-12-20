<?php

require_once('framework/controller.php');
require_once('models/TrainSet.php');
require_once('http.php');

class Index extends Controller  {
    public function getCaptchaStatus() {
        /*
        $http = new Http();
        $antigate = $http -> get('http://rucaptcha.com/res.php?key=d6c189ec8213ec0a00c39c8cbdfd2fc0&action=get&id='.$captchaId) -> body;
        $antigate = explode('|', $antigate);
        var_dump($antigate[1]);
        */
        
        //var_dump(TrainSet::getInstance() -> select() -> where('value IS NULL') -> getSql());
        //$model = TrainSet::find() -> select() -> where('value IS NULL') -> fetchAll();
        
        /*
        $model = TrainSet::find() -> select() -> where('value IS NULL') -> fetchAll();
        var_dump($model);
        */
        
        /*
        $model = new TrainSet();
        $model -> value = '73477';
        var_dump($model -> save());
        die();
        */
        
        
        
        
        //$model = TrainSet::find() -> select() -> where('value = "" OR value IS NULL') -> all();
        $model = TrainSet::find() -> select() -> where('value = "" OR value IS NULL') -> all();
        
        foreach ($model as $item) {
            
            $http = new Http();
            $antigate = $http -> get('http://rucaptcha.com/res.php?key=d6c189ec8213ec0a00c39c8cbdfd2fc0&action=get&id='.$item -> antigate_id) -> body;
            $antigate = explode('|', $antigate);
            
            $item -> value = @$antigate[1];
            
            //var_dump($item -> save());
            //var_dump($item -> id);
        }
        //var_dump($model[0] -> id);
        
        
        /*
        $model = TrainSet::find() -> select() -> where('value IS NULL') -> all();
        $model[0] -> value = '12345';
        var_dump($model[0] -> save());
        */
        
        
        //$model[0] -> value = '12345';
        
        
        //$model[0] -> value = '123';
        //$model[0] -> value = 'asd';
        //var_dump($model[0]);
        //$model[0] -> save();
        
    }
    
    public function sendToAntigate() {


/*
        $trainset = new TrainSet();
        $trainset -> content = base64_encode(file_get_contents('collect/0.png'));
        $trainset -> antigate_id = 236267;
        $trainset -> insert();
*/

/*
$image = TrainSet::find() -> select(['content']) -> where('id = 15') -> fetchAll();
        header('content-type: image/png');
echo(base64_decode($image[0]['content']));
        die();
        */
        
        //$trainset = new TrainSet();
        
        //var_dump($trainset -> sql('SHOW COLUMNS FROM `collect`') -> fetchAll());
        //var_dump($trainset -> getSchema());
        //TrainSet::find() -> select(['*']) -> fetchAll();
        
        //var_dump($trainset -> select() -> where('id = 1') -> fetchAll());
        //var_dump($trainset -> set() -> where('id = 1') -> fetchAll());
        //var_dump($trainset -> insert(['content' => '"test"', 'value' => '"TEST"']) -> execute());
        //var_dump($trainset -> select() -> fetchAll());

        /*
        $trainset = new TrainSet();
        $trainset -> content = 'asd';
        $trainset -> value = 'zxc';
        
        $trainset -> insert();
        */
        
        //var_dump(TrainSet::find() -> select() -> where('id = 12') -> fetchAll());
        
        //return false;
        $http = new Http();
        $image = base64_encode($http -> get('http://check.gibdd.ru/proxy/captcha.jpg') -> body);
        $antigate = $http -> post('http://rucaptcha.com/in.php', [
            'method' => 'base64',
            'key' => 'd6c189ec8213ec0a00c39c8cbdfd2fc0',
            'body' => $image,
        ]) -> body;
        
        $antigate = explode('|', $antigate);
        
        $trainset = new TrainSet();
        
        $trainset -> content = $image;
        $trainset -> antigate_id = $antigate[1];
        var_dump($trainset -> save());
    }
}