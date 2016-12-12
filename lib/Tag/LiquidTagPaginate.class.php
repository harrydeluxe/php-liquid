<?php
/**
 * Paginates a given collection
 *
 * @author Ryan Marshall (ryan@syngency.com)
 * 
 * @example
 * {% paginate blog.articles by 5 %} {% for article in blog.articles %} {% endpaginate %}
 * 
 * @package Liquid
 * @copyright Copyright (c) 2011-2012 Harald Hanek, 
 * fork of php-liquid (c) 2006 Mateo Murphy,
 * based on Liquid for Ruby (c) 2006 Tobias Luetke
 * @license http://harrydeluxe.mit-license.org
 */

class LiquidTagPaginate extends LiquidBlock
{
	/**
     * @var array The collection to paginate
     */
    private $_collectionName;

    /**
     * @var array The collection object
     */
    private $_collection;
    
    /**
     *
     * @var int The size of the collection
     */
    private $_collectionSize;

	/**
     * @var int The number of items to paginate by
     */
    private $_numberItems;
    
    /**
     * @var int The current page
     */
    private $_currentPage;
    
    /**
     * @var int Total pages
     */
    private $_totalPages;

    
    /**
     * Constructor
     *
     * @param string $markup
     * @param array $tokens
     * @param LiquidFileSystem $fileSystem
     * @return ForLiquidTag
     */
    public function __construct($markup, &$tokens, &$fileSystem)
    {
        parent::__construct($markup, $tokens, $fileSystem);

        $syntax = new LiquidRegexp('/(' . LIQUID_ALLOWED_VARIABLE_CHARS . '+)\s+by\s+(\w+)/');

        if ($syntax->match($markup))
        {
            $this->_collectionName = $syntax->matches[1];
            $this->_numberItems = $syntax->matches[2];
            $this->_currentPage = ( is_numeric($_GET['page']) ) ? $_GET['page'] : 1;
            $this->_currentOffset = ($this->_currentPage - 1) * $this->_numberItems;
            $this->extractAttributes($markup);
        }
        else
        {
            throw new LiquidException("Syntax Error - Valid syntax: paginate [collection] by [items]");
        }
    }

    /**
     * Renders the tag
     *
     * @param LiquidContext $context
     */
    public function render(&$context)
    {
    	$this->_collection = $context->get($this->_collectionName);
    	$this->_collectionSize = count($this->_collection);
    	$this->_totalPages = ceil($this->_collectionSize / $this->_numberItems);
    	$paginated_collection =  array_slice($this->_collection,$this->_currentOffset,$this->_numberItems);
    	
    	// Sets the collection if it's a key of another collection (ie search.results, collection.products, blog.articles)
    	$segments = explode('.',$this->_collectionName);
    	if ( count($segments) == 2 )
    	{
	    	$context->set($segments[0], array($segments[1] => $paginated_collection));
    	} 
    	else 
    	{
	    	$context->set($this->_collectionName, $paginated_collection);
    	}
    	
    	$paginate = array(
    		'page_size' => $this->_numberItems,
    		'current_page' => $this->_currentPage,
    		'current_offset' => $this->_currentOffset,
    		'pages' => $this->_totalPages,
    		'items' => $this->_collectionSize,
    		'previous' => false,
    		'next' => false
    	);
    	
    	if ( $this->_currentPage != 1 )
    	{
	    	$paginate['previous'] = array(
	    		'title' => '&laquo; Previous',
				'url' => $this->current_url() . '?page=' . ( $this->_currentPage - 1 )
			);
    	}
    	
    	if ( $this->_currentPage != $this->_totalPages )
    	{
	    	$paginate['next'] = array(
	    		'title' => 'Next &raquo;',
	    		'url' => $this->current_url() . '?page=' . ( $this->_currentPage + 1 )
	    	);
    	}

    	$context->set('paginate',$paginate);
        return parent::render($context);
    }
    
    /**
     * Returns the current page URL
     */
    public function current_url()
    {
	    $scheme = 'http';
		if ( $_SERVER['HTTPS'] == 'on' ) $scheme .= 's';
		$full_url = $scheme . '://' . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
		$parsed_url = parse_url($full_url);
		$current_url = $parsed_url['scheme'] . '://' . $parsed_url['host'] . $parsed_url['path'];
	    return $current_url;
    }
}
