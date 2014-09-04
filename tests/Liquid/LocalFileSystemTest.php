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
	public function testIllegalTemplateNameEmpty() {
		$fileSystem = new LocalFileSystem('');
		$fileSystem->fullPath('');
	}

	/**
	 * @expectedException \Liquid\LiquidException
	 */
	public function testIllegalTemplateNameIncludeExtension() {
		Liquid::set('INCLUDE_ALLOW_EXT', false);

		$fileSystem = new LocalFileSystem('');
		$fileSystem->fullPath('has_extension.ext');
	}

	/**
	 * @expectedException \Liquid\LiquidException
	 */
	public function testIllegalTemplateNameNotIncludeExtension() {
		Liquid::set('INCLUDE_ALLOW_EXT', true);

		$fileSystem = new LocalFileSystem('');
		$fileSystem->fullPath('has_extension');
	}

	/**
	 * @expectedException \Liquid\LiquidException
	 */
	public function testIllegalTemplatePathNoRoot() {
		$fileSystem = new LocalFileSystem('');
		$fileSystem->fullPath('mypartial');
	}

	/**
	 * @expectedException \Liquid\LiquidException
	 */
	public function testIllegalTemplatePathNoFileExists() {
		$fileSystem = new LocalFileSystem(dirname(__DIR__));
		$fileSystem->fullPath('no_such_file_exists');
	}

	public function testValidPathWithDefaultExtension() {
		$root = dirname(__FILE__) . DIRECTORY_SEPARATOR . self::TEMPLATES_DIR . DIRECTORY_SEPARATOR;
		$templateName = 'mypartial';

		$fileSystem = new LocalFileSystem($root);
		$this->assertEquals($root . Liquid::get('INCLUDE_PREFIX') . $templateName . '.' . Liquid::get('INCLUDE_SUFFIX'), $fileSystem->fullPath($templateName));
	}

	public function testValidPathWithCustomExtension() {
		Liquid::set('INCLUDE_PREFIX', '');
		Liquid::set('INCLUDE_SUFFIX', 'tpl');

		$root = dirname(__FILE__) . DIRECTORY_SEPARATOR . self::TEMPLATES_DIR . DIRECTORY_SEPARATOR;
		$templateName = 'mypartial';

		$fileSystem = new LocalFileSystem($root);
		$this->assertEquals($root . Liquid::get('INCLUDE_PREFIX') . $templateName . '.' . Liquid::get('INCLUDE_SUFFIX'), $fileSystem->fullPath($templateName));
	}

	public function testReadTemplateFile() {
		Liquid::set('INCLUDE_PREFIX', '');
		Liquid::set('INCLUDE_SUFFIX', 'tpl');

		$root = dirname(__FILE__) . DIRECTORY_SEPARATOR . self::TEMPLATES_DIR . DIRECTORY_SEPARATOR;

		$fileSystem = new LocalFileSystem($root);
		$this->assertEquals('test content', trim($fileSystem->readTemplateFile('mypartial')));
	}
}
