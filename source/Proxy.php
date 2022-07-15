<?php

session_start();

if (! defined('MODE')) {
	die ('Sorry, you have visited an empty page!');
} else {
	define('CONFIG', 'config.json');
}

require_once('../vendor/autoload.php');
include_once('App.php');

try {
	$app = App\app::getInstance(CONFIG, MODE);
	$app->run();
} catch (\Exception $error) {
	$return = array(
		'success' => false,
		'errMsg'  => $error->getMessage()
	);
	echo json_encode($return);
	return;
}

?>