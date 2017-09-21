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

class TagExtendsTest extends TestCase
{
	public function testBasicExtends()
	{
		$template = new Template();
		$template->setFileSystem(new LiquidTestFileSystem());
		$template->parse("{% extends 'base' %}{% block content %}{{ hello }}{% endblock %}");
		$output = $template->render(array("hello" => "Hello!"));
		$this->assertEquals("Hello!", $output);
	}
}
