<?php
/**
 * Quickly create a table from a collection
 *
 * @package Liquid
 */
class LiquidTagTablerow extends LiquidBlock
{
	
	/**
	 * The variable name of the table tag
	 *
	 * @var string
	 */
	var $variable_name;
	
	/**
	 * The collection name of the table tags
	 *
	 * @var string
	 */
	var $collection_name;
	
	/**
	 * Additional attributes
	 *
	 * @var array
	 */
	var $attributes;


	/**
	 * Constructor
	 *
	 * @param string $markup
	 * @param array $tokens
	 * @param LiquidFileSystem $file_system
	 * @return TableRowLiquidTag
	 */
	public function __construct($markup, & $tokens, & $file_system)
	{
		parent::__construct($markup, $tokens, $file_system);
		
		$syntax = new LiquidRegexp("/(\w+)\s+in\s+(".LIQUID_ALLOWED_VARIABLE_CHARS."+)/");
		
		if($syntax->match($markup))
		{
			$this->variable_name = $syntax->matches[1];
			$this->collection_name = $syntax->matches[2];		
			
			$this->extract_attributes($markup);
		}
		else
		{
			throw new LiquidException("Syntax Error in 'table_row loop' - Valid syntax: table_row [item] in [collection] cols=3");
		}
	}


	/**
	 * Renders the current node
	 *
	 * @param LiquidContext $context
	 * @return string
	 */
	public function render(&$context)
	{
		$collection = $context->get($this->collection_name);
		
		if(!is_array($collection))
		{
			die(debug('not array', $collection));
		}
		
		// discard keys
		$collection = array_values($collection);
		
		if(isset($this->attributes['limit']) || isset($this->attributes['offset']))
		{
			$limit = $context->get($this->attributes['limit']);
			$offset = $context->get($this->attributes['offset']);
			$collection = array_slice($collection, $offset, $limit);
		}
		
		$length = count($collection);
		
		$cols = $context->get($this->attributes['cols']);
		
		$row = 1;
		$col = 0;
		
		$result = "<tr class=\"row1\">\n";
		
		$context->push();
		
		foreach($collection as $index => $item)
		{
			$context->set($this->variable_name, $item);			
			$context->set('tablerowloop', array(
				'length' 	=> $length,
				'index' 	=> $index + 1,
				'index0' 	=> $index,
				'rindex'	=> $length - $index,
				'rindex0'	=> $length - $index - 1,
				'first'		=> (int)($index == 0),
				'last'		=> (int)($index == $length - 1)
			
			));
			
			$result .= "<td class=\"col".(++ $col)."\">" . $this->render_all($this->_nodelist, $context) . "</td>";
			
			if($col == $cols && ! ($index == $length - 1))
			{
				$col = 0;
				$result .= "</tr>\n<tr class=\"row".(++ $row)."\">";
			}
		}
		
		$context->pop();
		
		$result .= "</tr>\n";
		
		return $result;
	}	
}