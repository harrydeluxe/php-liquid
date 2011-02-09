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
 */
class CycleLiquidTag extends LiquidTag
{
	/**
	 * @var string The name of the cycle; if none is given one is created using the value list
	 */
	var $name;

	/**
	 * @var array The variables to cycle between
	 */
	var $variables;	


	/**
	 * Constructor
	 *
	 * @param string $markup
	 * @param array $tokens
	 * @return CycleLiquidTag
	 */
	function __construct($markup, &$tokens, &$file_system)
	{
		$simple_syntax = new LiquidRegexp("/".LIQUID_QUOTED_FRAGMENT."/");
		$named_syntax = new LiquidRegexp("/(".LIQUID_QUOTED_FRAGMENT.")\s*\:\s*(.*)/");
		
		if($named_syntax->match($markup))
		{
			$this->variables = $this->variables_from_string($named_syntax->matches[2]);
			$this->name = $named_syntax->matches[1];
		}
		elseif($simple_syntax->match($markup))
		{
			$this->variables = $this->variables_from_string($markup);
			$this->name = "'".implode($this->variables)."'";
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
	function render(&$context)
	{
		$context->push();
		
		$key = $context->get($this->name);
		
		if(isset($context->registers['cycle'][$key]))
		{
			$iteration = $context->registers['cycle'][$key];
		}
		else
		{
			$iteration = 0;
		}
		
		$result = $context->get($this->variables[$iteration]);
		
		$iteration += 1;

		if($iteration >= count($this->variables))
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
	function variables_from_string($markup)
	{
		$regexp = new LiquidRegexp('/\s*('.LIQUID_QUOTED_FRAGMENT.')\s*/');
		$parts = explode(',', $markup);
		$result = array();
		
		foreach($parts as $part)
		{
			$regexp->match($part);
			
			if($regexp->matches[1])
			{
				$result[] = $regexp->matches[1];
			}
		}
		
		return $result;
	}	
}