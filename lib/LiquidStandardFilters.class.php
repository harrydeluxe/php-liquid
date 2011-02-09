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
 * A selection of standard filters
 *
 * @package Liquid
 */
class LiquidStandardFilters
{
	/**
	 * Return the size of an array or of an string
	 *
	 * @param mixed $input
	 * @return int
	 */
	function size($input)
	{ 
		if(is_string($input) || is_numeric($input))
		{
			return strlen($input);
		}
		elseif (is_array($input))
		{
			return count($input);
		}
		elseif (is_object($input))
		{
			if(method_exists($input, 'size'))
			{
				return $input->size();
			}
		}
		
		return $input;
	}


	/**
	 * Convert an input to lowercase
	 *
	 * @param string $input
	 * @return string
	 */
	function downcase($input)
	{
		if(is_string($input))
		{
			return strtolower($input);
		}
		
		return $input;
	}


	/**
	 * Convert an input to uppercase
	 *
	 * @param string $input
	 * @return string
	 */
	function upcase($input)
	{
		if(is_string($input))
		{
			return strtoupper($input);
		}
		
		return $input;		
	}


	/**
	 * Truncate a string down to x characters
	 *
	 * @param string $input
	 * @param int $characters
	 * @return string
	 */
	function truncate($input, $characters = 100)
	{
		if(is_string($input) || is_numeric($input))
		{
			if(strlen($input) > $characters)
			{
				return substr($input, 0, $characters).'&hellip;';
			}
		}
		
		return $input;
	}


	/**
	 * Truncate string down to x words
	 *
	 * @param string $input
	 * @param int $words
	 * @return string
	 */
	function truncatewords($input, $words)
	{
		if(is_string($input))
		{
			$wordlist = explode(" ", $input);
			
			if(size($wordlist) > $words)
			{
				return implode(" ", array_slice($wordlist, 0, $words)).'$hellip;';
			}
		}
		
		return $input;
	}


	/**
	 * Removes html tags from text
	 *
	 * @param string $input
	 * @return string
	 */
	function strip_html($input)
	{
		return strip_tags($input);
	}


	/**
	 * Joins elements of an array with a given character between them
	 *
	 * @param array $input
	 * @param string $glue
	 * @return string
	 */
	function join($input, $glue = ' ')
	{	
		if(is_array($input))
		{
			return implode($glue, $input);
		}
		return $input;
	}


	/**
	 * Formats a date using strftime
	 *
	 * @param mixed $input
	 * @param string $format
	 * @return string
	 */
	function date($input, $format)
	{
		if(!is_numeric($input))
		{
			$input = strtotime($input);
		}
		
		return strftime($format, $input);
		
	}


	/**
	 * Returns the first element of an array
	 *
	 * @param array $input
	 * @return mixed
	 */
	function first($input)
	{
		if(is_array($input))
		{
			return reset($input);
		} 
		
		return $input;
	}


	/**
	 * Returns the last element of an array
	 *
	 * @param array $input
	 * @return mixed
	 */
	function last($input)
	{
		if(is_array($input))
		{
			return end($input);
		} 
		
		return $input;		
	}
}