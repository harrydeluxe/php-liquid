<?php
/**
 * Captures the output inside a block and assigns it to a variable
 * 
 * @example
 * {% capture foo %} bar {% endcapture %}
 *
 * @package Liquid
 * @copyright Copyright (c) 2011-2012 Harald Hanek, 
 * fork of php-liquid (c) 2006 Mateo Murphy,
 * based on Liquid for Ruby (c) 2006 Tobias Luetke
 * @license http://harrydeluxe.mit-license.org
 */

class LiquidTagCapture extends LiquidBlock
{
    /**
     * The variable to assign to
     *
     * @var string
     */
    private $_to;


    /**
     * Constructor
     *
     * @param string $markup
     * @param Array $tokens
     * @param LiquidFileSystem $fileSystem
     * @return CaptureLiquidTag
     */
    public function __construct($markup, &$tokens, &$fileSystem)
    {
        $syntaxRegexp = new LiquidRegexp('/(\w+)/');

        if ($syntaxRegexp->match($markup))
        {
            $this->_to = $syntaxRegexp->matches[1];
            parent::__construct($markup, $tokens, $fileSystem);
        }
        else
        {
            throw new LiquidException("Syntax Error in 'capture' - Valid syntax: assign [var] = [source]"); // harry
        }
    }


    /**
     * Renders the block
     *
     * @param LiquidContext $context
     */
    public function render(&$context)
    {
        $output = parent::render($context);

        $context->set($this->_to, $output);
    }
}
