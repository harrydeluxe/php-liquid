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

class VariableResolutionTest extends TestCase {
	
	function test_simple_variable()
	{		
		$template = new Template();
		$template->parse("{{test}}");
		$this->assertEqual('worked', $template->render(array('test'=>'worked')));		
	}
	
	function test_simple_with_whitespaces()
	{
		$template = new Template();

	    $template->parse('  {{ test }}  ');
		$this->assertEqual('  worked  ', $template->render(array('test' => 'worked')));
		$this->assertEqual('  worked wonderfully  ', $template->render(array('test' => 'worked wonderfully')));		
	}
	
	function test_ignore_unknown()
	{
		$template = new Template();
		
		$template->parse('{{ test }}');
		$this->assertEqual('', $template->render());		
	}
	
	function test_array_scoping()
	{
		$template = new Template();
		
		$template->parse('{{ test.test }}');
		$this->assertEqual('worked', $template->render(array('test'=>array('test'=>'worked'))));
		
		// this wasn't working properly in if tests, test seperately
		$template->parse('{{ foo.bar }}');
		$this->dump($template->render(array('foo' => array())));		
	}
	
}
