<?php


// Values to bind later
$bind = [];

// Query parts
$conditionsPart = '';
$limitPart = '';

// Prepare 'values' query part
if ($values != null && is_array($values)) {
	$values = implode(', ', $values);
}

// Prepare 'where' query part
if ($index != null) {
	$conditionsPart .= 'id = :id AND ';
	$bind[":id"] = $index;
}
if ($where != null) {
	foreach ($where as $key => $value) {
		$conditionsPart .= $key . ' = :' . $key . ' AND ';
		$bind[":$key"] = $value;
	}
}
if ($conditionsPart !== '') {
	$conditionsPart = preg_replace('/ AND $/', '', $conditionsPart);
	$conditionsPart = ' WHERE ' . $conditionsPart;
}

// Prepare 'limit' query part
if ($limit != null) {
	$limitPart = ' LIMIT ' . $limit;
}


// Finalize query
$query = 'SELECT ' . $values . ' FROM ' . $table . $conditionsPart . $limitPart;
$stmt = $db->prepare($query);

foreach ($bind as $key => $value) {
	if ($key === ':id') {
		$value = (int) $value;
		$stmt->bindParam($key, $value, PDO::PARAM_INT);
	}
	else {
		$stmt->bindParam($key, $value);
	}
}
$stmt->execute($bind);
//$stmt->debugDumpParams();die;

// Clean all notices in buffer 
ob_clean();
// Get results
if ($stmt->rowCount() === 1) {
	$result = $stmt->fetch(PDO::FETCH_ASSOC);
}
else if ($stmt->rowCount() > 1) {
	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Show result
if ($result)
	echo json_encode($result);
