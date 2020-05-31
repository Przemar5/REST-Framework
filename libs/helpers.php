<?php

declare(strict_types = 1);

namespace Libs\Helpers;


function getFunction(string $namespace, string $funcName) 
{
	return $namespace . $funcName;
}

function getResult(string $namespace, string $funcName, array $args = []) 
{
	return call_user_func_array([$namespace, $funcName], $args);
}