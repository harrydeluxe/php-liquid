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
 * A support class for regular expressions and
 * non liquid specific support classes and functions.
 */
class Regexp
{
	/**
	 * The regexp pattern
	 *
	 * @var string
	 */
	private $pattern;

	/**
	 * The matches from the last method called
	 *
	 * @var array;
	 */
	public $matches;

	/**
	 * Constructor
	 *
	 * @param string $pattern
	 *
	 * @return Regexp
	 */
	public function __construct($pattern)
	{
		$this->pattern = (substr($pattern, 0, 1) != '/')
			? '/' . $this->quote($pattern) . '/'
			: $pattern;
	}

	/**
	 * Quotes regular expression characters
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public function quote($string)
	{
		return preg_quote($string, '/');
	}

	/**
	 * Returns an array of matches for the string in the same way as Ruby's scan method
	 *
	 * @param string $string
	 *
	 * @return array
	 */
	public function scan($string)
	{
		preg_match_all($this->pattern, $string, $matches);

		if (count($matches) == 1) {
			return $matches[0];
		}

		array_shift($matches);

		$result = array();

		foreach ($matches as $matchKey => $subMatches) {
			foreach ($subMatches as $subMatchKey => $subMatch) {
				$result[$subMatchKey][$matchKey] = $subMatch;
			}
		}

		return $result;
	}

	/**
	 * Matches the given string. Only matches once.
	 *
	 * @param string $string
	 *
	 * @return int 1 if there was a match, 0 if there wasn't
	 */
	public function match($string)
	{
		return preg_match($this->pattern, $string, $this->matches);
	}

	/**
	 * Matches the given string. Matches all.
	 *
	 * @param string $string
	 *
	 * @return int The number of matches
	 */
	public function matchAll($string)
	{
		return preg_match_all($this->pattern, $string, $this->matches);
	}

	/**
	 * Splits the given string
	 *
	 * @param string $string
	 * @param int $limit Limits the amount of results returned
	 *
	 * @return array
	 */
	public function split($string, $limit = null)
	{
		return preg_split($this->pattern, $string, $limit);
	}
}
