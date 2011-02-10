<?php 
/**
 * Liquid for PHP
 * 
 * @package Liquid
 * @copyright Copyright (c) 2011 Harald Hanek, 
 * fork of php-liquid (c) 2006 Mateo Murphy,
 * based on Liquid for Ruby (c) 2006 Tobias Luetke
 * @license http://www.opensource.org/licenses/mit-license.php
 */


/**
 * The template class.
 * 
 * @example 
 * $tpl = new LiquidTemplate();
 * $tpl->parse(template_source);
 * $tpl->render(array('foo'=>1, 'bar'=>2);
 *
 * @package Liquid
 */
class LiquidTemplate
{
	/**
	 * @var LiquidDocument The root of the node tree
	 */
	private $root;
	
	/**
	 * @var LiquidBlankFileSystem The file system to use for includes
	 */
	var $file_system;
	
	/**
	 * @var array Globally included filters
	 */
	var $filters;


	/**
	 * Constructor
	 *
	 * @return LiquidTemplate
	 */
	public function __construct($path = null)
	{
		//$this->file_system = new LiquidBlankFileSystem();
		$this->file_system = (isset($path)) ? new LiquidLocalFileSystem($path) : new LiquidBlankFileSystem();
		$this->filters = array();
	}
	
/*	this is currently not needed
	function register_tag($name) {
		$this->tags[$name] = $name;
		
	}
*/	
	/**
	 * Register the filter
	 *
	 * @param unknown_type $filter
	 */
	function register_filter($filter)
	{
		$this->filters[] = $filter;
	}	


	/**
	 * Tokenizes the given source string
	 *
	 * @param string $source
	 * @return array
	 */
	public static function tokenize($source)
	{
		return (!$source) ? array() : preg_split(LIQUID_TOKENIZATION_REGEXP, $source, null, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
	}


	/**
	 * Parses the given source string
	 *
	 * @param string $source
	 */
	public function parse($source)
	{
		$this->root = new LiquidDocument(LiquidTemplate::tokenize($source), $this->file_system);
		return $this;
	}


	/**
	 * Renders the current template
	 *
	 * @param array $assigns An array of values for the template
	 * @param array $filters Additional filters for the template
	 * @param array $registers Additional registers for the template
	 * @return string
	 */
	public function render(array $assigns = array(), $filters = null, $registers = null)
	{
		$context = new LiquidContext($assigns, $registers);
		
		if(!is_null($filters))
		{
			if(is_array($filters))
			{
				array_merge($this->filters, $filters);
			}
			else
			{
				$this->filters[] = $filters;
			}
		}
	
		foreach($this->filters as $filter)
		{
			$context->add_filters($filter);
		}
		
		return $this->root->render($context);
	}
}