<?php
/**
 * Cycles between a list of values; calls to the tag will return each value in turn
 * 
 * @example
 * {%cycle "one", "two"%} {%cycle "one", "two"%} {%cycle "one", "two"%}
 * 
 * this will return:
 * one two one
 * 
 * Cycles can also be named, to differentiate between multiple cycle with the same values:
 * {%cycle 1: "one", "two" %} {%cycle 2: "one", "two" %} {%cycle 1: "one", "two" %} {%cycle 2: "one", "two" %}
 * 
 * will return
 * one one two two
 * 
 * @package Liquid
 * @copyright Copyright (c) 2011-2012 Harald Hanek, 
 * fork of php-liquid (c) 2006 Mateo Murphy,
 * based on Liquid for Ruby (c) 2006 Tobias Luetke
 * @license http://harrydeluxe.mit-license.org
 */

class LiquidTagCycle extends LiquidTag
{
    /**
     * @var string The name of the cycle; if none is given one is created using the value list
     */
    private $_name;

    /**
     * @var array The variables to cycle between
     */
    private $_variables;


    /**
     * Constructor
     *
     * @param string $markup
     * @param array $tokens
     * @return CycleLiquidTag
     */
    public function __construct($markup, &$tokens, &$fileSystem)
    {
        $simpleSyntax = new LiquidRegexp("/" . LIQUID_QUOTED_FRAGMENT . "/");
        $namedSyntax = new LiquidRegexp("/(" . LIQUID_QUOTED_FRAGMENT . ")\s*\:\s*(.*)/");

        if ($namedSyntax->match($markup))
        {
            $this->_variables = $this->_variablesFromString($namedSyntax->matches[2]);
            $this->_name = $namedSyntax->matches[1];
        }
        elseif ($simpleSyntax->match($markup))
        {
            $this->_variables = $this->_variablesFromString($markup);
            $this->_name = "'" . implode($this->_variables) . "'";
        }
        else
        {
            throw new LiquidException("Syntax Error in 'cycle' - Valid syntax: cycle [name :] var [, var2, var3 ...]");
        }
    }


    /**
     * Renders the tag
     * 
     * @var LiquidContext $context
     * @return string
     */
    public function render(&$context)
    {
        $context->push();

        $key = $context->get($this->_name);

        if (isset($context->registers['cycle'][$key]))
        {
            $iteration = $context->registers['cycle'][$key];
        }
        else
        {
            $iteration = 0;
        }

        $result = $context->get($this->_variables[$iteration]);

        $iteration += 1;

        if ($iteration >= count($this->_variables))
        {
            $iteration = 0;
        }

        $context->registers['cycle'][$key] = $iteration;

        $context->pop();

        return $result;
    }


    /**
     * Extract variables from a string of markup
     * 
     * @param string $markup
     * @return array;
     */
    private function _variablesFromString($markup)
    {
        $regexp = new LiquidRegexp('/\s*(' . LIQUID_QUOTED_FRAGMENT . ')\s*/');
        $parts = explode(',', $markup);
        $result = array();

        foreach($parts as $part)
        {
            $regexp->match($part);

            if ($regexp->matches[1])
            {
                $result[] = $regexp->matches[1];
            }
        }

        return $result;
    }
}
