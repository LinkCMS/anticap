<?php

class Ann {
    private $instance;
    private $maxEpochs;
    private $epochsBetweenReports = 1000;
    private $desiredError = 0.001;
    
    private function __counstruct($file = '', $layers) {
        if(isset($file) && file_exists($file)) {
            $this -> instance = fann_create_from_file($file);
        } else {
            $this -> instance = fann_create_standard_array(count($layers), $layers);

            fann_set_activation_function_hidden($this -> instance, FANN_SIGMOID_SYMMETRIC);
            fann_set_activation_function_output($this -> instance, FANN_SIGMOID_SYMMETRIC);
            fann_set_training_algorithm($this -> instance, FANN_TRAIN_RPROP);
            fann_set_train_stop_function($this -> instance, FANN_STOPFUNC_MSE);
        }
    }
    
    private function train($trainFile, $netConfigFile) {
        if(!fann_train_on_file($this -> instance, $trainFile, $this -> max_epochs, $this -> epochs_between_reports, $this -> desired_error)) {
            throw new Exception('Error on training!');
        } else {
            fann_save($this -> instance, $netConfigFile);
        }
    }
    
    private function test($inputs) {
        $out = fann_run($this -> instance, $inputs);
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
    
    private function validate() {
        
    }
}
