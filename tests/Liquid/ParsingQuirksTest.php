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

class ParsingQuirksTest extends TestCase
{
	public function testErrorWithCss()
	{
		$text = " div { font-weight: bold; } ";
		$template = new Template();
		$template->parse($text);

		$nodelist = $template->getRoot()->getNodelist();

		$this->assertEquals($text, $template->render());
		$this->assertInternalType('string', $nodelist[0]);
	}
}
