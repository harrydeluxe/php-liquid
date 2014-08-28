<?php

namespace Liquid;

/**
 * Liquid for PHP
 */

/**
 * The method is called on objects when resolving variables to see
 * if a given property exists
 *
 */
defined('LIQUID_HAS_PROPERTY_METHOD') or define('LIQUID_HAS_PROPERTY_METHOD', 'field_exists');

/**
 * This method is called on object when resolving variables when
 * a given property exists
 *
 */
defined('LIQUID_GET_PROPERTY_METHOD') or define('LIQUID_GET_PROPERTY_METHOD', 'get');

/**
 * Separator between filters
 *
 */
defined('LIQUID_FILTER_SEPARATOR') or define('LIQUID_FILTER_SEPARATOR', '\|');

/**
 * Separator for arguments
 *
 */
defined('LIQUID_ARGUMENT_SEPARATOR') or define('LIQUID_ARGUMENT_SEPARATOR', ',');

/**
 * Separator for argument names and values
 *
 */
defined('LIQUID_FILTER_ARGUMENT_SEPARATOR') or define('LIQUID_FILTER_ARGUMENT_SEPARATOR', ':');

/**
 * Separator for variable attributes
 *
 */
defined('LIQUID_VARIABLE_ATTRIBUTE_SEPARATOR') or define('LIQUID_VARIABLE_ATTRIBUTE_SEPARATOR', '.');

/**
 * Allow Templatenames with extension in include and extends tags. default = false
 *
 */
defined('LIQUID_INCLUDE_ALLOW_EXT') or define('LIQUID_INCLUDE_ALLOW_EXT', false);

/**
 * Suffix for include files
 *
 */
defined('LIQUID_INCLUDE_SUFFIX') or define('LIQUID_INCLUDE_SUFFIX', 'liquid');

/**
 * Prefix for include files
 *
 */
defined('LIQUID_INCLUDE_PREFIX') or define('LIQUID_INCLUDE_PREFIX', '_');
/**
 * Tag start
 *
 */
defined('LIQUID_TAG_START') or define('LIQUID_TAG_START', '{%');

/**
 * Tag end
 *
 */
defined('LIQUID_TAG_END') or define('LIQUID_TAG_END', '%}');

/**
 * Variable start
 *
 */
defined('LIQUID_VARIABLE_START') or define('LIQUID_VARIABLE_START', '{{');

/**
 * Variable end
 *
 */
defined('LIQUID_VARIABLE_END') or define('LIQUID_VARIABLE_END', '}}');

/**
 * The characters allowed in a variable
 *
 */
defined('LIQUID_ALLOWED_VARIABLE_CHARS') or define('LIQUID_ALLOWED_VARIABLE_CHARS', '[a-zA-Z_.-]');

/**
 * Regex for quoted fragments
 *
 */
defined('LIQUID_QUOTED_FRAGMENT') or define('LIQUID_QUOTED_FRAGMENT', '"[^"]+"|\'[^\']+\'|[^\s,|]+');

/**
 * Regex for recognizing tab attributes
 *
 */
defined('LIQUID_TAG_ATTRIBUTES') or define('LIQUID_TAG_ATTRIBUTES', '/(\w+)\s*\:\s*(' . LIQUID_QUOTED_FRAGMENT . ')/');

/**
 * Regex used to split tokens
 *
 */
defined('LIQUID_TOKENIZATION_REGEXP') or define('LIQUID_TOKENIZATION_REGEXP', '/(' . LIQUID_TAG_START . '.*?' . LIQUID_TAG_END . '|' . LIQUID_VARIABLE_START . '.*?' . LIQUID_VARIABLE_END . ')/');


defined('LIQUID_PATH') or define('LIQUID_PATH', dirname(__FILE__));


defined('LIQUID_AUTOLOAD') or define('LIQUID_AUTOLOAD', true);

class Liquid
{
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

	/**
	 * Checks if class exists in $_coreClasses array
	 *
	 * @param string $className class name
	 *
	 * @return boolean whether the class exists in $_coreClasses
	 */
	public static function classExists($className) {
		return (isset(self::$_coreClasses[$className])) ? true : false;
	}

	/**
	 * Class autoload loader.
	 * This method is provided to be invoked within an __autoload() magic method.
	 *
	 * @param string $className class name
	 *
	 * @return boolean whether the class has been loaded successfully
	 */
	public static function autoload($className) {
		if (isset(self::$_coreClasses[$className])) {
			include(LIQUID_PATH . self::$_coreClasses[$className]); // use include so that the error PHP file may appear
			//include_once(LIQUID_PATH.self::$_coreClasses[$className]);
			return true;
		}
		return false;
	}

	/**
	 * Registers a new class autoloader.
	 * The new autoloader will be placed before {@link autoload} and after
	 * any other existing autoloaders.
	 *
	 * @param callback $callback a valid PHP callback (function name or array($className,$methodName)).
	 */
	public static function registerAutoloader($callback) {
		spl_autoload_unregister(array(
			'Liquid', 'autoload'
		));
		spl_autoload_register($callback);
		spl_autoload_register(array(
			'Liquid', 'autoload'
		));
	}

	private static $_coreClasses = array(
		'LiquidException' => '/lib/LiquidException.class.php',
		'LiquidRegexp' => '/lib/LiquidRegexp.class.php',
		'LiquidBlock' => '/lib/LiquidBlock.class.php',
		'LiquidContext' => '/lib/LiquidContext.class.php',
		'LiquidDocument' => '/lib/LiquidDocument.class.php',
		'LiquidDrop' => '/lib/LiquidDrop.class.php',
		'LiquidCache' => '/lib/LiquidCache.class.php',
		'LiquidCacheApc' => '/lib/Cache/LiquidCacheApc.class.php',
		'LiquidCacheFile' => '/lib/Cache/LiquidCacheFile.class.php',
		'LiquidBlankFileSystem' => '/lib/LiquidBlankFileSystem.class.php',
		'LiquidLocalFileSystem' => '/lib/LiquidLocalFileSystem.class.php',
		'LiquidFilterbank' => '/lib/LiquidFilterbank.class.php',
		'LiquidTagTablerow' => '/lib/Tag/LiquidTagTablerow.class.php',
		'LiquidDecisionBlock' => '/lib/LiquidDecisionBlock.class.php',
		'LiquidTagInclude' => '/lib/Tag/LiquidTagInclude.class.php',
		'LiquidTagCase' => '/lib/Tag/LiquidTagCase.class.php',
		'LiquidTagAssign' => '/lib/Tag/LiquidTagAssign.class.php',
		'LiquidTagBlock' => '/lib/Tag/LiquidTagBlock.class.php',
		'LiquidTagExtends' => '/lib/Tag/LiquidTagExtends.class.php',
		'LiquidTagComment' => '/lib/Tag/LiquidTagComment.class.php',
		'LiquidTagCapture' => '/lib/Tag/LiquidTagCapture.class.php',
		'LiquidTagCycle' => '/lib/Tag/LiquidTagCycle.class.php',
		'LiquidTagFor' => '/lib/Tag/LiquidTagFor.class.php',
		'LiquidTagIf' => '/lib/Tag/LiquidTagIf.class.php',
		'LiquidTagIncrement' => '/lib/Tag/LiquidTagIncrement.class.php',
		'LiquidTagDecrement' => '/lib/Tag/LiquidTagDecrement.class.php',
		'LiquidStandardFilters' => '/lib/LiquidStandardFilters.class.php',
		'LiquidTag' => '/lib/LiquidTag.class.php',
		'LiquidTemplate' => '/lib/LiquidTemplate.class.php',
		'LiquidVariable' => '/lib/LiquidVariable.class.php'
	);
}

if (LIQUID_AUTOLOAD)
	spl_autoload_register(array(
		'Liquid', 'autoload'
	));
