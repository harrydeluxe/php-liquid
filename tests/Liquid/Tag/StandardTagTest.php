<?php

/**
 * This file is part of the Liquid package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Liquid
 */

namespace Liquid\Tag;

use Liquid\FileSystem;
use Liquid\TestCase;
use Liquid\Template;

/**
 * Helper FileSytem
 */
class LiquidTestFileSystem implements FileSystem
{
	/**
	 * @param string $templatePath
	 *
	 * @return string
	 */
	public function readTemplateFile($templatePath) {
		if ($templatePath == 'inner') {
			return "Inner: {{ inner }}{{ other }}";
		}

		return '';
	}
}

class LiquidStandardTagTest extends TestCase
{
	function test_no_transform() {

		$this->assertTemplateResult('this text should come out of the template without change...',
			'this text should come out of the template without change...');

		$this->assertTemplateResult('blah', 'blah');
		$this->assertTemplateResult('<blah>', '<blah>');
		$this->assertTemplateResult('|,.:', '|,.:');
		$this->assertTemplateResult('', '');

		$text = "this shouldnt see any transformation either but has multiple lines
 	     	              as you can clearly see here ...";

		$this->assertTemplateResult($text, $text);
	}

	function test_has_a_block_which_does_nothing() {
		$this->assertTemplateResult("the comment block should be removed  .. right?",
			"the comment block should be removed {%comment%} be gone.. {%endcomment%} .. right?");

		$this->assertTemplateResult('', '{%comment%}{%endcomment%}');
		$this->assertTemplateResult('', '{%comment%}{% endcomment %}');
		$this->assertTemplateResult('', '{% comment %}{%endcomment%}');
		$this->assertTemplateResult('', '{% comment %}{% endcomment %}');
		$this->assertTemplateResult('', '{%comment%}comment{%endcomment%}');
		$this->assertTemplateResult('', '{% comment %}comment{% endcomment %}');

		$this->assertTemplateResult('foobar', 'foo{%comment%}comment{%endcomment%}bar');
		$this->assertTemplateResult('foobar', 'foo{% comment %}comment{% endcomment %}bar');
		$this->assertTemplateResult('foobar', 'foo{%comment%} comment {%endcomment%}bar');
		$this->assertTemplateResult('foobar', 'foo{% comment %} comment {% endcomment %}bar');

		$this->assertTemplateResult('foo  bar', 'foo {%comment%} {%endcomment%} bar');
		$this->assertTemplateResult('foo  bar', 'foo {%comment%}comment{%endcomment%} bar');
		$this->assertTemplateResult('foo  bar', 'foo {%comment%} comment {%endcomment%} bar');

		$this->assertTemplateResult('foobar', 'foo{%comment%}
 	                                     {%endcomment%}bar');
	}

	function test_for() {
		$this->assertTemplateResult(' yo  yo  yo  yo ', '{%for item in array%} yo {%endfor%}', array('array' => array(1, 2, 3, 4)));
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

	function test_for_with_variable() {
		$this->assertTemplateResult(' 1  2  3 ', '{%for item in array%} {{item}} {%endfor%}', array('array' => array(1, 2, 3)));
		$this->assertTemplateResult('123', '{%for item in array%}{{item}}{%endfor%}', array('array' => array(1, 2, 3)));
		$this->assertTemplateResult('123', '{% for item in array %}{{item}}{% endfor %}', array('array' => array(1, 2, 3)));
		$this->assertTemplateResult('abcd', '{%for item in array%}{{item}}{%endfor%}', array('array' => array('a', 'b', 'c', 'd')));
		$this->assertTemplateResult('a b c', '{%for item in array%}{{item}}{%endfor%}', array('array' => array('a', ' ', 'b', ' ', 'c')));
		$this->assertTemplateResult('abc', '{%for item in array%}{{item}}{%endfor%}', array('array' => array('a', '', 'b', '', 'c')));
	}

	function test_for_helpers() {
		$assigns = array('array' => array(1, 2, 3));

		$this->assertTemplateResult(' 1/3  2/3  3/3 ', '{%for item in array%} {{forloop.index}}/{{forloop.length}} {%endfor%}', $assigns);
		$this->assertTemplateResult(' 1  2  3 ', '{%for item in array%} {{forloop.index}} {%endfor%}', $assigns);
		$this->assertTemplateResult(' 0  1  2 ', '{%for item in array%} {{forloop.index0}} {%endfor%}', $assigns);
		$this->assertTemplateResult(' 2  1  0 ', '{%for item in array%} {{forloop.rindex0}} {%endfor%}', $assigns);
		$this->assertTemplateResult(' 3  2  1 ', '{%for item in array%} {{forloop.rindex}} {%endfor%}', $assigns);
		$this->assertTemplateResult(' 1  0  0 ', '{%for item in array%} {{forloop.first}} {%endfor%}', $assigns);
		$this->assertTemplateResult(' 0  0  1 ', '{%for item in array%} {{forloop.last}} {%endfor%}', $assigns);
	}

	function test_for_and_if() {
		$assigns = array('array' => array(1, 2, 3));
		$this->assertTemplateResult(' yay     ', '{%for item in array%} {% if forloop.first %}yay{% endif %} {%endfor%}', $assigns);
		$this->assertTemplateResult(' yay  boo  boo ', '{%for item in array%} {% if forloop.first %}yay{% else %}boo{% endif %} {%endfor%}', $assigns);
		$this->assertTemplateResult('   boo  boo ', '{%for item in array%} {% if forloop.first %}{% else %}boo{% endif %} {%endfor%}', $assigns);
	}

	function test_limiting() {
		$assigns = array('array' => array(1, 2, 3, 4, 5, 6, 7, 8, 9, 0));
		$this->assertTemplateResult('12', '{%for i in array limit:2 %}{{ i }}{%endfor%}', $assigns);
		$this->assertTemplateResult('1234', '{%for i in array limit:4 %}{{ i }}{%endfor%}', $assigns);
		$this->assertTemplateResult('3456', '{%for i in array limit:4 offset:2 %}{{ i }}{%endfor%}', $assigns);
		$this->assertTemplateResult('3456', '{%for i in array limit: 4  offset: 2 %}{{ i }}{%endfor%}', $assigns);

		$assigns['limit'] = 2;
		$assigns['offset'] = 2;
		$this->assertTemplateResult('34', '{%for i in array limit: limit offset: offset %}{{ i }}{%endfor%}', $assigns);
	}

	function test_nested_for() {
		$assigns = array('array' => array(array(1, 2), array(3, 4), array(5, 6)));
		$this->assertTemplateResult('123456', '{%for item in array%}{%for i in item%}{{ i }}{%endfor%}{%endfor%}', $assigns);
	}

	function test_offset_only() {
		$assigns = array('array' => array(1, 2, 3, 4, 5, 6, 7, 8, 9, 0));
		$this->assertTemplateResult('890', '{%for i in array offset:7 %}{{ i }}{%endfor%}', $assigns);
	}

	function test_pause_resume() {
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

	function test_pause_resume_limit() {
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

	function test_pause_resume_BIG_limit() {
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

	function test_pause_resume_BIG_offset() {
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

	function test_assign() {
		$assigns = array('var' => 'content');
		$this->assertTemplateResult('var2:  var2:content', 'var2:{{var2}} {%assign var2 = var%} var2:{{var2}}', $assigns);
	}

	function test_capture() {
		$assigns = array('var' => 'content');
		$this->assertTemplateResult('content foo content foo ', '{{ var2 }}{% capture var2 %}{{ var }} foo {% endcapture %}{{ var2 }}{{ var2 }}', $assigns);
	}

	function test_case() {
		$assigns = array('condition' => 2);
		$this->assertTemplateResult(' its 2 ', '{% case condition %}{% when 1 %} its 1 {% when 2 %} its 2 {% endcase %}', $assigns);

		$assigns = array('condition' => 1);
		$this->assertTemplateResult(' its 1 ', '{% case condition %}{% when 1 %} its 1 {% when 2 %} its 2 {% endcase %}', $assigns);

		$assigns = array('condition' => 3);
		$this->assertTemplateResult('', '{% case condition %}{% when 1 %} its 1 {% when 2 %} its 2 {% endcase %}', $assigns);

		$assigns = array('condition' => "string here");
		$this->assertTemplateResult(' hit ', '{% case condition %}{% when "string here" %} hit {% endcase %}', $assigns);

		$assigns = array('condition' => "bad string here");
		$this->assertTemplateResult('', '{% case condition %}{% when "string here" %} hit {% endcase %}', $assigns);
	}

	function test_case_with_else() {
		$assigns = array('condition' => 5);
		$this->assertTemplateResult(' hit ', '{% case condition %}{% when 5 %} hit {% else %} else {% endcase %}', $assigns);

		$assigns = array('condition' => 6);
		$this->assertTemplateResult(' else ', '{% case condition %}{% when 5 %} hit {% else %} else {% endcase %}', $assigns);
	}

	function test_cycle() {
		$this->assertTemplateResult('one', '{%cycle "one", "two"%}');
		$this->assertTemplateResult('one two', '{%cycle "one", "two"%} {%cycle "one", "two"%}');
		$this->assertTemplateResult('one two one', '{%cycle "one", "two"%} {%cycle "one", "two"%} {%cycle "one", "two"%}');

	}

	function test_multiple_cycles() {
		$this->assertTemplateResult('1 2 1 1 2 3 1', '{%cycle 1,2%} {%cycle 1,2%} {%cycle 1,2%} {%cycle 1,2,3%} {%cycle 1,2,3%} {%cycle 1,2,3%} {%cycle 1,2,3%}');
	}

	function test_multiple_named_cycles() {
		$this->assertTemplateResult('one one two two one one', '{%cycle 1: "one", "two" %} {%cycle 2: "one", "two" %} {%cycle 1: "one", "two" %} {%cycle 2: "one", "two" %} {%cycle 1: "one", "two" %} {%cycle 2: "one", "two" %}');
	}

	function test_multiple_named_cycles_with_names_from_context() {
		$assigns = array("var1" => 1, "var2" => 2);
		$this->assertTemplateResult('one one two two one one', '{%cycle var1: "one", "two" %} {%cycle var2: "one", "two" %} {%cycle var1: "one", "two" %} {%cycle var2: "one", "two" %} {%cycle var1: "one", "two" %} {%cycle var2: "one", "two" %}', $assigns);
	}

	function test_size_of_array() {
		$assigns = array('array1' => array(1, 2, 3, 4));
		$this->assertTemplateResult('array has 4 elements', "array has {{ array1.size }} elements", $assigns, null);
	}

	// this test is a superflous, but we'll include it for completion's sake
	function test_size_of_hash() {
		$assigns = array("hash" => array('a' => 1, 'b' => 2, 'c' => 3, 'd' => 4));
		$this->assertTemplateResult('hash has 4 elements', "hash has {{ hash.size }} elements", $assigns);
	}

	function test_hash_can_override_size() {
		$assigns = array("hash" => array('a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'size' => '5000'));
		$this->assertTemplateResult('hash has 5000 elements', "hash has {{ hash.size }} elements", $assigns);
	}

	function test_include_tag() {
		$template = new Template();
		$template->setFileSystem(new LiquidTestFileSystem());

		$template->parse("Outer-{% include 'inner' with 'value' other:23 %}-Outer{% include 'inner' for var other:'loop' %}");

		$output = $template->render(array("var" => array(1, 2, 3)));

		$this->assertEquals("Outer-Inner: value23-OuterInner: 1loopInner: 2loopInner: 3loop", $output);
	}

	function test_include_tag_no_with() {
		$template = new Template();
		$template->setFileSystem(new LiquidTestFileSystem());

		$template->parse("Outer-{% include 'inner' %}-Outer-{% include 'inner' other:'23' %}");

		$output = $template->render(array("inner" => "orig", "var" => array(1, 2, 3)));

		$this->assertEquals("Outer-Inner: orig-Outer-Inner: orig23", $output);
	}

}
