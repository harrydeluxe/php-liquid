<?php
/**
 * Liquid for PHP
 * 
 * @package Liquid
 * @copyright Copyright (c) 2011 Harald Hanek, 
 * fork of php-liquid (c) 2006 Mateo Murphy,
 * based on Liquid for Ruby (c) 2006 Tobias Luetke
 * @license http://www.opensource.org/licenses/mit-license.php
 */


/**
 * Basic tests for the assignment of one variable to another. This also tests the 
 * assignment of filtered values to another variable.
 *
 * @author Viorel Dram
 *
 * @package Liquid
 */
class AssignTest extends UnitTestCase
{
	/**
	 * Tests the normal behavior of throwing an exception when the assignment is incorrect
	 *
	 * @return void
	 */
	public function testInvalidAssign()
	{
		//$this->setExpectedException('LiquidException');
		$this->expectException('LiquidException');

		$template = new Template;

		$template->parse('{% assign test %}');
		$this->assertTrue($template->render() === 'hello');
	}

	/**
	 * Tests a simple assignment with no filters
	 *
	 * @return void
	 */
	public function testSimpleAssign()
	{
		$template = new Template;

		$template->parse('{% assign test = "hello" %}{{ test }}');
		$this->assertTrue($template->render() === 'hello');
	}

	/**
	 * Tests filtered value assignment
	 *
	 * @return void
	 */
	public function testAssignWithFilters()
	{
		$template = new Template;

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
}
