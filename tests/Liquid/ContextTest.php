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

class HundredCentes {
	function toLiquid() {
		return 100;
	}
	
}

class CentsDrop extends Drop {
	function amount() {
		return new HundredCentes();
	}
	
}

class HiFilter  {
	function hi($value) {
		return $value . ' hi!';
	}
	
}

class GlobalFilter{
	function notice($value) {
		return "Global $value";
	}
	
}

class LocalFilter  {
	function notice($value) {
		return "Local $value";
	}
	
}


class ContextTest extends UnitTestCase
{
	
	/**
	 * @var Context
	 */
	var $context;
	
	function setup()
	{
		$this->context = new Context();
		
	}

	function test_variables()
	{
		$this->context->set('test', 'test');
		$this->assertEqual('test', $this->context->get('test'));
		
		// we add this text to make sure we can return values that evaluate to false properly
		$this->context->set('test_0', 0);
		$this->assertEqual('0', $this->context->get('test_0'));
	}
	
	function test_variables_not_existing()
	{
		$this->assertNull($this->context->get('test'));
		
	}

	function test_scoping()
	{
		$this->context->push();
		$this->assertNull($this->context->pop());
	
	}
	
	function test_length_query()
	{
		$this->context->set('numbers', array(1, 2, 3, 4));
		$this->assertEqual(4, $this->context->get('numbers.size'));		
	}
	
	
	function test_add_filter()
	{
		$context = new Context();
		$context->addFilters(new HiFilter());
		$this->assertEqual('hi? hi!', $context->invoke('hi', 'hi?'));

		$context = new Context();
		$this->assertEqual('hi?', $context->invoke('hi', 'hi?'));
			
		$context->addFilters(new HiFilter());
		$this->assertEqual('hi? hi!', $context->invoke('hi', 'hi?'));		
	}

	
	// skip this one for now, as we haven't implemented global filters yet
	function test_override_global_filter()
	{		
		$template = new Template();
		$template->registerFilter(new GlobalFilter());
		
		$template->parse("{{'test' | notice }}");
		$this->assertEqual('Global test', $template->render());
		$this->assertEqual('Local test', $template->render(array(), new LocalFilter()));		
	}
	
	function test_add_item_in_outer_scope()
	{		
		$this->context->set('test', 'test');
		$this->context->push();
		$this->assertEqual('test', $this->context->get('test'));
		$this->context->pop();
		$this->assertEqual('test', $this->context->get('test'));		
	}
	
	function test_add_item_in_inner_scope()
	{		
		$this->context->push();
		$this->context->set('test', 'test');
		$this->assertEqual('test', $this->context->get('test'));
		$this->context->pop();
		$this->assertEqual(null, $this->context->get('test'));		
	}
	
	function test_hierchal_data()
	{
		$this->context->set('hash', array('name' => 'tobi'));
		$this->assertEqual('tobi', $this->context->get('hash.name'));		
	}
	
	function test_keywords()
	{
		$this->assertEqual(true, $this->context->get('true'));
		$this->assertEqual(false, $this->context->get('false'));
	}

	function test_digits()
	{
		$this->assertEqual(100, $this->context->get(100));
		$this->assertEqual(100.00, $this->context->get(100.00));
	}
	
	function test_string()
	{
		$this->assertEqual("hello!", $this->context->get("'hello!'"));
		$this->assertEqual("hello!", $this->context->get('"hello!"'));
	}	

	function test_merge()
	{
		$this->context->merge(array('test' => 'test'));
		$this->assertEqual('test', $this->context->get('test'));
		
		$this->context->merge(array('test' => 'newvalue', 'foo' => 'bar'));	
		$this->assertEqual('newvalue', $this->context->get('test'));
		$this->assertEqual('bar', $this->context->get('foo'));
		
	}
	
	function test_cents()
	{
		$this->context->merge(array('cents' => new HundredCentes()));
		$this->assertEqual(100, $this->context->get('cents'));
	}
	
	
	function test_nested_cents()
	{
		$this->context->merge(array('cents' => array('amount' => new HundredCentes())));
		$this->assertEqual(100, $this->context->get('cents.amount'));
		
		
		$this->context->merge(array('cents' => array('cents' => array('amount' => new HundredCentes()))));
		$this->assertEqual(100, $this->context->get('cents.cents.amount'));		
		
	}
	
	function test_cents_through_drop()
	{
		$this->context->merge(array('cents' => new CentsDrop()));
		$this->assertEqual(100, $this->context->get('cents.amount'));
		
	}
	
	function test_cents_through_drop_nestedly()
	{
		$this->context->merge(array('cents' => array('cents' => new CentsDrop())));
		$this->assertEqual(100, $this->context->get('cents.cents.amount'));	
		
		$this->context->merge(array('cents' => array('cents' => array('cents' => new CentsDrop()))));
		$this->assertEqual(100, $this->context->get('cents.cents.cents.amount'));		
	}	
}
