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
 * Tests for standard liquid tags
 *
 * @author Viorel Dram
 *
 * @package Liquid
 */
class StandardTagTest extends LiquidTestcase
{
	public function testNoTransformation()
	{
		$this->assertTrueHelper('this text should come out of the template without change...', 'this text should come out of the template without change...');
		$this->assertTrueHelper('blah', 'blah');
		$this->assertTrueHelper('<blah>', '<blah>');
		$this->assertTrueHelper('|,.:', '|,.:');
		$this->assertTrueHelper('', '');

		$text = <<<END
this shouldnt see any transformation either but has multiple lines
as you can clearly see here ...
END;
		$this->assertTrueHelper($text, $text);
	}

	public function testHasABlockWhichDoesNothing()
	{
		$this->assertTrueHelper('the comment block should be removed {%comment%} be gone.. {%endcomment%} .. right?', 'the comment block should be removed  .. right?');
		$this->assertTrueHelper('{%comment%}{%endcomment%}', '');
		$this->assertTrueHelper('{%comment%}{% endcomment %}', '');
		$this->assertTrueHelper('{% comment %}{%endcomment%}', '');
		$this->assertTrueHelper('{% comment %}{% endcomment %}', '');
		$this->assertTrueHelper('{% comment %}comment{% endcomment %}', '');

		$this->assertTrueHelper('foo{%comment%}comment{%endcomment%}bar', 'foobar');
		$this->assertTrueHelper('foo{% comment %}comment{% endcomment %}bar', 'foobar');
		$this->assertTrueHelper('foo{% comment %} comment {% endcomment %}bar', 'foobar');

		$this->assertTrueHelper('foo {%comment%} {%endcomment%} bar', 'foo  bar');
		$this->assertTrueHelper('foo {%comment%}comment{%endcomment%} bar', 'foo  bar');
		$this->assertTrueHelper('foo {%comment%} comment {%endcomment%} bar', 'foo  bar');
	}

	public function testFor()
	{
		$this->assertTrueHelper('{%for item in array%} yo {%endfor%}', ' yo  yo  yo  yo ', array('array' => array(1, 2, 3, 4)));
		$this->assertTrueHelper('{%for item in array%}yo{%endfor%}', 'yoyo', array('array' => array(1, 2)));
		$this->assertTrueHelper('{%for item in array%} yo {%endfor%}', ' yo ', array('array' => array(1)));
		$this->assertTrueHelper('{%for item in array%}{%endfor%}', '', array('array' => array(1, 2)));
	}

	public function testForWithVariable()
	{
		$this->assertTrueHelper('{%for item in array%} {{item}} {%endfor%}', ' 1  2  3 ', array('array' => array(1, 2, 3)));
		$this->assertTrueHelper('{%for item in array%}{{item}}{%endfor%}', '123', array('array' => array(1, 2, 3)));
		$this->assertTrueHelper('{% for item in array %}{{item}}{% endfor %}', '123', array('array' => array(1, 2, 3)));
		$this->assertTrueHelper('{% for item in array %}{{item}}{% endfor %}', 'abc', array('array' => array('a', 'b', 'c')));
		$this->assertTrueHelper('{% for item in array %}{{item}}{% endfor %}', 'abc', array('array' => array('a', '', 'b', '', 'c')));
	}

	public function testForHelpers()
	{
		$template_data = array('array' => array(1, 2, 3));
		$this->assertTrueHelper('{%for item in array%} {{forloop.index}} {%endfor%}', ' 1  2  3 ', $template_data);
		$this->assertTrueHelper('{%for item in array%} {{forloop.index0}} {%endfor%}', ' 0  1  2 ', $template_data);
		$this->assertTrueHelper('{%for item in array%} {{forloop.rindex0}} {%endfor%}', ' 2  1  0 ', $template_data);
		$this->assertTrueHelper('{%for item in array%} {{forloop.rindex}} {%endfor%}', ' 3  2  1 ', $template_data);
		$this->assertTrueHelper('{%for item in array%} {{forloop.first}} {%endfor%}', ' 1  0  0 ', $template_data);
		$this->assertTrueHelper('{%for item in array%} {{forloop.last}} {%endfor%}', ' 0  0  1 ', $template_data);
		$this->assertTrueHelper('{%for item in array%} {{forloop.index}}/{{forloop.length}} {%endfor%}', ' 1/3  2/3  3/3 ', $template_data);
	}

	public function testForWithIf()
	{
		$template_data = array('array' => array(1, 2, 3));
		$this->assertTrueHelper('{%for item in array%}{% if forloop.first %}+{% else %}-{% endif %}{%endfor%}', '+--', $template_data);
	}

	public function testForLimiting()
	{
		$template_data = array('array' => array(1, 2, 3, 4, 5, 6, 7, 8, 9, 0));
		$this->assertTrueHelper('{%for i in array limit:2%}{{ i }}{%endfor%}', '12', $template_data);
		$this->assertTrueHelper('{%for i in array limit:4%}{{ i }}{%endfor%}', '1234', $template_data);
		$this->assertTrueHelper('{%for i in array limit:4 offset:2%}{{ i }}{%endfor%}', '3456', $template_data);
		$this->assertTrueHelper('{% for i in array limit : 4 offset : 2 %}{{ i }}{% endfor %}', '3456', $template_data);
	}

	public function testOffsetOnly()
	{
		$template_data = array('array' => array(1, 2, 3, 4, 5, 6, 7, 8, 9, 0));
		$this->assertTrueHelper('{%for i in array offset:7%}{{ i }}{%endfor%}', '890', $template_data);
	}

	public function testNestedFor()
	{
		$template_data = array('array' => array(array(1, 2), array(3, 4), array(5, 6)));
		$this->assertTrueHelper('{%for item in array%}{%for i in item%}{{ i }}{%endfor%}{%endfor%}', '123456', $template_data);
	}

	public function testCase()
	{
		$this->assertTrueHelper('{% case condition %}{% when 1 %} its 1 {% when 2 %} its 2 {% endcase %}', ' its 2 ', array('condition' => 2));
		$this->assertTrueHelper('{% case condition %}{% when 1 %} its 1 {% when 2 %} its 2 {% endcase %}', ' its 1 ', array('condition' => 1));
		$this->assertTrueHelper('{% case condition %}{% when 1 %} its 1 {% when 2 %} its 2 {% endcase %}', '', array('condition' => 3));
		$this->assertTrueHelper('{% case condition %}{% when 1 %}{% when "string here" %} hit {% endcase %}', ' hit ', array('condition' => 'string here'));
		$this->assertTrueHelper('{% case condition %}{% when 1 %}{% when "string here" %} hit {% endcase %}', '', array('condition' => 'bad string here'));
	}

	public function testCaseOnSize()
	{
		$this->assertTrueHelper('{% case a.size %}{% when 1 %}1{% when 2 %}2{% endcase %}', '', array('a' => array()));
		$this->assertTrueHelper('{% case a.size %}{% when 1 %}1{% when 2 %}2{% endcase %}', '1', array('a' => array(1)));
		$this->assertTrueHelper('{% case a.size %}{% when 1 %}1{% when 2 %}2{% endcase %}', '2', array('a' => array(1, 1)));
		$this->assertTrueHelper('{% case a.size %}{% when 1 %}1{% when 2 %}2{% endcase %}', '', array('a' => array(1, 1, 1)));
		$this->assertTrueHelper('{% case a.size %}{% when 1 %}1{% when 2 %}2{% endcase %}', '', array('a' => array(1, 1, 1, 1)));
		$this->assertTrueHelper('{% case a.size %}{% when 1 %}1{% when 2 %}2{% endcase %}', '', array('a' => array(1, 1, 1, 1, 1)));
		$this->assertTrueHelper('{% case a.size %}{% when 1 %}1{% when 2 %}2{% endcase %}', '', array('a' => array(1, 1, 1, 1, 1, 1)));
	}

	public function testCycle()
	{
		$this->assertTrueHelper('{%cycle "one", "two"%}', 'one');
		$this->assertTrueHelper('{%cycle "one", "two"%} {%cycle "one", "two"%}', 'one two');
		$this->assertTrueHelper('{%cycle "", "two"%} {%cycle "", "two"%}', ' two');
		$this->assertTrueHelper('{%cycle "one", "two"%} {%cycle "one", "two"%} {%cycle "one", "two"%}', 'one two one');
	}

	public function testSizeOfArray()
	{
		$this->assertTrueHelper('array has {{ array.size }} elements', 'array has 4 elements', array('array' => array(1, 2, 3, 4)));
	}
}
