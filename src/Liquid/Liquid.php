<?php

namespace Liquid;

/**
 * Liquid for PHP.
 */
class Liquid
{
	/**
	 * The method is called on objects when resolving variables to see
	 * if a given property exists.
	 */
	const LIQUID_HAS_PROPERTY_METHOD = 'field_exists';

	/**
	 * This method is called on object when resolving variables when
	 * a given property exists.
	 */
	const LIQUID_GET_PROPERTY_METHOD = 'get';

	/**
	 * Separator between filters.
	 */
	const LIQUID_FILTER_SEPARATOR =  '\|';

	/**
	 * Separator for arguments.
	 */
	const LIQUID_ARGUMENT_SEPARATOR =  ',';

	/**
	 * Separator for argument names and values.
	 */
	const LIQUID_FILTER_ARGUMENT_SEPARATOR =  ':';

	/**
	 * Separator for variable attributes.
	 */
	const LIQUID_VARIABLE_ATTRIBUTE_SEPARATOR =  '.';

	/**
	 * Allow template names with extension in include and extends tags.
	 */
	const LIQUID_INCLUDE_ALLOW_EXT = false;

	/**
	 * Suffix for include files.
	 */
	const LIQUID_INCLUDE_SUFFIX = 'liquid';

	/**
	 * Prefix for include files.
	 */
	const LIQUID_INCLUDE_PREFIX = '_';

	/**
	 * Tag start.
	 */
	const LIQUID_TAG_START = '{%';

	/**
	 * Tag end.
	 */
	const LIQUID_TAG_END = '%}';

	/**
	 * Variable start.
	 */
	const LIQUID_VARIABLE_START = '{{';

	/**
	 * Variable end.
	 */
	const LIQUID_VARIABLE_END = '}}';

	/**
	 * The characters allowed in a variable.
	 */
	const LIQUID_ALLOWED_VARIABLE_CHARS = '[a-zA-Z_.-]';

	/**
	 * Regex for quoted fragments.
	 */
	const LIQUID_QUOTED_FRAGMENT = '"[^":]*"|\'[^\':]*\'|(?:[^\s:,\|\'"]|"[^":]*"|\'[^\':]*\')+';

	/**
	 * Regex for recognizing tab attributes.
	 */
	const LIQUID_TAG_ATTRIBUTES = '/(\w+)\s*\:\s*("[^"]+"|\'[^\']+\'|[^\s,|]+)/';

	/**
	 * Regex used to split tokens.
	 */
	const LIQUID_TOKENIZATION_REGEXP = '/({%.*?%}|{{.*?}})/';

	/**
	 * Flatten a multidimensional array into a single array. Does not maintain keys.
	 *
	 * @param array $array
	 *
	 * @return array
	 */
	public static function array_flatten($array) {
		$return = array();

		foreach ($array as $element) {
			if (is_array($element)) {
				$return = array_merge($return, self::array_flatten($element));
			} else {
				$return[] = $element;
			}
		}
		return $return;
	}
}
