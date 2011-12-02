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
	 * @param LiquidFileSystem $file_system
	 * @return AssignLiquidTag
	 */
	public function __construct($markup, &$tokens, &$file_system)
	{
		$syntax = new LiquidRegexp("/(\w+)\s*(".LIQUID_ALLOWED_VARIABLE_CHARS."+)/");

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
		$initial_value = $context->get($this->_toIncrement);

		if (is_numeric($initial_value))
		{
			$context->set($this->_toIncrement, $initial_value + 1);
		}
	}
}
