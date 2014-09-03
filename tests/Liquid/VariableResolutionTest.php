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

class VariableResolutionTest extends UnitTestCase {
	
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
