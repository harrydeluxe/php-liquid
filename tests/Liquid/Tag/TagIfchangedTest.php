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

class TagIfchangedTest extends TestCase
{
	public function testWorks()
	{
		$text = "{% for i in array %}{% ifchanged %} {{ i }} {% endifchanged %}{% endfor %}";
		$expected = " 1  2  3 ";
		$this->assertTemplateResult($expected, $text, array('array' => array(1, 2, 3)));
	}

	public function testFails()
	{
		$text = "{% for i in array %}{% ifchanged %} {{ i }} {% endifchanged %}{% endfor %}";
		$expected = " 1  2  1 ";
		$this->assertTemplateResult($expected, $text, array('array' => array(1, 2, 2, 1)));
	}
}
