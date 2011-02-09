<?php

/**
 * Performs an assignment of one variable to another
 * 
 * @example 
 * {%assign var = var %}
 *
 * @package Liquid
 */
class AssignLiquidTag extends LiquidTag
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
		$syntax_regexp = new LiquidRegexp('/(\w+)\s*=\s*('.LIQUID_ALLOWED_VARIABLE_CHARS.'+)/');
		
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
		$context->set($this->_to, $context->get($this->_from));
	}	
}