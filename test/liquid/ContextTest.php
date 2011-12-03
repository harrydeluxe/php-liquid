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
 * Context tests
 *
 * @author Viorel Dram
 *
 * @package Liquid
 */
class ContextTest extends LiquidTestcase
{
	public function testVariables()
	{
		$context = new LiquidContext;
		$context->set('test1', 'val');
		$this->assertTrue($context->get('test1') === 'val');

		// make sure we can return values that evaluate to false properly
		$context->set('test2', 0);
		$this->assertTrue($context->get('test2') == '0');

		// test the value of undefined variables
		$this->assertTrue($context->get('thisVariableShouldNotExist') === null);
	}

	public function testScoping1()
	{
		$this->setExpectedException('LiquidException');

		$context = new LiquidContext;

		// we should have no elements to pop
		$context->pop();
	}

	public function testScoping2()
	{
		$this->setExpectedException('LiquidException');

		$context = new LiquidContext;
		$context->push();

		// this should be fine
		$context->pop();

		// it should throw exception here
		$context->pop();
	}

	public function testLengthQuery()
	{
		$context = new LiquidContext;
		$context->set('numbers', array(1, 2, 3, 4));
		$this->assertTrue($context->get('numbers.size') == 4);
	}

	public function testMergeFail()
	{
		$this->setExpectedException('LiquidException');

		$context = new LiquidContext;
		$context->merge(array('ping' => new PingFilter));

		// this should fail because PingFilter doesn't have a toLiquid method
		$this->assertTrue($context->get('ping') == 100);
	}

	public function testMerge()
	{
		$context = new LiquidContext;
		$context->set('test1', 'val');
		$this->assertTrue($context->get('test2') === null);
		$this->assertFalse($context->has_key('test2'));

		$new_assigns = array(
			'test2' => 'val2'
		);

		$context->merge($new_assigns);
		$this->assertTrue($context->get('test2') === 'val2');
		$this->assertTrue($context->has_key('test2'));

		$context->merge(array('cents' => new HundredCents));
		$this->assertTrue($context->get('cents') == 100);
	}

	public function testResolveError()
	{
		$this->setExpectedException('LiquidException');

		$context = new LiquidContext;

		// we shouldn't be able to resolve array keys
		$this->assertTrue($context->has_key(array(null)));
	}

	public function testResolve()
	{
		$context = new LiquidContext;

		$this->assertTrue($context->resolve(null) === null);
		$this->assertTrue($context->resolve('null') === null);
		$this->assertTrue($context->resolve('true') == true);
		$this->assertTrue($context->resolve('false') == false);
		$this->assertTrue($context->resolve("'test'") === 'test');
		$this->assertTrue($context->resolve('"test"') === 'test');
		$this->assertTrue($context->resolve('123') === '123');
		$this->assertTrue($context->resolve('123.4') === '123.4');
		$this->assertTrue($context->resolve(123) == 123);
		$this->assertTrue($context->resolve(123.4) == 123.4);
	}

	public function testAddFilter()
	{
		$context = new LiquidContext;
		$context->add_filters(new PingFilter);

		$this->assertTrue($context->invoke('ping', 'test') === 'test pong');
	}

	public function testDrop()
	{
		$context = new LiquidContext;
		$context->merge(array('cents' => new CentsDrop));
		$this->assertTrue($context->get('cents.amount') == 100);
	}
}


class PingFilter
{
	function ping($value)
	{
		return $value . ' pong';
	}
}

class HundredCents
{
	function toLiquid()
	{
		return 100;
	}
}

class CentsDrop extends LiquidDrop
{
	function amount()
	{
		return new HundredCents;
	}
}
