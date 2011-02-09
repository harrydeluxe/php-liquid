<?php
/**
 * Liquid for PHP
 * 
 * @package Liquid
 * @copyright Copyright (c) 2011 Harald Hanek, 
 * fork of php-liquid (c) 2006 Mateo Murphy,
 * based on Liquid for Ruby (c) 2006 Tobias Luetke
 * @license http://www.opensource.org/licenses/mit-license.php
 */

/**
 * The method is called on objects when resolving variables to see
 * if a given property exists
 *
 */
define('LIQUID_HAS_PROPERTY_METHOD', 'field_exists');

/**
 * This method is called on object when resolving variables when
 * a given property exists
 *
 */
define('LIQUID_GET_PROPERTY_METHOD', 'get');

/**
 * Seperator between filters
 *
 */
define('LIQUID_FILTER_SEPERATOR', '\|');

/**
 * Seperator for arguments
 *
 */
define('LIQUID_ARGUMENT_SPERATOR', ',');

/**
 * Seperator for argument names and values
 *
 */
define('LIQUID_FILTER_ARGUMENT_SEPERATOR', ':');

/**
 * Seperator for variable attributes
 *
 */
define('LIQUID_VARIABLE_ATTRIBUTE_SEPERATOR', '.');

/**
 * Suffix fuer include dateien
 *
 */
defined('LIQUID_INCLUDE_SUFFIX') or define('LIQUID_INCLUDE_SUFFIX', 'tpl');

/**
 * Tag start
 *
 */
define('LIQUID_TAG_START', '{%');

/**
 * Tag end
 *
 */
define('LIQUID_TAG_END', '%}');

/**
 * Variable start
 *
 */
define('LIQUID_VARIABLE_START', '{{');

/**
 * Variable end
 *
 */
define('LIQUID_VARIABLE_END', '}}');

/**
 * The characters allowed in a variable
 *
 */
define('LIQUID_ALLOWED_VARIABLE_CHARS', '[a-zA-Z_.-]');

/**
 * Regex for quoted fragments
 *
 */
define('LIQUID_QUOTED_FRAGMENT', '"[^"]+"|\'[^\']+\'|[^\s,|]+');

/**
 * Regex for recongnizing tab attributes
 *
 */
define('LIQUID_TAG_ATTRIBUTES', '/(\w+)\s*\:\s*('.LIQUID_QUOTED_FRAGMENT.')/');

/**
 * Regex used to split tokenss
 *
 */
define('LIQUID_TOKENIZATION_REGEXP', '/('.LIQUID_TAG_START.'.*?'.LIQUID_TAG_END.'|'.LIQUID_VARIABLE_START.'.*?'.LIQUID_VARIABLE_END.')/');


defined('LIQUID_PATH') or define('LIQUID_PATH',dirname(__FILE__));


class Liquid
{
	/**
	 * Flatten a multidimensional array into a single array. Does not maintain keys.
	 *
	 * @param array $array
	 * @return array 
	 */
	public static function array_flatten($array)
	{
		$return = array();
		
		foreach($array as $element)
		{
			if(is_array($element))
			{
				$return = array_merge($return, self::array_flatten($element));	
			}
			else
			{
				$return[] = $element;	
			}
		}
		return $return;	
	}


	/**
	 * Class autoload loader.
	 * This method is provided to be invoked within an __autoload() magic method.
	 * @param string $className class name
	 * @return boolean whether the class has been loaded successfully
	 */
	public static function autoload($className)
	{
		// use include so that the error PHP file may appear
		if(isset(self::$_coreClasses[$className]))
			include(LIQUID_PATH.self::$_coreClasses[$className]);
			//include_once(LIQUID_PATH.self::$_coreClasses[$className]);
		return true;
	}


	private static $_coreClasses = array(
		'LiquidException' => '/lib/LiquidException.class.php',
		'LiquidRegexp' => '/lib/LiquidRegexp.class.php',
		'LiquidBlock' => '/lib/LiquidBlock.class.php',
		'LiquidContext' => '/lib/LiquidContext.class.php',
		'LiquidDocument' => '/lib/LiquidDocument.class.php',
		'LiquidDrop' => '/lib/LiquidDrop.class.php',
		'LiquidBlankFileSystem' => '/lib/LiquidBlankFileSystem.class.php',
		'LiquidLocalFileSystem' => '/lib/LiquidLocalFileSystem.class.php',
		'LiquidFilterbank' => '/lib/LiquidFilterbank.class.php',
		'TableRowLiquidTag' => '/lib/Tag/TableRowLiquidTag.class.php',
		'LiquidDecisionBlock' => '/lib/Tag/LiquidDecisionBlock.class.php',
		'IncludeLiquidTag' => '/lib/Tag/IncludeLiquidTag.class.php',
		'CaseLiquidTag' => '/lib/Tag/CaseLiquidTag.class.php',
		'AssignLiquidTag' => '/lib/Tag/AssignLiquidTag.class.php',
		'CommentLiquidTag' => '/lib/Tag/CommentLiquidTag.class.php',
		'CaptureLiquidTag' => '/lib/Tag/CaptureLiquidTag.class.php',
		'CycleLiquidTag' => '/lib/Tag/CycleLiquidTag.class.php',
		'ForLiquidTag' => '/lib/Tag/ForLiquidTag.class.php',
		'IfLiquidTag' => '/lib/Tag/IfLiquidTag.class.php',
		'LiquidStandardFilters' => '/lib/LiquidStandardFilters.class.php',
		'LiquidTag' => '/lib/LiquidTag.class.php',
		'LiquidTemplate' => '/lib/LiquidTemplate.class.php',
		'LiquidVariable' => '/lib/LiquidVariable.class.php'
	);
}

spl_autoload_register(array('Liquid', 'autoload'));