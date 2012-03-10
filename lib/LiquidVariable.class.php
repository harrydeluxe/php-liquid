<?php
/**
 * Implements a template variable
 *
 * @package Liquid
 * @copyright Copyright (c) 2011-2012 Harald Hanek, 
 * fork of php-liquid (c) 2006 Mateo Murphy,
 * based on Liquid for Ruby (c) 2006 Tobias Luetke
 * @license http://harrydeluxe.mit-license.org
 */

class LiquidVariable
{
    /**
     * @var array The filters to execute on the variable
     */
    private $filters;

    /**
     * @var string The name of the variable
     */
    private $_name;
    //public $_name;

    /**
     * @var string The markup of the variable
     */
    private $markup;


    public function getName()
    {
        return $this->_name;
    }

    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Constructor
     *
     * @param string $markup
     * @return LiquidVariable
     */
    public function __construct($markup)
    {
        $this->markup = $markup;

        $quoted_fragment_regexp = new LiquidRegexp('/\s*(' . LIQUID_QUOTED_FRAGMENT . ')/');
        $filter_seperator_regexp = new LiquidRegexp('/' . LIQUID_FILTER_SEPARATOR . '\s*(.*)/');
        $filter_split_regexp = new LiquidRegexp('/' . LIQUID_FILTER_SEPARATOR . '/');
        $filter_name_regexp = new LiquidRegexp('/\s*(\w+)/');
        $filter_argument_regexp = new LiquidRegexp('/(?:' . LIQUID_FILTER_ARGUMENT_SEPARATOR . '|' . LIQUID_ARGUMENT_SEPARATOR . ')\s*(' . LIQUID_QUOTED_FRAGMENT . ')/');

        $quoted_fragment_regexp->match($markup);
        //$this->_name = $quoted_fragment_regexp->matches[1];
        $this->_name = (isset($quoted_fragment_regexp->matches[1])) ? $quoted_fragment_regexp->matches[1] : null; // harry


        if ($filter_seperator_regexp->match($markup))
        {

            $filters = $filter_split_regexp->split($filter_seperator_regexp->matches[1]);

            foreach($filters as $filter)
            {
                $filter_name_regexp->match($filter);
                $filtername = $filter_name_regexp->matches[1];

                $filter_argument_regexp->match_all($filter);
                //$matches = array_flatten($filter_argument_regexp->matches[1]);
                $matches = Liquid::array_flatten($filter_argument_regexp->matches[1]);

                $this->filters[] = array(
                    $filtername, $matches
                );
            }

        }
        else
        {
            $this->filters = array();
        }
    }


    /**
     * Renders the variable with the data in the context
     *
     * @param LiquidContext $context
     */
    function render($context)
    {
        $output = $context->get($this->_name);
        
        foreach($this->filters as $filter)
        {
            list($filtername, $filter_arg_keys) = $filter;

            $filter_arg_values = array();

            foreach($filter_arg_keys as $arg_key)
            {
                $filter_arg_values[] = $context->get($arg_key);
            }

            $output = $context->invoke($filtername, $output, $filter_arg_values);
        }

        return $output;
    }
}
