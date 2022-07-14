<?php

session_start();

if (! defined('MODE')) {
	die ('Sorry, You have visited an empty page!');
} else {
	define('CONFIG', 'config.json');
}

include_once('App.php');
require_once('../vendor/autoload.php');

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