<?php
/**
 * A switch statememt
 * 
 * @example
 * {% case condition %}{% when foo %} foo {% else %} bar {% endcase %}
 *
 * @package Liquid
 */
class LiquidTagCase extends LiquidDecisionBlock
{
	/**
	 * Stack of nodelists
	 *
	 * @var array
	 */
	var $nodelists;
	
	/**
	 * The nodelist for the else (default) nodelist
	 *
	 * @var array
	 */
	var $else_nodelist;
	
	/**
	 * The left value to compare
	 *
	 * @var string
	 */
	var $left;
	
	/**
	 * The current right value to compare
	 *
	 * @var unknown_type
	 */
	var $right;


	/**
	 * Constructor
	 *
	 * @param string $markup
	 * @param array $tokens
	 * @param LiquidFileSystem $file_system
	 * @return CaseLiquidTag
	 */
	function __construct($markup, &$tokens, &$file_system)
	{
		$this->nodelists = array();
		$this->else_nodelist = array();
		
		parent::__construct($markup, $tokens, $file_system);
		
		$syntax_regexp = new LiquidRegexp('/'.LIQUID_QUOTED_FRAGMENT.'/');
		
		if($syntax_regexp->match($markup))
		{
			$this->left = $syntax_regexp->matches[0];
		}
		else
		{
			throw new LiquidException("Syntax Error in tag 'case' - Valid syntax: case [condition]");// harry
		}
	}


	/**
	 * Pushes the last nodelist onto the stack
	 *
	 */
	function end_tag()
	{
		$this->push_nodelist();
	}


	/**
	 * Unknown tag handler
	 *
	 * @param string $tag
	 * @param array $params
	 * @param array $tokens
	 */
	function unknown_tag($tag, $params, & $tokens)
	{
		$when_syntax_regexp = new LiquidRegexp('/'.LIQUID_QUOTED_FRAGMENT.'/');
		
		switch ($tag) {
		case 'when':
			// push the current nodelist onto the stack and prepare for a new one
			if ($when_syntax_regexp->match($params)) {
				$this->push_nodelist();
				$this->right = $when_syntax_regexp->matches[0];
				$this->_nodelist = array();
				
			}
			else
			{
				throw new LiquidException("Syntax Error in tag 'case' - Valid when condition: when [condition]");// harry
			}
			break;
			
		case 'else':
			// push the last nodelist onto the stack and prepare to recieve the else nodes
			$this->push_nodelist();
			$this->right = null;
			$this->else_nodelist = & $this->_nodelist;
			$this->_nodelist = array();
			break;
		
		default:
			parent::unknown_tag($tag, $params, $tokens);	
		}
	}


	/**
	 * Pushes the current right value and nodelist into the nodelist stack
	 *
	 */
	function push_nodelist()
	{
		if(!is_null($this->right))
		{
			$this->nodelists[] = array($this->right, $this->_nodelist);
		}
	}


	/**
	 * Renders the node
	 *
	 * @param LiquidContext $context
	 */
	public function render(&$context)
	{
		$output = ''; // array();
		$run_else_block = true;
		
		foreach($this->nodelists as $data)
		{
			list($right, $nodelist) = $data;
			
			if($this->equal_variables($this->left, $right, $context))
			{
				$run_else_block = false;
				
				$context->push();
				$output .= $this->render_all($nodelist, $context);
				$context->pop();
			}
		}

		if($run_else_block)
		{
			$context->push();
			$output .= $this->render_all($this->else_nodelist, $context);
			$context->pop();
		}
	
		return $output;
	}
}