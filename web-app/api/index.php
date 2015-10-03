<?php
	error_reporting(E_ALL);
	ini_set('display_errors',1);
	
	require 'Routing.php';
	
	set_error_handler(function ($errorCode, $message, $file, $lineNumber, $localVariables) {
		die('Invalid request (error): '.$message.' in '.$file.' at line '.$lineNumber);
	});

	$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
	$path = substr($path, 5);
	$path = explode('/', $path);
	
	$method = $_SERVER['REQUEST_METHOD'];
	
	$requestBody = file_get_contents('php://input');
	$args = json_decode($requestBody, true);
	
	
	
	$email = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : null;
	$password = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : null;
	
	$routing = new Routing($email, $password);
	$response = $routing->route($method, $path, $args);
	
	header('Content-Type: application/json');
	echo json_encode($response);
	die();
?>