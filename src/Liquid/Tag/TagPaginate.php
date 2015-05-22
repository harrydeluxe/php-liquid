<?php

/**
 * This file is part of the Liquid package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Liquid
 */

namespace Liquid\Tag;

use Liquid\AbstractBlock;
use Liquid\Liquid;
use Liquid\Context;
use Liquid\LiquidException;
use Liquid\FileSystem;
use Liquid\Regexp;

/**
 * Quickly create a table from a collection
 */
class TagPaginate extends AbstractBlock
{
	/**
     * @var array The collection to paginate
     */
    private $collectionName;

    /**
     * @var array The collection object
     */
    private $collection;
    
    /**
     *
     * @var int The size of the collection
     */
    private $collectionSize;

	/**
     * @var int The number of items to paginate by
     */
    private $numberItems;
    
    /**
     * @var int The current page
     */
    private $currentPage;
    
    /**
     * @var int Total pages
     */
    private $totalPages;

    
    /**
     * Constructor
     *
     * @param string $markup
     * @param array $tokens
     * @param LiquidFileSystem $fileSystem
     * @return ForLiquidTag
     */
	public function __construct($markup, array &$tokens, FileSystem $fileSystem = null) {
        parent::__construct($markup, $tokens, $fileSystem);

        $syntax = new LiquidRegexp('/(' . Liquid::get('ALLOWED_VARIABLE_CHARS') . '+)\s+by\s+(\w+)/');

        if ($syntax->match($markup)){
            $this->collectionName = $syntax->matches[1];
            $this->numberItems = $syntax->matches[2];
            $this->currentPage = ( is_numeric($_GET['page']) ) ? $_GET['page'] : 1;
            $this->currentOffset = ($this->currentPage - 1) * $this->numberItems;
            $this->extractAttributes($markup);
        } else {
            throw new LiquidException("Syntax Error - Valid syntax: paginate [collection] by [items]");
        }
    }

    /**
     * Renders the tag
     *
     * @param LiquidContext $context
     */
    public function render(Context $context) {
    	$this->collection = $context->get($this->collectionName);
    	$this->collectionSize = count($this->collection);
    	$this->totalPages = ceil($this->collectionSize / $this->numberItems);
    	$paginated_collection =  array_slice($this->collection,$this->currentOffset,$this->numberItems);
    	
    	// Sets the collection if it's a key of another collection (ie search.results, collection.products, blog.articles)
    	$segments = explode('.',$this->collectionName);
    	if ( count($segments) == 2 ){
	    	$context->set($segments[0], array($segments[1] => $paginated_collection));
    	} else {
	    	$context->set($this->collectionName, $paginated_collection);
    	}
    	
    	$paginate = array(
    		'page_size' => $this->numberItems,
    		'current_page' => $this->currentPage,
    		'current_offset' => $this->currentOffset,
    		'pages' => $this->totalPages,
    		'items' => $this->collectionSize
    	);
    	
    	if ( $this->currentPage != 1 ){
	    	$paginate['previous']['title'] = 'Previous';
	    	$paginate['previous']['url'] = $this->currentUrl() . '?page=' . ($this->currentPage - 1);
    	
    	}
    	
    	if ( $this->currentPage != $total_pages ){
	    	$paginate['next']['title'] = 'Next';
	    	$paginate['next']['url'] = $this->currentUrl() . '?page=' . ($this->currentPage + 1);
    	}

    	$context->set('paginate',$paginate);
        return parent::render($context);
    }
    
    /**
     * Returns the current page URL
     */
    public function currentUrl(){
	    $url = 'http';
		if ($_SERVER['HTTPS'] == 'on') $url .= 's';
		$url .= '://' . $_SERVER["HTTP_HOST"] . reset(explode('?', $_SERVER["REQUEST_URI"]));
		return $url;
    }
    
}
