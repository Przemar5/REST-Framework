<?php

declare(strict_types = 1);


$dsn = DB_TYPE . 
	':host=' . DB_HOST . 
	';dbname=' . DB_NAME . 
	';charset=' . DB_CHARSET;

try {
	$db = new PDO($dsn, DB_USERNAME, DB_PASSWORD);
}
catch (\PDOException $e)
{
	die($e->getMessage());
}

$query = 'SELECT * FROM ' . $uri[0] . ' WHERE id = :id';
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $uri[1]);
$stmt->execute();

echo '<br>';
echo '<pre>';
var_dump($stmt->fetch(PDO::FETCH_ASSOC));
