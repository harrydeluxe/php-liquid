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

/**
 * Implements cache with data stored in an embedded variable with no handling of expiration dates for simplicity
 */
class Local extends Cache
{
	private $cache = array();

	/**
	 * {@inheritdoc}
	 */
	public function read($key, $unserialize = true)
	{
		if (isset($this->cache[$key])) {
			return $this->cache[$key];
		}

		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function exists($key)
	{
		return isset($this->cache[$key]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function write($key, $value, $serialize = true)
	{
		$this->cache[$key] = $value;
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function flush($expiredOnly = false)
	{
		$this->cache = array();
		return true;
	}
}
