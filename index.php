<?php
require_once('http.php');
set_time_limit(0);
error_reporting(E_ALL);
$http = new Http();
//$http -> post()
$time = explode('.', microtime(true));

$utc_str = gmdate("M d Y H:i:s", $time[0]);
$utc = strtotime($utc_str);
$timestamp = substr($utc.$time[1], 0, 13);

class Anticap {
    /**
     * @var Imagick 
     */
    private $image;
    
    private function loadImage($fileName) {
        //$this -> image = new Imagick('collect/'.$fileName);
        $this -> image = new Imagick($fileName);
        return $this;
    }
    
    public function image($fileName) {
        $this -> loadImage($fileName) -> preprocess() -> draw();
    }
    
    public function draw() {
        echo '<img src="data:image/png;base64,'. base64_encode($this -> image -> getImageBlob()) .'">';
    }
    
    public function map($fileName = null, $write = true, $visualize = true) {
        if(!is_null($fileName))
        $this -> loadImage($fileName) -> preprocess();
        
        $draw = new ImagickDraw();
        $segments = [0, 0, 0, 0, 0];
        $offset = 0;
        $b = 0;
        $count = 0;
        
        //$offsetGlobal = 0;
        $currentIsBig = false;

        //for($x = 0; $x <= $this -> image -> getImageWidth(); $x += 5) {
        do {
            $currentIsBig = false;

            for($x = $offset; $x <= $offset + 12 ; $x += 3) {
                for($y = 0; $y < 7; $y++) {
                    $pixel = $this -> image -> getImagePixelColor($x, $y);
                    if($pixel -> getColor()['a'] == 1) {
                        $draw -> setFillColor('red');
                        $draw -> point($x, $y);
                        $currentIsBig = true;
                    } else {
                        $draw -> setFillColor('green');
                        $draw -> point($x, $y);
                    }
                }
                
                if($currentIsBig) {
                    break;
                }
            }
            
            if($currentIsBig) {
                $segments[$count] = 1;
                /**
                 * @var Imagick
                 */
                $segment = clone $this -> image;
                $segment -> cropImage(20, 60, $offset + 9 , 0);
                //$segment -> setImageExtent(20, 30);
                $segment -> extentImage(20, 30, 0, 0);
                $this -> test($segment);
                //$segment -> writeImage('big/'.rand(0, 9999).'.png');
                //echo '<br>';
                //echo '<img src="data:image/png;base64,'. base64_encode($segment -> getImageBlob()) .'">';
                $offset += 20;
            } else {
                $segment = clone $this -> image;
                $segment -> cropImage(15, 60, $offset + 8 , 0);
                //$segment -> resizeImage(40, 40, 1, 0, true);
                //$segment -> cropImage(40, 60, 18 , 0);
                
                $points = [
                    0, 0, -5, 0,
                    90, 0, 80, 0,
                    90, 30, 90, 30,
                    0, 30, 0, 30,
                ];

                
                $segment -> distortImage(Imagick::DISTORTION_BILINEAR, $points, false);
                $segment -> setFormat('gif');
                $segment -> setImageBackgroundColor('white');
                $segment -> setBackgroundColor('white');
                
                //$segment -> setImageExtent(20, 30);
                $segment -> extentImage(20, 30, 0, 0);
                //$segment -> writeImage('small/'.rand(0, 9999).'.png');
                
                /*
                echo '<br>';
                echo '<img src="data:image/png;base64,'. base64_encode($segment -> getImageBlob()) .'">';
                */
                $offset += 15;
            }
                
                $count++;
                //for($y = 0; $y < $this -> image -> getImageHeight(); $y++) {
                
                
                /*
                for($y = 0; $y < 7; $y++) {
                    $pixel = $this -> image -> getImagePixelColor($x, $y);
                    if($pixel -> getColor()['a'] == 1) {
                        $offset = 3;
                        $draw -> setFillColor('red');
                        $draw -> point($x, $y);
                        $b++;
                        $count++;
                    } else {
                        $offset = 1;
                        $draw -> setFillColor('green');
                        //$b--;
    
                        $draw -> point($x, $y);
                    }
    
                  
                    //$image->floodfillPaintImage($hexcolor, $fuzz, $pixel, $x, $y, false);
                }
                */
            
            

            //echo  $b;

        //}
        } while($count < 5);
        
        //echo json_encode($segments);
        
        $this -> image -> drawImage($draw);
        if($visualize)
        $this -> draw();
    }
    
    public function slice($begin, $end) {
        for($i = $begin; $i < $end; $i++) {
            $this -> map($i.'.png');
        }
    }

    function getArrayOfPixels($image) {
        //$image = new Imagick($file);

        $iterator = $image -> getPixelIterator();
        $arr = [];

        foreach ($iterator as $row => $pixels) {
            foreach ($pixels as $col => $pixel) {
                
                $arr[] = $pixel -> getColor()['r'] ? 0 : 1;
            }
        }
        $iterator -> syncIterator();

        return $arr;
    }


    public function f($fileName) {
        $arr = $this -> getArrayOfPixels($fileName);

        for($i = 0; $i < count($arr); $i++) {
            echo $arr[$i];
            if($i !== 0 && $i % 20 === 0) echo '<br>';
        }
        
        file_put_contents($fileName.'.dat', '1 600 10'.PHP_EOL.implode(' ', $arr).PHP_EOL.'-1 -1 -1 -1 1 -1 -1 -1 -1 -1');
        
        /*
        for($x = 0; $x < 20; $x++)
        for($y = 0; $y < 30; $y++) {
            echo $arr[]
        }
        */
    }

    public function test($segment) {
        $ann = fann_create_from_file(dirname(__FILE__) . "/config.net");

        //$input = array_fill(0, 540, rand(0, 1));
        $input = $this -> getArrayOfPixels($segment);

        $calc_out = fann_run($ann, $input);
        // var_dump($calc_out);
        $val = null;
        $max = null;
        foreach ($calc_out as $i => $out) {
            if($out > $max) {
                $max = $out;
                $val = $i;
            }
        }

        echo strval($val).' ';
        
        fann_destroy($ann);
    }
    
    /*
    public function test($fileName) {
        $ann = fann_create_from_file(dirname(__FILE__) . "/config.net");

        //$input = array_fill(0, 540, rand(0, 1));
        $input = $this -> getArrayOfPixels($fileName);
        
        $calc_out = fann_run($ann, $input);
        var_dump($calc_out);
        $val = null;
        $max = null;
        foreach ($calc_out as $i => $out) {
            if($out > $max) {
                $max = $out;
                $val = $i;
            }
        }
        
        echo strval($val);
        die();
        fann_destroy($ann);
    }
    */
    
    public function generateTrainFile() {
        $array = [];
        $array[] = '';
        $count = 0;
        
        for($i = 0; $i <= 9; $i++) {
            $dir = scandir('samples/'.$i);
            array_shift($dir);
            array_shift($dir);
            foreach ($dir as $file) {
                $dat = $this -> getArrayOfPixels('samples/'.$i.'/'.$file);

                $val = array_fill(0, 10, 0);
                $val[$i] = 1;
                $count++;
                $array[] = implode(' ', $dat);
                $array[] = implode(' ', $val);
            }
            //file_put_contents('train.dat', "1 600 10\n".implode(' ', $dat)."\n".implode(' ', $val));
        }

        $array[0] = $count.' 600 10';
        
        
        file_put_contents('train.dat', implode(PHP_EOL, $array));
    }
    
    public function testOnLiveData() {
        $captcha = file_get_contents('http://check.gibdd.ru/proxy/captcha.jpg');
        file_put_contents('test.png', $captcha);
        
        $this -> loadImage('test.png') -> draw();
        $this -> preprocess() -> map(null, false, false);
    }
    
    public function train() {




        $max_epochs = 50000; //50000
        $epochs_between_reports = 1000;
        $desired_error = 0.001;
        $ann = fann_create_standard(3, 600, 295, 10);
        
        fann_set_activation_function_hidden($ann, FANN_SIGMOID_SYMMETRIC);
        fann_set_activation_function_output($ann, FANN_SIGMOID_SYMMETRIC);
        fann_set_training_algorithm($ann, FANN_TRAIN_RPROP);
        fann_set_train_stop_function($ann, FANN_STOPFUNC_MSE);
        
        if (fann_train_on_file($ann, 'train.dat', $max_epochs, $epochs_between_reports, $desired_error)) {
            fann_save($ann, dirname(__FILE__) . "/config.net");
        }

        fann_destroy($ann);
        die();
        
        
        
        $bigDir = scandir('big');
        array_shift($bigDir);
        array_shift($bigDir);
        //var_dump($bigDir);
        $fileName = $bigDir[0];
        
        if(@isset($_POST['value'])) {
            ini_set('memory_limit','2048M');
            $value = $_POST['value'];
            $max_epochs = 50000; //50000
            $epochs_between_reports = 1000;
            $desired_error = 0.001;
            $ann = fann_create_standard(3, 600, 300, 10);
            fann_set_activation_function_hidden($ann, FANN_SIGMOID_SYMMETRIC);
            fann_set_activation_function_output($ann, FANN_SIGMOID_SYMMETRIC);
            
            /*
            if(file_exists('data.net')) {
                $ann = fann_create_from_file(dirname(__FILE__) . "/config.net");
                
            }
            */

            if (fann_train_on_file($ann, 'train.dat', $max_epochs, $epochs_between_reports, $desired_error)) {
                fann_save($ann, dirname(__FILE__) . "/config.net");
            }
            
            fann_destroy($ann);
            
            return true;
            
            $val = array_fill(0, 10, -1);
            $val[$value] = 1;
            
            //var_dump($val);die();
            //var_dump($this -> getArrayOfPixels('big/'.$fileName));
            //die();
            /*
            for($i = 0; $i < $max_epochs; $i++) {
                if (fann_train($ann, $this -> getArrayOfPixels('big/'.$fileName), $val)) {
                    fann_save($ann, dirname(__FILE__) . "/data.net");
              
                }
            }
            */

            $dat = $this -> getArrayOfPixels('big/'.$fileName);

            //$train_data = fann_create_train_from_callback($num_data, $num_input, $num_output, "create_train_callback");

            //create_train_callback(1, 600, 1);
            
            //fann_save_train(fann_create_train_from_callback() {}, 'train.dat');
            //fann_save($ann, dirname(__FILE__) . "/data.net");
            //unlink('big/'.$fileName);
            //header('Refresh:0');
        } else {
            
            echo <<<FORM
    <form method="post">
        <img src="big/{$fileName}">
        <input type="hidden" name="filename" value="{$fileName}"><br>
        <input type="text" name="value">
        <input type="submit" name="post">
    </form>
FORM;

        }
    }
    
    public function preprocess() {
        $this -> image -> cropImage(95, 30, 10, 15);

        $this -> image ->segmentImage(12, 0, 0.1);
        $this -> image->transparentPaintImage('white', 0, 10000 , false);
        $this -> image -> levelImage(0, 0, 65536);
        //$image -> setColorspace(\Imagick::COLOR_BLACK);
        return $this;
    }
    
    public function __construct()
    {
        if(isset($_GET['action'])) {
            $action = $_GET['action'];
            
            if(method_exists($this, $action)) {
                $args = new ReflectionMethod($this, $action);
                $params = [];
                //var_dump($args-> getParameters()[0] -> getDefaultValue());die();
                
                foreach($args -> getParameters() as $arg) {
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
                die('Нет такого метода');
            }
        }
    }
}

header('Content-type: text/html; charset=utf-8');
$app = new Anticap();