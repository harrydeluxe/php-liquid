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
use Liquid\Template;

class TagForTest extends TestCase
{
	/**
	 * @expectedException \Liquid\Exception\ParseException
	 */
	public function testForInvalidSyntax()
	{
		$template = new Template();
		$template->parse("{% for elem %}{% endfor %}");
	}

	public function testFor()
	{
		$this->assertTemplateResult('', '{%for item in array%} yo {%endfor%}', array('array' => array()));
		$this->assertTemplateResult(' yo  yo  yo  yo ', '{%for item in array%} yo {%endfor%}', array('array' => array(1, 2, 3, 4)));
		$this->assertTemplateResult(' boo  boo  boo  boo ', '{%for item in array%} boo {%endfor%}', array('array' => new \ArrayIterator(array(1, 2, 3, 4))));
		$this->assertTemplateResult('yoyo', '{%for item in array%}yo{%endfor%}', array('array' => array(1, 2)));
		$this->assertTemplateResult(' yo ', '{%for item in array%} yo {%endfor%}', array('array' => array(1)));
		$this->assertTemplateResult('', '{%for item in array%}{%endfor%}', array('array' => array(1, 2)));

		$expected = <<<HERE

  yo

  yo

  yo

HERE;
		$template = <<<HERE
{%for item in array%}
  yo
{%endfor%}
HERE;
		$this->assertTemplateResult($expected, $template, array('array' => array(1, 2, 3)));
	}

	public function testForWithVariable()
	{
		$this->assertTemplateResult(' 1  2  3 ', '{%for item in array%} {{item}} {%endfor%}', array('array' => array(1, 2, 3)));
		$this->assertTemplateResult('123', '{%for item in array%}{{item}}{%endfor%}', array('array' => array(1, 2, 3)));
		$this->assertTemplateResult('123', '{% for item in array %}{{item}}{% endfor %}', array('array' => array(1, 2, 3)));
		$this->assertTemplateResult('abcd', '{%for item in array%}{{item}}{%endfor%}', array('array' => array('a', 'b', 'c', 'd')));
		$this->assertTemplateResult('a b c', '{%for item in array%}{{item}}{%endfor%}', array('array' => array('a', ' ', 'b', ' ', 'c')));
		$this->assertTemplateResult('abc', '{%for item in array%}{{item}}{%endfor%}', array('array' => array('a', '', 'b', '', 'c')));
	}
	
	public function testForWithHash()
	{
		$this->assertTemplateResult('a=b c=d e=f ', '{%for item in array%}{{item[0]}}={{item[1]}} {%endfor%}', array('array' => array('a' => 'b', 'c' => 'd', 'e' => 'f')));
	}

	public function testForHelpers()
	{
		$assigns = array('array' => array(1, 2, 3));

		$this->assertTemplateResult(' 1/3  2/3  3/3 ', '{%for item in array%} {{forloop.index}}/{{forloop.length}} {%endfor%}', $assigns);
		$this->assertTemplateResult(' 1  2  3 ', '{%for item in array%} {{forloop.index}} {%endfor%}', $assigns);
		$this->assertTemplateResult(' 0  1  2 ', '{%for item in array%} {{forloop.index0}} {%endfor%}', $assigns);
		$this->assertTemplateResult(' 2  1  0 ', '{%for item in array%} {{forloop.rindex0}} {%endfor%}', $assigns);
		$this->assertTemplateResult(' 3  2  1 ', '{%for item in array%} {{forloop.rindex}} {%endfor%}', $assigns);
		$this->assertTemplateResult(' 1  0  0 ', '{%for item in array%} {{forloop.first}} {%endfor%}', $assigns);
		$this->assertTemplateResult(' 0  0  1 ', '{%for item in array%} {{forloop.last}} {%endfor%}', $assigns);
	}

	public function testForHelpersWithOffsetAndLimit()
	{
		$assigns = array('array' => array(0, 1, 2, 3, 4));

		$this->assertTemplateResult(' 1/3  2/3  3/3 ', '{%for item in array offset:1 limit:3%} {{forloop.index}}/{{forloop.length}} {%endfor%}', $assigns);
		$this->assertTemplateResult(' 1  2  3 ', '{%for item in array offset:1 limit:3%} {{forloop.index}} {%endfor%}', $assigns);
		$this->assertTemplateResult(' 0  1  2 ', '{%for item in array offset:1 limit:3%} {{forloop.index0}} {%endfor%}', $assigns);
		$this->assertTemplateResult(' 2  1  0 ', '{%for item in array offset:1 limit:3%} {{forloop.rindex0}} {%endfor%}', $assigns);
		$this->assertTemplateResult(' 3  2  1 ', '{%for item in array offset:1 limit:3%} {{forloop.rindex}} {%endfor%}', $assigns);
		$this->assertTemplateResult(' 1  0  0 ', '{%for item in array offset:1 limit:3%} {{forloop.first}} {%endfor%}', $assigns);
		$this->assertTemplateResult(' 0  0  1 ', '{%for item in array offset:1 limit:3%} {{forloop.last}} {%endfor%}', $assigns);
	}

	public function testForAndIf()
	{
		$assigns = array('array' => array(1, 2, 3));
		$this->assertTemplateResult(' yay     ', '{%for item in array%} {% if forloop.first %}yay{% endif %} {%endfor%}', $assigns);
		$this->assertTemplateResult(' yay  boo  boo ', '{%for item in array%} {% if forloop.first %}yay{% else %}boo{% endif %} {%endfor%}', $assigns);
		$this->assertTemplateResult('   boo  boo ', '{%for item in array%} {% if forloop.first %}{% else %}boo{% endif %} {%endfor%}', $assigns);
	}

	public function testLimiting()
	{
		$assigns = array('array' => array(1, 2, 3, 4, 5, 6, 7, 8, 9, 0));
		$this->assertTemplateResult('12', '{%for i in array limit:2 %}{{ i }}{%endfor%}', $assigns);
		$this->assertTemplateResult('1234567890', '{%for i in array limit:20 %}{{ i }}{%endfor%}', $assigns);
		$this->assertTemplateResult('1234', '{%for i in array limit:4 %}{{ i }}{%endfor%}', $assigns);
		$this->assertTemplateResult('3456', '{%for i in array limit:4 offset:2 %}{{ i }}{%endfor%}', $assigns);
		$this->assertTemplateResult('3456', '{%for i in array limit: 4  offset: 2 %}{{ i }}{%endfor%}', $assigns);

		$assigns['limit'] = 2;
		$assigns['offset'] = 2;
		$this->assertTemplateResult('34', '{%for i in array limit: limit offset: offset %}{{ i }}{%endfor%}', $assigns);
	}

	public function testNestedFor()
	{
		$assigns = array('array' => array(array(1, 2), array(3, 4), array(5, 6)));
		$this->assertTemplateResult('123456', '{%for item in array%}{%for i in item%}{{ i }}{%endfor%}{%endfor%}', $assigns);
	}

	public function testOffsetOnly()
	{
		$assigns = array('array' => array(1, 2, 3, 4, 5, 6, 7, 8, 9, 0));
		$this->assertTemplateResult('890', '{%for i in array offset:7 %}{{ i }}{%endfor%}', $assigns);
	}

	public function testPauseResume()
	{
		$assigns = array('array' => array('items' => array(1, 2, 3, 4, 5, 6, 7, 8, 9, 0)));

		$markup = <<<MKUP
{%for i in array.items limit: 3 %}{{i}}{%endfor%}
next
{%for i in array.items offset:continue limit: 3 %}{{i}}{%endfor%}
next
{%for i in array.items offset:continue limit: 3 %}{{i}}{%endfor%}
MKUP;
		$expected = <<<XPCTD
123
next
456
next
789
XPCTD;
		$this->assertTemplateResult($expected, $markup, $assigns);
	}

	public function testPauseResumeLimit()
	{
		$assigns = array('array' => array('items' => array(1, 2, 3, 4, 5, 6, 7, 8, 9, 0)));

		$markup = <<<MKUP
{%for i in array.items limit: 3 %}{{i}}{%endfor%}
next
{%for i in array.items offset:continue limit: 3 %}{{i}}{%endfor%}
next
{%for i in array.items offset:continue limit: 1 %}{{i}}{%endfor%}
MKUP;
		$expected = <<<XPCTD
123
next
456
next
7
XPCTD;
		$this->assertTemplateResult($expected, $markup, $assigns);
	}

	public function testPauseResumeBIGLimit()
	{
		$assigns = array('array' => array('items' => array(1, 2, 3, 4, 5, 6, 7, 8, 9, 0)));

		$markup = <<<MKUP
{%for i in array.items limit: 3 %}{{i}}{%endfor%}
next
{%for i in array.items offset:continue limit: 3 %}{{i}}{%endfor%}
next
{%for i in array.items offset:continue limit: 1000 %}{{i}}{%endfor%}
MKUP;
		$expected = <<<XPCTD
123
next
456
next
7890
XPCTD;
		$this->assertTemplateResult($expected, $markup, $assigns);
	}

	public function testPauseResumeBIGOffset()
	{
		$assigns = array('array' => array('items' => array(1, 2, 3, 4, 5, 6, 7, 8, 9, 0)));

		$markup = <<<MKUP
{%for i in array.items limit: 3 %}{{i}}{%endfor%}
next
{%for i in array.items offset:continue limit: 3 %}{{i}}{%endfor%}
next
{%for i in array.items offset:continue limit: 1000 offset:1000 %}{{i}}{%endfor%}
MKUP;
		$expected = <<<XPCTD
123
next
456
next

XPCTD;
		$this->assertTemplateResult($expected, $markup, $assigns);
	}

	public function testForWithRanges()
	{
		$this->assertTemplateResult('123456789', '{%for i in (1..9)%}{{i}}{%endfor%}');
		$this->assertTemplateResult(' 9 10 11', '{%for i in (9..11)%} {{i}}{%endfor%}');
		$this->assertTemplateResult('9991000', '{%for i in (999..1000)%}{{i}}{%endfor%}');

		$assigns = array('variable' => 100);
		$this->assertTemplateResult('9596979899100', '{%for i in (95..variable)%}{{i}}{%endfor%}', $assigns);
	}
}
