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
use Liquid\Template;

/**
 * Basic tests for the assignment of one variable to another. This also tests the
 * assignment of filtered values to another variable.
 */
class TagAssignTest extends TestCase
{
	/**
	 * Tests the normal behavior of throwing an exception when the assignment is incorrect
	 *
	 * @expectedException \Liquid\LiquidException
	 */
	public function testInvalidAssign() {
		$template = new Template();

		$template->parse('{% assign test %}');
	}

	/**
	 * Tests a simple assignment with no filters
	 */
	public function testSimpleAssign() {
		$template = new Template();

		$template->parse('{% assign test = "hello" %}{{ test }}');
		$this->assertTrue($template->render() === 'hello');
	}

	/**
	 * Tests filtered value assignment
	 */
	public function testAssignWithFilters() {
		$template = new Template();

		$template->parse('{% assign test = "hello" | upcase %}{{ test }}');
		$this->assertTrue($template->render() === 'HELLO');

		$template->parse('{% assign test = "hello" | upcase | downcase | capitalize %}{{ test }}');
		$this->assertTrue($template->render() === 'Hello');

		$template->parse('{% assign test = var1 | first | upcase %}{{ test }}');
		$this->assertTrue($template->render(array('var1' => array('a', 'b', 'c'))) === 'A');

		$template->parse('{% assign test = var1 | last | upcase %}{{ test }}');
		$this->assertTrue($template->render(array('var1' => array('a', 'b', 'c'))) === 'C');

		$template->parse('{% assign test = var1 | join %}{{ test }}');
		$this->assertTrue($template->render(array('var1' => array('a', 'b', 'c'))) === 'a b c');

		$template->parse('{% assign test = var1 | join : "." %}{{ test }}');
		$this->assertTrue($template->render(array('var1' => array('a', 'b', 'c'))) === 'a.b.c');

		$this->assertTemplateResult("true", "{% assign emptyArray = '' | split: ', ' %}{% if emptyArray %}true{% endif %}");
	}
}
