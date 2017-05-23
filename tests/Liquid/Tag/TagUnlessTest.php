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

class TagUnlessTest extends TestCase
{

	public function testUnless() {
		$this->assertTemplateResult('  ', ' {% unless true %} this text should not go into the output {% endunless %} ');
		$this->assertTemplateResult('  this text should go into the output  ',
      ' {% unless false %} this text should go into the output {% endunless %} ');
    	$this->assertTemplateResult('  you rock ?',
    		'{% unless true %} you suck {% endunless %} {% unless false %} you rock {% endunless %}?');
	}

	public function testUnlessElse() {
		$this->assertTemplateResult(' YES ', '{% unless true %} NO {% else %} YES {% endunless %}');
    	$this->assertTemplateResult(' YES ', '{% unless false %} YES {% else %} NO {% endunless %}');
    	$this->assertTemplateResult(' YES ', '{% unless "foo" %} NO {% else %} YES {% endunless %}');
	}

	public function testUnlessInLoop() {
		$this->assertTemplateResult('23',
			'{% for i in choices %}{% unless i %}{{ forloop.index }}{% endunless %}{% endfor %}',
			array('choices' => array(1, null, false)));
	}

	public function testUnlessElseInLoop() {
		$this->assertTemplateResult(' TRUE  2  3 ',
			'{% for i in choices %}{% unless i %} {{ forloop.index }} {% else %} TRUE {% endunless %}{% endfor %}',
			array('choices' => array(1, null, false)));
	}

	public function testEmpty() {
		$this->assertTemplateResult(" false ",
			"{% assign emptyString = '' %}{% unless emptyString %} true {% else %} false {% endunless %}");
	}

	public function testTrueEqlTrue() {
		$text = " {% unless true == true %} true {% else %} false {% endunless %} ";
		$expected = "  false  ";
		$this->assertTemplateResult($expected, $text);
	}

	public function testTrueNotEqlTrue() {
		$text = " {% unless true != true %} true {% else %} false {% endunless %} ";
		$expected = "  true  ";
		$this->assertTemplateResult($expected, $text);
		$this->assertTemplateResult(' true ', '{% unless true != true %} true {% else %} false {% endunless %}');
	}

}