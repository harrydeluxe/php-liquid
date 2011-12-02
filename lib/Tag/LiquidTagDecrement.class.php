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
	 * @param LiquidFileSystem $file_system
	 * @return AssignLiquidTag
	 */
	public function __construct($markup, &$tokens, &$file_system)
	{
		$syntax = new LiquidRegexp("/(\w+)\s*(".LIQUID_ALLOWED_VARIABLE_CHARS."+)/");

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
		$initial_value = $context->get($this->_toDecrement);

		if (is_numeric($initial_value))
		{
			$context->set($this->_toDecrement, $initial_value - 1);
		}
	}
}
