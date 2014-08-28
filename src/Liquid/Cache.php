<?php

namespace Liquid;

/**
 * Base class for Cache.
 */
abstract class Cache
{
	protected $_expire = 3600;

	protected $_prefix = 'liquid_';

	protected $_path;

	public function __construct($options = array()) {
		if (isset($options['cache_expire'])) {
			$this->_expire = $options['cache_expire'];
		}

		if (isset($options['cache_prefix'])) {
			$this->_prefix = $options['cache_prefix'];
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
