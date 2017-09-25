<?php

/*
 * This file is part of the Liquid package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Liquid
 */

namespace Liquid\Tag;

use Liquid\Liquid;
use Liquid\AbstractBlock;
use Liquid\Regexp;

/**
 * Allows output of Liquid code on a page without being parsed.
 *
 * Example:
 *
 *     {% raw %}{{ 5 | plus: 6 }}{% endraw %} is equal to 11.
 *
 *     will return:
 *     {{ 5 | plus: 6 }} is equal to 11.
 */
class TagRaw extends AbstractBlock
{
	/**
	 * @param array $tokens
	 */
	public function parse(array &$tokens)
	{
		$tagRegexp = new Regexp('/^' . Liquid::get('TAG_START') . '\s*(\w+)\s*(.*)?' . Liquid::get('TAG_END') . '$/');

		$this->nodelist = array();

		while (count($tokens)) {
			$token = array_shift($tokens);

			if ($tagRegexp->match($token)) {
				// If we found the proper block delimiter just end parsing here and let the outer block proceed
				if ($tagRegexp->matches[1] == $this->blockDelimiter()) {
					break;
				}
			}

			$this->nodelist[] = $token;
		}
	}
}
