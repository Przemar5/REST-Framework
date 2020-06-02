<?php


declare(strict_types = 1);
define('ROOT', __DIR__);

require_once 'config/config.php';


// If there is no request, end script execution
if (!$uri = $_SERVER['PATH_INFO'])
	die(__LINE__);

// URI parts
$uri = array_slice(explode('/', $uri), 1);
$page = array_shift($uri);

if ($page === 'request') {
	require_once 'requester.php';
	die;
}
else if ($page !== 'api') {
	die;
}


// Get request method
switch ($_SERVER['REQUEST_METHOD']) {
	case 'GET': 	$method = $_GET; 	$mode = 'select';	break;
	case 'POST': 	$method = $_POST; 	$mode = 'insert';	break;
	case 'PUT': 	$method = $_PUT; 	$mode = 'update';	break;
	case 'DELETE': 	$method = $_DELETE; $mode = 'delete';	break;
	default: 		$method = $_GET;	$mode = 'select';	break;
}

// Get indexes of requested parts
switch (count($uri)) {
		
	case 1:
		$method['table'] = $uri[0];
		break;

	case 2:
		$method['table'] = $uri[0];
		$method['index'] = $uri[1];
		break;
}

// Get config file
echo '<pre>';
$config = file_get_contents(ROOT . DS . 'config' . DS . 'config.json');
$data = json_decode($config, true);
//var_dump($data);die;

// Get needed value names and their specific attributes
$additionalData = array_merge($data['query']['default'], $data['query'][ $mode ]);


function checkParam($param, $regex): bool
{
	if (!is_array($param)) {
		return preg_match($regex, $param);
	}
	else {
		foreach ($param as $key => $value) {
			if (!preg_match($regex['key'], $key) || 
				!preg_match($regex['value'], $value)) {
					return false;
				}
		}
		return true;
	}
}


foreach ($additionalData as $key => $params) {
	$valuesToCheck[ $key ] = [
		$params['required']	??	false,
		$params['default']
	];
}


foreach ($valuesToCheck as $param => [$required, $default]) {
	
	if ($required && ($method[ $param ] === null || 
					 !checkParam($method[ $param ], $additionalData[ $param ]['regex']))) {
		die(__LINE__);
	}
	else if (!$method[ $param ] === null && 
			 !checkParam($method[ $param ], $additionalData[ $param ]['regex'])) {
		die(__LINE__);
	}
	
	${ $param } = $method[ $param ] ?? $default;
}


// Establish connection
$dsn = $dbtype . 
	':host=' . $dbhost . 
	';dbname=' . $dbname . 
	';charset=' . $dbcharset;

try {
	$db = new PDO($dsn, $dbuser, $dbpassword);
}
catch (\PDOException $e)
{
	die($e->getMessage());
}

// Build query
require_once 'libs/db/' . $mode . '.php';
