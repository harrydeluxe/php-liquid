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

class TagDecrementTest extends TestCase
{
	/**
	 * @expectedException \Liquid\LiquidException
	 */
	public function testSyntaxError()
	{
		$this->assertTemplateResult('', '{% decrement %}');
	}

	/**
	 * Undefined variable will become -1
	 */
	public function testDecrementNonExistingVariable()
	{
		$this->assertTemplateResult(-1, '{% decrement no_such_var %}{{ no_such_var }}');
	}

	public function testDecrementVariable()
	{
		$this->assertTemplateResult(42, '{% decrement var %}{{ var }}', array('var' => 43));
	}

	public function testDecrementNestedVariable()
	{
		$this->assertTemplateResult(42, '{% for var in vars %}{% decrement var %}{{ var }}{% endfor %}', array('vars' => array(43)));
	}

	public function testVariableNameContainingNumber()
	{
		$this->assertTemplateResult(42, '{% decrement var123 %}{{ var123 }}', array('var123' => 43));
	}
}
