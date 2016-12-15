<?php
require_once('config.php');
require_once('framework/app.php');

$app = new App();
$app -> run($config);