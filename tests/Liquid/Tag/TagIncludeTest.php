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
use Liquid\Template;
use Liquid\Liquid;
use Liquid\Cache\Local;
use Liquid\TestFileSystem;

class TagIncludeTest extends TestCase
{
	private $fs;

	protected function setUp()
	{
		$this->fs = TestFileSystem::fromArray(array(
			'a' => "{% include 'b' %}",
			'b' => "{% include 'c' %}",
			'c' => "{% include 'd' %}",
			'd' => '({{ inner }})',
			'inner' => "Inner: {{ inner }}{{ other }}",
			'example' => "Example: {% include 'inner' %}",
		));
	}

	protected function tearDown()
	{
		// PHP goes nuts unless we unset it
		unset($this->fs);
	}

	/**
	 * @expectedException \Liquid\Exception\ParseException
	 * @expectedExceptionMessage Error in tag
	 */
	public function testInvalidSyntaxNoTemplateName()
	{
		$template = new Template();
		$template->setFileSystem($this->fs);
		$template->parse("{% include %}");
	}

	/**
	 * @expectedException \Liquid\Exception\MissingFilesystemException
	 * @expectedExceptionMessage No file system
	 */
	public function testMissingFilesystem()
	{
		$template = new Template();
		$template->parse("{% include 'hello' %}");
	}

	public function testInvalidSyntaxInvalidKeyword()
	{
		$template = new Template();
		$template->setFileSystem($this->fs);
		$template->parse("{% include 'hello' no_keyword %}");

		$this->markTestIncomplete("Exception is expected here");
	}

	public function testInvalidSyntaxNoObjectCollection()
	{
		$template = new Template();
		$template->setFileSystem($this->fs);
		$template->parse("{% include 'hello' with %}");

		$this->markTestIncomplete("Exception is expected here");
	}

	public function testIncludeTag()
	{
		$template = new Template();
		$template->setFileSystem($this->fs);

		$template->parse("Outer-{% include 'inner' with 'value' other:23 %}-Outer{% include 'inner' for var other:'loop' %}");

		$output = $template->render(array("var" => array(1, 2, 3)));

		$this->assertEquals("Outer-Inner: value23-OuterInner: 1loopInner: 2loopInner: 3loop", $output);
	}

	public function testIncludeTagNoWith()
	{
		$template = new Template();
		$template->setFileSystem($this->fs);

		$template->parse("Outer-{% include 'inner' %}-Outer-{% include 'inner' other:'23' %}");

		$output = $template->render(array("inner" => "orig", "var" => array(1, 2, 3)));

		$this->assertEquals("Outer-Inner: orig-Outer-Inner: orig23", $output);
	}

	public function testWithCache()
	{
		$template = new Template();
		$template->setFileSystem($this->fs);
		$template->setCache(new Local());

		foreach (array("Before cache:", "With cache:") as $type) {
			$template->parse("{{ type }} {% for item in list %}{% include 'example' inner:item %} {% endfor %}{% include 'a' %}");
			$template->render(array("inner" => "foo", "list" => array(1, 2, 3)), array());
			$this->assertEquals("$type Example: Inner: 1 Example: Inner: 2 (bar)", $template->render(array("type" => $type, "inner" => "bar", "list" => array(1, 2))));
		}

		$template->setCache(null);
	}

	public function testIncludeTemplateFile()
	{
		Liquid::set('INCLUDE_PREFIX', '');
		Liquid::set('INCLUDE_SUFFIX', 'tpl');

		$template = new Template(dirname(__DIR__).DIRECTORY_SEPARATOR.self::TEMPLATES_DIR);
		$template->parse("{% include 'mypartial' %}");
		// template include inserts a new line
		$this->assertEquals("test content\n", $template->render());
	}

	public function testIncludePassPlainValue()
	{
		$template = new Template();
		$template->setFileSystem(TestFileSystem::fromArray(array(
			'inner' => "[{{ other }}]",
			'example' => "({% include 'inner' other:var %})",
		)));

		$template->parse("{% include 'example' %}");

		$output = $template->render(array("var" => "test"));
		$this->assertEquals("([test])", $output);
	}

	/**
	 * @expectedException \Liquid\Exception\RenderException
	 * @expectedExceptionMessage Use index operator
	 */
	public function testIncludePassArrayWithoutIndex()
	{
		$template = new Template();
		$template->setFileSystem(TestFileSystem::fromArray(array(
			'inner' => "[{{ other }}]",
			'example' => "({% include 'inner' other:var %})",
		)));

		$template->parse("{% include 'example' %}");
		$template->render(array("var" => array("a", "b", "c")));
	}

	public function testIncludePassArrayWithIndex()
	{
		$template = new Template();
		$template->setFileSystem(TestFileSystem::fromArray(array(
			'inner' => "[{{ other[0] }}]",
			'example' => "({% include 'inner' other:var %})",
		)));

		$template->parse("{% include 'example' %}");

		$output = $template->render(array("var" => array("a", "b", "c")));
		$this->assertEquals("([a])", $output);
	}

	public function testIncludePassObjectValue()
	{
		$template = new Template();
		$template->setFileSystem(TestFileSystem::fromArray(array(
			'inner' => "[{{ other.a }}]",
			'example' => "({% include 'inner' other:var %})",
		)));

		$template->parse("{% include 'example' %}");

		$output = $template->render(array("var" => (object) array('a' => 'b')));
		$this->assertEquals("([b])", $output);
	}

	public function testIncludeWithoutQuotes()
	{
		$template = new Template();
		$template->setFileSystem(TestFileSystem::fromArray(array(
			'inner' => "[{{ other }}]",
			'example' => "{%include inner other:var %} ({{var}})",
		)));

		$template->parse("{% include example other:var %}");

		$output = $template->render(array("var" => "test"));
		$this->assertEquals("[test] (test)", $output);

		$template->parse("{% include inner %}");

		$output = $template->render(array("other" => "test"));
		$this->assertEquals("[test]", $output);
	}

	/**
	 * Render calls in this test shall give same results with cache enabled
	 */
	public function testIncludeWithExtends()
	{
		$template = new Template();
		$template->setFileSystem(TestFileSystem::fromArray(array(
			'outer' => "{% block content %}Content for outer block{% endblock %} / {% block footer %}Footer for outer block{% endblock %}",
			'content' => 'Content for {{ name }} block',
			'middle' => "{% extends 'outer' %}{% block content %}{% include 'content' name:'middle' %}{% endblock %}",
			'main' => "Main: {% extends 'middle' %}{% block footer %}{% include 'footer-top' hello:message %}{% endblock %}",
			'footer-bottom' => "{{ name }} with message: {{ hello }}",
			'footer-top' => "Footer top and {% include 'footer-bottom' name:'bottom' %}",
		)));

		$template->setCache(new Local());

		foreach (array("Before cache", "With cache") as $type) {
			$this->assertEquals("Block with message: $type", $template->parseFile('footer-bottom')->render(array("name" => "Block", "hello" => $type)));
			$this->assertEquals('Content for middle block / Footer for outer block', $template->parseFile('middle')->render());
			$this->assertEquals("Main: Content for middle block / Footer top and bottom with message: $type", $template->parseFile('main')->render(array("message" => $type)));

			$template->parse("{% include 'main' hello:message %}");
			$output = $template->render(array("message" => $type));
			$this->assertEquals("Main: Content for middle block / Footer top and bottom with message: $type", $output);
		}

		$template->setCache(null);
	}

	public function testCacheDiscardedIfFileChanges()
	{
		$template = new Template();
		$template->setCache(new Local());

		$content = "[{{ name }}]";
		$template->setFileSystem(TestFileSystem::fromArray(array(
			'example' => &$content,
		)));

		$template->parse("{% include 'example' %}");
		$output = $template->render(array("name" => "Example"));
		$this->assertEquals("[Example]", $output);

		$content = "<{{ name }}>";
		$template->parse("{% include 'example' %}");
		$output = $template->render(array("name" => "Example"));
		$this->assertEquals("<Example>", $output);
	}
}
