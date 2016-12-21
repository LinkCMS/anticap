<?php

define('PATH', __DIR__);

return $config = [
    //'path' => __DIR__,
    'bootstrap' => '',
    'db' => [
        'host' => 'localhost',
        'username' => 'root',
        'password' => 'root',
        'database' => 'gb',
    ],
    'fann' => [
        'inputs' => 260,
        'net' => 'setup.net'
    ]
];