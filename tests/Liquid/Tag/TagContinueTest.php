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

class TagContinueTest extends TestCase
{
	public function testFor()
	{
		$this->assertTemplateResult('    ', '{%for item in array%} {%continue%} yo {%endfor%}', array('array' => array(1, 2, 3, 4)));
		$this->assertTemplateResult(' yo  yo  yo  yo ', '{%for item in array%} yo {%continue%} {%endfor%}', array('array' => array(1, 2, 3, 4)));
		$this->assertTemplateResult('  1   2     4 ', '{%for item in array%} {%if item == 3%} {%continue%} {%endif%} {{ item }} {%endfor%}', array('array' => array(1, 2, 3, 4)));
	}

	public function testRange()
	{
		$this->assertTemplateResult('    ', '{%for item in (3..6)%} {%continue%} yo {%endfor%}');
		$this->assertTemplateResult(' yo  yo  yo  yo ', '{%for item in (3..6)%} yo {%continue%} {%endfor%}');
		$this->assertTemplateResult('  3   4     6 ', '{%for item in (3..6)%} {%if item == 5%} {%continue%} {%endif%} {{ item }} {%endfor%}');
	}

	public function testTablerow()
	{
		$this->assertTemplateResult(
			"<tr class=\"row1\">\n</tr>\n",
			'{%tablerow item in array%} {%continue%} yo {%endtablerow%}',
			array('array' => array(1, 2, 3, 4))
		);
		$this->assertTemplateResult(
			"<tr class=\"row1\">\n<td class=\"col1\"> yo </td><td class=\"col2\"> yo </td><td class=\"col3\"> yo </td><td class=\"col4\"> yo </td></tr>\n",
			'{%tablerow item in array%} yo {%continue%} {%endtablerow%}',
			array('array' => array(1, 2, 3, 4))
		);
		$this->assertTemplateResult(
			"<tr class=\"row1\">\n<td class=\"col1\">  1 </td><td class=\"col2\">  2 </td><td class=\"col3\">  4 </td></tr>\n",
			'{%tablerow item in array%} {%if item == 3%} {%continue%} {%endif%} {{ item }} {%endtablerow%}',
			array('array' => array(1, 2, 3, 4))
		);
	}
}
