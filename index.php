<?php
require_once('http.php');
require_once('ann.php');
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
    /**
     * @var Ann
     */
    private $ann;
    
    private function getAnnInstance() {
        if(!$this -> ann) {
            $this -> ann = new Ann();
        }
        return $this -> ann;
    }
    
    private function loadImage($fileName) {
        //$this -> image = new Imagick('collect/'.$fileName);
        $this -> image = new Imagick($fileName);
        return $this;
    }
    
    public function draw() {
        echo '<hr><img src="data:image/png;base64,'. base64_encode($this -> image -> getImageBlob()) .'">';
    }
    
    public function getTestSet($count) {
        for($i = 0; $i < $count; $i++) {
            $captcha = file_get_contents('http://check.gibdd.ru/proxy/captcha.jpg');
            file_put_contents('test.png', $captcha);

            $this -> loadImage('test.png');
            $result = $this -> preprocess() -> map(null, false, false);
            //var_dump($result);
            rename('test.png', 'test/'.$result.'.png');
            //$this -> image -> writeImage('test/'.$result.'.png');
        }
    }
    
    public function map($fileName = null, $write = true, $visualize = true) {
        $this -> getAnnInstance() -> load('ann.net');
        
        if(!is_null($fileName))
        $this -> loadImage($fileName) -> preprocess();
        
        $draw = new ImagickDraw();
        $segments = [0, 0, 0, 0, 0];
        $offset = 0;
        $b = 0;
        $count = 0;
        
        $result = '';
        
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
                //$result .= $this -> test($segment);
                
                //$result .= $this -> getAnnInstance() -> test($this -> getArrayOfPixels($segment))[0];
                $result .= array_keys($this -> getAnnInstance() -> test($this -> getArrayOfPixels($segment)))[0];
                
                if($write) {
                    $segment -> writeImage('big/'.rand(0, 9999).'.png');
                }
                
                if($visualize) {
                    echo '<br>';
                    echo '<img src="data:image/png;base64,'. base64_encode($segment -> getImageBlob()) .'">';
                }
                $offset += 20;
            } else {
                $segment = clone $this -> image;
                $segment -> cropImage(17, 60, $offset + 6 , 0);
                //$segment -> resizeImage(40, 40, 1, 0, true);
                //$segment -> cropImage(40, 60, 18 , 0);
                
                $points = [
                    0, 0, -6, 0,
                    90, 0, 80, 0,
                    90, 30, 90, 30,
                    0, 30, 0, 30,
                ];

                
                //$segment -> distortImage(Imagick::DISTORTION_BILINEAR, $points, false);
                $segment -> distortImage(Imagick::DISTORTION_PERSPECTIVE, $points, false);
                //$segment -> setFormat('gif');
                $segment -> setImageBackgroundColor('white');
                $segment -> setBackgroundColor('white');
                
                //$segment -> setImageExtent(20, 30);
                $segment -> resizeImage(22, 38, 2, 3, true);
                $segment -> extentImage(20, 30, -1, 8);
                //$segment -> levelImage(0, 0, 65535);
                $segment -> levelImage(0, 0, 50000);
                
                if($write) {
                    $segment->writeImage('small/' . rand(0, 9999) . '.png');
                }
                
                $result .= array_keys($this -> getAnnInstance() -> test($this -> getArrayOfPixels($segment)))[0];
                
                //$result .= $this -> test($segment);
                
                if($visualize) {
                    echo '<br>';
                    echo '<img src="data:image/png;base64,'. base64_encode($segment -> getImageBlob()) .'">';
                }
                
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
        if($visualize) {
            $this -> draw();
        }
        
        return $result;
    }
    
    public function getAccuracy() {
        $dir = scandir('test');
        array_shift($dir);
        array_shift($dir);

        $success = 0;
        $error = 0;
        $digits = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        
        foreach ($dir as $file) {
            //$this -> loadImage('test/'.$file);
            //$this -> draw();
            $expected = str_replace('.png', '', $file);
            $resp = $this -> map('test/'.$file, false, false);
            if($resp == $expected) {
                $success++;
            } else {
                for($i = 0; $i < 5; $i++) {
                    if($resp[$i] != $expected[$i]) {
                        $digits[$expected[$i]]++;
                    }
                }
                $error++;
            }
        }

        echo(json_encode($digits));echo '<hr>';

        var_dump($success);
        echo '<br>';
        var_dump($error);
    }
    
    public function slice($begin, $end) {
        for($i = $begin; $i < $end; $i++) {
            $this -> map('collect/'.$i.'.png', true, false);
        }
    }

    function getArrayOfPixels($image = null) {
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

    public function test($segment) {
        if(!$this -> ann) {
            $this -> ann = fann_create_from_file(dirname(__FILE__) . "/ann.net");
        }

        //$input = array_fill(0, 540, rand(0, 1));
        $input = $this -> getArrayOfPixels($segment);

        $calc_out = fann_run($this -> ann, $input);
        // var_dump($calc_out);
        $val = null;
        $max = null;
        foreach ($calc_out as $i => $out) {
            if($out > $max) {
                $max = $out;
                $val = $i;
            }
        }

        //echo strval($val);
        //fann_destroy($this -> ann);
        return strval($val);
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
            $dir = scandir('samples/big/'.$i);
            
            array_shift($dir);
            array_shift($dir);
            foreach ($dir as $file) {
                $this -> loadImage('samples/big/'.$i.'/'.$file);
                $dat = $this -> getArrayOfPixels($this -> image);

                $val = array_fill(0, 10, 0);
                $val[$i] = 1;
                $count++;
                $array[] = implode(' ', $dat);
                $array[] = implode(' ', $val);
            }
        
            $dir = scandir('samples/small/'.$i);
            array_shift($dir);
            array_shift($dir);
            
            foreach ($dir as $file) {
                $this -> loadImage('samples/small/'.$i.'/'.$file);
                $dat = $this -> getArrayOfPixels($this -> image);

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
       $this -> getAnnInstance() -> create([600, 100, 100, 100, 10]) -> train('train.dat', 'ann.net');
    }
    
    public function preprocess() {
        $this -> image -> cropImage(95, 30, 10, 15);

        $this -> image -> segmentImage(12, 0, 0.1);
        $this -> image -> transparentPaintImage('white', 0, 10000 , false);
        $this -> image -> levelImage(0, 0, 65536);
        //$image -> setColorspace(\Imagick::COLOR_BLACK);
        return $this;
    }
    
    public function exception(Exception $e) {
        die($e -> getMessage());
    }
    
    public function __construct()
    {
        set_exception_handler([$this, 'exception']);
        
        if(isset($_GET['action'])) {
            $action = $_GET['action'];
            
            if(method_exists($this, $action)) {
                $method = new ReflectionMethod($this, $action);
                if(!$method -> isPublic()) {
                    throw new Exception('Данный метод не является действием');
                }
                //$args = new ReflectionMethod($this, $action);
                $params = [];
                //var_dump($args-> getParameters()[0] -> getDefaultValue());die();
                
                foreach($method -> getParameters() as $arg) {
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
                throw new Exception('Нет такого действия');
            }
        }
    }
}

header('Content-type: text/html; charset=utf-8');
$app = new Anticap();