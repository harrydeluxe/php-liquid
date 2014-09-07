<?php

/**
 * This file is part of the Liquid package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Liquid
 */

namespace Liquid;

class HundredCentes
{
	public function toLiquid() {
		return 100;
	}
}

class CentsDrop extends Drop
{
	public function amount() {
		return new HundredCentes();
	}
}

class NoToLiquid {}

class HiFilter
{
	public function hi($value) {
		return $value . ' hi!';
	}
}

class GlobalFilter
{
	public function notice($value) {
		return "Global $value";
	}
}

class LocalFilter
{
	public function notice($value) {
		return "Local $value";
	}
}

class ContextTest extends TestCase
{
	/** @var Context */
	var $context;

	public function setup() {
		parent::setUp();

		$this->context = new Context();
	}

	public function testScoping() {
		$this->context->push();
		$this->assertNull($this->context->pop());
	}

	/**
	 * @expectedException \Liquid\LiquidException
	 */
	public function testNoScopeToPop() {
		$this->context->pop();
	}

	/**
	 * @expectedException \Liquid\LiquidException
	 */
	public function testGetArray() {
		$this->context->get(array());
	}

	public function testGetNotVariable() {
		$data = array(
			null => null,
			'null' => null,
			'true' => true,
			'false' => false,
			"'quoted_string'" => 'quoted_string',
			'"double_quoted_string"' => "double_quoted_string",
		);

		foreach ($data as $key => $expected) {
			$this->assertEquals($expected, $this->context->get($key));
		}

		$this->assertEquals(42.00, $this->context->get(42.00));
	}

	public function testVariablesNotExisting() {
		$this->assertNull($this->context->get('test'));
	}

	/**
	 * @expectedException \Liquid\LiquidException
	 */
	public function testVariableIsObjectWithNoToLiquid() {
		$this->context->set('test', new NoToLiquid());
		$this->context->get('test');
	}

	public function testVariables() {
		$this->context->set('test', 'test');
		$this->assertEquals('test', $this->context->get('test'));

		// We add this text to make sure we can return values that evaluate to false properly
		$this->context->set('test_0', 0);
		$this->assertEquals('0', $this->context->get('test_0'));
	}

	public function testLengthQuery() {
		$this->context->set('numbers', array(1, 2, 3, 4));
		$this->assertEquals(4, $this->context->get('numbers.size'));
	}

	public function testOverrideSize() {
		$this->context->set('hash', array('a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'size' => '5000'));
		$this->assertEquals(5000, $this->context->get('hash.size'));
	}

	public function testHierchalData() {
		$this->context->set('hash', array('name' => 'tobi'));
		$this->assertEquals('tobi', $this->context->get('hash.name'));
	}

	public function testHierchalDataNoKey() {
		$this->context->set('hash', array('name' => 'tobi'));
		$this->assertNotNull('tobi', $this->context->get('hash.no_key'));
	}

	public function testAddFilter() {
		$context = new Context();
		$context->addFilters(new HiFilter());
		$this->assertEquals('hi? hi!', $context->invoke('hi', 'hi?'));

		$context = new Context();
		$this->assertEquals('hi?', $context->invoke('hi', 'hi?'));

		$context->addFilters(new HiFilter());
		$this->assertEquals('hi? hi!', $context->invoke('hi', 'hi?'));
	}

	public function testOverrideGlobalFilter() {
		$template = new Template();
		$template->registerFilter(new GlobalFilter());

		$template->parse("{{'test' | notice }}");
		$this->assertEquals('Global test', $template->render());
		$this->assertEquals('Local test', $template->render(array(), new LocalFilter()));
	}

	public function testAddItemInOuterScope() {
		$this->context->set('test', 'test');
		$this->context->push();
		$this->assertEquals('test', $this->context->get('test'));
		$this->context->pop();
		$this->assertEquals('test', $this->context->get('test'));
	}

	public function testAddItemInInnerScope() {
		$this->context->push();
		$this->context->set('test', 'test');
		$this->assertEquals('test', $this->context->get('test'));
		$this->context->pop();
		$this->assertEquals(null, $this->context->get('test'));
	}

	public function testMerge() {
		$this->context->merge(array('test' => 'test'));
		$this->assertEquals('test', $this->context->get('test'));

		$this->context->merge(array('test' => 'newvalue', 'foo' => 'bar'));
		$this->assertEquals('newvalue', $this->context->get('test'));
		$this->assertEquals('bar', $this->context->get('foo'));
	}

	public function testCents() {
		$this->context->merge(array('cents' => new HundredCentes()));
		$this->assertEquals(100, $this->context->get('cents'));
	}

	public function testNestedCents() {
		$this->context->merge(array('cents' => array('amount' => new HundredCentes())));
		$this->assertEquals(100, $this->context->get('cents.amount'));

		$this->context->merge(array('cents' => array('cents' => array('amount' => new HundredCentes()))));
		$this->assertEquals(100, $this->context->get('cents.cents.amount'));
	}

	public function testCentsThroughDrop() {
		$this->context->merge(array('cents' => new CentsDrop()));
		$this->assertEquals(100, $this->context->get('cents.amount'));
	}

	public function testCentsThroughDropNestedly() {
		$this->context->merge(array('cents' => array('cents' => new CentsDrop())));
		$this->assertEquals(100, $this->context->get('cents.cents.amount'));

		$this->context->merge(array('cents' => array('cents' => array('cents' => new CentsDrop()))));
		$this->assertEquals(100, $this->context->get('cents.cents.cents.amount'));
	}
}
