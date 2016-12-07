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
    $image->transparentPaintImage('white', 0, 0.09 * \Imagick::getQuantum(), false);
    /*
    $image -> modulateImage(100, 0, 100);
    $image ->segmentImage(12, 0, 1.6);
    $image -> whiteThresholdImage('#f4fcff');
    $image -> blackThresholdImage('#f4fcff');
    $image ->gaussianBlurImage(2, 2);
    $image -> levelImage(0, 0, 60000);
    */
    //$image -> blackThresholdImage('#f4fcff');

        $image -> modulateImage(100, 0, 100);
        //$image ->gaussianBlurImage(2, 2);
        $image -> levelImage(0, 0, 65535);

        //$image2 = clone($image);
        //$image2 ->gaussianBlurImage(1, 2);
        //$image2 -> segmentImage('white', 0, 0.01, false);

//        $image2 -> ;
        //$image -> getPixelRegionIterator(10, 0, 0, 30);
        $iterator = $image->getPixelIterator();

        foreach($iterator as $row => $pixels) { // По строкам
            if($row < 1) { // 9 - маленькие
                continue;
            }
            foreach($pixels as $col => $pixel) { // По столбцам
                /*
                if($col % 5 != 0) {
                    continue;
                }
                */
                $color = $pixel->getColor();      // values are 0-255
                //$alpha = $pixel->getColor(true);  // values are 0.0-1.0
                // manipulate r, g, b and a as necessary
                //
                // you could also read arbitrary pixels from
                // another image with similar dimensions like so:
                // $otherimg_pixel = $other_img->getImagePixelColor($col,$row);
                // $other_color = $otherimg_pixel->getColor();
                //
                // then write them back into the iterator
                // and sync it

                //echo (intval($color['r']) + intval($color['g']) + intval($color['b']));
                //echo $color['b'];

                //if(($color['r'] + $color['g'] + $color['b']) != 0) {
                if($color['a'] != 0) {
                    $pixel->setColor("rgba(255,0,0,1)");
                    //break;
                }

            }

            $iterator->syncIterator();
            break;
        }

    echo '<div style="padding: 10px; background: #ccc"><img style="width: 90px; height: 30px;" src="data:image/png;base64,'.base64_encode($image).'"></div>';
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
}