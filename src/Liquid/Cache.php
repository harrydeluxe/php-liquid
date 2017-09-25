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

/**
 * Base class for Cache.
 */
abstract class Cache
{
	/** @var int */
	protected $expire = 3600;
	/** @var string */
	protected $prefix = 'liquid_';
	/** @var string  */
	protected $path;

	/**
	 * @param array $options
	 */
	public function __construct(array $options = array())
	{
		if (isset($options['cache_expire'])) {
			$this->expire = $options['cache_expire'];
		}

		if (isset($options['cache_prefix'])) {
			$this->prefix = $options['cache_prefix'];
		}
	}

	/**
	 * Retrieves a value from cache with a specified key.
	 *
	 * @param string $key a unique key identifying the cached value
	 * @param bool $unserialize
	 *
	 * @return mixed|boolean the value stored in cache, false if the value is not in the cache or expired.
	 */
	abstract public function read($key, $unserialize = true);

	/**
	 * Check if specified key exists in cache.
	 *
	 * @param string $key a unique key identifying the cached value
	 *
	 * @return boolean true if the key is in cache, false otherwise
	 */
	abstract public function exists($key);

	/**
	 * Stores a value identified by a key in cache.
	 *
	 * @param string $key the key identifying the value to be cached
	 * @param mixed $value the value to be cached
	 * @param bool $serialize
	 *
	 * @return boolean true if the value is successfully stored into cache, false otherwise
	 */
	abstract public function write($key, $value, $serialize = true);

	/**
	 * Deletes all values from cache.
	 *
	 * @param bool $expiredOnly
	 *
	 * @return boolean whether the flush operation was successful.
	 */
	abstract public function flush($expiredOnly = false);
}
