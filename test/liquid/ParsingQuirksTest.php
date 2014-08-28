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

class ParsingQuirksTest extends UnitTestCase
{
	
	function test_error_with_css()
	{
		$text = " div { font-weight: bold; } ";
		$template = new Template();
		$template->parse($text);
		
		$nodelist = $template->getRoot()->getNodelist();
		
		$this->assertEqual($text, $template->render());
		$this->assertIsA($nodelist[0], 'string');
	}
	
}
