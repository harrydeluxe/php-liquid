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
class AssignTest extends LiquidTestcase
{
	/**
	 * Tests the normal behavior of throwing an exception when the assignment is incorrect
	 *
	 * @return void
	 */
	public function testInvalidAssign()
	{
		$this->setExpectedException('LiquidException');

		// since the assignment is incorrect we should rise an exception
		$this->assertTrueHelper('{% assign test %}', 'hello');
	}

	/**
	 * Tests a simple assignment with no filters
	 *
	 * @return void
	 */
	public function testSimpleAssign()
	{
		$this->assertTrueHelper('{% assign header = "" %}<h1>{{ header }}</h1>', '<h1></h1>');
		$this->assertTrueHelper('{% assign header = "hello" %}<h1>{{ header }}</h1>', '<h1>hello</h1>');
		$this->assertTrueHelper('{% assign val = 1 %}number: {{ val }}', 'number: 1');
		$this->assertTrueHelper('{% assign val = 1.2 %}number: {{ val }}', 'number: 1.2');
	}

	/**
	 * Tests filtered value assignment
	 *
	 * @return void
	 */
	public function testAssignWithFilters()
	{
		$this->assertTrueHelper('{% assign test = "hello" | upcase %}{{ test }}', 'HELLO');
		$this->assertTrueHelper('{% assign test = "hello" | upcase | downcase | capitalize %}{{ test }}', 'Hello');
		$this->assertTrueHelper('{% assign test = var1 | first | upcase %}{{ test }}', 'A', array('var1' => array('a', 'b', 'c')));
		$this->assertTrueHelper('{% assign test = var1 | last | upcase %}{{ test }}', 'C', array('var1' => array('a', 'b', 'c')));
		$this->assertTrueHelper('{% assign test = var1 | join %}{{ test }}', 'a b c', array('var1' => array('a', 'b', 'c')));
		$this->assertTrueHelper('{% assign test = var1 | join : "." %}{{ test }}', 'a.b.c', array('var1' => array('a', 'b', 'c')));
	}
}
