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



class HtmlTagTest extends LiquidTestCase
{
	
	function test_html_table()
	{
    	$this->assert_template_result("<tr class=\"row1\">\n<td class=\"col1\"> 1 </td><td class=\"col2\"> 2 </td><td class=\"col3\"> 3 </td></tr>\n<tr class=\"row2\"><td class=\"col1\"> 4 </td><td class=\"col2\"> 5 </td><td class=\"col3\"> 6 </td></tr>\n",
                           '{% tablerow n in numbers cols:3%} {{n}} {% endtablerow %}', 
                           array('numbers' => array(1,2,3,4,5,6)));		
		
		
    	$this->assert_template_result("<tr class=\"row1\">\n</tr>\n",
                            '{% tablerow n in numbers cols:3%} {{n}} {% endtablerow %}', 
                            array('numbers' => array()));
	}

	function test_html_table_with_different_cols() {
		$this->assert_template_result("<tr class=\"row1\">\n<td class=\"col1\"> 1 </td><td class=\"col2\"> 2 </td><td class=\"col3\"> 3 </td><td class=\"col4\"> 4 </td><td class=\"col5\"> 5 </td></tr>\n<tr class=\"row2\"><td class=\"col1\"> 6 </td></tr>\n",
                           '{% tablerow n in numbers cols:5%} {{n}} {% endtablerow %}', 
                           array('numbers' => array(1,2,3,4,5,6)));		
		
	}
}