<?php

/**
 * プリミティブ型をどこからでも呼べるようにする
 */

/**
 * Int
 *
 * @param int $value
 * @return Int
 */
function Int($value)
{
	global $container;
	$int = $container->get('System\Type\Primitive\Int');
	$int->setValue($value);

	return $int;
}

/**
 * String
 *
 * @param string $value
 * @return String
 */
function String($value)
{
	global $container;
	$string = $container->get('System\Type\Primitive\String');
	$string->setValue($value);

	return $string;
}

/**
 * Froat
 *
 * @param float $value
 * @return Float
 */
function Float($value)
{
	global $container;
	$float = $container->get('System\Type\Primitive\Float');
	$float->setValue($value);

	return $float;
}
/**
 * Boolean
 *
 * @param int $value
 * @return Boolean
 */
function Boolean($value)
{
	global $container;
	$boolean = $container->get('System\Type\Primitive\Boolean');
	$boolean->setValue($value);

	return $boolean;
}
