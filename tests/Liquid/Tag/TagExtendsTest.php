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

/**
 * @see TagExtends
 */
class TagExtendsTest extends TestCase
{
	private $fs;

	protected function setUp()
	{
		$this->fs = new Virtual(function ($templatePath) {
			if ($templatePath == 'base') {
				return "{% block content %}{% endblock %}{% block footer %}{% endblock %}";
			}

			if ($templatePath == 'sub-base') {
				return "{% extends 'base' %}{% block content %}{% endblock %}{% block footer %} Boo! {% endblock %}";
			}
		});
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
