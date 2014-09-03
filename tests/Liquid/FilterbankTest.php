<?php

function test_function_filter($value)
{	
	return "worked";	
}

class TestClassFilter
{
	
	var $variable = 'not set';
	
	
	function static_test()
	{
		return "worked";	
		
	}
	
	function instance_test_one()
	{
		$this->variable = 'set';
		return 'set';		
	}
	
	function instance_test_two()
	{
		return $this->variable;
		
	}	
}


class FilterbankTest extends UnitTestCase
{
	
	/**
	 * @var Filterbank
	 */
	var $context;
	
	function setup()
	{
		$this->context = new Context();
	}
	
	/**
	 * Test using a simple function
	 */
	function test_function_filter()
	{
		$var = new Variable('var | test_function_filter');
		$this->context->set('var', 1000);
		$this->context->addFilters('test_function_filter');
		$this->assertIdentical('worked', $var->render($this->context));		
	}
	
	/**
	 * Test using a static class
	 */
	function test_static_class_filter()
	{
		$var = new Variable('var | static_test');
		$this->context->set('var', 1000);
		$this->context->addFilters('TestClassFilter');
		$this->assertIdentical('worked', $var->render($this->context));	
	}
	
	/**
	 * test using an object as a filter; an object fiter will retain its state
	 * between calls to its filters
	 */
	function test_object_filter()
	{
		$var = new Variable('var | instance_test_one');
		$this->context->set('var', 1000);
		$this->context->addFilters( new TestClassFilter());
		$this->assertIdentical('set', $var->render($this->context));			
		
		$var = new Variable('var | instance_test_two');
		$this->assertIdentical('set', $var->render($this->context));
		
	}	
}
