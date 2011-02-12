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
 */
class LiquidTagFor extends LiquidBlock
{
	/**
	 * @var array The collection to loop over
	 */
	var $collection_name;
	
	/**
	 * @var string The variable name to assign collection elemnts to
	 */
	var $variable_name;

	/**
	 * @var string The name of the loop, which is a compound of the collection and variable names
	 */
	var $name;


	/**
	 * Constructor
	 *
	 * @param string $markup
	 * @param array $tokens
	 * @param LiquidFileSystem $file_system
	 * @return ForLiquidTag
	 */
	function __construct($markup, &$tokens, &$file_system)
	{
		parent::__construct($markup, $tokens, $file_system);
		
		$syntax_regexp = new LiquidRegexp('/(\w+)\s+in\s+('.LIQUID_ALLOWED_VARIABLE_CHARS.'+)/');
		
		if($syntax_regexp->match($markup))
		{
			$this->variable_name = $syntax_regexp->matches[1];
			$this->collection_name = $syntax_regexp->matches[2];
			$this->name = $syntax_regexp->matches[1].'-'.$syntax_regexp->matches[2];
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
	function render(&$context)
	{
		if(!isset($context->registers['for']))
		{
			$context->registers['for'] = array();
		}
		
		$collection = $context->get($this->collection_name);
		
		if(is_null($collection) || count($collection) == 0)
		{
			return '';
		}
		
		$range = array(0, count($collection));
		
		if(isset($this->attributes['limit']) || isset($this->attributes['offset']))
		{
			$offset = 0;
			
			if(isset($this->attributes['offset']))
			{
				if($this->attributes['offset'] == 'continue')
				{
					$offset = $context->registers['for'][$this->name];
				}
				else
				{
					$offset = $context->get($this->attributes['offset']);
				}
			} 
			
			//$limit = $context->get($this->attributes['limit']);
			$limit = (isset($this->attributes['limit'])) ? $context->get($this->attributes['limit']) : null;
			
			$range_end = $limit ? $limit : count($collection) - $offset;
			
			$range = array($offset, $range_end);
			
			$context->registers['for'][$this->name] = $range_end + $offset;
			
		}
		
		$result = '';
		
		$segment = array_slice($collection, $range[0], $range[1]);
		
		if(!count($segment))
		{
			return null;
		}
		
		$context->push();
		
		$length = count($segment);

		foreach($segment as $index => $item)
		{
			$context->set($this->variable_name, $item);
			$context->set('forloop', array(
				'name'		=> $this->name,
				'length' 	=> $length,
				'index' 	=> $index + 1,
				'index0' 	=> $index,
				'rindex'	=> $length - $index,
				'rindex0'	=> $length - $index - 1,
				'first'		=> (int)($index == 0),
				'last'		=> (int)($index == $length - 1)
			));
			
			$result .= $this->render_all($this->_nodelist, $context);
		}
		
		$context->pop();
		
		return $result;
	}
}