<?php
/**
 * Loops over an array, assigning the current value to a given variable
 * 
 * @example
 * {%for item in array%} {{item}} {%endfor%}
 * 
 * With an array of 1, 2, 3, 4, will return 1 2 3 4
 * 
 * @package Liquid
 * @copyright Copyright (c) 2011-2012 Harald Hanek, 
 * fork of php-liquid (c) 2006 Mateo Murphy,
 * based on Liquid for Ruby (c) 2006 Tobias Luetke
 * @license http://harrydeluxe.mit-license.org
 */

class LiquidTagFor extends LiquidBlock
{
    /**
     * @var array The collection to loop over
     */
    private $_collectionName;
    
    /**
     * @var mixed The range start
     */
    private $_rangeStart;


    /**
     * @var mixed The range end
     */
    private $_rangeEnd;
    

    /**
     * @var string The variable name to assign collection elements to
     */
    private $_variableName;

    /**
     * @var string The name of the loop, which is a compound of the collection and variable names
     */
    private $_name;


    /**
     * Constructor
     *
     * @param string $markup
     * @param array $tokens
     * @param LiquidFileSystem $file_system
     * @return ForLiquidTag
     */
    public function __construct($markup, &$tokens, &$file_system)
    {
        parent::__construct($markup, $tokens, $file_system);

        $syntax_regexp = new LiquidRegexp('/(\w+)\s+in\s+(' . LIQUID_ALLOWED_VARIABLE_CHARS . '+)/');
        $syntax_range_regexp = new LiquidRegexp('/(\w+)\s+in\s+\(([0-9a-zA-Z_.-]+..[0-9a-zA-Z_.-]+)\)/');

        if ($syntax_regexp->match($markup))
        {
            $this->_variableName = $syntax_regexp->matches[1];
            $this->_collectionName = $syntax_regexp->matches[2];
            $this->_name = $syntax_regexp->matches[1] . '-' . $syntax_regexp->matches[2];
            
            $this->extract_attributes($markup);
        }
        else if ($syntax_range_regexp->match($markup)) {
            $this->_variableName = $syntax_range_regexp->matches[1];
            
            $range = explode("..",$syntax_range_regexp->matches[2]);
            $this->_rangeStart = $range[0];
            $this->_rangeEnd = $range[1];
            
            $this->_name = $syntax_range_regexp->matches[1] . '-' . $syntax_range_regexp->matches[2];
            $this->extract_attributes($markup);
        }
        else
        {
            throw new LiquidException("Syntax Error in 'for loop' - Valid syntax: for [item] in [collection]");
        }
    }


    /**
     * Renders the tag
     *
     * @param LiquidContext $context
     */
    public function render(&$context)
    {
        if (!isset($context->registers['for']))
        {
            $context->registers['for'] = array();
        }

        if ($this->_rangeStart || $this->_rangeEnd) {
            
            $start = (int)$context->get($this->_rangeStart);
            $end = (int)$context->get($this->_rangeEnd);
            $collection = range($start,$end);
            
        } else {
            $collection = $context->get($this->_collectionName);
        }

        if (is_null($collection) || !is_array($collection) || count($collection) == 0)
        {
            return '';
        }

        $range = array(
            0, count($collection)
        );

        if (isset($this->attributes['limit']) || isset($this->attributes['offset']))
        {
            $offset = 0;

            if (isset($this->attributes['offset']))
            {
                $offset = ($this->attributes['offset'] == 'continue') ? $context->registers['for'][$this->_name] : $context->get($this->attributes['offset']);
            }

            //$limit = $context->get($this->attributes['limit']);
            $limit = (isset($this->attributes['limit'])) ? $context->get($this->attributes['limit']) : null;

            $range_end = $limit ? $limit : count($collection) - $offset;

            $range = array(
                $offset, $range_end
            );

            $context->registers['for'][$this->_name] = $range_end + $offset;

        }

        $result = '';

        $segment = array_slice($collection, $range[0], $range[1]);

        if (!count($segment))
        {
            return null;
        }

        $context->push();

        $length = count($segment);

        /**
         * @todo If $segment keys are not integer, forloop not work
         * array_values is only a little help without being tested.
         */
        $segment = array_values($segment);


        foreach($segment as $index => $item)
        {
            $context->set($this->_variableName, $item);
            $context->set('forloop', array(
                    'name' => $this->_name,
                    'length' => $length,
                    'index' => $index + 1,
                    'index0' => $index,
                    'rindex' => $length - $index,
                    'rindex0' => $length - $index - 1,
                    'first' => (int) ($index == 0),
                    'last' => (int) ($index == $length - 1)
            ));

            $result .= $this->render_all($this->_nodelist, $context);
        }

        $context->pop();

        return $result;
    }
}
