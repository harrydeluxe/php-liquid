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

class IfElseTest extends Testcase
{

	function test_if() {
		$this->assertTemplateResult('  ', ' {% if false %} this text should not go into the output {% endif %} ');
		$this->assertTemplateResult('  this text should go into the output  ',
			' {% if true %} this text should go into the output {% endif %} ');
		$this->assertTemplateResult('  you rock ?', '{% if false %} you suck {% endif %} {% if true %} you rock {% endif %}?');
	}

	function test_if_else() {
		$this->assertTemplateResult(' YES ', '{% if false %} NO {% else %} YES {% endif %}');
		$this->assertTemplateResult(' YES ', '{% if true %} YES {% else %} NO {% endif %}');
		$this->assertTemplateResult(' YES ', '{% if "foo" %} YES {% else %} NO {% endif %}');
	}

	function test_if_boolean() {
		$this->assertTemplateResult(' YES ', '{% if var %} YES {% endif %}', array('var' => true));
	}

	function test_if_from_variable() {
		$this->assertTemplateResult('', '{% if var %} NO {% endif %}', array('var' => false));
		$this->assertTemplateResult('', '{% if var %} NO {% endif %}', array('var' => null));
		$this->assertTemplateResult('', '{% if foo.bar %} NO {% endif %}', array('foo' => array('bar' => false)));
		$this->assertTemplateResult('', '{% if foo.bar %} NO {% endif %}', array('foo' => array()));
		$this->assertTemplateResult('', '{% if foo.bar %} NO {% endif %}', array('foo' => null));
		//$this->assert_template_result('','{% if foo.bar %} NO {% endif %}', array('foo' => true));

		$this->assertTemplateResult(' YES ', '{% if var %} YES {% endif %}', array('var' => "text"));
		$this->assertTemplateResult(' YES ', '{% if var %} YES {% endif %}', array('var' => true));
		$this->assertTemplateResult(' YES ', '{% if var %} YES {% endif %}', array('var' => 1));
		//$this->assert_template_result(' YES ','{% if var %} YES {% endif %}', array('var' => array()));
		//$this->assert_template_result(' YES ','{% if var %} YES {% endif %}', array('var' => array()));
		$this->assertTemplateResult(' YES ', '{% if "foo" %} YES {% endif %}');
		$this->assertTemplateResult(' YES ', '{% if foo.bar %} YES {% endif %}', array('foo' => array('bar' => true)));
		$this->assertTemplateResult(' YES ', '{% if foo.bar %} YES {% endif %}', array('foo' => array('bar' => "text")));
		$this->assertTemplateResult(' YES ', '{% if foo.bar %} YES {% endif %}', array('foo' => array('bar' => 1)));
		//$this->assert_template_result(' YES ','{% if foo.bar %} YES {% endif %}', array('foo' => array('bar' => array())));

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

	function test_nested_if() {
		$this->assertTemplateResult('', '{% if false %}{% if false %} NO {% endif %}{% endif %}');
		$this->assertTemplateResult('', '{% if false %}{% if true %} NO {% endif %}{% endif %}');
		$this->assertTemplateResult('', '{% if true %}{% if false %} NO {% endif %}{% endif %}');
		$this->assertTemplateResult(' YES ', '{% if true %}{% if true %} YES {% endif %}{% endif %}');

		$this->assertTemplateResult(' YES ', '{% if true %}{% if true %} YES {% else %} NO {% endif %}{% else %} NO {% endif %}');
		$this->assertTemplateResult(' YES ', '{% if true %}{% if false %} NO {% else %} YES {% endif %}{% else %} NO {% endif %}');
		$this->assertTemplateResult(' YES ', '{% if false %}{% if true %} NO {% else %} NONO {% endif %}{% else %} YES {% endif %}');
	}

	function test_comparisons_on_null() {
		$this->assertTemplateResult('', '{% if null < 10 %} NO {% endif %}');
		$this->assertTemplateResult('', '{% if null <= 10 %} NO {% endif %}');
		$this->assertTemplateResult('', '{% if null >= 10 %} NO {% endif %}');
		$this->assertTemplateResult('', '{% if null > 10 %} NO {% endif %}');

		$this->assertTemplateResult('', '{% if 10 < null %} NO {% endif %}');
		$this->assertTemplateResult('', '{% if 10 <= null %} NO {% endif %}');
		$this->assertTemplateResult('', '{% if 10 >= null %} NO {% endif %}');
		$this->assertTemplateResult('', '{% if 10 > null %} NO {% endif %}');
	}

	function test_syntax_error_no_variable() {
		//$this->expectError('if tag was never closed');
		//$this->assert_template_result('', '{% if jerry == 1 %}');

		try {
			$this->assertTemplateResult('', '{% if jerry == 1 %}');
			$this->fail("Exception was expected.");
		} catch (\Exception $e) {
			$this->assertEqual($e->getMessage(), 'if tag was never closed');
			$this->pass();
		}
	}

}
