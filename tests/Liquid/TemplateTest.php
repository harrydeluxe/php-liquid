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

class TemplateTest extends TestCase
{
	function test_tokenize_strings() {
		$this->assertEqual(array(' '), Template::tokenize(' '));
		$this->assertEqual(array('hello world'), Template::tokenize('hello world'));
	}

	function test_tokenize_variables() {
		$this->assertEqual(array('{{funk}}'), Template::tokenize('{{funk}}'));
		$this->assertEqual(array(' ', '{{funk}}', ' '), Template::tokenize(' {{funk}} '));
		$this->assertEqual(array(' ', '{{funk}}', ' ', '{{so}}', ' ', '{{brother}}', ' '), Template::tokenize(' {{funk}} {{so}} {{brother}} '));
		$this->assertEqual(array(' ', '{{  funk  }}', ' '), Template::tokenize(' {{  funk  }} '));


	}

	function test_tokenize_blocks() {
		$this->assertEqual(array('{%comment%}'), Template::tokenize('{%comment%}'));
		$this->assertEqual(array(' ', '{%comment%}', ' '), Template::tokenize(' {%comment%} '));
		$this->assertEqual(array(' ', '{%comment%}', ' ', '{%endcomment%}', ' '), Template::tokenize(' {%comment%} {%endcomment%} '));
		$this->assertEqual(array('  ', '{% comment %}', ' ', '{% endcomment %}', ' '), Template::tokenize("  {% comment %} {% endcomment %} "));
	}

}
