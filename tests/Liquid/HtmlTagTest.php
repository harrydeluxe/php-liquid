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

class HtmlTagTest extends TestCase
{
	public function test_html_table()
	{
    	$this->assertTemplateResult("<tr class=\"row1\">\n<td class=\"col1\"> 1 </td><td class=\"col2\"> 2 </td><td class=\"col3\"> 3 </td></tr>\n<tr class=\"row2\"><td class=\"col1\"> 4 </td><td class=\"col2\"> 5 </td><td class=\"col3\"> 6 </td></tr>\n",
                           '{% tablerow n in numbers cols:3%} {{n}} {% endtablerow %}', 
                           array('numbers' => array(1,2,3,4,5,6)));		
		
		
    	$this->assertTemplateResult("<tr class=\"row1\">\n</tr>\n",
                            '{% tablerow n in numbers cols:3%} {{n}} {% endtablerow %}', 
                            array('numbers' => array()));
	}

	public function test_html_table_with_different_cols() {
		$this->assertTemplateResult("<tr class=\"row1\">\n<td class=\"col1\"> 1 </td><td class=\"col2\"> 2 </td><td class=\"col3\"> 3 </td><td class=\"col4\"> 4 </td><td class=\"col5\"> 5 </td></tr>\n<tr class=\"row2\"><td class=\"col1\"> 6 </td></tr>\n",
                           '{% tablerow n in numbers cols:5%} {{n}} {% endtablerow %}', 
                           array('numbers' => array(1,2,3,4,5,6)));		
		
	}
}
