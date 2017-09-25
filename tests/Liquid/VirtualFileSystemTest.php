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
use Liquid\Cache\File;

class VirtualFileSystemTest extends TestCase
{
	/**
	 * @expectedException \Liquid\LiquidException
	 * @expectedExceptionMessage Not a callback
	 */
	public function testInvalidCallback()
	{
		new Virtual('');
	}

	public function testReadTemplateFile()
	{
		$fs = new Virtual(function ($templatePath) {
			if ($templatePath == 'foo') {
				return "Contents of foo";
			}

			if ($templatePath == 'bar') {
				return "Bar";
			}

			return '';
		});

		$this->assertEquals('Contents of foo', $fs->readTemplateFile('foo'));
		$this->assertEquals('Bar', $fs->readTemplateFile('bar'));
		$this->assertEquals('', $fs->readTemplateFile('nothing'));
	}

	/**
	 * @expectedException \Liquid\LiquidException
	 * @expectedExceptionMessage cannot be used with a serializing cache
	 */
	public function testWithFileCache()
	{
		$template = new Template();
		$template->setFileSystem(new Virtual(function ($templatePath) {
			return '';
		}));
		$template->setCache(new File(array(
			'cache_dir' => __DIR__,
		)));
		$template->parse("Hello");
	}

	public function virtualFileSystemCallback($templatePath)
	{
		return 'OK';
	}

	public function testWithRegularCallback()
	{
		$template = new Template();
		$template->setFileSystem(new Virtual(array($this, 'virtualFileSystemCallback'), true));
		$template->setCache(new File(array(
			'cache_dir' => __DIR__.'/cache_dir/',
		)));

		$template->parse("Test: {% include 'hello' %}");
		$this->assertEquals('Test: OK', $template->render());
	}
}
