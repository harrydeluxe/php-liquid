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

class FileTest extends TestCase
{
	/** @var \Liquid\Cache\File */
	protected $cache;
	protected $cacheDir;

	protected function setUp()
	{
		parent::setUp();

		$this->cacheDir = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'cache_dir';
		$this->cache = new File(array(
			'cache_dir' => $this->cacheDir,
			'cache_expire' => 3600,
			'cache_prefix' => 'liquid_',
		));
	}

	protected function tearDown()
	{
		parent::tearDown();

		// Remove tmp cache files
		array_map('unlink', glob($this->cacheDir . DIRECTORY_SEPARATOR . '*'));
	}

	/**
	 * @expectedException \Liquid\Exception\FilesystemException
	 */
	public function testConstructInvalidOptions()
	{
		new File();
	}

	/**
	 * @expectedException \Liquid\Exception\FilesystemException
	 */
	public function testConstructNoSuchDirOrNotWritable()
	{
		new File(array('cache_dir' => '/no/such/dir/liquid/cache'));
	}

	public function testGetExistsNoFile()
	{
		$this->assertFalse($this->cache->exists('no_key'));
	}

	public function testGetExistsExpired()
	{
		$key = 'test';
		$cacheFile = $this->cacheDir . DIRECTORY_SEPARATOR . 'liquid_' . $key;
		touch($cacheFile, time() - 1000000); // long ago
		$this->assertFalse($this->cache->exists($key));
	}

	public function testGetExistsNotExpired()
	{
		$key = 'test';
		$cacheFile = $this->cacheDir . DIRECTORY_SEPARATOR . 'liquid_' . $key;
		touch($cacheFile);
		$this->assertTrue($this->cache->exists($key));
	}

	public function testFlushAll()
	{
		touch($this->cacheDir . DIRECTORY_SEPARATOR . 'liquid_test');
		touch($this->cacheDir . DIRECTORY_SEPARATOR . 'liquid_test_two');

		$this->assertCount(2, glob($this->cacheDir . DIRECTORY_SEPARATOR . '*'));

		$this->cache->flush();

		$this->assertCount(0, glob($this->cacheDir . DIRECTORY_SEPARATOR . '*'));
	}

	public function testFlushExpired()
	{
		touch($this->cacheDir . DIRECTORY_SEPARATOR . 'liquid_test');
		touch($this->cacheDir . DIRECTORY_SEPARATOR . 'liquid_test_two', time() - 1000000);

		$this->assertCount(2, glob($this->cacheDir . DIRECTORY_SEPARATOR . '*'));

		$this->cache->flush(true);

		$this->assertCount(1, glob($this->cacheDir . DIRECTORY_SEPARATOR . '*'));
	}

	public function testWriteNoSerialize()
	{
		$key = 'test';
		$value = 'test_value';

		$this->assertTrue($this->cache->write($key, $value, false));

		$this->assertEquals($value, file_get_contents($this->cacheDir . DIRECTORY_SEPARATOR . 'liquid_' . $key));
	}

	public function testWriteSerialized()
	{
		$key = 'test';
		$value = 'test_value';

		$this->assertTrue($this->cache->write($key, $value));

		$this->assertEquals(serialize($value), file_get_contents($this->cacheDir . DIRECTORY_SEPARATOR . 'liquid_' . $key));
	}

	public function testWriteGc()
	{
		$key = 'test';
		$value = 'test_value';

		// This cache file must be removed by GC
		touch($this->cacheDir . DIRECTORY_SEPARATOR . 'liquid_test_two', time() - 1000000);

		$this->assertTrue($this->cache->write($key, $value, false));

		$this->assertCount(1, glob($this->cacheDir . DIRECTORY_SEPARATOR . '*'));
	}

	public function testReadNonExisting()
	{
		$this->assertFalse($this->cache->read('no_such_key'));
	}

	public function testReadNoUnserialize()
	{
		$key = 'test';
		$value = 'test_value';

		file_put_contents($this->cacheDir . DIRECTORY_SEPARATOR . 'liquid_' . $key, $value);

		$this->assertSame($value, $this->cache->read($key, false));
	}

	public function testReadSerialize()
	{
		$key = 'test';
		$value = 'test_value';

		file_put_contents($this->cacheDir . DIRECTORY_SEPARATOR . 'liquid_' . $key, serialize($value));

		$this->assertSame($value, $this->cache->read($key));
	}
}
