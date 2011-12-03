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
 * Tests for decrementing a counter in a template
 *
 * @author Viorel Dram
 *
 * @package Liquid
 */
class DecrementTest extends LiquidTestcase
{
	/**
	 * The following decrement statement is incorrect so we should get an exception
	 *
	 * @return void
	 */
	public function testInvalidDecrement()
	{
		$this->setExpectedException('LiquidException');
		$this->assertTrueHelper('{% decrement %}', '');
	}

	/**
	 * Tests the normal behavior of the decrement tag
	 *
	 * @return void
	 */
	public function testDecrement()
	{
		$this->assertTrueHelper('{% decrement val %}{{ val }}', '-1');
		$this->assertTrueHelper('{% assign val = "0" %}{% decrement val %}{{ val }}', '-1');
		$this->assertTrueHelper('{% assign val = "1" %}{% decrement val %}{{ val }}', '0');
		$this->assertTrueHelper('{% assign val = "11" %}{% decrement val %}{{ val }}', '10');
		$this->assertTrueHelper('{% assign val = "-1" %}{% decrement val %}{{ val }}', '-2');
		$this->assertTrueHelper('{% assign val = "1.3" %}{% decrement val %}{{ val }}', '0.3');
		$this->assertTrueHelper('{% assign val = "1" %}{% decrement val %}{% decrement val %}{{ val }}', '-1');
		$this->assertTrueHelper('{% assign val = "-1" %}{% decrement val %}{% decrement val %}{% decrement val %}{{ val }}', '-4');
		$this->assertTrueHelper('{% decrement a %}{% decrement b %}{% decrement a %}{% decrement a %}{% decrement b %}{{ a }} {{ b }}', '-3 -2');
		$this->assertTrueHelper('{% decrement val %}{{ val }}', '1', array('val' => 2));
	}

	/**
	 * Tests the decrement tag outside context
	 *
	 * @return void
	 */
	public function testOutOfContextDecrement()
	{
		$this->assertTrueHelper("{% assign val = 9 %}{% for item in list %}{% decrement val %}{% endfor %}{{ val }}", '6', array('list' => array(1, 2, 3)));
		$this->assertTrueHelper("{% assign val = 8 %}{% for item in list %}{% decrement val %}{% decrement val %}{% endfor %}{{ val }}", '2', array('list' => array(1, 2, 3)));
	}
}