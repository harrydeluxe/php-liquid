<?php
/**
 * An if statement
 * 
 * @example
 * {% if true %} YES {% else %} NO {% endif %}
 * 
 * will return:
 * YES
 *
 * @package Liquid
 */
class IfLiquidTag extends LiquidDecisionBlock
{
	
	/**
	 * Nodes to render when condition is true
	 *
	 * @var array
	 */
	var $nodelist_true;
	
	/**
	 * Nodes to render when condition is false
	 *
	 * @var array
	 */
	var $nodelist_false;
	
	/**
	 * Operator for comparison
	 *
	 * @var string
	 */
	var $operator;


	/**
	 * Constructor
	 *
	 * @param string $markup
	 * @param array $tokens
	 * @param LiquidFileSystem $file_system
	 * @return IfLiquidTag
	 */
	function __construct($markup, &$tokens, &$file_system)
	{
		$regex = new LiquidRegexp('/('.LIQUID_QUOTED_FRAGMENT.')\s*([=!<>]+)?\s*('.LIQUID_QUOTED_FRAGMENT.')?/');
		
		$this->nodelist_true = & $this->nodelist;
		$this->nodelist = array();
		
		$this->nodelist_false = array();
		
		parent::__construct($markup, $tokens, $file_system);
		
		if($regex->match($markup))
		{
			$this->left = (isset($regex->matches[1])) ? $regex->matches[1] : null;
			$this->operator = (isset($regex->matches[2])) ? $regex->matches[2] : null;
			$this->right = (isset($regex->matches[3])) ? $regex->matches[3] : null;
		}
		else
		{
			throw new LiquidException("Syntax Error in tag 'if' - Valid syntax: if [condition]");
		}
	}


	/**
	 * Handler for unknown tags, handle else tags
	 *
	 * @param string $tag
	 * @param array $params
	 * @param array $tokens
	 */
	function unknown_tag($tag, $params, &$tokens)	// harry
	{
		if($tag == 'else')
		{
			$this->nodelist = & $this->nodelist_false;
			$this->nodelist_false = array();
		}
		else
		{
			parent::unknown_tag($tag, $params, $tokens);
		}
	}


	/**
	 * Render the tag
	 *
	 * @param LiquidContext $context
	 */
	function render(& $context)
	{
		$context->push();
		
		if($this->interpret_condition($this->left, $this->right, $this->operator, $context))
		{
			$result = $this->render_all($this->nodelist_true, $context);
		}
		else
		{
			$result = $this->render_all($this->nodelist_false, $context);
		}
		
		$context->pop();
		
		return $result;
	}
}