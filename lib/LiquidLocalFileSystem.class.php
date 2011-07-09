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
 * This implements an abstract file system which retrieves template files named in a manner similar to Rails partials,
 * ie. with the template name prefixed with an underscore. The extension ".liquid" is also added.
 * 
 * For security reasons, template paths are only allowed to contain letters, numbers, and underscore.
 * 
 * @package Liquid
 */
class LiquidLocalFileSystem extends LiquidBlankFileSystem
{
	/**
	 * The root path
	 *
	 * @var string
	 */
	private $_root;


	/**
	 * Constructor
	 *
	 * @param string $root The root path for templates
	 * @return LiquidLocalFileSystem
	 */
	public function __construct($root)
	{
		$this->_root = $root;
	}


	/**
	 * Retrieve a template file
	 *
	 * @param string $template_path
	 * @return string
	 */
	public function read_template_file($templatePath)
	{
		$full_path = $this->full_path($templatePath);
		if($full_path)
		{
			return file_get_contents($full_path);
		}
		else
		{
			throw new LiquidException("No such template '$templatePath'");
		}
		
	}


	/**
	 * Resolves a given path to a full template file path, making sure it's valid
	 *
	 * @param string $template_path
	 * @return string
	 */
	public function full_path($template_path)
	{

		$name_regex = new LiquidRegexp('/^[^.\/][a-zA-Z0-9_\/]+$/');
		
		if(!$name_regex->match($template_path))
		{
			throw new LiquidException("Illegal template name '$template_path'");
		}
		
		if(strpos($template_path, '/') !== false)
		{
			$full_path = $this->_root.dirname($template_path).'/'.LIQUID_INCLUDE_PREFIX.basename($template_path).'.'.LIQUID_INCLUDE_SUFFIX;
			//$full_path = $this->_root.dirname($template_path).'/'.basename($template_path).'.'.LIQUID_INCLUDE_SUFFIX;
		}
		else
		{
			$full_path = $this->_root.LIQUID_INCLUDE_PREFIX. $template_path.'.'.LIQUID_INCLUDE_SUFFIX;
			//$full_path = $this->_root.$template_path.'.'.LIQUID_INCLUDE_SUFFIX;
		}

		$root_regex = new LiquidRegexp('/'.preg_quote(realpath($this->_root), '/').'/');
		
		
		if(!$root_regex->match(realpath($full_path)))
		{
			throw new LiquidException("Illegal template path '".realpath($full_path)."'");
		}
		
		return $full_path;
	}	
}
