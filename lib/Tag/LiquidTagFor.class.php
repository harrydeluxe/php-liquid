<?php
/**
 * Loops over an array, assigning the current value to a given variable
 * 
 * @example
 * {%for item in array%} {{item}} {%endfor%}
 * 
 * With an array of 1, 2, 3, 4, will return 1 2 3 4
 * or
 *
 * {%for i in (1..10)%} {{i}} {%endfor%}
 * {%for i in (1..variable)%} {{i}} {%endfor%} 
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
	 * @var string The variable name to assign collection elements to
	 */
	private $_variableName;

	/**
	 * @var string The name of the loop, which is a compound of the collection and variable names
	 */
	private $_name;

	/**
	 * @var type The type of the loop (collection or digit)
	*/
	private $_type = 'collection';

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
		
		$syntax_regexp = new LiquidRegexp('/(\w+)\s+in\s+('.LIQUID_ALLOWED_VARIABLE_CHARS.'+)/');
		
		if($syntax_regexp->match($markup))
		{
			$this->_variableName = $syntax_regexp->matches[1];
			$this->_collectionName = $syntax_regexp->matches[2];
			$this->_name = $syntax_regexp->matches[1].'-'.$syntax_regexp->matches[2];
			$this->extractAttributes($markup);
		}
		else
		{
				
			$syntax_regexp = new LiquidRegexp('/(\w+)\s+in\s+\((\d|'.LIQUID_ALLOWED_VARIABLE_CHARS.'+)\s*..\s*(\d|'.LIQUID_ALLOWED_VARIABLE_CHARS.'+)\)/');
			if ($syntax_regexp->match($markup)){
				$this->_type = 'digit';
				$this->_variableName = $syntax_regexp->matches[1];
				$this->_start = $syntax_regexp->matches[2];
				$this->_collectionName = $syntax_regexp->matches[3];
				$this->_name = $syntax_regexp->matches[1].'-digit';
				$this->extractAttributes($markup);
			} else {
				throw new LiquidException("Syntax Error in 'for loop' - Valid syntax: for [item] in [collection] OR for [int] in ([start]..[end])");
			}
			
		}
	}

	
	/**
	 * Renders the tag
	 *
	 * @param LiquidContext $context
	 */
	public function render(&$context)
	{
		if(!isset($context->registers['for']))
		{
			$context->registers['for'] = array();
		}
		
		switch ($this->_type){
		
			case 'collection':
			
				$collection = $context->get($this->_collectionName);
				
				if(is_null($collection) || !is_array($collection) || count($collection) == 0)
				{
					return '';
				}
				
				$range = array(0, count($collection));
				
				if(isset($this->attributes['limit']) || isset($this->attributes['offset']))
				{
					$offset = 0;
					
					if(isset($this->attributes['offset']))
					{
						$offset = ($this->attributes['offset'] == 'continue') ? $context->registers['for'][$this->_name] : $context->get($this->attributes['offset']);
					} 
					
					//$limit = $context->get($this->attributes['limit']);
					$limit = (isset($this->attributes['limit'])) ? $context->get($this->attributes['limit']) : null;
					
					$range_end = $limit ? $limit : count($collection) - $offset;
					
					$range = array($offset, $range_end);
					
					$context->registers['for'][$this->_name] = $range_end + $offset;
					
				}
				
				$result = '';
				
				$segment = array_slice($collection, $range[0], $range[1]);
				
				if(!count($segment))
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
						'name'		=> $this->_name,
						'length' 	=> $length,
						'index' 	=> $index + 1,
						'index0' 	=> $index,
						'rindex'	=> $length - $index,
						'rindex0'	=> $length - $index - 1,
						'first'		=> (int)($index == 0),
						'last'		=> (int)($index == $length - 1)
					));
					
					$result .= $this->renderAll($this->_nodelist, $context);
				}
				
			break;
			
			case 'digit':
			
				$start = $this->_start;
				if (!is_integer($this->_start)){
					$start = $context->get($this->_start);
				}
				
				$end = $this->_collectionName;
				if (!is_integer($this->_collectionName)){
					$end = $context->get($this->_collectionName);
				}
				
				$range = array($start, $end);
				
				$context->push();
				$result = '';
				$index = 0;
				$length = $range[1] - $range[0];
				for ($i=$range[0]; $i<=$range[1]; $i++){
				
					$context->set($this->_variableName, $i);
					$context->set('forloop', array(
						'name'		=> $this->_name,
						'length' 	=> $length,
						'index' 	=> $index + 1,
						'index0' 	=> $index,
						'rindex'	=> $length - $index,
						'rindex0'	=> $length - $index - 1,
						'first'		=> (int)($index == 0),
						'last'		=> (int)($index == $length - 1)
					));
					
					$result .= $this->renderAll($this->_nodelist, $context);
					
					$index++;
				}
			
			break;
		
		}
		
		$context->pop();
		
		return $result;
	}
}