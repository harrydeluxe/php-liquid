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
}

class OutputTest extends TestCase
{
	protected $assigns = array();

	protected function setup()
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
	 * @expectedException \Liquid\Exception\ParseException
	 * @expectedExceptionMessage was not properly terminated
	 */
	public function testVariableWithANewLine()
	{
		$text = "{{ aaa\n }}";
		$this->assertTemplateResult('', $text, $this->assigns);
	}
}
