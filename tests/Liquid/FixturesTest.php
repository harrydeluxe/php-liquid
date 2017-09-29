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

use Liquid\FileSystem\Virtual;

class FixturesTest extends TestCase
{
	/**
	 * @dataProvider fixtures
	 * @param string $liquid
	 * @param string $data
	 * @param string $expected
	 */
	public function testFixture($liquid, $data, $expected)
	{
		$template = new Template();
		$template->setFileSystem(new Virtual(function ($filename) {
			if (is_file(__DIR__.'/fixtures/'.$filename)) {
				return file_get_contents(__DIR__.'/fixtures/'.$filename);
			}
		}));

		$template->parse(file_get_contents($liquid));
		$result = $template->render(include $data);

		$this->assertEquals(file_get_contents($expected), $result);
	}

	public function fixtures()
	{
		return array_map(null, glob(__DIR__.'/fixtures/*.liquid'), glob(__DIR__.'/fixtures/*.php'), glob(__DIR__.'/fixtures/*.html'));
	}
}
