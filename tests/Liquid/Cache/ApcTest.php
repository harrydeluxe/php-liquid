<?php

/*
 * This file is part of the Liquid package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Liquid
 */

namespace Liquid\Cache;

use Liquid\TestCase;

class ApcTest extends TestCase
{
	/** @var \Liquid\Cache\Apc */
	protected $cache;

	protected function setUp()
	{
		parent::setUp();

		if (!function_exists('apc_fetch')) {
			$this->markTestSkipped("Alternative PHP Cache (APC) not available");
		}

		if (!ini_get('apc.enable_cli')) {
			$this->markTestSkipped("APC not enabled with cli. Run with: php -d apc.enable_cli=1");
		}

		$this->cache = new Apc();
	}

	public function testNotExists()
	{
		$this->assertFalse($this->cache->exists('no_such_key'));
	}

	public function testReadNotExisting()
	{
		$this->assertFalse($this->cache->read('no_such_key'));
	}

	public function testSetGetFlush()
	{
		$this->assertTrue($this->cache->write('test', 'example'), "Failed to set value.");
		$this->assertSame('example', $this->cache->read('test'));
		$this->assertTrue($this->cache->flush());
		$this->assertFalse($this->cache->read('test'));
	}
}
