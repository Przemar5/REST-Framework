<?php


declare(strict_types = 1);
define('ROOT', __DIR__);


//function reduce(array $args, $accum = true)
//{
//	if (count($args) === 0)
//		return $accum;
//	
//	else 
//		return reduce(array_slice($args, 1), {$args[0]}($accum))
//}
//
//function both($a, $b)
//{
//	return $a && $b;
//}

function trueOrThrow($value, string $msg)
{
	if ($value)
		return true;
	else
		throw new \Exception($msg);
}


function myMin(string $value, int $min)
{
	return strlen($value) >= $min;
}

function regex(string $value, string $pattern) 
{
	return preg_match($pattern, $value);
}

function first(string $part) 
{
	echo 1;
}

function second(string $part) 
{
	echo 2;
}

require_once 'config/config.php';
require_once 'libs/helpers.php';
//require_once 'functions.php';


$parts = ['table', 'value'];
$handlers = ['first', 'second'];
$uri = $_SERVER['PATH_INFO'];
$uri = array_slice(explode('/', $uri), 1);
$functions = [];
$indexes = [];


switch (count($uri)) {
	case 1:
		$indexes = [0];
		break;
		
	case 2:
		$indexes = [0, 1];
		break;
}

echo '<pre>';
$config = file_get_contents(ROOT . DS . 'config' . DS . 'config.json');
$data = json_decode($config);
//var_dump($data);
//die;

foreach ($indexes as $index) {
	$currentData = $data->logic->{ $parts[ $index ] };
	$validators = $currentData->validators;
	
	try {
		foreach ($validators as $validator => $args) {
			
			if (!call_user_func_array($validator, 
				array_merge([$uri[$index]], $args->args))) {
				
				throw new \Exception($args->msg);
			}
		}
	}
	catch (\Exception $e) {
		die($e->getMessage());
	}
}

switch ($_SERVER['REQUEST_METHOD']) {
	case 'GET': 
		$method = $_GET; 
		break;
	case 'POST': 
		$method = $_POST; 
		break;
	case 'PUT': 
		$method = $_PUT; 
		break;
	case 'DELETE': 
		$method = $_DELETE; 
		break;
}



$dbType		= $method['dbtype'] ?? DB_TYPE;
$dbHost		= $method['host'] ?? DB_HOST;
$dbName		= $method['dbname'] ?? DB_NAME;
$dbCharset	= $method['dbcharset'] ?? DB_CHARSET;
$user 		= $method['user'] ?? DB_USERNAME;
$password 	= $method['password'] ?? DB_PASSWORD;

$values		= $method['values'];
$where		= $method['where'];
$limit		= $method['limit'];

//if ($values) $values = explode(',', $values);

$where = explode(',', substr($_GET['where'], 1, strlen($_GET['where']) - 2));
$where = array_reduce($where, function($result, $item) {
	[$a, $b] = explode('=', $item);
	$result[$a] = $b;
	return $result;
}, []);


//var_dump($where); die;

$dsn = $dbType . 
	':host=' . $dbHost . 
	';dbname=' . $dbName . 
	';charset=' . $dbCharset;

try {
	$db = new PDO($dsn, $user, $password);
}
catch (\PDOException $e)
{
	die($e->getMessage());
}


foreach ($where as $key => $value) {
	$conditions = $key . ' = :' . $key . ' AND ';
}
$conditions = preg_replace('/ AND $/', '', $conditions);

$query = 'SELECT ' . ($values ?? '*') . ' FROM ' . $uri[0] . ' WHERE ' . $conditions;
var_dump($query);die;
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $uri[1]);
$stmt->execute();

echo '<br>';
echo '<pre>';
var_dump($stmt->fetch(PDO::FETCH_ASSOC));


