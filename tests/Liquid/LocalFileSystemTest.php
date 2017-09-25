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

use Liquid\FileSystem\Local;

class LocalFileSystemTest extends TestCase
{
	protected $root;

	protected function setUp()
	{
		$this->root = __DIR__ . DIRECTORY_SEPARATOR . self::TEMPLATES_DIR . DIRECTORY_SEPARATOR;
		// reset to defaults
		Liquid::set('INCLUDE_ALLOW_EXT', false);
	}

	/**
	 * @expectedException \Liquid\LiquidException
	 */
	public function testIllegalTemplateNameEmpty()
	{
		$fileSystem = new Local('');
		$fileSystem->fullPath('');
	}

	/**
	 * @expectedException \Liquid\LiquidException
	 */
	public function testIllegalRootPath()
	{
		$fileSystem = new Local('invalid/not/found');
		$fileSystem->fullPath('');
	}

	/**
	 * @expectedException \Liquid\LiquidException
	 */
	public function testIllegalTemplateNameIncludeExtension()
	{
		Liquid::set('INCLUDE_ALLOW_EXT', false);

		$fileSystem = new Local('');
		$fileSystem->fullPath('has_extension.ext');
	}

	/**
	 * @expectedException \Liquid\LiquidException
	 */
	public function testIllegalTemplateNameNotIncludeExtension()
	{
		Liquid::set('INCLUDE_ALLOW_EXT', true);

		$fileSystem = new Local('');
		$fileSystem->fullPath('has_extension');
	}

	/**
	 * @expectedException \Liquid\LiquidException
	 */
	public function testIllegalTemplatePathNoRoot()
	{
		$fileSystem = new Local('');
		$fileSystem->fullPath('mypartial');
	}

	/**
	 * @expectedException \Liquid\LiquidException
	 */
	public function testIllegalTemplatePathNoFileExists()
	{
		$fileSystem = new Local(dirname(__DIR__));
		$fileSystem->fullPath('no_such_file_exists');
	}

	/**
	 * @expectedException \Liquid\LiquidException
	 * @expectedExceptionMessage not under
	 */
	public function testIllegalTemplatePathNotUnderTemplateRoot()
	{
		Liquid::set('INCLUDE_ALLOW_EXT', true);
		$fileSystem = new Local(dirname($this->root));
		// find any fail under deeper under the root, so all other checks would pass
		$filesUnderCurrentDir = array_map('basename', glob(dirname(__DIR__).'/../*'));
		// path relative to root; we can't start it with a dot since it isn't allowed anyway
		$fileSystem->fullPath(self::TEMPLATES_DIR."/../../../{$filesUnderCurrentDir[0]}");
	}

	public function testValidPathWithDefaultExtension()
	{
		$templateName = 'mypartial';

		$fileSystem = new Local($this->root);
		$this->assertEquals($this->root . Liquid::get('INCLUDE_PREFIX') . $templateName . '.' . Liquid::get('INCLUDE_SUFFIX'), $fileSystem->fullPath($templateName));
	}

	public function testValidPathWithCustomExtension()
	{
		Liquid::set('INCLUDE_PREFIX', '');
		Liquid::set('INCLUDE_SUFFIX', 'tpl');

		$templateName = 'mypartial';

		$fileSystem = new Local($this->root);
		$this->assertEquals($this->root . Liquid::get('INCLUDE_PREFIX') . $templateName . '.' . Liquid::get('INCLUDE_SUFFIX'), $fileSystem->fullPath($templateName));
	}

	/**
	 * @expectedException \Liquid\LiquidException
	 * @expectedExceptionMessage File not found
	 */
	public function testReadIllegalTemplatePathNoFileExists()
	{
		$fileSystem = new Local(dirname(__DIR__));
		$fileSystem->readTemplateFile('no_such_file_exists');
	}

	public function testReadTemplateFile()
	{
		Liquid::set('INCLUDE_PREFIX', '');
		Liquid::set('INCLUDE_SUFFIX', 'tpl');

		$fileSystem = new Local($this->root);
		$this->assertEquals('test content', trim($fileSystem->readTemplateFile('mypartial')));
	}

	public function testDeprecatedLocalFileSystemExists()
	{
		$this->assertInstanceOf(Local::class, new LocalFileSystem($this->root));
	}

	public function testParseTemplateFile()
	{
		Liquid::set('INCLUDE_PREFIX', '');
		Liquid::set('INCLUDE_SUFFIX', 'tpl');

		$template = new Template($this->root);
		$this->assertEquals("test content\n", $template->parseFile('mypartial')->render());
	}

	/**
	 * @expectedException \Liquid\LiquidException
	 * @expectedExceptionMessage Could not load a template
	 */
	public function testParseTemplateFileError()
	{
		$template = new Template();
		$template->parseFile('mypartial');
	}
}
