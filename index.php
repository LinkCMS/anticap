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
        $iterator = $image -> getPixelRegionIterator($j + (17 * $j) + 1, 2, 10, 0);
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

function segment($image, $visualize = false) {
    $segments = [0, 0, 0, 0, 0];
    for($j = 0; $j < 5; $j++) {
        //$iterator = $image -> getPixelRegionIterator($j + (17 * $j) + 2, 2, 14, 0);
        $iterator = $image -> getPixelRegionIterator($j + (17 * $j) + 1, 2, 10, 0);
        foreach ($iterator -> current() as $pixel) {
            if($pixel -> getColor()['a']) {
                $color = 'green';
                $segments[$j] = 1;
                break;
            } else {
                $color = 'red';
            }
            
            if($visualize) {
                $pixel -> setColor($color);
            }
        }
        if(@$segments[$j]) {
            continue;
        }
        $iterator -> syncIterator();
    }
    
    return @$segments;
}

function getArrayOfPixels($segment) {
    
}

function neuro() {
    set_time_limit(0);
    $max_epochs = 500; // 500000
    $epochs_between_reports = 10; // 1000
    $desired_error = 0.001;
    //var_dump(array_fill(0, 10000, 540));
    $ann = fann_create_standard_array(10, array_fill(0, 10, 100));
    fann_set_activation_function_hidden($ann, FANN_SIGMOID_SYMMETRIC);
    fann_set_activation_function_output($ann, FANN_SIGMOID_SYMMETRIC);
    
    //$segment = getArrayOfPixels();
    
    //if (fann_train_on_data($ann, $segment, $max_epochs, $epochs_between_reports, $desired_error))
    if (fann_train_on_file($ann, dirname(__FILE__) . "/4.png", $max_epochs, $epochs_between_reports, $desired_error)) {
        fann_save($ann, dirname(__FILE__) . "/data.net");
    }
    /*
    $filename = dirname(__FILE__) . "/xor.data";
    if (fann_train_on_file($ann, $filename, $max_epochs, $epochs_between_reports, $desired_error))
        fann_save($ann, dirname(__FILE__) . "/xor_float.net");

    fann_destroy($ann);
    */
}


function slice($image, $segments) {
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
            
            //$letter -> cropImage(30, 60, $offset, 0);
            $letter -> cropImage(20, 60, $offset + 10, 0);
            $offset += 19;
            echo ' <img src="data:image/png;base64,'.base64_encode($letter).'">&nbsp;&nbsp;';
        } else {
            $letter -> cropImage(14, 60, $offset + 9, 0);
            $offset += 15;
//            $offset += 3;
            echo ' <img src="data:image/png;base64,'.base64_encode($letter).'">&nbsp;&nbsp;';
        }
    }
    
    echo '</div>';
}

if(@$_GET['action'] == 'collect') {
    for($i = 0; $i < 200; $i++) {
        file_put_contents("collect/{$i}.png", $http -> get('http://check.gibdd.ru/proxy/captcha.jpg') -> body);
    }
} else if(@$_GET['action'] == 'image') {
    //Header('Content-type: image/png');
    //$image = imagecreatefrompng('collect/1.png');
    for($i = 0; $i < 10; $i++) {
    $image = new Imagick('collect/'.$i.'.png');

    $image -> cropImage(90, 30, 10, 15);

    $image ->segmentImage(12, 0, 0.1);
    $image->transparentPaintImage('white', 0, 10000 , false);
    //$image -> modulateImage(100, 0, 100);
    $image -> levelImage(0, 0, 65536);
    $image -> setColorspace(\Imagick::COLOR_BLACK);
    //$iterator = $image -> getPixelRegionIterator()
        
    //var_dump(segment($image));
    slice($image, segment($image));
    //map($image);
    //echo '<div style="padding: 10px; background: #ccc"><img style="width: 90px; height: 30px;" src="data:image/png;base64,'.base64_encode($image).'"></div>';
    }
} else if(@$_GET['action'] == 'captcha') {
	Header("Content-type: image/png");
	$captcha = $http -> get('http://check.gibdd.ru/proxy/captcha.jpg?'.$timestamp);
	preg_match('/JSESSIONID=(\w{32})/', $captcha -> headers, $matches);
	echo $captcha -> body;
} else if($_GET['action'] == 'dtp') {

    header('Content-Type: application/json; charset=utf-8');
	//var_dump($http -> post('http://check.gibdd.ru/proxy/check/auto/dtp', 'vin=GX90-3079813&captchaWord=49560&checkType=aiusdtp'))
	echo($http -> post('http://check.gibdd.ru/proxy/check/auto/dtp', 'vin=GX90-3079813&captchaWord='.$_GET['captcha'].'&checkType=aiusdtp') -> body);
} else if(@$_GET['action'] == 'fann') {
    neuro();
}