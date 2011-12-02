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
 * Testcase class
 *
 * @author Viorel Dram
 *
 * @package Liquid
 */
class LiquidTestcase extends PHPUnit_Framework_Testcase
{
	/**
	 * Helper class used to test the true assertion
	 * You only need to specify the liquid template and what is the
	 * expected rendered result
	 *
	 * @param string $templateString The template
	 * @param string $expected What to expect after the template is rendered
	 * @param string $data Optional values passed to the template
	 * @return void
	 */
	public function assertTrueHelper($templateString, $expected, $data = array())
	{
		$template = new LiquidTemplate;
		$template->parse($templateString);
		$this->assertTrue($template->render($data) === $expected);
	}
}
