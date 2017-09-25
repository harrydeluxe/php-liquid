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

class TagCommentTest extends TestCase
{
	public function testHasABlockWhichDoesNothing()
	{
		$this->assertTemplateResult(
			"the comment block should be removed  .. right?",
			"the comment block should be removed {%comment%} be gone.. {%endcomment%} .. right?"
		);

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

		$this->assertTemplateResult('foobar', 'foo{%comment%} {%endcomment%}bar');
	}
}
