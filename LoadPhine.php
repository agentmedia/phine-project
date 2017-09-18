<?php
define('PHINE_PATH', __DIR__);
$autoloadFile = __DIR__ . '/vendor/autoload.php';
if file_exists($autoloadFile) {
	require_once $autoloadFile;
}