<?php

/**
 * Performs an assignment of one variable to another
 * 
 * @example 
 * {%assign var = var %}
 * {%assign var = "hello" | upcase %}
 *
 * @package Liquid
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
	 * @param LiquidFileSystem $file_system
	 * @return AssignLiquidTag
	 */
	public function __construct($markup, &$tokens, &$file_system)
	{
		$syntax_regexp = new LiquidRegexp('/(\w+)\s*=\s*('.LIQUID_QUOTED_FRAGMENT.'+)/');

		$filter_seperator_regexp = new LiquidRegexp('/'.LIQUID_FILTER_SEPARATOR.'\s*(.*)/');
		$filter_split_regexp = new LiquidRegexp('/'.LIQUID_FILTER_SEPARATOR.'/');
		$filter_name_regexp = new LiquidRegexp('/\s*(\w+)/');
		$filter_argument_regexp = new LiquidRegexp('/(?:'.LIQUID_FILTER_ARGUMENT_SEPARATOR.'|'.LIQUID_ARGUMENT_SEPARATOR.')\s*('.LIQUID_QUOTED_FRAGMENT.')/');

		$this->filters = array();

		if ($filter_seperator_regexp->match($markup))
		{
			$filters = $filter_split_regexp->split($filter_seperator_regexp->matches[1]);

			foreach($filters as $filter)
			{
				$filter_name_regexp->match($filter);
				$filtername = $filter_name_regexp->matches[1];

				$filter_argument_regexp->match_all($filter);
				$matches = Liquid::array_flatten($filter_argument_regexp->matches[1]);

				array_push($this->filters, array($filtername, $matches));
			}
		}

		if($syntax_regexp->match($markup))
		{
			$this->_to = $syntax_regexp->matches[1];
			$this->_from = $syntax_regexp->matches[2];
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

		foreach ($this->filters as $filter)
		{
			list($filtername, $filter_arg_keys) = $filter;

			$filter_arg_values = array();

			foreach($filter_arg_keys as $arg_key)
			{
				$filter_arg_values[] = $context->get($arg_key);
			}

			$output = $context->invoke($filtername, $output, $filter_arg_values);
		}

		$context->set($this->_to, $output);
	}	
}