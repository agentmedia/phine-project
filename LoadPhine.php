<?php
define('PHINE_PATH', __DIR__);
//TODO: use vendor_dir from composer
$autoloadFile = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadFile)) {
	require_once $autoloadFile;
} else {
    throw new Exception('composer autoload not found');
}