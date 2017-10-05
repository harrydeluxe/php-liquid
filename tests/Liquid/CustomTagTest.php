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

use Liquid\Tag\TagComment;

class TagFoo extends TagComment
{
}

class CustomTagTest extends TestCase
{
	/**
	 * @expectedException \Liquid\Exception\ParseException
	 * @expectedExceptionMessage Unknown tag foo
	 */
	public function testUnknownTag()
	{
		$template = new Template();
		$template->parse('[ba{% foo %} Comment {% endfoo %}r]');
	}

	public function testCustomTag()
	{
		$template = new Template();
		$template->registerTag('foo', TagFoo::class);
		$template->parse('[ba{% foo %} Comment {% endfoo %}r]');
		$this->assertEquals('[bar]', $template->render());
	}
}
