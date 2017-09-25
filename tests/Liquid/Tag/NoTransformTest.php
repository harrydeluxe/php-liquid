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

class NoTransformTest extends TestCase
{
	public function testNoTransform()
	{
		$this->assertTemplateResult(
			'this text should come out of the template without change...',
			'this text should come out of the template without change...'
		);

		$this->assertTemplateResult('blah', 'blah');
		$this->assertTemplateResult('<blah>', '<blah>');
		$this->assertTemplateResult('|,.:', '|,.:');
		$this->assertTemplateResult('', '');

		$text = "this shouldnt see any transformation either but has multiple lines
		         as you can clearly see here ...";

		$this->assertTemplateResult($text, $text);
	}
}
