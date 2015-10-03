<?php
	error_reporting(E_ALL);
	ini_set('display_errors',1);
	
	require 'Routing.php';
	
	/*set_error_handler(function ($errorCode, $message, $file, $lineNumber, $localVariables) {
		die('Invalid request (error)');
	});*/

	$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
	$path = substr($path, 5);
	$path = explode('/', $path);
	
	$method = $_SERVER['REQUEST_METHOD'];
	
	$requestBody = file_get_contents('php://input');
	$args = json_decode($requestBody, true);
	if (isset($args['session_id'])) {
		$sessionId = $args['session_id'];
		unset($args['session_id']);
	} else {
		$sessionId = null;
	}
	
	
	
	// - START DEBUG OVERRIDE
	/*$sessionId = '067fa28b87b5de65df53affabbe52001b3a3b98a';
	$method = 'POST';
	$args['id'] = 1;
	$args['type'] = 'Boats';*/
	// - END DEBUG OVERRIDE
	
	$routing = new Routing($sessionId);
	$response = $routing->route($method, $path, $args);
	
	header('Content-Type: application/json');
	echo json_encode($response);
	die();
?>