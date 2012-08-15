<?php
/**
 * Performs an assignment of one variable to another
 * 
 * @example 
 * {% assign var = var %}
 * {% assign var = "hello" | upcase %}
 *
 * @package Liquid
 * @copyright Copyright (c) 2011-2012 Harald Hanek, 
 * fork of php-liquid (c) 2006 Mateo Murphy,
 * based on Liquid for Ruby (c) 2006 Tobias Luetke
 * @license http://harrydeluxe.mit-license.org
 */

class LiquidTagAssign extends LiquidTag
{
    /**
     * @var string The variable to assign from
     */
    private $_from;

    /**
     * @var string The variable to assign to
     */
    private $_to;


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
        $syntaxRegexp = new LiquidRegexp('/(\w+)\s*=\s*(' . LIQUID_QUOTED_FRAGMENT . '+)/');

        $filterSeperatorRegexp = new LiquidRegexp('/' . LIQUID_FILTER_SEPARATOR . '\s*(.*)/');
        $filterSplitRegexp = new LiquidRegexp('/' . LIQUID_FILTER_SEPARATOR . '/');
        $filterNameRegexp = new LiquidRegexp('/\s*(\w+)/');
        $filterArgumentRegexp = new LiquidRegexp('/(?:' . LIQUID_FILTER_ARGUMENT_SEPARATOR . '|' . LIQUID_ARGUMENT_SEPARATOR . ')\s*(' . LIQUID_QUOTED_FRAGMENT . ')/');

        $this->filters = array();

        if ($filterSeperatorRegexp->match($markup))
        {
            $filters = $filterSplitRegexp->split($filterSeperatorRegexp->matches[1]);

            foreach($filters as $filter)
            {
                $filterNameRegexp->match($filter);
                $filtername = $filterNameRegexp->matches[1];

                $filterArgumentRegexp->match_all($filter);
                $matches = Liquid::array_flatten($filterArgumentRegexp->matches[1]);

                array_push($this->filters, array(
                    $filtername, $matches
                ));
            }
        }

        if ($syntaxRegexp->match($markup))
        {
            $this->_to = $syntaxRegexp->matches[1];
            $this->_from = $syntaxRegexp->matches[2];
        }
        else
        {
            throw new LiquidException("Syntax Error in 'assign' - Valid syntax: assign [var] = [source]");
        }
    }


    /**
     * Renders the tag
     *
     * @param LiquidContext $context
     */
    public function render(&$context)
    {
        $output = $context->get($this->_from);

        foreach($this->filters as $filter)
        {
            list($filtername, $filterArgKeys) = $filter;

            $filterArgValues = array();

            foreach($filterArgKeys as $arg_key)
            {
                $filterArgValues[] = $context->get($arg_key);
            }

            $output = $context->invoke($filtername, $output, $filterArgValues);
        }

        $context->set($this->_to, $output, true);
    }
}
