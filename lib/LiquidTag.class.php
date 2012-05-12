<?php
/**
 * Base class for tags
 * 
 * @package Liquid
 * @copyright Copyright (c) 2011-2012 Harald Hanek, 
 * fork of php-liquid (c) 2006 Mateo Murphy,
 * based on Liquid for Ruby (c) 2006 Tobias Luetke
 * @license http://harrydeluxe.mit-license.org
 */

abstract class LiquidTag
{
    /**
     * The markup for the tag
     *
     * @var string
     */
    protected $_markup;

    /**
     * Filesystem object is used to load included template files
     *
     * @var LiquidFileSystem
     */
    protected $_fileSystem;

    /**
     * Additional attributes
     *
     * @var array
     */
    protected $_attributes;


    /**
     * Constructor
     *
     * @param string $markup
     * @param array $tokens
     * @param LiquidFileSystem $fileSystem
     * @return LiquidTag
     */
    public function __construct($markup, &$tokens, &$fileSystem)
    {
        $this->_markup = $markup;
        $this->_fileSystem = $fileSystem;
        return $this->parse($tokens);
    }


    /**
     * Parse the given tokens
     *
     * @param array $tokens
     */
    public function parse(&$tokens)
    {
    }


    /**
     * Extracts tag attributes from a markup string
     *
     * @param string $markup
     */
    public function extractAttributes($markup)
    {
        $this->_attributes = array();

        $attribute_regexp = new LiquidRegexp(LIQUID_TAG_ATTRIBUTES);

        $matches = $attribute_regexp->scan($markup);

        foreach($matches as $match)
        {
            $this->_attributes[$match[0]] = $match[1];
        }
    }


    /**
     * Returns the name of the tag
     *
     * @return string
     */
    public function name()
    {
        return strtolower(get_class($this));
    }


    /**
     * Render the tag with the given context
     *
     * @param LiquidContext $context
     * @return string
     */
    public function render(&$context)
    {
        return '';
    }
}
