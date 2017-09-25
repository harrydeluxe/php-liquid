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
 * A selection of custom filters.
 */
class CustomFilters
{
	
	/**
	 * Sort an array by key.
	 *
	 * @param array $input
	 *
	 * @return array
	 */
	public static function sort_key(array $input)
	{
		ksort($input);
		return $input;
	}
}
