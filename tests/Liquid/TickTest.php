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

class TickTest extends TestCase
{
	public function tickDataProvider()
	{
		return [
			[10, 11, '{% for i in (1..10) %}x{% endfor %}'],
			[20, 40, '{% for i in (1..10) %}{% for ii in (1..2) %}x{% endfor %}{% endfor %}'],
			[1, 2, '{% if true %} {% endif %}'],
			[7, 8, '{% assign a = 0 %} {% increment a %} {% increment a %} {% increment a %}'],
			[1, 1, ' ']
		];
	}

	/**
	 * @dataProvider tickDataProvider
	 *
	 * @param int $min
	 * @param int $max
	 * @param string $source
	 */
	public function testTicks($min, $max, $source)
	{
		$ticks = 0;

		$template = new Template();
		$template->parse($source);
		$template->setTickFunction(function (Context $context) use (&$ticks) {
			$ticks++;
		});

		$template->render();

		$this->assertGreaterThanOrEqual($min, $ticks);
		$this->assertLessThanOrEqual($max, $ticks);
	}
}
