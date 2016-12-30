<?php

require_once('framework/controller.php');
require_once('models/TrainingSet.php');
require_once('http.php');
require_once('captcha.php');
require_once('fann.php');

class Index extends Controller  {
    public function generateTestFile() {
        set_time_limit(0);

        $dir = scandir('test');
        array_shift($dir);
        array_shift($dir);
        
        $testSet = null;
        $testSet .= (count($dir) * 5).' '.App::$config['fann']['inputs'].' 10';

        foreach ($dir as $i => $item) {
            $captcha = new Captcha(file_get_contents('test/'.$item));
            $captcha -> preprocess();
            $segments = $captcha -> map();

            foreach ($segments as $j => $segment) {
                /*    
                    $segment -> draw();
                    continue;
                */
                $output = array_fill(0, 10, 0);
                $testSet .= PHP_EOL.implode(' ', $segment -> getArrayOfPixels());
                $output[$item[$j]] = 1;
                $testSet .= PHP_EOL.implode(' ', $output);
            }
        }

        file_put_contents('test.dat', $testSet);
    }

    public function vis($digit) {
        for($digit = 0; $digit <= 9; $digit++) {
            echo '<div style="position: relative; float: left; width: 20px; height: 20px;">';
            $dir = scandir('segments/'.$digit);
            array_shift($dir);
            array_shift($dir);
            foreach($dir as $i => $file) {
                $seg = new Captcha(file_get_contents('segments/'.$digit.'/'.$file));
                $seg -> visualize();
                
            }
            echo '</div>';
        }
    }

    public function map($file) {
        $captcha = new Captcha(file_get_contents($file));
        $captcha -> preprocess();
        $segments = $captcha -> map();
        foreach($segments as $segment) {
            $segment -> draw();
        }
    }
    
    public function generateSegments() {
        set_time_limit(0);
        $model = TrainingSet::find() -> select() -> all();

        foreach ($model as $i => $item) {
            $captcha = new Captcha($item -> image);
            $captcha -> preprocess();
            $segments = $captcha -> map();

            foreach ($segments as $j => $segment) {
                $segment -> save('segments/'.$item -> code[$j].'/'.rand(0, 9999999).'.png');
            }
        }
    }
    
    public function generateTrainFile() {
        set_time_limit(0);
        //$count = TrainingSet::find() -> count();
    
        $trainingSet = [];
        $trainingSet[] = null;
        $count = 0;
        //$trainingSet .= ($count * 5).' '.App::$config['fann']['inputs'].' 10';
        
        for($i = 0; $i <= 9; $i++) {
            $dir = scandir('segments/'.$i);
            array_shift($dir);
            array_shift($dir);
            
            foreach($dir as $file) {
                $segment = new Segment(file_get_contents('segments/'.$i.'/'.$file));

                $count++;
                
                $output = array_fill(0, 10, 0);
                $trainingSet[] = implode(' ', $segment -> getArrayOfPixels());
                $output[$i] = 1;
                $trainingSet[] = implode(' ', $output);
            }
            
            $trainingSet[0] = $count.' '.App::$config['fann']['inputs'].' 10';
        }
        
        /*
        foreach ($model as $i => $item) {
            $captcha = new Captcha($item -> image);
            $captcha -> preprocess();
            $segments = $captcha -> map();
            
            foreach ($segments as $j => $segment) {
            
                $output = array_fill(0, 10, 0);
                $trainingSet .= PHP_EOL.implode(' ', $segment -> getArrayOfPixels());
                $output[$item -> code[$j]] = 1;
                $trainingSet .= PHP_EOL.implode(' ', $output);
            }
        }
        */

        file_put_contents('train.dat', implode(PHP_EOL, $trainingSet));
    }
    
    public function train() {
        set_time_limit(0);
       
        $fann = new Fann();
        //$fann -> load(App::$config['fann']['net']);
        $fann -> create([App::$config['fann']['inputs'], 198, 136, 74, 10]);
        $fann -> train('train.dat', App::$config['fann']['net']);
    }
    
    public function test($input = []) {
        $fann = new Fann();
        $output = null;
        $dir = scandir('test');
        array_shift($dir);
        array_shift($dir);
        
        $captcha = new Captcha(file_get_contents('test/'.$dir[array_rand($dir)]));
        $captcha -> draw();
        $fann -> load(App::$config['fann']['net']);
        $captcha -> preprocess();
        $segments = $captcha -> map();
        foreach($segments as $segment) {
            var_dump($fann -> test($segment -> getArrayOfPixels(), $output));
            var_dump($output);
        }
    }
    
    public function check() {
        $dir = scandir('test');
        array_shift($dir);
        array_shift($dir);

        $success = 0;
        $error = 0;
        $digits = array_fill(0, 10, 0);

        foreach ($dir as $file) {
            //$this -> loadImage('test/'.$file);
            //$this -> draw();
            $captcha = new Captcha(file_get_contents('test/'.$file));
            $captcha -> preprocess();
            
            $expected = str_replace('.png', '', $file);
            $segments = $captcha -> map();

            $fann = new Fann();
            $fann -> load(App::$config['fann']['net']);
            $response = null;
            
            foreach($segments as $segment) {
                $response .= key($fann -> test($segment -> getArrayOfPixels()));
            }
            
            if($response == $expected) {
                $success++;
            } else {
                for($i = 0; $i < 5; $i++) {
                    if($response[$i] != $expected[$i]) {
                        $digits[$expected[$i]]++;
                    }
                }
                $error++;
            }
        }

        //echo(json_encode($digits));echo '<hr>';
        echo 'Digits errors: ';
        var_dump($digits);

        echo 'Success: ';
        var_dump($success);
        echo 'Errors: ';
        var_dump($error);
    }
    
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
        $model = TrainingSet::find() -> select() -> where('`code` IS NULL') -> all();
        
        foreach ($model as $item) {
            
            $http = new Http();
            $antigate = $http -> get('http://rucaptcha.com/res.php?key=d6c189ec8213ec0a00c39c8cbdfd2fc0&action=get&id='.$item -> antigate_id) -> body;
            $antigate = explode('|', $antigate);
            
            $item -> code = @$antigate[1];
            var_dump($item -> save());
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
    
    public function sendCollection() {
        /*
        $http = new Http();
        for($i = 0; $i < 0; $i++) {
            $image = file_get_contents("collect/{$i}.png");
            
            $antigate = $http -> post('http://rucaptcha.com/in.php', [
                'method' => 'base64',
                'key' => 'd6c189ec8213ec0a00c39c8cbdfd2fc0',
                'body' => base64_encode($image),
            ]) -> body;

            $antigate = explode('|', $antigate);

            $trainset = new TrainSet();
            $trainset -> image = $image;
            $trainset -> antigate_id = $antigate[1];
            //var_dump($trainset -> save());
            
            var_dump($trainset -> save());
        }
        */
    }
    
    public function sendToAntigate($count = 1) {


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
        
        for($i = 0; $i < $count; $i++) {
            $image = $http -> get('http://check.gibdd.ru/proxy/captcha.jpg') -> body;
            //$image = base64_encode(file_get_contents('collect/0.png'));
            
            $antigate = $http -> post('http://rucaptcha.com/in.php', [
                'method' => 'base64',
                'key' => 'd6c189ec8213ec0a00c39c8cbdfd2fc0',
                'body' => base64_encode($image),
            ]) -> body;
    
            $antigate = explode('|', $antigate);
            
            $trainingSet = new TrainingSet();
            $trainingSet -> image = $image;
            $trainingSet -> antigate_id = $antigate[1];
            var_dump($trainingSet -> save());
        }
    }
}