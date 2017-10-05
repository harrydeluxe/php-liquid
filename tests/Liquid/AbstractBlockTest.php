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

class AbstractBlockTest extends TestCase
{
	/**
	 * @expectedException \Liquid\Exception\ParseException
	 */
	public function testUnterminatedBlockError()
	{
		$this->assertTemplateResult('', '{% block }');
	}
}
