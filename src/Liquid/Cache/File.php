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

use Liquid\Cache;
use Liquid\Exception\NotFoundException;

/**
 * Implements cache stored in files.
 */
class File extends Cache
{
	/**
	 * Constructor.
	 *
	 * It checks the availability of cache directory.
	 *
	 * @param array $options
	 *
	 * @throws NotFoundException if Cachedir not exists.
	 */
	public function __construct(array $options = array())
	{
		parent::__construct($options);

		if (isset($options['cache_dir']) && is_writable($options['cache_dir'])) {
			$this->path = realpath($options['cache_dir']) . DIRECTORY_SEPARATOR;
		} else {
			throw new NotFoundException('Cachedir not exists or not writable');
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function read($key, $unserialize = true)
	{
		if (!$this->exists($key)) {
			return false;
		}

		if ($unserialize) {
			return unserialize(file_get_contents($this->path . $this->prefix . $key));
		}

		return file_get_contents($this->path . $this->prefix . $key);
	}

	/**
	 * {@inheritdoc}
	 */
	public function exists($key)
	{
		$cacheFile = $this->path . $this->prefix . $key;

		if (!file_exists($cacheFile) || filemtime($cacheFile) + $this->expire < time()) {
			return false;
		}

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function write($key, $value, $serialize = true)
	{
		$bytes = file_put_contents($this->path . $this->prefix . $key, $serialize ? serialize($value) : $value);
		$this->gc();

		return $bytes !== false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function flush($expiredOnly = false)
	{
		foreach (glob($this->path . $this->prefix . '*') as $file) {
			if ($expiredOnly) {
				if (filemtime($file) + $this->expire < time()) {
					unlink($file);
				}
			} else {
				unlink($file);
			}
		}
	}

	/**
	 * {@inheritdoc}
	 */
	protected function gc()
	{
		$this->flush(true);
	}
}
