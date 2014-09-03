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

class VariableTest extends TestCase
{
	function test_variable()
	{
		$var = new Variable('hello');
		$this->assertEqual('hello', $var->getName());		
	}
	
	function test_filters()
	{
		$var = new Variable('hello | textileze');
		$this->assertEqual('hello', $var->getName());
		$this->assertEqual(array(array('textileze', array())), $var->getFilters());
		
		$var = new Variable('hello | textileze | paragraph');
		$this->assertEqual('hello', $var->getName());
		$this->assertEqual(array(array('textileze', array()), array('paragraph', array())), $var->getFilters());

		$var = new Variable(" hello | strftime: '%Y'");
		$this->assertEqual('hello', $var->getName());
		$this->assertEqual(array(array('strftime', array("'%Y'"))), $var->getFilters());
		
		$var = new Variable(" 'typo' | link_to: 'Typo', true ");
		$this->assertEqual("'typo'", $var->getName());
		$this->assertEqual(array(array('link_to', array("'Typo'", "true"))), $var->getFilters());
		
		$var = new Variable(" 'typo' | link_to: 'Typo', false ");
		$this->assertEqual("'typo'", $var->getName());
		$this->assertEqual(array(array('link_to', array("'Typo'", "false"))), $var->getFilters());
		
		$var = new Variable(" 'foo' | repeat: 3 ");
		$this->assertEqual("'foo'", $var->getName());
		$this->assertEqual(array(array('repeat', array("3"))), $var->getFilters());				
		
		$var = new Variable(" 'foo' | repeat: 3, 3" );
		$this->assertEqual("'foo'", $var->getName());
		$this->assertEqual(array(array('repeat', array("3", "3"))), $var->getFilters());		
		
		$var = new Variable(" 'foo' | repeat: 3, 3, 3 ");
		$this->assertEqual("'foo'", $var->getName());
		$this->assertEqual(array(array('repeat', array("3", "3", "3"))), $var->getFilters());				
		
		$var = new Variable(" hello | strftime: '%Y, okay?'");
		$this->assertEqual('hello', $var->getName());
		$this->assertEqual(array(array('strftime', array("'%Y, okay?'"))), $var->getFilters());		
		
		$var = new Variable(" hello | things: \"%Y, okay?\", 'the other one'");
		$this->assertEqual('hello', $var->getName());
		$this->assertEqual(array(array('things', array('"%Y, okay?"', "'the other one'"))), $var->getFilters());
		
	}
	
	function test_filters_without_whitespace()
	{
		$var = new Variable('hello | textileze | paragraph');
		$this->assertEqual('hello', $var->getName());
		$this->assertEqual(array(array('textileze', array()), array('paragraph', array())), $var->getFilters());		
		
		$var = new Variable('hello|textileze|paragraph');
		$this->assertEqual('hello', $var->getName());
		$this->assertEqual(array(array('textileze', array()), array('paragraph', array())), $var->getFilters());		
		
	}
	
	function test_symbol()
	{
		$var = new Variable("http://disney.com/logo.gif | image: 'med' ");
		$this->assertEqual('http://disney.com/logo.gif', $var->getName());
		$this->assertEqual(array(array('image', array("'med'"))), $var->getFilters());			
		
	}
	
	function test_string_single_quoted()
	{
		$var = new Variable(' "hello" ');
		$this->assertEqual('"hello"', $var->getName());		
	}
	
	function test_string_double_quoted()
	{
		$var = new Variable(" 'hello' ");
		$this->assertEqual("'hello'", $var->getName());				
	}
	
	function test_integer()
	{
		$var = new Variable(' 1000 ');
		$this->assertEqual('1000', $var->getName());
	}
	
	function test_float()
	{
		$var = new Variable(' 1000.01 ');
		$this->assertEqual('1000.01', $var->getName());		
	}
	
	function test_string_with_special_chars()
	{
		$var = new Variable("'hello! $!@.;\"ddasd\" ' ");
		$this->assertEqual("'hello! $!@.;\"ddasd\" '", $var->getName());
	}
	
	function test_string_dot()
	{
		$var = new Variable(" test.test ");
		$this->assertEqual('test.test', $var->getName());		
	}	
}

