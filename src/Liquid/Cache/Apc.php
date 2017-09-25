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
use Liquid\LiquidException;

/**
 * Implements cache stored in Apc.
 *
 * @codeCoverageIgnore
 */
class Apc extends Cache
{
	/**
	 * Constructor.
	 *
	 * It checks the availability of apccache.
	 *
	 * @param array $options
	 *
	 * @throws LiquidException if APC cache extension is not loaded or is disabled.
	 */
	public function __construct(array $options = array())
	{
		parent::__construct($options);

		if (!function_exists('apc_fetch')) {
			throw new LiquidException(get_class($this).' requires PHP apc extension or similar to be loaded.');
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function read($key, $unserialize = true)
	{
		return apc_fetch($this->prefix . $key);
	}

	/**
	 * {@inheritdoc}
	 */
	public function exists($key)
	{
		apc_fetch($this->prefix . $key, $success);
		return (bool) $success;
	}

	/**
	 * {@inheritdoc}
	 */
	public function write($key, $value, $serialize = true)
	{
		return apc_store($this->prefix . $key, $value, $this->expire);
	}

	/**
	 * {@inheritdoc}
	 */
	public function flush($expiredOnly = false)
	{
		return apc_clear_cache('user');
	}
}
