<?php

namespace Liquid\Cache;

use Liquid\Cache;
use Liquid\LiquidException;

/**
 * Implements cache stored in Apc.
 */
class Apc extends Cache
{
    /**
     * Constructor.
     * 
     * It checks the availability of apccache.
	 *
     * @throws LiquidException if APC cache extension is not loaded or is disabled.
     */
    public function __construct($options = array())
    {
        parent::__construct($options);

        if (!extension_loaded('apc'))
            throw new LiquidException('LiquidCacheApc requires PHP apc extension to be loaded.');
    }

	/**
	 * {@inheritdoc}
	 */
	public function read($key, $unserialize = true)
    {
        return apc_fetch($this->_prefix . $key);
    }

	/**
	 * {@inheritdoc}
	 */
    public function exists($key)
    {
        apc_fetch($this->_prefix . $key, $success);
        return $success;
    }

	/**
	 * {@inheritdoc}
	 */
    public function write($key, $value, $serialize = true)
    {
        return apc_store($this->_prefix . $key, $value, $this->_expire);
    }

	/**
	 * {@inheritdoc}
	 */
    public function flush($expiredOnly = false)
    {
        return apc_clear_cache('user');
    }
}
