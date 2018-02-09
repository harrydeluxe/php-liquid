<?php

/*
 * This file is part of the Liquid package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Liquid
 */

namespace Liquid;

/**
 * Liquid for PHP.
 */
class Liquid
{
	/**
	 * We cannot make settings constants, because we cannot create compound
	 * constants in PHP (before 5.6).
	 *
	 * @var array configuration array
	 */
	public static $config = array(
		// The method is called on objects when resolving variables to see
		// if a given property exists.
		'HAS_PROPERTY_METHOD' => 'field_exists',

		// This method is called on object when resolving variables when
		// a given property exists.
		'GET_PROPERTY_METHOD' => 'get',

		// Separator between filters.
		'FILTER_SEPARATOR' => '\|',

		// Separator for arguments.
		'ARGUMENT_SEPARATOR' => ',',

		// Separator for argument names and values.
		'FILTER_ARGUMENT_SEPARATOR' => ':',

		// Separator for variable attributes.
		'VARIABLE_ATTRIBUTE_SEPARATOR' => '.',

		// Allow template names with extension in include and extends tags.
		'INCLUDE_ALLOW_EXT' => false,

		// Suffix for include files.
		'INCLUDE_SUFFIX' => 'liquid',

		// Prefix for include files.
		'INCLUDE_PREFIX' => '_',

		// Tag start.
		'TAG_START' => '{%',

		// Tag end.
		'TAG_END' => '%}',

		// Variable start.
		'VARIABLE_START' => '{{',

		// Variable end.
		'VARIABLE_END' => '}}',

		// Variable name.
		'VARIABLE_NAME' => '[a-zA-Z_][a-zA-Z_0-9.-]*',

		'QUOTED_STRING' => '"[^"]*"|\'[^\']*\'',
		'QUOTED_STRING_FILTER_ARGUMENT' => '"[^"]*"|\'[^\']*\'',

		// Automatically escape any variables unless told otherwise by a "raw" filter
		'ESCAPE_BY_DEFAULT' => false,

		// The name of the key to use when building pagination query strings e.g. ?page=1
		'PAGINATION_REQUEST_KEY' => 'page',

		// The name of the context key used to denote the current page number
		'PAGINATION_CONTEXT_KEY' => 'page'
	);

	/**
	 * Get a configuration setting.
	 *
	 * @param string $key setting key
	 *
	 * @return string
	 */
	public static function get($key)
	{
		// backward compatibility
		if ($key === 'ALLOWED_VARIABLE_CHARS') {
			return substr(self::$config['VARIABLE_NAME'], 0, -1);
		}
		if (array_key_exists($key, self::$config)) {
			return self::$config[$key];
		}
		// This case is needed for compound settings
		switch ($key) {
				case 'QUOTED_FRAGMENT':
					return self::$config['QUOTED_STRING'] . '|(?:[^\s,\|\'"]|' . self::$config['QUOTED_STRING'] . ')+';
				case 'TAG_ATTRIBUTES':
					return '/(\w+)\s*\:\s*(' . self::get('QUOTED_FRAGMENT') . ')/';
				case 'TOKENIZATION_REGEXP':
					return '/(' . self::$config['TAG_START'] . '.*?' . self::$config['TAG_END'] . '|' . self::$config['VARIABLE_START'] . '.*?' . self::$config['VARIABLE_END'] . ')/';
				default:
					return null;
			}
	}

	/**
	 * Changes/creates a setting.
	 *
	 * @param string $key
	 * @param string $value
	 */
	public static function set($key, $value)
	{
		// backward compatibility
		if ($key === 'ALLOWED_VARIABLE_CHARS') {
			$key = 'VARIABLE_NAME';
			$value .= '+';
		}
		self::$config[$key] = $value;
	}

	/**
	 * Flatten a multidimensional array into a single array. Does not maintain keys.
	 *
	 * @param array $array
	 *
	 * @return array
	 */
	public static function arrayFlatten($array)
	{
		$return = array();

		foreach ($array as $element) {
			if (is_array($element)) {
				$return = array_merge($return, self::arrayFlatten($element));
			} else {
				$return[] = $element;
			}
		}
		return $return;
	}
}
