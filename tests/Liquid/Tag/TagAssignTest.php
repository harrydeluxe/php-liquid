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
	 * @expectedException \Liquid\Exception\ParseException
	 */
	public function testInvalidAssign()
	{
		$template = new Template();

		$template->parse('{% assign test %}');
	}

	/**
	 * Tests a simple assignment with no filters
	 */
	public function testSimpleAssign()
	{
		$template = new Template();

		$template->parse('{% assign test = "hello" %}{{ test }}');
		$this->assertTrue($template->render() === 'hello');
	}

	/**
	 * Tests filtered value assignment
	 */
	public function testAssignWithFilters()
	{
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
	}

	/**
	 * Tests filtered value assignment with separators
	 */
	public function testTagAssignWithSplit()
	{
		$template = new Template();

		$template->parse('{% assign rows = "one|two|three,one|two|three" | upcase | split: "," %}{% for row in rows %}{% assign cols = row | split: "|" %}{% for col in cols %} {{col}}{%endfor%}{% endfor %}');
		$this->assertEquals($template->render(), ' ONE TWO THREE ONE TWO THREE');

		$template->parse('{% assign issue_numbers = "1339|1338|1321" | split: "|" %}{% for issue in issue_numbers %} {{ issue }}{% endfor %}');
		$this->assertEquals($template->render(), ' 1339 1338 1321');
	}

	/**
	 * Tests a simple assignment with numbers
	 */
	public function testNumbersAssign()
	{
		$this->assertTemplateResult('42', '{% assign i = 42 %}{{ i }}');
		$this->assertTemplateResult('3.14', '{% assign i = 3.14 %}{{ i }}');
		$this->assertTemplateResult('-100', '{% assign i = -100 %}{{ i }}');
		$this->assertTemplateResult('-10', '{% assign i = -10.0 %}{{ i }}');
		$this->assertTemplateResult('-10.5', '{% assign i = -10.5 %}{{ i }}');
	}
}
