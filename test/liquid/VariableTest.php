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
 * Tests for liquid variables
 *
 * @author Viorel Dram
 *
 * @package Liquid
 */
class VariableTest extends LiquidTestcase
{
	public function testVariable()
	{
		$var = new LiquidVariable('hello');
		$this->assertTrue($var->getName() === 'hello');

		$var = new LiquidVariable(' "hello" ');
		$this->assertTrue($var->getName() === '"hello"');

		$var = new LiquidVariable(' 1000 ');
		$this->assertTrue($var->getName() === '1000');

		$var = new LiquidVariable(' 1000.01 ');
		$this->assertTrue($var->getName() === '1000.01');

		$var = new LiquidVariable("'hello! $!@.;\"ddasd\" ' ");
		$this->assertTrue($var->getName() === "'hello! $!@.;\"ddasd\" '");

		$var = new LiquidVariable(' test.test ');
		$this->assertTrue($var->getName() === 'test.test');
	}

	public function testFilterSimple()
	{
		$var = new LiquidVariable('hello | textileze');
		$this->assertTrue($var->getFilters() == array(array('textileze', array())));

		$var = new LiquidVariable('hello | textileze | paragraph');
		$this->assertTrue($var->getFilters() == array(array('textileze', array()), array('paragraph', array())));

		$var = new LiquidVariable('hello | strftime : "%Y"');
		$this->assertTrue($var->getFilters() == array(array('strftime', array('"%Y"'))));

		$var = new LiquidVariable('hello | strftime : "%Y"');
		$this->assertTrue($var->getFilters() == array(array('strftime', array('"%Y"'))));
	}

	public function testInvalidArrayFilter()
	{
		$this->setExpectedException('LiquidException');

		$template = new LiquidTemplate;
		$template->registerFilter(array('this should throw an exception'));

		$template->parse('{{ var }}');
		$this->assertTrue($template->render(array('var' => 1)) === 1);
	}

	public function testInvalidIntFilter()
	{
		$this->setExpectedException('LiquidException');

		$template = new LiquidTemplate;
		$template->registerFilter(1);

		$template->parse('{{ var }}');
		$this->assertTrue($template->render(array('var' => 1)) === 1);
	}

	public function testObjectFilter()
	{
		$template = new LiquidTemplate;
		$template->registerFilter(new ObjectFilter);

		$template->parse('{{ var | append : "b" }}');
		$this->assertTrue($template->render(array('var' => 'hello')) === 'hellob');
	}

	public function testFunctionFilter()
	{
		$template = new LiquidTemplate;
		$template->registerFilter('filterFunction1');

		$template->parse('{{ var | filterFunction1 }}');
		$this->assertTrue($template->render(array('var' => 'hello')) === 'HELLO');
	}

	public function testNonExistingFilter()
	{
		$template = new LiquidTemplate;

		$template->parse('{{ var | thisFilterShouldNotExist }}');
		$this->assertTrue($template->render(array('var' => 'hello')) === 'hello');
	}

	public function testFilterWithDateParameter()
	{
		$this->assertTrueHelper('{{ "2006-06-05" | date : "%d/%m/%Y" }}', '05/06/2006');
	}

	public function testSimpleVariable()
	{
		$this->assertTrueHelper('{{test}}', 'worked', array('test' => 'worked'));
		$this->assertTrueHelper('{{test}}', 'worked wonderfully', array('test' => 'worked wonderfully'));
	}

	public function testSimpleWithWhitespace()
	{
		$this->assertTrueHelper('{{ test }}', 'worked', array('test' => 'worked'));
		$this->assertTrueHelper('{{ test }}', 'worked wonderfully', array('test' => 'worked wonderfully'));
	}

	public function testIgnoreUnknown()
	{
		$this->assertTrueHelper('{{ test }}', '');
	}

	public function testHashScoping()
	{
		$this->assertTrueHelper('{{ test.test }}', 'worked', array('test' => array('test' => 'worked')));
	}

	public function testFilteredVariables()
	{
		$this->assertTrueHelper('{{ hello | upcase }}', 'TEST', array('hello' => 'test'));
		$this->assertTrueHelper('{{ hello | truncate : 2 }}', 'te&hellip;', array('hello' => 'test'));
		$this->assertTrueHelper('{{ hello | upcase | truncate : 2 }}', 'TE&hellip;', array('hello' => 'test'));
	}
}

class ObjectFilter
{
	public function append($var, $value)
	{
		return $var . $value;
	}
}

function filterFunction1($var)
{
	return strtoupper($var);
}
