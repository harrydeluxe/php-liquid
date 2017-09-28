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

use Liquid\TestCase;

class TagRawTest extends TestCase
{
	public function testRaw()
	{
		$this->assertTemplateResult(
			'{{ y | plus: x }}{% if %} is equal to 11.',
			'{% raw %}{{ y | plus: x }}{% if %}{% endraw %} is equal to 11.',
			array('x' => 5, 'y' => 6)
		);

		$this->assertTemplateResult('', '{% raw %}{% endraw %}');
	}
}
