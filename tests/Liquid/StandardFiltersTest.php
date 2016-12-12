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

class MoneyFilter
{
	public function money($value) {
		return sprintf(' %d$ ', $value);
	}

	public function money_with_underscore($value) {
		return sprintf(' %d$ ', $value);
	}
}

class CanadianMoneyFilter
{
	public function money($value) {
		return sprintf(' %d$ CAD ', $value);
	}
}

class SizeClass
{
	const SIZE = 42;

	public function toLiquid() {
		return $this;
	}

	public function size() {
		return self::SIZE;
	}
}


class StandardFiltersTest extends TestCase
{
	/**
	 * The current context
	 *
	 * @var Context
	 */
	var $context;

	protected function setup() {
		parent::setUp();

		$this->context = new Context();
	}

	public function testSize() {
		$data = array(
			4 => 1000,
			3 => 100,
			2 => array('one', 'two'),
			SizeClass::SIZE => new SizeClass(),
		);

		foreach ($data as $expected => $element) {
			$this->assertEquals($expected, StandardFilters::size($element));
		}
	}

	public function testDowncase() {
		$data = array(
			'UpperCaseMiXed' => 'uppercasemixed',
			3 => 3,
		);

		foreach ($data as $element => $expected) {
			$this->assertEquals($expected, StandardFilters::downcase($element));
		}
	}

	public function testUpcase() {
		$data = array(
			'UpperCaseMiXed' => 'UPPERCASEMIXED',
			3 => 3,
		);

		foreach ($data as $element => $expected) {
			$this->assertEquals($expected, StandardFilters::upcase($element));
		}
	}

	public function testCapitalize() {
		$data = array(
			'one Word not' => 'One Word Not',
			'' => '',
		);

		foreach ($data as $element => $expected) {
			$this->assertEquals($expected, StandardFilters::capitalize($element));
		}
	}

	public function testRaw()
	{
		$data = array(
			"Anything" => "Anything",
			3 => 3,
		);

		foreach ($data as $element => $expected) {
			$this->assertEquals($expected, StandardFilters::raw($element));
		}
	}

	public function testEscape() {
		$data = array(
			"one Word's not" => "one Word&#039;s not",
			"&><\"'" => "&amp;&gt;&lt;&quot;&#039;",
		);

		foreach ($data as $element => $expected) {
			$this->assertEquals($expected, StandardFilters::escape($element));
		}
	}

	public function testEscapeOnce() {
		$data = array(
			"<b><script>alert()</script>" => "&lt;b&gt;&lt;script&gt;alert()&lt;/script&gt;",
			"a < b & c" => "a &lt; b &amp; c",
			"a &lt; b &amp; c" => "a &lt; b &amp; c",
			"&lt;\">" => "&lt;&quot;&gt;",
		);

		foreach ($data as $element => $expected) {
			$this->assertEquals($expected, StandardFilters::escape_once($element));
		}
	}

	public function testStripNewLines() {
		$data = array(
			"one Word\r\n not\r\n\r\n" => "one Word not",
			'test' => 'test',
			3 => 3,
		);

		foreach ($data as $element => $expected) {
			$this->assertEquals($expected, StandardFilters::strip_newlines($element));
		}
	}

	public function testNewLineToBr() {
		$data = array(
			"one Word\r\n not\r\n" => "one Word<br /><br /> not<br /><br />",
			'test' => 'test',
			3 => 3,
		);

		foreach ($data as $element => $expected) {
			$this->assertEquals($expected, StandardFilters::newline_to_br($element));
		}
	}

	public function testReplace() {
		// Replace for empty string
		$data = array(
			"one Word not Word" => "one  not ",
			'test' => 'test',
			3 => 3,
		);

		foreach ($data as $element => $expected) {
			$this->assertEquals($expected, StandardFilters::replace($element, 'Word'));
		}

		// Replace for "Hello" string
		$data = array(
			"one Word not Word" => "one Hello not Hello",
			'test' => 'test',
			3 => 3,
		);

		foreach ($data as $element => $expected) {
			$this->assertEquals($expected, StandardFilters::replace($element, 'Word', 'Hello'));
		}
	}

	public function testReplaceFirst() {
		// Replace for empty string
		$data = array(
			"one Word not Word" => "one  not Word",
			'test' => 'test',
			3 => 3,
		);

		foreach ($data as $element => $expected) {
			$this->assertEquals($expected, StandardFilters::replace_first($element, 'Word'));
		}

		// Replace for "Hello" string
		$data = array(
			"one Word not Word" => "one Hello not Word",
			'test' => 'test',
			3 => 3,
		);

		foreach ($data as $element => $expected) {
			$this->assertEquals($expected, StandardFilters::replace_first($element, 'Word', 'Hello'));
		}
	}

	public function testRemove() {
		$data = array(
			"one Word not Word" => "one  not ",
			'test' => 'test',
			3 => 3,
		);

		foreach ($data as $element => $expected) {
			$this->assertEquals($expected, StandardFilters::remove($element, 'Word'));
		}
	}

	public function testRemoveFirst() {
		$data = array(
			"one Word not Word" => "one  not Word",
			'test' => 'test',
			3 => 3,
		);

		foreach ($data as $element => $expected) {
			$this->assertEquals($expected, StandardFilters::remove_first($element, 'Word'));
		}
	}

	public function testAppend() {
		$data = array(
			"one Word not Word" => "one Word not Word appended",
			'' => ' appended',
			3 => '3 appended',
		);

		foreach ($data as $element => $expected) {
			$this->assertEquals($expected, StandardFilters::append($element, ' appended'));
		}
	}

	public function testPrepend() {
		$data = array(
			"one Word not Word" => "prepended one Word not Word",
			'' => 'prepended ',
			3 => 'prepended 3',
		);

		foreach ($data as $element => $expected) {
			$this->assertEquals($expected, StandardFilters::prepend($element, 'prepended '));
		}
	}

	public function testSlice() {
		// Slize up to the end
		$data = array(
			array(
				array(),
				array(),
			),
			array(
				'',
				'',
			),
			array(
				array(1,2,3,4,5),
				array(3,4,5),
			),
			array(
				'12345',
				'345'
			),
			array(
				100,
				100
			),
		);

		foreach ($data as $item) {
			$this->assertEquals($item[1], StandardFilters::slice($item[0], 2));
		}

		// Slize a few elements
		$data = array(
			array(
				array(),
				array(),
			),
			array(
				'',
				'',
			),
			array(
				array(1,2,3,4,5),
				array(3,4),
			),
			array(
				'12345',
				'34'
			),
			array(
				100,
				100
			),
		);

		foreach ($data as $item) {
			$this->assertEquals($item[1], StandardFilters::slice($item[0], 2, 2));
		}
	}

	public function testTruncate() {
		// Truncate with default ending
		$data = array(
			'' => '',
			str_repeat('a', 150) => str_repeat('a', 100) . '...',
			'test' => 'test',
			3 => 3,
		);

		foreach ($data as $element => $expected) {
			$this->assertEquals($expected, StandardFilters::truncate($element));
		}

		// Custom length
		$this->assertEquals('abc...', StandardFilters::truncate('abcdef', 3));

		// Custom ending
		$this->assertEquals('abcend', StandardFilters::truncate('abcdef', 3, 'end'));
	}

	public function testTruncateWords() {
		// Truncate with default ending
		$data = array(
			'' => '',
			str_repeat('abc ', 10) => rtrim(str_repeat('abc ', 3)) . '...',
			'test two' => 'test two',
			3 => 3,
		);

		foreach ($data as $element => $expected) {
			$this->assertEquals($expected, StandardFilters::truncatewords($element));
		}

		// Custom length
		$this->assertEquals('hello...', StandardFilters::truncatewords('hello from string', 1));

		// Custom ending
		$this->assertEquals('helloend', StandardFilters::truncatewords('hello from string', 1, 'end'));
	}

	public function testStripHtml() {
		$data = array(
			'' => '',
			'test no html tags' => 'test no html tags',
			'test <br /> <p>paragraph</p> hello' => 'test  paragraph hello',
			3 => 3,
		);

		foreach ($data as $element => $expected) {
			$this->assertEquals($expected, StandardFilters::strip_html($element));
		}
	}

	public function testJoin() {
		$data = array(
			array(
				array(),
				'',
			),
			array(
				'',
				'',
			),
			array(
				array(1,2,3,4,5),
				'1 2 3 4 5'
			),
			array(
				100,
				100
			),
		);

		foreach ($data as $item) {
			$this->assertEquals($item[1], StandardFilters::join($item[0]));
		}

		// Custom glue
		$this->assertEquals('1-2-3', StandardFilters::join(array(1, 2, 3), '-'));
	}

	public function testSort() {
		$data = array(
			array(
				array(),
				array(),
			),
			array(
				array(1, 5, 3, 4, 2),
				array(1, 2, 3, 4, 5),
			),
		);

		foreach ($data as $item) {
			$this->assertEquals($item[1], StandardFilters::sort($item[0]), '', 0, 10, true);
		}

		// Sort by inner key
		$original = array(
			array('a' => 20, 'b' => 10),
			array('a' => 45, 'b' => 5),
			array('a' => 30, 'b' => 48),
		);
		$expected = array(
			array('a' => 45, 'b' => 5),
			array('a' => 20, 'b' => 10),
			array('a' => 30, 'b' => 48),
		);

		$this->assertEquals($expected, StandardFilters::sort($original, 'b'), '', 0, 10, true);
	}

/*
	
	I've commented this out as its not one of the Ruby Standard Filters
	
	public function testSortKey() {
		$data = array(
			array(
				array(),
				array(),
			),
			array(
				array('b' => 1, 'c' => 5, 'a' => 3, 'z' => 4, 'h' => 2),
				array('a' => 3, 'b' => 1, 'c' => 5, 'h' => 2, 'z' => 4),
			),
		);

		foreach ($data as $item) {
			$this->assertEquals($item[1], StandardFilters::sort_key($item[0]));
		}
	}
*/

	public function testDefault() {
		$this->assertEquals('hello', StandardFilters::_default('', 'hello'));
		$this->assertEquals('world', StandardFilters::_default('world', 'hello'));
	}
	
	public function testUnique() {
		$data = array(
			array(
				array(),
				array(),
			),
			array(
				array(1, 1, 5, 3, 4, 2, 5, 2),
				array(1, 5, 3, 4, 2),
			),
		);

		foreach ($data as $item) {
			$this->assertEquals($item[1], StandardFilters::uniq($item[0]), '', 0, 10, true);
		}
	}

	public function testReverse() {
		$data = array(
			array(
				array(),
				array(),
			),
			array(
				array(1, 1, 5, 3, 4, 2, 5, 2),
				array(2, 5, 2, 4, 3, 5, 1, 1),
			),
		);

		foreach ($data as $item) {
			$this->assertEquals($item[1], StandardFilters::reverse($item[0]), '', 0, 10, true);
		}
	}

	public function testMap() {
		$data = array(
			array(
				array(),
				'',
			),
			array(
				array(
					function() {
						return 'from function ';
					},
					array(
						'b' => 10,
						'attr' => 'value ',
					),
					array(
						'a' => 20,
						'no_attr' => 'another value '
					),
				),
				'from function value ',
			),
		);

		foreach ($data as $item) {
			$this->assertEquals($item[1], StandardFilters::map($item[0], 'attr'));
		}
	}

	public function testFirst() {
		$data = array(
			array(
				array(),
				false,
			),
			array(
				array('two', 'one', 'three'),
				'two',
			),
			array(
				array(100, 400, 200),
				100,
			),
		);

		foreach ($data as $item) {
			$this->assertEquals($item[1], StandardFilters::first($item[0]));
		}
	}

	public function testLast() {
		$data = array(
			array(
				array(),
				false,
			),
			array(
				array('two', 'one', 'three'),
				'three',
			),
			array(
				array(100, 400, 200),
				200,
			),
		);

		foreach ($data as $item) {
			$this->assertEquals($item[1], StandardFilters::last($item[0]));
		}
	}

	public function testSplit() {
		$data = array(
			array(
				'',
				array(0 => ''),
			),
			array(
				'two-one-three',
				array('two', 'one', 'three'),
			),
		);

		foreach ($data as $item) {
			$this->assertEquals($item[1], StandardFilters::split($item[0], '-'));
		}
	}

	public function testStrip() {
		$data = array(
			array(
				'',
				'',
			),
			array(
				' hello   ',
				'hello',
			),
			array(
				1,
				1,
			),
		);

		foreach ($data as $item) {
			$this->assertEquals($item[1], StandardFilters::strip($item[0]));
		}
	}

	public function testLStrip() {
		$data = array(
			array(
				'',
				'',
			),
			array(
				' hello   ',
				'hello   ',
			),
			array(
				1,
				1,
			),
		);

		foreach ($data as $item) {
			$this->assertEquals($item[1], StandardFilters::lstrip($item[0]));
		}
	}

	public function testRStrip() {
		$data = array(
			array(
				'',
				'',
			),
			array(
				' hello   ',
				' hello',
			),
			array(
				1,
				1,
			),
		);

		foreach ($data as $item) {
			$this->assertEquals($item[1], StandardFilters::rstrip($item[0]));
		}
	}

	public function testPlus() {
		$data = array(
			array(
				'',
				'',
				0,
			),
			array(
				10,
				20,
				30,
			),
			array(
				1.5,
				2.7,
				3,
			),
		);

		foreach ($data as $item) {
			$this->assertSame($item[2], StandardFilters::plus($item[0], $item[1]));
		}
	}

	public function testMinus() {
		$data = array(
			array(
				'',
				'',
				0,
			),
			array(
				10,
				20,
				-10,
			),
			array(
				1.5,
				2.7,
				-1,
			),
		);

		foreach ($data as $item) {
			$this->assertSame($item[2], StandardFilters::minus($item[0], $item[1]));
		}
	}

	public function testTimes() {
		$data = array(
			array(
				'',
				'',
				0,
			),
			array(
				10,
				20,
				200,
			),
			array(
				1.5,
				2.7,
				2,
			),
		);

		foreach ($data as $item) {
			$this->assertSame($item[2], StandardFilters::times($item[0], $item[1]));
		}
	}

	public function testDivideBy() {
		$data = array(
			array(
				'20',
				10,
				2,
			),
			array(
				10,
				20,
				0.5,
			),
			array(
				0,
				200,
				0,
			),
		);

		foreach ($data as $item) {
			$this->assertSame($item[2], StandardFilters::divided_by($item[0], $item[1]));
		}
	}

	public function testModulo() {
		$data = array(
			array(
				'20',
				10,
				0,
			),
			array(
				10,
				20,
				10,
			),
			array(
				8,
				3,
				2,
			),
		);

		foreach ($data as $item) {
			$this->assertSame($item[2], StandardFilters::modulo($item[0], $item[1]));
		}
	}

	public function testRound() {
		$data = array(
			array(
				'20.003',
				2,
				20.00,
			),
			array(
				10,
				3,
				10.000,
			),
			array(
				8,
				0,
				8.0,
			),
		);

		foreach ($data as $item) {
			$this->assertSame($item[2], StandardFilters::round($item[0], $item[1]));
		}
	}

	public function testCeil() {
		$data = array(
			array(
				'20.003',
				21,
			),
			array(
				10,
				10,
			),
			array(
				0.42,
				1,
			),
		);

		foreach ($data as $item) {
			$this->assertSame($item[1], StandardFilters::ceil($item[0]));
		}
	}

	public function testFloor() {
		$data = array(
			array(
				'20.003',
				20,
			),
			array(
				10,
				10,
			),
			array(
				0.42,
				0,
			),
		);

		foreach ($data as $item) {
			$this->assertSame($item[1], StandardFilters::floor($item[0]));
		}
	}

	public function testLocalFilter() {
		$var = new Variable('var | money');
		$this->context->set('var', 1000);
		$this->context->addFilters(new MoneyFilter());
		$this->assertEquals(' 1000$ ', $var->render($this->context));
	}

	public function testUnderscoreInFilterName() {
		$var = new Variable('var | money_with_underscore ');
		$this->context->set('var', 1000);
		$this->context->addFilters(new MoneyFilter());
		$this->assertEquals(' 1000$ ', $var->render($this->context));
	}

	public function testSecondFilterOverwritesFirst() {
		$var = new Variable('var | money ');
		$this->context->set('var', 1000);
		$this->context->addFilters(new MoneyFilter(), 'money');
		$this->context->addFilters(new CanadianMoneyFilter(), 'money');
		$this->assertEquals(' 1000$ CAD ', $var->render($this->context));
	}
}
