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

class Stringable
{
	public function __toString()
	{
		return "100";
	}
}

class HasToLiquid
{
	public function toLiquid()
	{
		return "100";
	}
}

class TagCaseTest extends TestCase
{
	public function testCase()
	{
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

	public function testCaseWithElse()
	{
		$assigns = array('condition' => 5);
		$this->assertTemplateResult(' hit ', '{% case condition %}{% when 5 %} hit {% else %} else {% endcase %}', $assigns);

		$assigns = array('condition' => 6);
		$this->assertTemplateResult(' else ', '{% case condition %}{% when 5 %} hit {% else %} else {% endcase %}', $assigns);
	}

	/**
	 * @expectedException \Liquid\Exception\ParseException
	 */
	public function testSyntaxErrorCase()
	{
		$this->assertTemplateResult('', '{% case %}{% when 5 %}{% endcase %}');
	}

	/**
	 * @expectedException \Liquid\Exception\ParseException
	 */
	public function testSyntaxErrorWhen()
	{
		$this->assertTemplateResult('', '{% case condition %}{% when %}{% endcase %}');
	}

	/**
	 * @expectedException \Liquid\Exception\ParseException
	 */
	public function testSyntaxErrorEnd()
	{
		$this->assertTemplateResult('', '{% case condition %}{% end %}');
	}

	/**
	 * @expectedException \Liquid\Exception\RenderException
	 */
	public function testObject()
	{
		$this->assertTemplateResult('', '{% case variable %}{% when 5 %}{% endcase %}', array('variable' => (object) array()));
	}

	public function testStringable()
	{
		$this->assertTemplateResult('hit', '{% case variable %}{% when 100 %}hit{% endcase %}', array('variable' => new Stringable()));
	}

	public function testToLiquid()
	{
		$this->assertTemplateResult('hit', '{% case variable %}{% when 100 %}hit{% endcase %}', array('variable' => new HasToLiquid()));
	}
}
