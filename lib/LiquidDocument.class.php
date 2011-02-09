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
 * This class represents the entire template document
 *
 * @package Liquid
 */
class LiquidDocument extends LiquidBlock
{
	
	/**
	 * Constructor
	 *
	 * @param array $tokens
	 * @param LiquidFileSystem $file_system
	 * @return LiquidDocument
	 */
	function __construct($tokens, &$file_system)
	{
		$this->file_system = $file_system;
		$this->parse($tokens);
	}


	/**
	 * There isn't a real delimiter
	 *
	 * @return string
	 */
	function block_delimiter()
	{
		return '';
	}


	/**
	 * Document blocks don't need to be terminated since they are not actually opened
	 *
	 */
	function assert_missing_delimitation()
	{
	}
}