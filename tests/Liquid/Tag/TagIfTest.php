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

use Liquid\TestCase;

class TagIfTest extends TestCase
{

	public function testIf() {
		$this->assertTemplateResult('  ', ' {% if false %} this text should not go into the output {% endif %} ');
		$this->assertTemplateResult('  this text should go into the output  ',
			' {% if true %} this text should go into the output {% endif %} ');
		$this->assertTemplateResult('  you rock ?', '{% if false %} you suck {% endif %} {% if true %} you rock {% endif %}?');
	}

	public function testLiteralComparisons() {
		$this->assertTemplateResult(' NO ', '{% assign v = false %}{% if v %} YES {% else %} NO {% endif %}');
		$this->assertTemplateResult(' YES ', '{% assign v = nil %}{% if v == nil %} YES {% else %} NO {% endif %}');
	}

	public function test_if_else() {
		$this->assertTemplateResult(' YES ', '{% if false %} NO {% else %} YES {% endif %}');
		$this->assertTemplateResult(' YES ', '{% if true %} YES {% else %} NO {% endif %}');
		$this->assertTemplateResult(' YES ', '{% if "foo" %} YES {% else %} NO {% endif %}');
	}

	public function test_if_boolean() {
		$this->assertTemplateResult(' YES ', '{% if var %} YES {% endif %}', array('var' => true));
	}

	public function test_if_or() {
		$this->assertTemplateResult(' YES ', '{% if a or b %} YES {% endif %}', array('a' => true, 'b' => true));
		$this->assertTemplateResult(' YES ', '{% if a or b %} YES {% endif %}', array('a' => true, 'b' => false));
		$this->assertTemplateResult(' YES ', '{% if a or b %} YES {% endif %}', array('a' => false, 'b' => true));
		$this->assertTemplateResult('',      '{% if a or b %} YES {% endif %}', array('a' => false, 'b' => false));

		$this->assertTemplateResult(' YES ', '{% if a or b or c %} YES {% endif %}', array('a' => false, 'b' => false, 'c' => true));
		$this->assertTemplateResult('',      '{% if a or b or c %} YES {% endif %}', array('a' => false, 'b' => false, 'c' => false));
	}

	public function test_if_or_with_operators() {
		$this->assertTemplateResult(' YES ', '{% if a == true or b == true %} YES {% endif %}', array('a' => true, 'b' => true));
		$this->assertTemplateResult(' YES ', '{% if a == true or b == false %} YES {% endif %}', array('a' => true, 'b' => true));
		$this->assertTemplateResult('', '{% if a == false or b == false %} YES {% endif %}', array('a' => true, 'b' => true));
	}

	// public function test_comparison_of_strings_containing_and_or_or() {
	// 	$awful_markup = "a == 'and' and b == 'or' and c == 'foo and bar' and d == 'bar or baz' and e == 'foo' and foo and bar";
	// 	$assigns = array('a' => 'and', 'b' => 'or', 'c' => 'foo and bar', 'd' => 'bar or baz', 'e' => 'foo', 'foo' => true, 'bar' => true);
	// 	$this->assertTemplateResult(' YES ', "{% if {$awful_markup} %} YES {% endif %}", $assigns);
	// }

	public function test_comparison_of_expressions_starting_with_and_or_or() {
		$assigns = array('order' => array('items_count' => 0), 'android' => array('name' => 'Roy'));
		$this->assertTemplateResult('YES', "{% if android.name == 'Roy' %}YES{% endif %}", $assigns);
		$this->assertTemplateResult('YES', "{% if order.items_count == 0 %}YES{% endif %}", $assigns);
	}

	public function test_if_and() {
		$this->assertTemplateResult(' YES ', '{% if true and true %} YES {% endif %}');
		$this->assertTemplateResult('', '{% if false and true %} YES {% endif %}');
		$this->assertTemplateResult('', '{% if false and true %} YES {% endif %}');
	}
	
	public function test_hash_miss_generates_false() {
		$this->assertTemplateResult('', '{% if foo.bar %} NO {% endif %}', array('foo' => array()));
	}

	public function test_multiple_conditions() {
		$tpl = "{% if a or b and c %}true{% else %}false{% endif %}";
		$tests = array(
			array(true, true, true, 'true'),
			array(true, true, false, 'true'),
			array(true, false, true, 'true'),
			array(true, false, false, 'true'),
			array(false, true, true, 'true'),
			array(false, true, false, 'false'),
			array(false, false, true, 'false'),
			array(false, false, false, 'false'),
		);
		foreach ($tests as $args) {
			$this->assertTemplateResult($args[3], $tpl, array('a' => $args[0], 'b' => $args[1], 'c' => $args[2]));
		}
	}

	public function test_comparisons_on_null() {
		$this->assertTemplateResult('', '{% if null < 10 %} NO {% endif %}');
		$this->assertTemplateResult('', '{% if null <= 10 %} NO {% endif %}');
		$this->assertTemplateResult('', '{% if null >= 10 %} NO {% endif %}');
		$this->assertTemplateResult('', '{% if null > 10 %} NO {% endif %}');

		$this->assertTemplateResult('', '{% if 10 < null %} NO {% endif %}');
		$this->assertTemplateResult('', '{% if 10 <= null %} NO {% endif %}');
		$this->assertTemplateResult('', '{% if 10 >= null %} NO {% endif %}');
		$this->assertTemplateResult('', '{% if 10 > null %} NO {% endif %}');
	}

	public function test_else_if() {
		$this->assertTemplateResult('0', '{% if 0 == 0 %}0{% elsif 1 == 1%}1{% else %}2{% endif %}');
		$this->assertTemplateResult('1', '{% if 0 != 0 %}0{% elsif 1 == 1%}1{% else %}2{% endif %}');
		$this->assertTemplateResult('2', '{% if 0 != 0 %}0{% elsif 1 != 1%}1{% else %}2{% endif %}');
		$this->assertTemplateResult('elsif', '{% if false %}if{% elsif true %}elsif{% endif %}');
	}

	public function testZero() {
		$this->assertTemplateResult("  true  ", " {% if 0 %} true {% else %} false {% endif %} ");
	}

	public function testEmptyString() {
		$text = " {% if emptyString %} true {% else %} false {% endif %} ";
		$expected = "  true  ";
		$this->assertTemplateResult($expected, $text, array('emptyString' => ''));
	}

	public function testEmptyArray() {
		$text = " {% if emptyArray %} true {% else %} false {% endif %} ";
		$expected = "  true  ";
		$this->assertTemplateResult($expected, $text, array('emptyArray' => array()));
	}

	public function testTrueEqlTrue() {
		$text = " {% if true == true %} true {% else %} false {% endif %} ";
		$expected = "  true  ";
		$this->assertTemplateResult($expected, $text);
	}

	public function testTrueNotEqlTrue() {
		$text = " {% if true != true %} true {% else %} false {% endif %} ";
		$expected = "  false  ";
		$this->assertTemplateResult($expected, $text);
	}

	public function testTrueLqTrue() {
		$text = " {% if 0 > 0 %} true {% else %} false {% endif %} ";
		$expected = "  false  ";
		$this->assertTemplateResult($expected, $text);
	}

	public function testOneLqZero() {
		$text = " {% if 1 > 0 %} true {% else %} false {% endif %} ";
		$expected = "  true  ";
		$this->assertTemplateResult($expected, $text);
	}

	public function testZeroLqOne() {
		$text = " {% if 0 < 1 %} true {% else %} false {% endif %} ";
		$expected = "  true  ";
		$this->assertTemplateResult($expected, $text);
	}

	public function testZeroLqOrEqualOne() {
		$text = " {% if 0 <= 0 %} true {% else %} false {% endif %} ";
		$expected = "  true  ";
		$this->assertTemplateResult($expected, $text);
	}

	public function testZeroLqOrEqualOneInvolvingNil() {
		$text = " {% if null <= 0 %} true {% else %} false {% endif %} ";
		$expected = "  false  ";
		$this->assertTemplateResult($expected, $text);


		$text = " {% if 0 <= null %} true {% else %} false {% endif %} ";
		$expected = "  false  ";
		$this->assertTemplateResult($expected, $text);
	}

	public function testZeroLqqOrEqualOne() {
		$text = " {% if 0 >= 0 %} true {% else %} false {% endif %} ";
		$expected = "  true  ";
		$this->assertTemplateResult($expected, $text);
	}

	public function testStrings() {
		$text = " {% if 'test' == 'test' %} true {% else %} false {% endif %} ";
		$expected = "  true  ";
		$this->assertTemplateResult($expected, $text);
	}

	public function testStringsNotEqual() {
		$text = " {% if 'test' != 'test' %} true {% else %} false {% endif %} ";
		$expected = "  false  ";
		$this->assertTemplateResult($expected, $text);
	}

	public function testVarStringsEqual() {
		$text = " {% if var == \"hello there!\" %} true {% else %} false {% endif %} ";
		$expected = "  true  ";
		$this->assertTemplateResult($expected, $text, array('var' => 'hello there!'));
	}

	public function testVarStringsAreNotEqual() {
		$text = " {% if \"hello there!\" == var %} true {% else %} false {% endif %} ";
		$expected = "  true  ";
		$this->assertTemplateResult($expected, $text, array('var' => 'hello there!'));
	}

	public function testVarAndLongStringAreEqual() {
		$text = " {% if var == 'hello there!' %} true {% else %} false {% endif %} ";
		$expected = "  true  ";
		$this->assertTemplateResult($expected, $text, array('var' => 'hello there!'));
	}

	public function testVarAndLongStringAreEqualBackwards() {
		$text = " {% if 'hello there!' == var %} true {% else %} false {% endif %} ";
		$expected = "  true  ";
		$this->assertTemplateResult($expected, $text, array('var' => 'hello there!'));
	}

	public function testIsCollectionEmpty() {
		$text = " {% if array == empty %} true {% else %} false {% endif %} ";
		$expected = "  true  ";
		$this->assertTemplateResult($expected, $text, array('array' => array()));
	}

	public function testIsNotCollectionEmpty() {
		$text = " {% if array == empty %} true {% else %} false {% endif %} ";
		$expected = "  false  ";
		$this->assertTemplateResult($expected, $text, array('array' => array(1, 2, 3)));
	}

	public function testNil() {
		$text = " {% if var == null %} true {% else %} false {% endif %} ";
		$expected = "  true  ";
		$this->assertTemplateResult($expected, $text, array('var' => null));

		$text = " {% if var == null %} true {% else %} false {% endif %} ";
		$expected = "  true  ";
		$this->assertTemplateResult($expected, $text, array('var' => null));
	}

	public function testNotNil() {
		$text = " {% if var != null %} true {% else %} false {% endif %} ";
		$expected = "  true  ";
		$this->assertTemplateResult($expected, $text, array('var' => 1));

		$text = " {% if var != null %} true {% else %} false {% endif %} ";
		$expected = "  true  ";
		$this->assertTemplateResult($expected, $text, array('var' => 1));
	}

	public function testIfFromVariable() {
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

	public function testNestedIf() {
		$this->assertTemplateResult('', '{% if false %}{% if false %} NO {% endif %}{% endif %}');
		$this->assertTemplateResult('', '{% if false %}{% if true %} NO {% endif %}{% endif %}');
		$this->assertTemplateResult('', '{% if true %}{% if false %} NO {% endif %}{% endif %}');
		$this->assertTemplateResult(' YES ', '{% if true %}{% if true %} YES {% endif %}{% endif %}');

		$this->assertTemplateResult(' YES ', '{% if true %}{% if true %} YES {% else %} NO {% endif %}{% else %} NO {% endif %}');
		$this->assertTemplateResult(' YES ', '{% if true %}{% if false %} NO {% else %} YES {% endif %}{% else %} NO {% endif %}');
		$this->assertTemplateResult(' YES ', '{% if false %}{% if true %} NO {% else %} NONO {% endif %}{% else %} YES {% endif %}');
	}

	public function testComplexConditions() {
		$this->assertTemplateResult('true', '{% if 10 == 10 and "h" == "h" %}true{% else %}false{% endif %}');
		$this->assertTemplateResult('true', '{% if 8 == 10 or "h" == "h" %}true{% else %}false{% endif %}');
		$this->assertTemplateResult('false', '{% if 8 == 10 and "h" == "h" %}true{% else %}false{% endif %}');
		$this->assertTemplateResult('true', '{% if 10 == 10 or "h" == "k" or "k" == "k" %}true{% else %}false{% endif %}');
	}

	public function testContains() {
		$this->assertTemplateResult('true', '{% if foo contains "h" %}true{% else %}false{% endif %}', array('foo' => array('k', 'h', 'z')));
		$this->assertTemplateResult('false', '{% if foo contains "y" %}true{% else %}false{% endif %}', array('foo' => array('k', 'h', 'z')));
		$this->assertTemplateResult('true', '{% if foo contains "e" %}true{% else %}false{% endif %}', array('foo' => 'abcedf'));
		$this->assertTemplateResult('true', '{% if foo contains "e" %}true{% else %}false{% endif %}', array('foo' => 'e'));
		$this->assertTemplateResult('false', '{% if foo contains "y" %}true{% else %}false{% endif %}', array('foo' => 'abcedf'));
	}

	/**
	 * @expectedException \Liquid\LiquidException
	 * @expectedExceptionMessage if tag was never closed
	 */
	public function testSyntaxErrorNotClosed() {
		$this->assertTemplateResult('', '{% if jerry == 1 %}');
	}

	/**
	 * @expectedException \Liquid\LiquidException
	 */
	public function testInvalidOperator() {
		$this->assertTemplateResult('', '{% if foo === y %}true{% else %}false{% endif %}', array('foo' => true, 'y' => true));
	}
}
