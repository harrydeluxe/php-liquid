<?php
/**
 * Used to decrement a counter into a template
 * 
 * @example 
 * {% decrement value %}
 *
 * @author Viorel Dram
 *
 * @package Liquid
 */

class LiquidTagDecrement extends LiquidTag
{
    /**
     * Name of the variable to decrement
     *
     * @var int
     */
    private $_toDecrement;

    /**
     * Constructor
     *
     * @param string $markup
     * @param array $tokens
     * @param LiquidFileSystem $fileSystem
     * @return AssignLiquidTag
     */
    public function __construct($markup, &$tokens, &$fileSystem)
    {
        $syntax = new LiquidRegexp("/(" . LIQUID_ALLOWED_VARIABLE_CHARS . "+)/");

        if ($syntax->match($markup))
        {
            $this->_toDecrement = $syntax->matches[0];
        }
        else
        {
            throw new LiquidException("Syntax Error in 'decrement' - Valid syntax: decrement [var]");
        }
    }

    /**
     * Renders the tag
     *
     * @param LiquidContext $context
     */
    public function render(&$context)
    {
        // if the value is not set in the environment check to see if it
        // exists in the context, and if not set it to 0
        if (!isset($context->environments[0][$this->_toDecrement]))
        {
            // check for a context value
            $from_context = $context->get($this->_toDecrement);

            // we already have a value in the context
            $context->environments[0][$this->_toDecrement] = (null !== $from_context) ? $from_context : 0;
        }

        // decrement the environment value
        $context->environments[0][$this->_toDecrement]--;
    }
}
