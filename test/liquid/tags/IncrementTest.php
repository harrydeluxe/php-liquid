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
 * Tests for incrementing a counter in a template
 *
 * @author Viorel Dram
 *
 * @package Liquid
 */
class IncrementTest extends LiquidTestcase
{
	/**
	 * The following increment statement is incorrect so we should get an exception
	 *
	 * @return void
	 */
	public function testInvalidIncrement()
	{
		$this->setExpectedException('LiquidException');
		$this->assertTrueHelper('{% increment %}', '');
	}

	/**
	 * Tests the normal behavior of the increment tag
	 *
	 * @return void
	 */
	public function testIncrement()
	{
		$this->assertTrueHelper('{% increment val %}{{ val }}', '0');
		$this->assertTrueHelper('{% assign val = "0" %}{% increment val %}{{ val }}', '1');
		$this->assertTrueHelper('{% assign val = "1" %}{% increment val %}{{ val }}', '2');
		$this->assertTrueHelper('{% assign val = "11" %}{% increment val %}{{ val }}', '12');
		$this->assertTrueHelper('{% assign val = "-1" %}{% increment val %}{{ val }}', '0');
		$this->assertTrueHelper('{% assign val = "1.3" %}{% increment val %}{{ val }}', '2.3');
		$this->assertTrueHelper('{% assign val = "1" %}{% increment val %}{% increment val %}{{ val }}', '3');
		$this->assertTrueHelper('{% assign val = "-1" %}{% increment val %}{% increment val %}{% increment val %}{{ val }}', '2');
		$this->assertTrueHelper('{% increment a %}{% increment b %}{% increment a %}{% increment a %}{% increment b %}{{ a }} {{ b }}', '2 1');
		$this->assertTrueHelper('{% increment val %}{{ val }}', '3', array('val' => 2));
	}

	/**
	 * Tests the increment tag outside context
	 *
	 * @return void
	 */
	public function testOutOfContextIncrement()
	{
		$this->assertTrueHelper("{% assign val = 4 %}{% for item in list %}{% increment val %}{% endfor %}{{ val }}", '7', array('list' => array(1, 2, 3)));
		$this->assertTrueHelper("{% assign val = 2 %}{% for item in list %}{% increment val %}{% increment val %}{% endfor %}{{ val }}", '8', array('list' => array(1, 2, 3)));
	}
}