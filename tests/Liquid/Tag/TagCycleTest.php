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

class TagCycleTest extends TestCase
{
	/**
	 * @expectedException \Liquid\Exception\ParseException
	 */
	public function testInvalidSyntax()
	{
		$template = new Template();
		$template->parse("{% cycle %}");
	}

	public function testCycle()
	{
		$this->assertTemplateResult('one', '{%cycle "one", "two"%}');
		$this->assertTemplateResult('one two', '{%cycle "one", "two"%} {%cycle "one", "two"%}');
		$this->assertTemplateResult('one two one', '{%cycle "one", "two"%} {%cycle "one", "two"%} {%cycle "one", "two"%}');
	}

	public function testMultipleCycles()
	{
		$this->assertTemplateResult('1 2 1 1 2 3 1', '{%cycle 1,2%} {%cycle 1,2%} {%cycle 1,2%} {%cycle 1,2,3%} {%cycle 1,2,3%} {%cycle 1,2,3%} {%cycle 1,2,3%}');
	}

	public function testMultipleNamedCycles()
	{
		$this->assertTemplateResult('one one two two one one', '{%cycle 1: "one", "two" %} {%cycle 2: "one", "two" %} {%cycle 1: "one", "two" %} {%cycle 2: "one", "two" %} {%cycle 1: "one", "two" %} {%cycle 2: "one", "two" %}');
	}

	public function testMultipleNamedCyclesWithNamesFromContext()
	{
		$assigns = array("var1" => 1, "var2" => 2);
		$this->assertTemplateResult('one one two two one one', '{%cycle var1: "one", "two" %} {%cycle var2: "one", "two" %} {%cycle var1: "one", "two" %} {%cycle var2: "one", "two" %} {%cycle var1: "one", "two" %} {%cycle var2: "one", "two" %}', $assigns);
	}
}
