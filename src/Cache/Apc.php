<?php
/**
 * LiquidCacheApc class file
 *
 * @package Liquid
 * @copyright Copyright (c) 2011-2012 Harald Hanek
 * @license http://harrydeluxe.mit-license.org
 */

class LiquidCacheApc extends LiquidCache
{
    /**
     * Initializes this component.
     * 
     * It checks the availability of apccache.
     * @throws LiquidException if APC cache extension is not loaded or is disabled.
     */
    public function __construct($options = array())
    {
        parent::__construct($options);

        if (!extension_loaded('apc'))
            throw new LiquidException('LiquidCacheApc requires PHP apc extension to be loaded.');
    }


    /**
     * Retrieves a value from cache with a specified key.
     *
     * @param string $key a unique key identifying the cached value
     * @return string the value stored in cache, false if the value is not in the cache or expired.
     */
    public function read($key, $unserialize = true)
    {
        return apc_fetch($this->_prefix . $key);
    }


    /**
     * Check if specified key exists in cache.
     *
     * @param string $key a unique key identifying the cached value
     * @return boolean true if the key is in cache, false otherwise
     */
    public function exists($key)
    {
        apc_fetch($this->_prefix . $key, &$success);
        return $success;
    }


    /**
     * Stores a value identified by a key in cache.
     *
     * @param string $key the key identifying the value to be cached
     * @param string $value the value to be cached
     * @return boolean true if the value is successfully stored into cache, false otherwise
     */
    public function write($key, &$value, $serialize = true)
    {
        return apc_store($this->_prefix . $key, $value, $this->_expire);
    }


    /**
     * Deletes all values from cache.
     *
     * @return boolean whether the flush operation was successful.
     */
    public function flush($expiredOnly = false)
    {
        return apc_clear_cache('user');
    }
}
