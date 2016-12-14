<?php

class Ann {
    private $instance;
    private $maxEpochs = 50000;
    private $epochsBetweenReports = 1000;
    private $desiredError = 0.001;
    
    public function __counstruct($file = null, $layers = []) {
        /*
        if(isset($file) && file_exists($file)) {
            $this -> instance = fann_create_from_file($file);
        } else {
            $this -> instance = fann_create_standard_array(count($layers), $layers);

            fann_set_activation_function_hidden($this -> instance, FANN_SIGMOID_SYMMETRIC);
            fann_set_activation_function_output($this -> instance, FANN_SIGMOID_SYMMETRIC);
            fann_set_training_algorithm($this -> instance, FANN_TRAIN_RPROP);
            fann_set_train_stop_function($this -> instance, FANN_STOPFUNC_MSE);
        }
        
        return $this;
        */
    }

    public function load($fileName) {
        $this -> instance = fann_create_from_file($fileName);
        return $this;
    }
    
    public function create($layers) {
        $this -> instance = fann_create_standard_array(count($layers), $layers);

        fann_set_activation_function_hidden($this -> instance, FANN_SIGMOID_SYMMETRIC);
        fann_set_activation_function_output($this -> instance, FANN_SIGMOID_SYMMETRIC);
        fann_set_training_algorithm($this -> instance, FANN_TRAIN_RPROP);
        fann_set_train_stop_function($this -> instance, FANN_STOPFUNC_MSE);
        
        return $this;
    }
    
    public function train($trainFile, $netConfigFile) {
        if(!fann_train_on_file($this -> instance, $trainFile, $this -> maxEpochs, $this -> epochsBetweenReports, $this -> desiredError)) {
            throw new Exception('Error on training!');
        } else {
            fann_save($this -> instance, $netConfigFile);
            return true;
        }
    }
    
    public function test($inputs) {
        $out = fann_run($this -> instance, $inputs);
        arsort($out);
        return $out;
        /*
        var_dump($out);die();
        sort($out, SORT_DESC);
        return $out;
        /*
        $val = null;
        $max = null;
        
        foreach ($out as $i => $out) {
            if($out > $max) {
                $max = $out;
                $val = $i;
            }
        }
        */

        //echo strval($val);
        //fann_destroy($this -> ann);
        //return strval($val);
    }
    
    public function validate() {
        
    }
}
