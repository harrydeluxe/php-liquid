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
	public function testUnknownTag()
	{
		$template = new Template();

		if (array_key_exists('foo', $template->getTags())) {
			$this->markTestIncomplete("Test tag already registered. Are you missing @depends?");
		}

		$this->expectException(\Liquid\Exception\ParseException::class);
		$this->expectExceptionMessage('Unknown tag foo');

		$template->parse('[ba{% foo %} Comment {% endfoo %}r]');
	}

	/**
	 * @depends testUnknownTag
	 */
	public function testCustomTag()
	{
		$template = new Template();
		$template->registerTag('foo', TagFoo::class);

		$template->parse('[ba{% foo %} Comment {% endfoo %}r]');
		$this->assertEquals('[bar]', $template->render());
	}
}
