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

class TagIfTest extends TestCase
{
	public function testTrueEqlTrue()
	{
		$text = " {% if true == true %} true {% else %} false {% endif %} ";
		$expected = "  true  ";
		$this->assertTemplateResult($expected, $text);
	}

	public function testTrueNotEqlTrue()
	{
		$text = " {% if true != true %} true {% else %} false {% endif %} ";
		$expected = "  false  ";
		$this->assertTemplateResult($expected, $text);
	}

	public function testTrueLqTrue()
	{
		$text = " {% if 0 > 0 %} true {% else %} false {% endif %} ";
		$expected = "  false  ";
		$this->assertTemplateResult($expected, $text);
	}

	public function testOneLqZero()
	{
		$text = " {% if 1 > 0 %} true {% else %} false {% endif %} ";
		$expected = "  true  ";
		$this->assertTemplateResult($expected, $text);
	}

	public function testZeroLqOne()
	{
		$text = " {% if 0 < 1 %} true {% else %} false {% endif %} ";
		$expected = "  true  ";
		$this->assertTemplateResult($expected, $text);
	}

	public function testZeroLqOrEqualOne()
	{
		$text = " {% if 0 <= 0 %} true {% else %} false {% endif %} ";
		$expected = "  true  ";
		$this->assertTemplateResult($expected, $text);
	}

	public function testZeroLqOrEqualOneInvolvingNil()
	{
		$text = " {% if null <= 0 %} true {% else %} false {% endif %} ";
		$expected = "  false  ";
		$this->assertTemplateResult($expected, $text);


		$text = " {% if 0 <= null %} true {% else %} false {% endif %} ";
		$expected = "  false  ";
		$this->assertTemplateResult($expected, $text);
	}

	public function testZeroLqqOrEqualOne()
	{
		$text = " {% if 0 >= 0 %} true {% else %} false {% endif %} ";
		$expected = "  true  ";
		$this->assertTemplateResult($expected, $text);
	}

	public function testStrings()
	{
		$text = " {% if 'test' == 'test' %} true {% else %} false {% endif %} ";
		$expected = "  true  ";
		$this->assertTemplateResult($expected, $text);
	}

	public function testStringsNotEqual()
	{
		$text = " {% if 'test' != 'test' %} true {% else %} false {% endif %} ";
		$expected = "  false  ";
		$this->assertTemplateResult($expected, $text);
	}

	public function testVarStringsEqual()
	{
		$text = " {% if var == \"hello there!\" %} true {% else %} false {% endif %} ";
		$expected = "  true  ";
		$this->assertTemplateResult($expected, $text, array('var' => 'hello there!'));
	}

	public function testVarStringsAreNotEqual()
	{
		$text = " {% if \"hello there!\" == var %} true {% else %} false {% endif %} ";
		$expected = "  true  ";
		$this->assertTemplateResult($expected, $text, array('var' => 'hello there!'));
	}

	public function testVarAndLongStringAreEqual()
	{
		$text = " {% if var == 'hello there!' %} true {% else %} false {% endif %} ";
		$expected = "  true  ";
		$this->assertTemplateResult($expected, $text, array('var' => 'hello there!'));
	}

	public function testVarAndLongStringAreEqualBackwards()
	{
		$text = " {% if 'hello there!' == var %} true {% else %} false {% endif %} ";
		$expected = "  true  ";
		$this->assertTemplateResult($expected, $text, array('var' => 'hello there!'));
	}

	public function testIsCollectionEmpty()
	{
		$text = " {% if array == empty %} true {% else %} false {% endif %} ";
		$expected = "  true  ";
		$this->assertTemplateResult($expected, $text, array('array' => array()));

		$text = " {% if empty == array %} true {% else %} false {% endif %} ";
		$expected = "  true  ";
		$this->assertTemplateResult($expected, $text, array('array' => array()));
	}

	public function testIsNotCollectionEmpty()
	{
		$text = " {% if array == empty %} true {% else %} false {% endif %} ";
		$expected = "  false  ";
		$this->assertTemplateResult($expected, $text, array('array' => array(1, 2, 3)));
	}

	public function testNil()
	{
		$text = " {% if var == null %} true {% else %} false {% endif %} ";
		$expected = "  true  ";
		$this->assertTemplateResult($expected, $text, array('var' => null));

		$text = " {% if var == null %} true {% else %} false {% endif %} ";
		$expected = "  true  ";
		$this->assertTemplateResult($expected, $text, array('var' => null));
	}

	public function testNotNil()
	{
		$text = " {% if var != null %} true {% else %} false {% endif %} ";
		$expected = "  true  ";
		$this->assertTemplateResult($expected, $text, array('var' => 1));

		$text = " {% if var != null %} true {% else %} false {% endif %} ";
		$expected = "  true  ";
		$this->assertTemplateResult($expected, $text, array('var' => 1));
	}

	public function testIfFromVariable()
	{
		$this->assertTemplateResult('', '{% if var %} NO {% endif %}', array('var' => false));
		$this->assertTemplateResult('', '{% if var %} NO {% endif %}', array('var' => null));
		$this->assertTemplateResult('', '{% if foo.bar %} NO {% endif %}', array('foo' => array('bar' => false)));
		$this->assertTemplateResult('', '{% if foo.bar %} NO {% endif %}', array('foo' => array()));
		$this->assertTemplateResult('', '{% if foo.bar %} NO {% endif %}', array('foo' => null));

		$this->assertTemplateResult(' YES ', '{% if var %} YES {% endif %}', array('var' => "text"));
		$this->assertTemplateResult(' YES ', '{% if var %} YES {% endif %}', array('var' => true));
		$this->assertTemplateResult(' YES ', '{% if var %} YES {% endif %}', array('var' => 1));
		$this->assertTemplateResult(' YES ', '{% if "foo" %} YES {% endif %}');
		$this->assertTemplateResult(' YES ', '{% if foo.bar %} YES {% endif %}', array('foo' => array('bar' => true)));
		$this->assertTemplateResult(' YES ', '{% if foo.bar %} YES {% endif %}', array('foo' => array('bar' => "text")));
		$this->assertTemplateResult(' YES ', '{% if foo.bar %} YES {% endif %}', array('foo' => array('bar' => 1)));

		$this->assertTemplateResult(' YES ', '{% if var %} NO {% else %} YES {% endif %}', array('var' => false));
		$this->assertTemplateResult(' YES ', '{% if var %} NO {% else %} YES {% endif %}', array('var' => null));
		$this->assertTemplateResult(' YES ', '{% if var %} YES {% else %} NO {% endif %}', array('var' => true));
		$this->assertTemplateResult(' YES ', '{% if "foo" %} YES {% else %} NO {% endif %}', array('var' => "text"));

		$this->assertTemplateResult(' YES ', '{% if foo.bar %} NO {% else %} YES {% endif %}', array('foo' => array('bar' => false)));
		$this->assertTemplateResult(' YES ', '{% if foo.bar %} YES {% else %} NO {% endif %}', array('foo' => array('bar' => true)));
		$this->assertTemplateResult(' YES ', '{% if foo.bar %} YES {% else %} NO {% endif %}', array('foo' => array('bar' => "text")));
		$this->assertTemplateResult(' YES ', '{% if foo.bar %} NO {% else %} YES {% endif %}', array('foo' => array('notbar' => true)));
		$this->assertTemplateResult(' YES ', '{% if foo.bar %} NO {% else %} YES {% endif %}', array('foo' => array()));
		$this->assertTemplateResult(' YES ', '{% if foo.bar %} NO {% else %} YES {% endif %}', array('notfoo' => array('bar' => true)));
	}

	public function testNestedIf()
	{
		$this->assertTemplateResult('', '{% if false %}{% if false %} NO {% endif %}{% endif %}');
		$this->assertTemplateResult('', '{% if false %}{% if true %} NO {% endif %}{% endif %}');
		$this->assertTemplateResult('', '{% if true %}{% if false %} NO {% endif %}{% endif %}');
		$this->assertTemplateResult(' YES ', '{% if true %}{% if true %} YES {% endif %}{% endif %}');

		$this->assertTemplateResult(' YES ', '{% if true %}{% if true %} YES {% else %} NO {% endif %}{% else %} NO {% endif %}');
		$this->assertTemplateResult(' YES ', '{% if true %}{% if false %} NO {% else %} YES {% endif %}{% else %} NO {% endif %}');
		$this->assertTemplateResult(' YES ', '{% if false %}{% if true %} NO {% else %} NONO {% endif %}{% else %} YES {% endif %}');
	}

	public function testComplexConditions()
	{
		$this->assertTemplateResult('true', '{% if 10 == 10 and "h" == "h" %}true{% else %}false{% endif %}');
		$this->assertTemplateResult('true', '{% if 8 == 10 or "h" == "h" %}true{% else %}false{% endif %}');
		$this->assertTemplateResult('false', '{% if 8 == 10 and "h" == "h" %}true{% else %}false{% endif %}');
		$this->assertTemplateResult('true', '{% if 10 == 10 or "h" == "k" or "k" == "k" %}true{% else %}false{% endif %}');
	}

	public function testContains()
	{
		$this->assertTemplateResult('true', '{% if foo contains "h" %}true{% else %}false{% endif %}', array('foo' => array('k', 'h', 'z')));
		$this->assertTemplateResult('false', '{% if foo contains "y" %}true{% else %}false{% endif %}', array('foo' => array('k', 'h', 'z')));
		$this->assertTemplateResult('true', '{% if foo contains "e" %}true{% else %}false{% endif %}', array('foo' => 'abcedf'));
		$this->assertTemplateResult('true', '{% if foo contains "e" %}true{% else %}false{% endif %}', array('foo' => 'e'));
		$this->assertTemplateResult('false', '{% if foo contains "y" %}true{% else %}false{% endif %}', array('foo' => 'abcedf'));
	}

	/**
	 * @expectedException \Liquid\Exception\ParseException
	 * @expectedExceptionMessage if tag was never closed
	 */
	public function testSyntaxErrorNotClosed()
	{
		$this->assertTemplateResult('', '{% if jerry == 1 %}');
	}

	/**
	 * @expectedException \Liquid\Exception\ParseException
	 */
	public function testSyntaxErrorEnd()
	{
		$this->assertTemplateResult('', '{% if jerry == 1 %}{% end %}');
	}

	/**
	 * @expectedException \Liquid\Exception\RenderException
	 */
	public function testInvalidOperator()
	{
		$this->assertTemplateResult('', '{% if foo === y %}true{% else %}false{% endif %}', array('foo' => true, 'y' => true));
	}

	/**
	 * @expectedException \Liquid\Exception\RenderException
	 */
	public function testIncomparable()
	{
		$this->assertTemplateResult('', '{% if foo == 1 %}true{% endif %}', array('foo' => (object) array()));
	}

	/**
	 * @expectedException \Liquid\Exception\ParseException
	 * @expectedExceptionMessage does not expect else tag
	 */
	public function testSyntaxErrorElse()
	{
		$this->assertTemplateResult('', '{% if foo == 1 %}{% endif %}{% else %}');
	}

	/**
	 * @expectedException \Liquid\Exception\ParseException
	 * @expectedExceptionMessage Unknown tag
	 */
	public function testSyntaxErrorUnknown()
	{
		$this->assertTemplateResult('', '{% unknown-tag %}');
	}
}
