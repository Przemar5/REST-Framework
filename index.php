<?php


declare(strict_types = 1);
define('ROOT', __DIR__);

require_once 'config/config.php';


// If there is no request, end script execution
if (!$uri = $_SERVER['PATH_INFO'])
	die;

// URI parts
$uri = array_slice(explode('/', $uri), 1);

// requested parts
$requestedParts = ['table', 'index'];

// Indexes of functions to call
$indexes = [];

// Get request method
switch ($_SERVER['REQUEST_METHOD']) {
		
	case 'GET': 	$method = $_GET; 		break;
	case 'POST': 	$method = $_POST; 		break;
	case 'PUT': 	$method = $_PUT; 		break;
	case 'DELETE': 	$method = $_DELETE; 	break;
	default: 		$method = $_POST;		break;
}

// Get indexes of requested parts
switch (count($uri)) {
		
	case 1:
		$indexes = [0];
		break;

	case 2:
		$indexes = [0, 1];
		break;
}

// Get config file
//echo '<pre>';
$config = file_get_contents(ROOT . DS . 'config' . DS . 'config.json');
$data = json_decode($config, false);
//var_dump($data);die;

// Extract regexes
$regexes = $data->regex;

// Apply regexes to requested values and end script if any doesn't match
foreach ($indexes as $index) {
	
	$propertyPattern = $regexes->{ $requestedParts[$index] };

	if (!preg_match($propertyPattern, $uri[$index]))
		die;
}

// Extract specific data
$dbType		= 	$method['dbtype'] 		?? 	DB_TYPE;
$dbHost		= 	$method['dbhost'] 		?? 	DB_HOST;
$dbName		= 	$method['dbname'] 		?? 	DB_NAME;
$dbCharset	= 	$method['dbcharset'] 	?? 	DB_CHARSET;
$dbUser 	= 	$method['dbuser'] 		?? 	DB_USERNAME;
$dbPassword = 	$method['dbpassword'] 	?? 	DB_PASSWORD;

// Query parts
$values		= 	$method['values'];
$where		= 	$method['where'];
$limit		= 	$method['limit'];

// Process 'values' request part
if ($values !== null) {
	
	$valuesPattern = $regexes->values;

	foreach ($values as $value) {
		
		if (!preg_match($valuesPattern, $value)) {
			
			die('values');
		}
	}
}

// Process 'where' request part
if ($where !== null) {
	
	$whereParamPattern = $regexes->where->key;
	$whereValuePattern = $regexes->where->value;
	
	foreach ($where as $key => $value) {
		
		if (!preg_match($whereParamPattern, $key) ||
			!preg_match($whereValuePattern, $value)) {
			
			die('where');
		}
	}
}

// Process 'limit' request part
if ($limit !== null) {
	
	if (!preg_match('/^[0-9]+$/', $limit)) {
		
		die;
	}
}




// Establish connection
$dsn = $dbType . 
	':host=' . $dbHost . 
	';dbname=' . $dbName . 
	';charset=' . $dbCharset;

try {
	$db = new PDO($dsn, $dbUser, $dbPassword);
}
catch (\PDOException $e)
{
	die($e->getMessage());
}



// Build query
switch ($method) {
	case 'GET': 	select();	break;
	case 'POST': 	insert();	break;
	case 'PUT': 	update();	break;
	case 'DELETE': 	delete();	break;
	default: 		select();	break;
}


function select() {
	// Values to bind later
	$bind = [];

	// Query parts
	$valuesPart = '*';
	$conditionsPart = '';
	$limitPart = '';


	// Prepare 'values' query part
	if ($values !== null && count($values)) {
		$valuesPart = implode(', ', $values);
	}

	// Prepare 'where' query part
	if ($where !== null) {
		$conditionsPart = ' WHERE ';

		if ($uri[1] !== null) {
			$conditionsPart = 'id = :id';
			$bind[":id"] = $uri[1];
		}

		foreach ($where as $key => $value) {
			$conditionsPart .= $key . ' = :' . $key . ' AND ';
			$bind[":$key"] = $value;
		}

		$conditionsPart = preg_replace('/ AND $/', '', $conditionsPart);
	}

	// Prepare 'limit' query part
	if ($limit !== null) {
		$limitPart = ' LIMIT ' . $limit;
	}


	// Finalize query


	$query = $action . $valuesPart . ' FROM ' . $uri[0] . $conditionsPart . $limitPart;
	$stmt = $db->prepare($query);
	$stmt->bindParam(':id', $uri[1]);

	foreach ($bind as $key => $value) {
		$stmt->bindParam($key, $value);
	}
	$stmt->execute();

	ob_clean();
	// Get results
	$result = $stmt->fetch(PDO::FETCH_ASSOC);
	if ($result)
		echo json_encode($result);

}


