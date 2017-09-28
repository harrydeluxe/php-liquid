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
use Liquid\Cache\Local;
use Liquid\FileSystem\Virtual;
use Liquid\TestFileSystem;

/**
 * @see TagExtends
 */
class TagExtendsTest extends TestCase
{
	private $fs;

	protected function setUp()
	{
		$this->fs = TestFileSystem::fromArray(array(
			'base' => "{% block content %}{% endblock %}{% block footer %}{% endblock %}",
			'sub-base' => "{% extends 'base' %}{% block content %}{% endblock %}{% block footer %} Boo! {% endblock %}",
		));
	}

	protected function tearDown()
	{
		// PHP goes nuts unless we unset it
		unset($this->fs);
	}

	public function testBasicExtends()
	{
		$template = new Template();
		$template->setFileSystem($this->fs);
		$template->parse("{% extends 'base' %}{% block content %}{{ hello }}{% endblock %}");
		$output = $template->render(array("hello" => "Hello!"));
		$this->assertEquals("Hello!", $output);
	}

	public function testDefaultContentExtends()
	{
		$template = new Template();
		$template->setFileSystem($this->fs);
		$template->parse("{% block content %}{{ hello }}{% endblock %}\n{% extends 'sub-base' %}");
		$output = $template->render(array("hello" => "Hello!"));
		$this->assertEquals("Hello!\n Boo! ", $output);
	}

	public function testDeepExtends()
	{
		$template = new Template();
		$template->setFileSystem($this->fs);
		$template->parse('{% extends "sub-base" %}{% block content %}{{ hello }}{% endblock %}{% block footer %} I am a footer.{% endblock %}');

		$output = $template->render(array("hello" => "Hello!"));
		$this->assertEquals("Hello! I am a footer.", $output);
	}

	public function testWithCache()
	{
		$template = new Template();
		$template->setFileSystem($this->fs);
		$template->setCache(new Local());

		foreach (array("Before cache", "With cache") as $type) {
			$template->parse("{% extends 'base' %}{% block content %}{{ hello }}{% endblock %}");
			$output = $template->render(array("hello" => "$type"));
			$this->assertEquals($type, $output);
		}

		$template->setCache(null);
	}

	/**
	 * Render calls in this test will give different results (and fail the test) with cache enabled
	 */
	public function testExtendsReplaceContentWithCache()
	{
		$template = new Template();
		$template->setFileSystem(TestFileSystem::fromArray(array(
			'outer' => "{% block content %}Content for outer block{% endblock %} / {% block footer %}Footer for outer block{% endblock %}",
			'inner' => "{% extends 'outer' %}{% block content %}Content for inner block{% endblock %}",
		)));

		$contentsWithoutCache = $template->parseFile('inner')->render();

		$template->setCache(new Local());
		$template->parseFile('outer');

		$this->assertEquals($contentsWithoutCache, $template->parseFile('inner')->render());
	}

	public function testExtendsReplaceContentWithVariables()
	{
		$template = new Template();
		$template->setFileSystem(TestFileSystem::fromArray(array(
			'outer' => "{% block content %}Outer{{ a }}{% endblock %}Spacer{{ a }}{% block footer %}Footer{{ a }}{% endblock %}",
			'middle' => "{% extends 'outer' %}{% block content %}Inner{{ a }}{% endblock %}",
		)));

		$template->setCache(new Local());

		$template->parseFile('outer')->render(['a' => '0']);
		$template->parseFile('middle')->render(['a' => '1']);
		$template->parseFile('middle')->render(['a' => '2']);
		$this->assertEquals('Inner3Spacer3Footer3', $template->parseFile('middle')->render(['a' => '3']));
	}

	public function testCacheDiscardedIfFileChanges()
	{
		$template = new Template();
		$template->setCache(new Local());

		$content = "[{{ name }}]";
		$template->setFileSystem(TestFileSystem::fromArray(array(
			'outer' => &$content,
			'inner' => "{% extends 'outer' %}"
		)));

		$template->parseFile('inner');
		$output = $template->render(array("name" => "Example"));
		$this->assertEquals("[Example]", $output);

		// this should go from cache
		$template->parse("{% extends 'outer' %}");
		$output = $template->render(array("name" => "Example"));
		$this->assertEquals("[Example]", $output);

		// content change should trigger re-render
		$content = "<{{ name }}>";
		$template->parseFile('inner');
		$output = $template->render(array("name" => "Example"));
		$this->assertEquals("<Example>", $output);
	}

	/**
	 * @expectedException \Liquid\LiquidException
	 */
	public function testInvalidSyntaxNoTemplateName()
	{
		$template = new Template();
		$template->parse("{% extends %}");
	}

	/**
	 * @expectedException \Liquid\LiquidException
	 */
	public function testInvalidSyntaxNotQuotedTemplateName()
	{
		$template = new Template();
		$template->parse("{% extends base %}");
	}

	/**
	 * @expectedException \Liquid\LiquidException
	 */
	public function testInvalidSyntaxEmptyTemplateName()
	{
		$template = new Template();
		$template->parse("{% extends '' %}");
	}

	/**
	 * @expectedException \Liquid\LiquidException
	 */
	public function testInvalidSyntaxInvalidKeyword()
	{
		$template = new Template();
		$template->parse("{% extends 'base' nothing-should-be-here %}");
	}
}
