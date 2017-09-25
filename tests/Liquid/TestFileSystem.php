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

use Liquid\FileSystem\Virtual;

class TestFileSystem extends Virtual
{
	/** @return TestFileSystem */
	public static function fromArray($array)
	{
		return new static(function ($path) use ($array) {
			if (isset($array[$path])) {
				return $array[$path];
			}

			return '';
		});
	}
}
