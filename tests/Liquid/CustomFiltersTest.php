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

class CustomFiltersTest extends TestCase
{
	/**
	 * The current context
	 *
	 * @var Context
	 */
	public $context;

	protected function setup()
	{
		parent::setUp();

		$this->context = new Context();
	}

	public function testSortKey()
	{
		$data = array(
			array(
				array(),
				array(),
			),
			array(
				array('b' => 1, 'c' => 5, 'a' => 3, 'z' => 4, 'h' => 2),
				array('a' => 3, 'b' => 1, 'c' => 5, 'h' => 2, 'z' => 4),
			),
		);

		foreach ($data as $item) {
			$this->assertEquals($item[1], CustomFilters::sort_key($item[0]));
		}
	}
}
