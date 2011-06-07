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
class LiquidTagIf extends LiquidDecisionBlock
{
	
	/**
	 * Nodes to render when condition is true
	 *
	 * @var array
	 */
	private $_nodelistTrue;
	
	/**
	 * Nodes to render when condition is false
	 *
	 * @var array
	 */
	private $_nodelistFalse;
	
	/**
	 * Operator for comparison
	 *
	 * @var string
	 */
	private $_operator;


	/**
	 * Constructor
	 *
	 * @param string $markup
	 * @param array $tokens
	 * @param LiquidFileSystem $file_system
	 * @return IfLiquidTag
	 */
	public function __construct($markup, &$tokens, &$file_system)
	{
		//$regex = new LiquidRegexp('/('.LIQUID_QUOTED_FRAGMENT.')\s*([=!<>]+)?\s*('.LIQUID_QUOTED_FRAGMENT.')?/');
		$regex = new LiquidRegexp('/('.LIQUID_QUOTED_FRAGMENT.')\s*([=!<>a-z_]+)?\s*('.LIQUID_QUOTED_FRAGMENT.')?/');
		
		$this->_nodelistTrue = & $this->_nodelist;
		$this->_nodelist = array();
		
		$this->_nodelistFalse = array();
		
		parent::__construct($markup, $tokens, $file_system);
		
		if($regex->match($markup))
		{
			$this->left = (isset($regex->matches[1])) ? $regex->matches[1] : null;
			$this->_operator = (isset($regex->matches[2])) ? $regex->matches[2] : null;
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
	function unknown_tag($tag, $params, &$tokens)
	{
		if($tag == 'else' || $tag == 'elsif')
		{
			$this->_nodelist = & $this->_nodelistFalse;
			$this->_nodelistFalse = array();
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
	public function render(&$context)
	{
		$context->push();
		
		if($this->interpret_condition($this->left, $this->right, $this->_operator, $context))
		{
			$result = $this->render_all($this->_nodelistTrue, $context);
		}
		else
		{
			$result = $this->render_all($this->_nodelistFalse, $context);
		}
		
		$context->pop();
		
		return $result;
	}
}
