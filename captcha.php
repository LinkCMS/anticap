<?php

class Captcha {
    private $image;
    
    public function __construct($imageBlob) {
        $this -> image = new Imagick();
        $this -> image -> readImageBlob($imageBlob);
    }
    
    public function draw() {
        echo '<img src="data:image/png;base64,'. base64_encode($this -> image -> getImageBlob()) .'">';
    }
    
    public function visualize() {
        $this -> image -> transparentPaintImage('white', 0, 10000 , false);
        //$this -> image -> setImageOpacity(1);
        $this -> image -> evaluateImage(Imagick::EVALUATE_MULTIPLY, 0.006, Imagick::CHANNEL_ALPHA);
        echo '<img style="position: absolute" src="data:image/png;base64,'. base64_encode($this -> image -> getImageBlob()) .'">';
    }

    public function preprocess() {
        $this -> image -> cropImage(95, 30, 10, 15);
        
        $mask = clone $this -> image;
        $mask -> transparentPaintImage('black', 0, 20000, true);
        $mask -> setImageAlphaChannel(5);

        $this -> image -> segmentImage(12, 0, 0.1);
        $this -> image -> transparentPaintImage('white', 0, 10000 , false);
        
        $blur = clone $this -> image;
        $blur -> setImageAlphaChannel(6);
        $blur -> blurImage(1, 8);
        
        $mask -> compositeImage($blur, 38, 0 ,0); // 10
        $mask -> levelImage(0, 0 , 58000);
         
        $this -> image -> compositeImage($mask, 25, 0 ,0); //12
        $this -> image -> transparentPaintImage('white', 0, 10000 , false);
        $this -> image -> transparentPaintImage('black', 0, 10000 , false);
        $this -> image -> levelImage(0, 0, 65536);
        //$this -> image -> levelImage(100, 0, 0);

        /*
        echo '<div style="background: #ccc;">';
        echo '<img src="data:image/png;base64,'.base64_encode($mask -> getImageBlob()).'">';
        echo '<img src="data:image/png;base64,'.base64_encode($blur -> getImageBlob()).'">';
        echo '<img src="data:image/png;base64,'.base64_encode($this -> image -> getImageBlob()).'">';
        echo '</div>';
        die();
        */
        //$image -> setColorspace(\Imagick::COLOR_BLACK);
        return $this;
    }
    
    public function cropSmallSymbol($segment) {
        $segment = new Segment($segment);
        $mask = new ImagickDraw();
        //$mask -> setFillColor('white');
        //$mask -> rectangle(0, 0, 90, 30);
        $mask -> setFillColor('black');
        //$mask -> rectangle(5, 10, 15, 30);
        $num = 1;
        $offset = 16;
        
        $mask -> polygon([
            ['x' => 2 + $num * $offset, 'y' => 8],
            ['x' => 17 + $num * $offset, 'y' => 8],
            ['x' => 12 + $num * $offset, 'y' => 30],
            ['x' => -2 + $num * $offset, 'y' => 30]
        ]);
        
        $m = new Imagick();
        $m -> setFormat('PNG');
        $m -> newImage(90, 30, 'white');
        $m -> drawImage($mask);
        
        //$segment -> image -> drawImage($mask);
        //$segment -> image -> setImageClipMask($m);
        $segment -> image -> compositeImage($m, 3, 0, 0);
        
        $segment -> draw();
        //echo '<img src="data:image/png;base64,'.base64_encode($segment -> draw()).'">';
        
        //$segment -> draw();
        die();
    }

    public function map($fileName = null) {
        //$this -> getAnnInstance() -> load('ann.net');

        if(!is_null($fileName))
            $this -> loadImage($fileName) -> preprocess();

        $draw = new ImagickDraw();
        $segments = [0, 0, 0, 0, 0];
        $offset = 0;
        $b = 0;
        $count = 0;
        $symbols = [];

        $currentIsBig = false;
        
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
                $this -> cropSmallSymbol($segment);
                
                $segment -> cropImage(20, 60, $offset + 9 , 0);
                //$segment -> setImageExtent(20, 30);
                $segment -> extentImage(20, 30, 0, 0);
                //$segment -> resizeImage(20, 20, 2, 0, 1);

                $segment -> resizeImage(20, 20, 4, 1, true);
                $segment -> levelImage(0, 1, 65535);
                //$segment -> resizeImage(20, 20, 2, 3.5, 1);
                
                
                /*
                $segment -> resizeImage(20, 10, 4, 1, true);
                $segment -> levelImage(0, 1, 65535);
                */
                
                
                
                
                
                
                //$segment -> levelImage(40000, 0.4, 40000);
                //$segment -> levelImage(40000, 1000, 10000);
/*
                $segment -> blurImage(2, 10);
                $segment -> levelImage(0, 0, 50000);
*/
                //$result .= $this -> test($segment);

                //$result .= $this -> getAnnInstance() -> test($this -> getArrayOfPixels($segment))[0];
                //$result .= array_keys($this -> getAnnInstance() -> test($this -> getArrayOfPixels($segment)))[0];

                $symbols[] = new Segment($segment);

                $offset += 20;
            } else {
                $segment = clone $this -> image;
                $segment -> cropImage(17, 60, $offset + 6 , 0);
                //$segment -> resizeImage(40, 40, 1, 0, true);
                //$segment -> cropImage(40, 60, 18 , 0);
                
                $points = [
                    -1, 0, -6, 0,
                    90, 0, 80, 0,
                    90, 30, 90, 30,
                    0, 30, 0, 30,
                ];


                $segment -> distortImage(Imagick::DISTORTION_BILINEAR, $points, false);
                //$segment -> distortImage(Imagick::DISTORTION_PERSPECTIVE, $points, false);
                //$segment -> setFormat('gif');
                $segment -> setImageBackgroundColor('white');
                $segment -> setBackgroundColor('white');

                //$segment -> setImageExtent(20, 30);
                $segment -> resizeImage(22, 38, 8, 0.6, true);
                
                $segment -> extentImage(20, 30, -1, 8);
                $segment -> levelImage(60000, 1000, 65535);


                $segment -> resizeImage(20, 20, 4, 1, true);
                $segment -> levelImage(0, 0.8, 65535);
                /*
                $segment -> resizeImage(20, 10, 4, 1, true);
                $segment -> levelImage(0, 0.8, 65535);
                */
                
                
                //$segment -> levelImage(0, 0, 65535);
                //$segment -> levelImage(30000, 0, 50000);
//$segment -> resizeImage(20, 20, 1, 0, 1);
                $symbols[] = new Segment($segment);

                //$result .= array_keys($this -> getAnnInstance() -> test($this -> getArrayOfPixels($segment)))[0];

                //$result .= $this -> test($segment);


                $offset += 16;
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

        //$this -> image -> drawImage($draw);
        /*
        if($visualize) {
            $this -> draw();
        }
        */

        return $symbols;
    }
}

class Segment {
    public $image;
    
    public function __construct($segment) {
        $this -> image = new Imagick();
        $this -> image -> readImageBlob($segment);
    }
    
    public function draw() {
        echo '<img src="data:image/png;base64,'. base64_encode($this -> image -> getImageBlob()) .'">';
    }
    
    public function save($filename) {
        $this -> image -> writeImage($filename);
    }
    
    public function getArrayOfPixels() {
        $iterator = $this -> image -> getPixelIterator();
        $arr = [];

        foreach ($iterator as $row => $pixels) {
            foreach ($pixels as $col => $pixel) {
                $arr[] = $pixel -> getColor()['r'] > 1 ? 0 : 1;
            }
        }
        $iterator -> syncIterator();

        return $arr;
    }
}