<?php

/**
 * This file is part of the Liquid package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Liquid
 */

namespace Liquid;

class LocalFileSystemTest extends Testcase
{
	/**
	 * @expectedException \Liquid\LiquidException
	 */
	public function testIllegalTemplateNameIncludeExtension() {
		Liquid::set('INCLUDE_ALLOW_EXT', false);

		$fileSystem = new LocalFileSystem('');
		$fileSystem->fullPath('has_extension.ext');
	}

	/*
	public function test_local() {
		$root = dirname(__FILE__) . '/templates/';

		$file_system = new LiquidLocalFileSystem($root);
		$this->assertEqual($root . "mypartial.tpl", $file_system->fullPath("mypartial"));
		$this->assertEqual($root . "dir/mypartial.tpl", $file_system->fullPath("dir/mypartial"));


		try {
			$file_system->fullPath('../dir/mypartial');
			$this->fail();
		} catch (\Exception $e) {
			$this->assertEqual($e->getMessage(), "Illegal template name '../dir/mypartial'");
		}


		try {
			$file_system->fullPath("/dir/../../dir/mypartial");
			$this->fail();
		} catch (\Exception $e) {
			$this->assertEqual($e->getMessage(), "Illegal template name '/dir/../../dir/mypartial'");
		}

		try {
			$file_system->fullPath("/etc/passwd");
			$this->fail();
		} catch (\Exception $e) {
			$this->assertEqual($e->getMessage(), "Illegal template name '/etc/passwd'");
		}
	}
	*/
}
