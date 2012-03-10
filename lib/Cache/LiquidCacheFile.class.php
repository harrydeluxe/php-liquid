<?php
/**
 * LiquidCacheFile class file
 *
 * @package Liquid
 * @copyright Copyright (c) 2011-2012 Harald Hanek
 * @license http://harrydeluxe.mit-license.org
 */

class LiquidCacheFile extends LiquidCache
{
    /**
     * Initializes this component.
     * 
     * It checks the availability of apccache.
     * @throws LiquidException if Cachedir not exists.
     */
    public function __construct($options = array())
    {
        parent::__construct($options);

        if (isset($options['cache_dir']) && is_writable($options['cache_dir']))
            $this->_path = realpath($options['cache_dir']) . DIRECTORY_SEPARATOR;
        else
            throw new LiquidException('Cachedir not exists or not writable');
    }


    /**
     * Retrieves a value from cache with a specified key.
     *
     * @param string $key a unique key identifying the cached value
     * @return string the value stored in cache, false if the value is not in the cache or expired.
     */
    public function read($key, $unserialize = true)
    {
        if (!$this->exists($key))
            return false;

        if ($unserialize)
            return unserialize(file_get_contents($this->_path . $this->_prefix . $key));

        return file_get_contents($this->_path . $this->_prefix . $key);
    }


    /**
     * Check if specified key exists in cache.
     *
     * @param string $key a unique key identifying the cached value
     * @return boolean true if the key is in cache, false otherwise
     */
    public function exists($key)
    {
        $cacheFile = $this->_path . $this->_prefix . $key;

        if (!file_exists($cacheFile) || @filemtime($cacheFile) + $this->_expire < time())
            return false;

        return true;
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
        if (@file_put_contents($this->_path . $this->_prefix . $key, $serialize ? serialize($value) : $value) !== false)
        {
            $this->gc();
            return true;
        }

        throw new LiquidException('Can not write cache file');
    }


    /**
     * Deletes all values from cache.
     *
     * @return boolean whether the flush operation was successful.
     */
    public function flush($expiredOnly = false)
    {
        foreach(glob($this->_path . $this->_prefix . '*') as $file)
        {
            if ($expiredOnly)
            {
                if (@filemtime($file) + $this->_expire < time())
                    @unlink($file);
            }
            else
                @unlink($file);
        }
    }


    /**
     * Removes expired cache files.
     * 
     * 
     */
    protected function gc()
    {
        $this->flush(true);
    }
}
