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
use Liquid\Template;
use Liquid\Liquid;
use Liquid\Cache\Local;

class TagIncludeTest extends TestCase
{
	/**
	 * @expectedException \Liquid\LiquidException
	 */
	public function testInvalidSyntaxNoTemplateName() {
		$template = new Template();
		$template->parse("{% include %}");
	}

	/**
	 * @expectedException \Liquid\LiquidException
	 */
	public function testInvalidSyntaxNotQuotedTemplateName() {
		$template = new Template();
		$template->parse("{% include hello %}");
	}

	/**
	 * @expectedException \Liquid\LiquidException
	 */
	public function testInvalidSyntaxInvalidKeyword() {
		$template = new Template();
		$template->parse("{% include 'hello' no_keyword %}");
	}

	/**
	 * @expectedException \Liquid\LiquidException
	 */
	public function testInvalidSyntaxNoObjectCollection() {
		$template = new Template();
		$template->parse("{% include 'hello' with %}");
	}

	public function testIncludeTag() {
		$template = new Template();
		$template->setFileSystem(new LiquidTestFileSystem());

		$template->parse("Outer-{% include 'inner' with 'value' other:23 %}-Outer{% include 'inner' for var other:'loop' %}");

		$output = $template->render(array("var" => array(1, 2, 3)));

		$this->assertEquals("Outer-Inner: value23-OuterInner: 1loopInner: 2loopInner: 3loop", $output);
	}

	public function testIncludeTagNoWith() {
		$template = new Template();
		$template->setFileSystem(new LiquidTestFileSystem());

		$template->parse("Outer-{% include 'inner' %}-Outer-{% include 'inner' other:'23' %}");

		$output = $template->render(array("inner" => "orig", "var" => array(1, 2, 3)));

		$this->assertEquals("Outer-Inner: orig-Outer-Inner: orig23", $output);
	}

	public function testWithCache() {
		$template = new Template();
		$template->setFileSystem(new LiquidTestFileSystem());
		$template->setCache(new Local());

		foreach (array("Before cache:", "With cache:") as $type) {
			$template->parse("{{ type }} {% for item in list %}{% include 'example' inner:item %} {% endfor %}");
			$template->render(array("inner" => "foo", "list" => array(1, 2, 3)), array());
			$this->assertEquals("$type Example: Inner: 1 Example: Inner: 2 ", $template->render(array("type" => $type, "inner" => "bar", "list" => array(1, 2))));
		}

		$template->setCache(null);
	}

	public function testIncludeTemplateFile() {
		Liquid::set('INCLUDE_PREFIX', '');
		Liquid::set('INCLUDE_SUFFIX', 'tpl');

		$template = new Template(dirname(__DIR__).DIRECTORY_SEPARATOR.self::TEMPLATES_DIR);
		$template->parse("{% include 'mypartial' %}");
		// template include inserts a new line
		$this->assertEquals("test content\n", $template->render());
	}
}
