<?php

/**
 * This file is part of the Liquid package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Liquid
 */

namespace Liquid\Tag;

use Liquid\TestCase;

class TagUnlessTest extends TestCase
{
	public function testTrueEqlTrue() {
		$text = " {% unless true == true %} true {% else %} false {% endunless %} ";
		$expected = "  false  ";
		$this->assertTemplateResult($expected, $text);
	}

	public function testTrueNotEqlTrue() {
		$text = " {% unless true != true %} true {% else %} false {% endunless %} ";
		$expected = "  true  ";
		$this->assertTemplateResult($expected, $text);
	}
}