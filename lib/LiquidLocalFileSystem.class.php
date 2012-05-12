<?php
/**
 * This implements an abstract file system which retrieves template files named in a manner similar to Rails partials,
 * ie. with the template name prefixed with an underscore. The extension ".liquid" is also added.
 *
 * For security reasons, template paths are only allowed to contain letters, numbers, and underscore.
 *
 * @package Liquid
 * @copyright Copyright (c) 2011-2012 Harald Hanek,
 * fork of php-liquid (c) 2006 Mateo Murphy,
 * based on Liquid for Ruby (c) 2006 Tobias Luetke
 * @license http://harrydeluxe.mit-license.org
 */

class LiquidLocalFileSystem extends LiquidBlankFileSystem
{
    /**
     * The root path
     *
     * @var string
     */
    private $_root;


    /**
     * Constructor
     *
     * @param string $root The root path for templates
     * @return LiquidLocalFileSystem
     */
    public function __construct($root)
    {
        $this->_root = $root;
    }


    /**
     * Retrieve a template file
     *
     * @param string $templatePath
     * @return string
     */
    public function readTemplateFile($templatePath)
    {
        if (!($full_path = $this->fullPath($templatePath)))
        {
            throw new LiquidException("No such template '$templatePath'");
        }
        return file_get_contents($full_path);
    }


    /**
     * Resolves a given path to a full template file path, making sure it's valid
     *
     * @param string $templatePath
     * @return string
     */
    public function fullPath($templatePath)
    {
        $name_regex = new LiquidRegexp('/^[^.\/][a-zA-Z0-9_\/]+$/');

        if (!$name_regex->match($templatePath))
        {
            throw new LiquidException("Illegal template name '$templatePath'");
        }

        if (strpos($templatePath, '/') !== false)
        {
            $full_path = $this->_root . dirname($templatePath) . '/' . LIQUID_INCLUDE_PREFIX . basename($templatePath) . '.' . LIQUID_INCLUDE_SUFFIX;
        }
        else
        {
            $full_path = $this->_root . LIQUID_INCLUDE_PREFIX . $templatePath . '.' . LIQUID_INCLUDE_SUFFIX;
        }

        $root_regex = new LiquidRegexp('/' . preg_quote(realpath($this->_root), '/') . '/');


        if (!$root_regex->match(realpath($full_path)))
        {
            throw new LiquidException("Illegal template path '" . realpath($full_path) . "'");
        }

        return $full_path;
    }
}
