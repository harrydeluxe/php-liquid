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

	protected function setUp(): void
	{
		$this->root = __DIR__ . DIRECTORY_SEPARATOR . self::TEMPLATES_DIR . DIRECTORY_SEPARATOR;
		// reset to defaults
		Liquid::set('INCLUDE_ALLOW_EXT', false);
	}

	/**
	 */
	public function testIllegalTemplateNameEmpty()
	{
		$this->expectException(\Liquid\LiquidException::class);

		$fileSystem = new Local('');
		$fileSystem->fullPath('');
	}

	/**
	 */
	public function testIllegalRootPath()
	{
		$this->expectException(\Liquid\LiquidException::class);

		$fileSystem = new Local('invalid/not/found');
		$fileSystem->fullPath('');
	}

	/**
	 */
	public function testIllegalTemplateNameIncludeExtension()
	{
		$this->expectException(\Liquid\LiquidException::class);

		Liquid::set('INCLUDE_ALLOW_EXT', false);

		$fileSystem = new Local('');
		$fileSystem->fullPath('has_extension.ext');
	}

	/**
	 */
	public function testIllegalTemplateNameNotIncludeExtension()
	{
		$this->expectException(\Liquid\LiquidException::class);

		Liquid::set('INCLUDE_ALLOW_EXT', true);

		$fileSystem = new Local('');
		$fileSystem->fullPath('has_extension');
	}

	/**
	 */
	public function testIllegalTemplatePathNoRoot()
	{
		$this->expectException(\Liquid\LiquidException::class);

		$fileSystem = new Local('');
		$fileSystem->fullPath('mypartial');
	}

	/**
	 */
	public function testIllegalTemplatePathNoFileExists()
	{
		$this->expectException(\Liquid\LiquidException::class);

		$fileSystem = new Local(dirname(__DIR__));
		$fileSystem->fullPath('no_such_file_exists');
	}

	/**
	 */
	public function testIllegalTemplatePathNotUnderTemplateRoot()
	{
		$this->expectException(\Liquid\LiquidException::class);
		$this->expectExceptionMessage('not under');

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
	 */
	public function testReadIllegalTemplatePathNoFileExists()
	{
		$this->expectException(\Liquid\LiquidException::class);
		$this->expectExceptionMessage('File not found');

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
		$this->assertEquals("test content" . PHP_EOL, $template->parseFile('mypartial')->render());
	}

	/**
	 */
	public function testParseTemplateFileError()
	{
		$this->expectException(\Liquid\LiquidException::class);
		$this->expectExceptionMessage('Could not load a template');

		$template = new Template();
		$template->parseFile('mypartial');
	}
}
