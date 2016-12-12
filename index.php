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

function map($image) {
    for($j = 0; $j < 5; $j++) {
        //$iterator = $image -> getPixelRegionIterator($j + (17 * $j) + 2, 2, 15, 0);
        $iterator = $image -> getPixelRegionIterator($j + (17 * $j) + 3, 5, 10, 0);
        foreach ($iterator -> current() as $pixel) {
            if($pixel -> getColor()['a']) {
                $color = 'green';
            } else {
                $color = 'red';
            }
            $pixel -> setColor($color);
        }

        $iterator -> syncIterator();
    }
}

function getSymbol($image, $number) {
    slice($image, segment($image));
}

function segment(Imagick $image, $visualize = false) {
    $segments = [0, 0, 0, 0, 0];
    for($j = 0; $j < 5; $j++) {
        $iterator = $image -> getPixelRegionIterator($j + (17 * $j) + 2, 5, 10, 0);
        foreach ($iterator -> current() as $pixel) {
            if($pixel -> getColor()['a']) {
                $color = 'green';
                $segments[$j] = 1;
                $pixel -> setColor($color);
                break;
            } else {
                $color = '';
            }
            
            if($visualize) {
                $pixel -> setColor($color);
            }
        }
        
        $iterator -> syncIterator();
    }
    
    return @$segments;
}

function getArrayOfPixels($file, $segment = null) {
    $image = new Imagick($file);

    $iterator = $image -> getPixelIterator();
    $arr = [];
    
    foreach ($iterator as $row => $pixels) {
        foreach ($pixels as $col => $pixel) {
            $arr[] = $pixel -> getColor()['a'];
        }
    }
    $iterator -> syncIterator();
    
    return $arr;
}

function train() {
    set_time_limit(0);
    ini_set('memory_limit','2048M');
    $max_epochs = 10; //50000
    $epochs_between_reports = 1000;
    $desired_error = 0.001;
    //var_dump(array_fill(0, 10000, 540));
    $ann = fann_create_standard(3, 600, 200, 10);
    fann_set_activation_function_hidden($ann, FANN_SIGMOID_SYMMETRIC);
    fann_set_activation_function_output($ann, FANN_SIGMOID_SYMMETRIC);
    
    //$segment = getArrayOfPixels();
    
    //if (fann_train_on_data($ann, $segment, $max_epochs, $epochs_between_reports, $desired_error))
    //if (fann_train_on_file($ann, dirname(__FILE__) . "/4.png", $max_epochs, $epochs_between_reports, $desired_error)) {
    //if (fann_train($ann, getArrayOfPixels(), $max_epochs, $epochs_between_reports, $desired_error)) {
    //if (fann_train($ann, getArrayOfPixels(), $desired_error)) {
    
    for($i = 0; $i < 1; $i++) {
        if (fann_train($ann, getArrayOfPixels('4/'.($i+1).'.png'), [-1, -1, -1, -1, 1, -1, -1, -1, -1, -1])) {
            fann_save($ann, dirname(__FILE__) . "/data.net");
        }
    }
    
    /*
    $filename = dirname(__FILE__) . "/xor.data";
    if (fann_train_on_file($ann, $filename, $max_epochs, $epochs_between_reports, $desired_error))
        fann_save($ann, dirname(__FILE__) . "/xor_float.net");

    fann_destroy($ann);
    */
}

function test() {
    
    $ann = fann_create_from_file(dirname(__FILE__) . "/data.net");
    
    //$input = array_fill(0, 540, rand(0, 1));
    $input = getArrayOfPixels('6.png');
    $calc_out = fann_run($ann, $input);
    var_dump($calc_out);
    fann_destroy($ann);
}

function prepocessImage($image) {
    $image -> cropImage(90, 30, 10, 15);

    $image ->segmentImage(12, 0, 0.1);
    $image->transparentPaintImage('white', 0, 10000 , false);
    $image -> levelImage(0, 0, 65536);
    //$image -> setColorspace(\Imagick::COLOR_BLACK);
    return $image;
}

function slice(Imagick $image, $segments) {
    echo '<div style="padding: 10px; background: #ccc">';
    echo '<img src="data:image/png;base64,'.base64_encode($image).'">_________';
    /*
    for($j = 0; $j < 5; $j++) {
        if(@$segments[$j]) { // Большая буква
            //$letter = $image -> cropImage(30, 30, 0, 0);
            $letter = clone($image);
            $letter -> cropImage(30, 60, 0, 0);
            echo '<img src="data:image/png;base64,'.base64_encode($letter).'">|';
        } else {
            
        }
    }
    */
    $offset = 0;
    foreach ($segments as $i => $segment) {
        $letter = clone($image);
        if(@$segments[$i]) { // Большая буква
            //$letter = $image -> cropImage(30, 30, 0, 0);

            $letter -> extentImage(20, 30, $offset, 0);
            //$letter -> cropImage(30, 60, $offset, 0);
            //$letter -> cropImage(19, 60, $offset + 12, 0);
            $offset += 19;
            echo ' <img src="data:image/png;base64,'.base64_encode($letter).'">&nbsp;&nbsp;';
        } else {
            //$letter -> rotateImage('transparent', -2);

            $points = [
                0, 0, -5, 0,
                90, 0, 80, 0,
                90, 30, 90, 30,
                0, 30, 0, 30,
            ];
            
            /*
            $letter -> distortImage(Imagick::DISTORTION_BILINEAR, $points, false);
            $letter -> cropImage(15, 30, $offset - 4 , 0);
            $letter -> resizeImage(20, 41, 1, 0, false);
            $letter -> extentImage(20, 30, 0, 10);
            */
            //$letter -> cropImage(15, 30, $offset + 10 , 15);
            //$letter -> cropImage(20, 30, 0, 10);
            //$letter -> levelImage(10, 10, 10);

            //$letter -> cropImage(15, 60, $offset + 9, 0);
            /*
            //$letter -> setImagePage(20, 30, 0, 0);
            $letter -> resizeImage(23, 60, 0, 0, true);
            $letter -> setBackgroundColor('transparent');
            //$letter -> setImageColorspace(Imagick::COLOR_BLACK);
            //$letter -> setColorspace(Imagick::COLOR_BLACK);
            
            $letter -> thumbnailImage(20, 30, true);
            //$letter -> resampleImage(20, 30, 0, 0);
            //$letter -> resampleImage(110, 100, 0, 0);
            
            //$letter -> cropImage(20, 30, 20, 40);
            */
            $offset += 15;
//            $offset += 3;
            //echo ' <img src="data:image/png;base64,'.base64_encode($letter).'">&nbsp;&nbsp;';
        }
    }

    echo '</div>';
}











class Anticap {
    /**
     * @var Imagick 
     */
    private $image;
    
    private function loadImage($fileName) {
        $this -> image = new Imagick('collect/'.$fileName);
        return $this;
    }
    
    public function image($fileName) {
        $this -> loadImage($fileName) -> preprocess() -> draw();
    }
    
    public function draw() {
        echo '<img src="data:image/png;base64,'. base64_encode($this -> image -> getImageBlob()) .'">';
    }
    
    public function map($fileName) {
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
                $segment -> cropImage(19, 60, $offset + 10 , 0);
                echo '<br>';
                echo '<img src="data:image/png;base64,'. base64_encode($segment -> getImageBlob()) .'">';
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
                    echo '<br>';
                    echo '<img src="data:image/png;base64,'. base64_encode($segment -> getImageBlob()) .'">';
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
        
        echo json_encode($segments);
        
        $this -> image -> drawImage($draw);
        
        $this -> draw();
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
                
                foreach($args -> getParameters() as $arg) {
                    $params[] = $_GET[strtolower($arg -> name)];
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

die();


if(isset($_GET['action'])) {
    $action = $_GET['action'];
    $params = $_GET;
    unset($params['action']);
    /*
    var_dump(function_exists($_GET['action']));
    if(function_exists($_GET['action'])) {
     
        call_user_func($_GET['action']);
    }
    */
}


die();

if(@$_GET['action'] == 'collect') {
    for($i = 0; $i < 200; $i++) {
        file_put_contents("collect/{$i}.png", $http -> get('http://check.gibdd.ru/proxy/captcha.jpg') -> body);
    }
} else if(@$_GET['action'] == 'image') {
    //Header('Content-type: image/png');
    //$image = imagecreatefrompng('collect/1.png');
    
    
    
    
    for($i = 0; $i < 100; $i++) {
    $image = new Imagick('collect/'.$i.'.png');

        prepocessImage($image);
        /*
    $image -> cropImage(90, 30, 10, 15);

    $image ->segmentImage(12, 0, 0.1);
    $image->transparentPaintImage('white', 0, 10000 , false);
    //$image -> modulateImage(100, 0, 100);
    $image -> levelImage(0, 0, 65536);
    //$image -> setColorspace(\Imagick::COLOR_BLACK);
    $image -> setColorspace(\Imagick::COLORSPACE_GRAY);
        */
        
        
    //$image -> setImageColorspace(\Imagick::COLOR_BLACK);
    //$iterator = $image -> getPixelRegionIterator()
        
    //var_dump(segment($image));
    //slice($image, segment($image ,1));
        map($image);
        
    //map($image);
    echo '<div style="padding: 10px; background: #ccc"><img style="width: 90px; height: 30px;" src="data:image/png;base64,'.base64_encode($image).'"></div>';
    }
} else if(@$_GET['action'] == 'captcha') {
	header("Content-type: image/png");
	$captcha = $http -> get('http://check.gibdd.ru/proxy/captcha.jpg?'.$timestamp);
	preg_match('/JSESSIONID=(\w{32})/', $captcha -> headers, $matches);
	echo $captcha -> body;
} else if($_GET['action'] == 'dtp') {

    header('Content-Type: application/json; charset=utf-8');
	//var_dump($http -> post('http://check.gibdd.ru/proxy/check/auto/dtp', 'vin=GX90-3079813&captchaWord=49560&checkType=aiusdtp'))
	echo($http -> post('http://check.gibdd.ru/proxy/check/auto/dtp', 'vin=GX90-3079813&captchaWord='.$_GET['captcha'].'&checkType=aiusdtp') -> body);
} else if(@$_GET['action'] == 'train') {
    train();
} else if(@$_GET['action'] == 'test') {
    test();
} else if(@$_GET['action'] == 'symbol') {
    $image = new Imagick('collect/'.$_GET['image'].'.png');
    getSymbol(prepocessImage($image), $_GET['number']);
}

