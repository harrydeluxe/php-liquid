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

class AbstractBlockTest extends TestCase
{
	public function testUnterminatedBlockError()
	{
		$this->expectException(\Liquid\Exception\ParseException::class);

		$this->assertTemplateResult('', '{% block }');
	}

	public function testWhitespaceHandler()
	{
		$this->assertTemplateResult('foo', '{% if true %}foo{% endif %}');
		$this->assertTemplateResult(' foo ', '{% if true %} foo {% endif %}');
		$this->assertTemplateResult('  foo  ', ' {% if true %} foo {% endif %} ');
		$this->assertTemplateResult('foo ', '{% if true -%} foo {% endif %}');
		$this->assertTemplateResult('foo', '{% if true -%} foo {%- endif %}');
		$this->assertTemplateResult('foo', ' {%- if true -%} foo {%- endif %}');
		$this->assertTemplateResult('foo', ' {%- if true -%} foo {%- endif -%} ');
		$this->assertTemplateResult('foo', ' {%- if true -%} foo {%- endif -%}  {%- if false -%} bar {%- endif -%} ');
		$this->assertTemplateResult('foobar', ' {%- if true -%} foo {%- endif -%}  {%- if true -%} bar {%- endif -%} ');
		$this->assertTemplateResult('-> foo', '{% if true %}-> {% endif %} {%- if true -%} foo {%- endif -%}');
	}
}
