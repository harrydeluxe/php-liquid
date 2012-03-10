<?php
/**
 * Base class for Cache
 *
 * @package Liquid
 * @copyright Copyright (c) 2011-2012 Harald Hanek
 * @license http://harrydeluxe.mit-license.org
 */

abstract class LiquidCache
{
    protected $_expire = 3600;

    protected $_prefix = 'liquid_';

    protected $_path;


    public function __construct($options = array())
    {
        if (isset($options['cache_expire']))
        {
            $this->_expire = $options['cache_expire'];
        }

        if (isset($options['cache_prefix']))
        {
            $this->_prefix = $options['cache_prefix'];
        }
    }

    public function read($key, $unserialize = true)
    {
    }

    public function exists($key)
    {
    }

    public function write($key, &$value, $serialize = true)
    {
    }

    public function flush($expiredOnly = false)
    {
    }
}
