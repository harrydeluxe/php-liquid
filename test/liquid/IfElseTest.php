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



class IfElseTest extends LiquidTestcase
{

	function test_if()
	{
		$this->assert_template_result('  ',' {% if false %} this text should not go into the output {% endif %} ');
		$this->assert_template_result('  this text should go into the output  ',
		          ' {% if true %} this text should go into the output {% endif %} ');
		$this->assert_template_result('  you rock ?','{% if false %} you suck {% endif %} {% if true %} you rock {% endif %}?');
	}
		
	function test_if_else()
	{
		$this->assert_template_result(' YES ','{% if false %} NO {% else %} YES {% endif %}');
		$this->assert_template_result(' YES ','{% if true %} YES {% else %} NO {% endif %}');
		$this->assert_template_result(' YES ','{% if "foo" %} YES {% else %} NO {% endif %}');
	}
		
	function test_if_boolean()
	{
		$this->assert_template_result(' YES ','{% if var %} YES {% endif %}', array('var' => true));  
	}

	function test_if_from_variable()
	{
		$this->assert_template_result('','{% if var %} NO {% endif %}', array('var' => false));
		$this->assert_template_result('','{% if var %} NO {% endif %}', array('var' => null));
		$this->assert_template_result('','{% if foo.bar %} NO {% endif %}', array('foo' => array('bar' => false)));
		$this->assert_template_result('','{% if foo.bar %} NO {% endif %}', array('foo' => array()));
		$this->assert_template_result('','{% if foo.bar %} NO {% endif %}', array('foo' => null));
		//$this->assert_template_result('','{% if foo.bar %} NO {% endif %}', array('foo' => true));
		
		$this->assert_template_result(' YES ','{% if var %} YES {% endif %}', array('var' => "text"));
		$this->assert_template_result(' YES ','{% if var %} YES {% endif %}', array('var' => true));
		$this->assert_template_result(' YES ','{% if var %} YES {% endif %}', array('var' => 1));
		//$this->assert_template_result(' YES ','{% if var %} YES {% endif %}', array('var' => array()));
		//$this->assert_template_result(' YES ','{% if var %} YES {% endif %}', array('var' => array()));
		$this->assert_template_result(' YES ','{% if "foo" %} YES {% endif %}');
		$this->assert_template_result(' YES ','{% if foo.bar %} YES {% endif %}', array('foo' => array('bar' => true)));
		$this->assert_template_result(' YES ','{% if foo.bar %} YES {% endif %}', array('foo' => array('bar' => "text")));
		$this->assert_template_result(' YES ','{% if foo.bar %} YES {% endif %}', array('foo' => array('bar' => 1)));
		//$this->assert_template_result(' YES ','{% if foo.bar %} YES {% endif %}', array('foo' => array('bar' => array())));
		
		$this->assert_template_result(' YES ','{% if var %} NO {% else %} YES {% endif %}', array('var' => false));
		$this->assert_template_result(' YES ','{% if var %} NO {% else %} YES {% endif %}', array('var' => null));
		$this->assert_template_result(' YES ','{% if var %} YES {% else %} NO {% endif %}', array('var' => true));
		$this->assert_template_result(' YES ','{% if "foo" %} YES {% else %} NO {% endif %}', array('var' => "text"));
		
		$this->assert_template_result(' YES ','{% if foo.bar %} NO {% else %} YES {% endif %}', array('foo' => array('bar' => false)));
		$this->assert_template_result(' YES ','{% if foo.bar %} YES {% else %} NO {% endif %}', array('foo' => array('bar' => true)));
		$this->assert_template_result(' YES ','{% if foo.bar %} YES {% else %} NO {% endif %}', array('foo' => array('bar' => "text")));
		$this->assert_template_result(' YES ','{% if foo.bar %} NO {% else %} YES {% endif %}', array('foo' => array('notbar' => true)));
		$this->assert_template_result(' YES ','{% if foo.bar %} NO {% else %} YES {% endif %}', array('foo' => array()));
		$this->assert_template_result(' YES ','{% if foo.bar %} NO {% else %} YES {% endif %}', array('notfoo' => array('bar' => true)));
	}

	function test_nested_if() {
		$this->assert_template_result('', '{% if false %}{% if false %} NO {% endif %}{% endif %}');
		$this->assert_template_result('', '{% if false %}{% if true %} NO {% endif %}{% endif %}');
		$this->assert_template_result('', '{% if true %}{% if false %} NO {% endif %}{% endif %}');
		$this->assert_template_result(' YES ', '{% if true %}{% if true %} YES {% endif %}{% endif %}');
		
		$this->assert_template_result(' YES ', '{% if true %}{% if true %} YES {% else %} NO {% endif %}{% else %} NO {% endif %}');
		$this->assert_template_result(' YES ', '{% if true %}{% if false %} NO {% else %} YES {% endif %}{% else %} NO {% endif %}');
		$this->assert_template_result(' YES ', '{% if false %}{% if true %} NO {% else %} NONO {% endif %}{% else %} YES {% endif %}');		
	}
  
	function test_comparisons_on_null()
	{
		$this->assert_template_result('','{% if null < 10 %} NO {% endif %}');
		$this->assert_template_result('','{% if null <= 10 %} NO {% endif %}');
		$this->assert_template_result('','{% if null >= 10 %} NO {% endif %}');
		$this->assert_template_result('','{% if null > 10 %} NO {% endif %}');
		
		$this->assert_template_result('','{% if 10 < null %} NO {% endif %}');
		$this->assert_template_result('','{% if 10 <= null %} NO {% endif %}');
		$this->assert_template_result('','{% if 10 >= null %} NO {% endif %}');
		$this->assert_template_result('','{% if 10 > null %} NO {% endif %}');
	}

	function test_syntax_error_no_variable()
	{
		//$this->expectError('if tag was never closed');
		//$this->assert_template_result('', '{% if jerry == 1 %}');

		try 
		{
			$this->assert_template_result('', '{% if jerry == 1 %}');
			$this->fail("Exception was expected.");
		} 
		catch (Exception $e)
		{
			$this->assertEqual($e->getMessage(), 'if tag was never closed');
			$this->pass();
		}	
	}
  
}