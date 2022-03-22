<?php

/*
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
	public function make_funny($input)
	{
		return 'LOL';
	}

	public function cite_funny($input)
	{
		return 'LOL: ' . $input;
	}

	public function add_smiley($input, $smiley = ":-)")
	{
		return $input . ' ' . $smiley;
	}

	public function add_tag($input, $tag = "p", $id = "foo")
	{
		return "<" . $tag . " id=\"" . $id . "\">" . $input . "</" . $tag . ">";
	}

	public function paragraph($input)
	{
		return "<p>" . $input . "</p>";
	}

	public function link_to($name, $url, $protocol)
	{
		return "<a href=\"" . $protocol . '://' .$url . "\">" . $name . "</a>";
	}

	public function str_replace($input, $data)
	{
		foreach ($data as $k => $v) {
			$input = str_replace("[" . $k . "]", $v, $input);
		}
		return $input;
	}

	public function img_url($input, $size, $opts = null)
	{
		$output = "image_" . $size;
		if (isset($opts['crop'])) {
			$output .= "_cropped_" . $opts['crop'];
		}
		if (isset($opts['scale'])) {
			$output .= "@" . $opts['scale'] . 'x';
		}
		return $output . ".png";
	}
}

class OutputTest extends TestCase
{
	protected $assigns = array();

	protected function setUp(): void
	{
		parent::setUp();

		$this->assigns = array(
			'best_cars' => 'bmw',
			'car' => array('bmw' => 'good', 'gm' => 'bad')
		);

		$this->filters = new FunnyFilter();
	}

	public function testVariable()
	{
		$text = " {{best_cars}} ";
		$expected = " bmw ";

		$this->assertTemplateResult($expected, $text, $this->assigns);
	}

	public function testVariableTrasversing()
	{
		$text = " {{car.bmw}} {{car.gm}} {{car.bmw}} ";

		$expected = " good bad good ";
		$this->assertTemplateResult($expected, $text, $this->assigns);
	}

	public function testVariablePiping()
	{
		$text = " {{ car.gm | make_funny }} ";
		$expected = " LOL ";

		$this->assertTemplateResult($expected, $text, $this->assigns);
	}

	public function testVariablePipingWithInput()
	{
		$text = " {{ car.gm | cite_funny }} ";
		$expected = " LOL: bad ";

		$this->assertTemplateResult($expected, $text, $this->assigns);
	}

	public function testVariablePipingWithArgs()
	{
		$text = " {{ car.gm | add_smiley : '=(' }} ";
		$expected = " bad =( ";

		$this->assertTemplateResult($expected, $text, $this->assigns);
	}

	public function textVariablePipingWithNoArgs()
	{
		$text = " {{ car.gm | add_smile }} ";
		$expected = " bad =( ";

		$this->assertTemplateResult($expected, $text, $this->assigns);
	}

	public function testMultipleVariablePipingWithArgs()
	{
		$text = " {{ car.gm | add_smiley : '=(' | add_smiley : '=('}} ";
		$expected = " bad =( =( ";

		$this->assertTemplateResult($expected, $text, $this->assigns);
	}

	public function testVariablePipingWithTwoArgs()
	{
		$text = " {{ car.gm | add_tag : 'span', 'bar'}} ";
		$expected = " <span id=\"bar\">bad</span> ";

		$this->assertTemplateResult($expected, $text, $this->assigns);
	}

	public function testVariablePipingWithVariableArgs()
	{
		$text = " {{ car.gm | add_tag : 'span', car.bmw}} ";
		$expected = " <span id=\"good\">bad</span> ";

		$this->assertTemplateResult($expected, $text, $this->assigns);
	}

	public function testVariablePipingWithKeywordArg()
	{
		$text = " {{ 'Welcome, [name]' | str_replace: name: 'Santa' }} ";
		$expected = " Welcome, Santa ";

		$this->assertTemplateResult($expected, $text, $this->assigns);
	}

	public function testVariablePipingWithArgsAndKeywordArgs()
	{
		$text = " {{ car.gm | img_url: '450x450', crop: 'center', scale: 2 }} ";
		$expected = " image_450x450_cropped_center@2x.png ";

		$this->assertTemplateResult($expected, $text, $this->assigns);
	}

	public function testMultiplePipings()
	{
		$text = " {{ best_cars | cite_funny | paragraph }} ";
		$expected = " <p>LOL: bmw</p> ";

		$this->assertTemplateResult($expected, $text, $this->assigns);
	}

	public function testLinkTo()
	{
		$text = " {{ 'Typo' | link_to: 'typo.leetsoft.com':'http' }} ";
		$expected = " <a href=\"http://typo.leetsoft.com\">Typo</a> ";

		$this->assertTemplateResult($expected, $text, $this->assigns);
	}

	/**
	 */
	public function testVariableWithANewLine()
	{
		$text = "{{ aaa\n }}";
		$this->assertTemplateResult('', $text, $this->assigns);
	}
}
