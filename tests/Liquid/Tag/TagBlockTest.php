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

class TagBlockTest extends TestCase
{
	/**
	 * @expectedException \Liquid\LiquidException
	 */
	public function testSyntaxError() {
		$this->assertTemplateResult('', '{% block %}');
	}

	public function testCreateBlock() {
		$this->assertTemplateResult('block content', '{% block foo %}block content{% endblock %}');
	}
}
