<?php

declare(strict_types = 1);

namespace Libs\Validators;


function min(string $value, int $min)
{
	return strlen($value) >= $min;
}

function max(string $value, int $max)
{
	return strlen($value) <= $max;
}

function regex(string $value, string $pattern)
{
	return preg_match($pattern, $value);
}