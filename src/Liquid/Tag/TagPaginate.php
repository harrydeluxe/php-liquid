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
 * The paginate tag works in conjunction with the for tag to split content into numerous pages.
 *
 * Example:
 *
 *	{% paginate collection.products by 5 %}
 * 		{% for product in collection.products %}
 * 			<!--show product details here -->
 * 		{% endfor %}
 * 	{% endpaginate %}
 *
 */

class TagPaginate extends AbstractBlock
{
	/**
     * @var string The collection to paginate
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
     * @var int The current offset (no of pages times no of items)
     */
    private $currentOffset;

    /**
     * @var int Total pages
     */
    private $totalPages;


    /**
     * Constructor
     *
     * @param string $markup
     * @param array $tokens
	 * @param FileSystem $fileSystem
     *
	 * @throws \Liquid\LiquidException
     *
     */
	public function __construct($markup, array &$tokens, FileSystem $fileSystem = null) {

        parent::__construct($markup, $tokens, $fileSystem);

        $syntax = new Regexp('/(' . Liquid::get('VARIABLE_NAME') . ')\s+by\s+(\w+)/');

        if ($syntax->match($markup)) {
            $this->collectionName = $syntax->matches[1];
            $this->numberItems = $syntax->matches[2];
            $this->extractAttributes($markup);
        } else {
            throw new LiquidException("Syntax Error - Valid syntax: paginate [collection] by [items]");
        }

    }

    /**
     * Renders the tag
     *
     * @param Context $context
     *
     * @return string
     *
     */
    public function render(Context $context) {

        $this->currentPage = ( is_numeric($context->get('page')) ) ? $context->get('page') : 1;
        $this->currentOffset = ($this->currentPage - 1) * $this->numberItems;
    	$this->collection = $context->get($this->collectionName);
		if ($this->collection instanceof \Traversable) {
			$this->collection = iterator_to_array($this->collection);
		}
    	$this->collectionSize = count($this->collection);
    	$this->totalPages = (int) ceil($this->collectionSize / $this->numberItems);
    	$paginatedCollection =  array_slice($this->collection, $this->currentOffset, $this->numberItems);

    	// Sets the collection if it's a key of another collection (ie search.results, collection.products, blog.articles)
    	$segments = explode('.', $this->collectionName);
    	if (count($segments) == 2) {
	    	$context->set($segments[0], array($segments[1] => $paginatedCollection));
    	} else {
	    	$context->set($this->collectionName, $paginatedCollection);
    	}
    	
    	$paginate = array(
    		'page_size' => $this->numberItems,
    		'current_page' => $this->currentPage,
    		'current_offset' => $this->currentOffset,
    		'pages' => $this->totalPages,
    		'items' => $this->collectionSize
    	);
    	
    	if ( $this->currentPage != 1 ) {
	    	$paginate['previous']['title'] = 'Previous';
	    	$paginate['previous']['url'] = $this->currentUrl($context) . '?page=' . ($this->currentPage - 1);
    	
    	}
    	
    	if ( $this->currentPage != $this->totalPages ) {
	    	$paginate['next']['title'] = 'Next';
	    	$paginate['next']['url'] = $this->currentUrl($context) . '?page=' . ($this->currentPage + 1);
    	}

    	$context->set('paginate', $paginate);
    	
        return parent::render($context);

    }

    /**
     * Returns the current page URL
     *
     * @param Context $context
     *
     * @return string
     *
     */
    public function currentUrl($context) {

	    $uri = explode('?', $context->get('REQUEST_URI'));

	    $url = 'http';
		if ($context->get('HTTPS') == 'on') $url .= 's';
		$url .= '://' . $context->get('HTTP_HOST') . reset($uri);
		
		return $url;
		
    }

}
