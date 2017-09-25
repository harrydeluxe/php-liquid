<?php

/*
 * This file is part of the Liquid package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Liquid
 */

namespace Liquid\Tag;

use Liquid\TestCase;

class TagBreakTest extends TestCase
{
	public function testFor()
	{
		$this->assertTemplateResult(' ', '{%for item in array%} {%break%} yo {%endfor%}', array('array' => array(1, 2, 3, 4)));
		$this->assertTemplateResult(' yo ', '{%for item in array%} yo {%break%} {%endfor%}', array('array' => array(1, 2, 3, 4)));
		$this->assertTemplateResult('  1   2   ', '{%for item in array%} {%if item == 3%} {%break%} {%endif%} {{ item }} {%endfor%}', array('array' => array(1, 2, 3, 4)));
	}

	public function testRange()
	{
		$this->assertTemplateResult(' ', '{%for item in (3..6)%} {%break%} yo {%endfor%}');
		$this->assertTemplateResult(' yo ', '{%for item in (3..6)%} yo {%break%} {%endfor%}');
		$this->assertTemplateResult('  3   4   ', '{%for item in (3..6)%} {%if item == 5%} {%break%} {%endif%} {{ item }} {%endfor%}');
	}

	public function testTablerow()
	{
		$this->assertTemplateResult(
			"<tr class=\"row1\">\n</tr>\n",
			'{%tablerow item in array%} {%break%} yo {%endtablerow%}',
			array('array' => array(1, 2, 3, 4))
		);
		$this->assertTemplateResult(
			"<tr class=\"row1\">\n<td class=\"col1\"> yo </td></tr>\n",
			'{%tablerow item in array%} yo {%break%} {%endtablerow%}',
			array('array' => array(1, 2, 3, 4))
		);
		$this->assertTemplateResult(
			"<tr class=\"row1\">\n<td class=\"col1\">  1 </td><td class=\"col2\">  2 </td></tr>\n",
			'{%tablerow item in array%} {%if item == 3%} {%break%} {%endif%} {{ item }} {%endtablerow%}',
			array('array' => array(1, 2, 3, 4))
		);
	}
}
