<?php
/**
 * Used to increment a counter into a template
 * 
 * @example 
 * {% increment value %}
 *
 * @author Viorel Dram
 *
 * @package Liquid
 */

class LiquidTagIncrement extends LiquidTag
{
    /**
     * Name of the variable to increment
     *
     * @var string
     */
    private $_toIncrement;

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
            $this->_toIncrement = $syntax->matches[0];
        }
        else
        {
            throw new LiquidException("Syntax Error in 'increment' - Valid syntax: increment [var]");
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
        // exists in the context, and if not set it to -1
        if (!isset($context->environments[0][$this->_toIncrement]))
        {
            // check for a context value
            $from_context = $context->get($this->_toIncrement);

            // we already have a value in the context
            $context->environments[0][$this->_toIncrement] = (null !== $from_context) ? $from_context : -1;
        }

        // increment the value
        $context->environments[0][$this->_toIncrement]++;
    }
}
