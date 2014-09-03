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

class LiquidRegexpTest extends TestCase
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
