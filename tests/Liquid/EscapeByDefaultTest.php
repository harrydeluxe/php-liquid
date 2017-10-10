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

class ObjectWithToString
{
	private $string = '';

	public function __construct($string)
	{
		$this->string = $string;
	}

	public function __toString()
	{
		return $this->string;
	}
}

class EscapeByDefaultTest extends TestCase
{
	const XSS = "<script>alert()</script>";
	const XSS_FAILED = "&lt;script&gt;alert()&lt;/script&gt;";

	protected $assigns = array();

	protected function setup()
	{
		parent::setUp();

		$this->assigns = array(
			'xss' => self::XSS,
		);
	}

	public function testUnescaped()
	{
		$text = "{{ xss }}";
		$expected = self::XSS;
		$this->assertTemplateResult($expected, $text, $this->assigns);
	}

	public function testEscapedManually()
	{
		$text = "{{ xss | escape }}";
		$expected = self::XSS_FAILED;
		$this->assertTemplateResult($expected, $text, $this->assigns);
	}

	public function testRawWithoutAutoEscape()
	{
		$text = "{{ xss | raw }}";
		$expected = self::XSS;
		$this->assertTemplateResult($expected, $text, $this->assigns);
	}

	public function testEscapedAutomatically()
	{
		Liquid::set('ESCAPE_BY_DEFAULT', true);

		$text = "{{ xss }}";
		$expected = self::XSS_FAILED;
		$this->assertTemplateResult($expected, $text, $this->assigns);
	}

	public function testEscapedManuallyInAutoMode()
	{
		Liquid::set('ESCAPE_BY_DEFAULT', true);

		// text should only be escaped once
		$text = "{{ xss | escape }}";
		$expected = self::XSS_FAILED;
		$this->assertTemplateResult($expected, $text, $this->assigns);
	}

	public function testRawInAutoMode()
	{
		Liquid::set('ESCAPE_BY_DEFAULT', true);

		$text = "{{ xss | raw }}";
		$expected = self::XSS;
		$this->assertTemplateResult($expected, $text, $this->assigns);
	}

	public function testNlToBr()
	{
		Liquid::set('ESCAPE_BY_DEFAULT', true);
		$text = "{{ xss | newline_to_br }}";
		$expected = self::XSS."<br />\n".self::XSS;
		$this->assertTemplateResult($expected, $text, array('xss' => self::XSS."\n".self::XSS));
	}

	public function testToStringEscape()
	{
		$this->assertTemplateResult(self::XSS_FAILED, "{{ xss | escape }}", array('xss' => new ObjectWithToString(self::XSS)));
	}

	public function testToStringEscapeDefault()
	{
		Liquid::set('ESCAPE_BY_DEFAULT', true);
		$this->assertTemplateResult(self::XSS_FAILED, "{{ xss }}", array('xss' => new ObjectWithToString(self::XSS)));
	}

	/** System default value for the escape flag */
	private static $escapeDefault;

	public static function setUpBeforeClass()
	{
		// save system default value for the escape flag before all tests
		self::$escapeDefault = Liquid::get('ESCAPE_BY_DEFAULT');
	}

	public function tearDown()
	{
		// reset to the default after each test
		Liquid::set('ESCAPE_BY_DEFAULT', self::$escapeDefault);
	}
}
