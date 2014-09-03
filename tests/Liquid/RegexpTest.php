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

class LiquidRegexpTest extends UnitTestCase
{

	/**
	 * @var Regexp
	 */
	var $regexp;
	
	function setup()
	{
		$this->regexp = new Regexp('/'.LIQUID_QUOTED_FRAGMENT.'/');
	}
	
	function test_empty()
	{
		$this->assertEqual(array(), $this->regexp->scan(''));
	}
	
	function test_quote()
	{
		$this->assertEqual(array('"arg 1"'), $this->regexp->scan('"arg 1"'));
	}
	
	function test_words()
	{
	    $this->assertEqual(array('arg1', 'arg2'), $this->regexp->scan('arg1 arg2'));
	}
 	
	function test_quoted_words()
	{
	    $this->assertEqual(array('arg1', 'arg2', '"arg 3"'), $this->regexp->scan('arg1 arg2 "arg 3"'));
	}
 	
	function test_quoted_words2()
	{
		$this->assertEqual(array('arg1', 'arg2', "'arg 3'"), $this->regexp->scan('arg1 arg2 \'arg 3\''));
	}
 	
	function test_quoted_words_in_the_middle()
	{
 	    $this->assertEqual(array('arg1', 'arg2', '"arg 3"', 'arg4'), $this->regexp->scan('arg1 arg2 "arg 3" arg4   '));	
	}

}
