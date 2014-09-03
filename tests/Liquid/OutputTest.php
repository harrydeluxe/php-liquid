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

class FunnyFilter
{
	function make_funny($input) {
		return 'LOL';
	}

	function cite_funny($input) {
		return 'LOL: ' . $input;
	}

	function add_smiley($input, $smiley = ":-)") {
		return $input . ' ' . $smiley;
	}

	function add_tag($input, $tag = "p", $id = "foo") {
		return "<" . $tag . " id=\"" . $id . "\">" . $input . "</" . $tag . ">";
	}

	function paragraph($input) {
		return "<p>" . $input . "</p>";
	}

	function link_to($name, $url) {
		return "<a href=\"" . $url . "\">" . $name . "</a>";
	}

}

class OutputTest extends TestCase
{

	function setup() {
		$this->assigns = array(
			'best_cars' => 'bmw',
			'car' => array('bmw' => 'good', 'gm' => 'bad')
		);

		$this->filters = new FunnyFilter();
	}

	function test_variable() {
		$text = " {{best_cars}} ";
		$expected = " bmw ";

		$this->assertTemplateResult($expected, $text, $this->assigns);
	}

	function test_variable_trasversing() {
		$text = " {{car.bmw}} {{car.gm}} {{car.bmw}} ";

		$expected = " good bad good ";
		$this->assertTemplateResult($expected, $text, $this->assigns);
	}

	function test_variable_piping() {
		$text = " {{ car.gm | make_funny }} ";
		$expectd = " LOL ";

		$this->assertTemplateResult($expectd, $text, $this->assigns);
	}

	function test_variable_piping_with_input() {
		$text = " {{ car.gm | cite_funny }} ";
		$expectd = " LOL: bad ";

		$this->assertTemplateResult($expectd, $text, $this->assigns);
	}

	function test_variable_piping_with_args() {
		$text = " {{ car.gm | add_smiley : ':-(' }} ";
		$expected = " bad :-( ";

		$this->assertTemplateResult($expected, $text, $this->assigns);
	}

	function text_variable_piping_with_no_args() {
		$text = " {{ car.gm | add_smile }} ";
		$expected = " bad :-( ";

		$this->assertTemplateResult($expected, $text, $this->assigns);
	}


	function test_multiple_variable_piping_with_args() {
		$text = " {{ car.gm | add_smiley : ':-(' | add_smiley : ':-('}} ";
		$expected = " bad :-( :-( ";

		$this->assertTemplateResult($expected, $text, $this->assigns);
	}

	function test_variable_piping_with_two_args() {
		$text = " {{ car.gm | add_tag : 'span', 'bar'}} ";
		$expected = " <span id=\"bar\">bad</span> ";

		$this->assertTemplateResult($expected, $text, $this->assigns);
	}


	function test_variable_piping_with_variable_args() {
		$text = " {{ car.gm | add_tag : 'span', car.bmw}} ";
		$expected = " <span id=\"good\">bad</span> ";

		$this->assertTemplateResult($expected, $text, $this->assigns);
	}

	function test_multiple_pipings() {
		$text = " {{ best_cars | cite_funny | paragraph }} ";
		$expected = " <p>LOL: bmw</p> ";

		$this->assertTemplateResult($expected, $text, $this->assigns);
	}

	function test_link_to() {
		$text = " {{ 'Typo' | link_to: 'http://typo.leetsoft.com' }} ";
		$expected = " <a href=\"http://typo.leetsoft.com\">Typo</a> ";

		$this->assertTemplateResult($expected, $text, $this->assigns);
	}
}
