<?php

/*
 * This file is part of the Liquid package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Liquid
 */

namespace Liquid\Tag;

use Liquid\AbstractBlock;
use Liquid\Exception\ParseException;
use Liquid\Liquid;
use Liquid\Context;
use Liquid\FileSystem;
use Liquid\Regexp;
use Liquid\Exception\RenderException;

/**
 * The paginate tag works in conjunction with the for tag to split content into numerous pages.
 *
 * Example:
 *
 *	{% paginate collection.products by 5 %}
 *		{% for product in collection.products %}
 *			<!--show product details here -->
 *		{% endfor %}
 *	{% endpaginate %}
 *
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
	 * @throws \Liquid\Exception\ParseException
	 *
	 */
	public function __construct($markup, array &$tokens, FileSystem $fileSystem = null)
	{
		parent::__construct($markup, $tokens, $fileSystem);

		$syntax = new Regexp('/(' . Liquid::get('VARIABLE_NAME') . ')\s+by\s+(\w+)/');

		if ($syntax->match($markup)) {
			$this->collectionName = $syntax->matches[1];
			$this->numberItems = $syntax->matches[2];
			$this->extractAttributes($markup);
		} else {
			throw new ParseException("Syntax Error - Valid syntax: paginate [collection] by [items]");
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
	public function render(Context $context)
	{
		$this->collection = $context->get($this->collectionName);

		if ($this->collection instanceof \Traversable) {
			$this->collection = iterator_to_array($this->collection);
		}

		if (!is_array($this->collection)) {
			// TODO do not throw up if error mode allows, see #83
			throw new RenderException("Missing collection with name '{$this->collectionName}'");
		}

		// How many pages are there?
		$this->collectionSize = count($this->collection);
		$this->totalPages = ceil($this->collectionSize / $this->numberItems);

		// Whatever there is in the context, we need a number
		$this->currentPage = intval($context->get(Liquid::get('PAGINATION_CONTEXT_KEY')));

		// Page number can only be between 1 and a number of pages
		$this->currentPage = max(1, min($this->currentPage, $this->totalPages));

		// Find the offset and select that part
		$this->currentOffset = ($this->currentPage - 1) * $this->numberItems;
		$paginatedCollection = array_slice($this->collection, $this->currentOffset, $this->numberItems);

		// We must work in a new scope so we won't pollute a global scope
		$context->push();

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

		// Get the name of the request field to use in URLs
		$pageRequestKey = Liquid::get('PAGINATION_REQUEST_KEY');

		if ($this->currentPage > 1) {
			$paginate['previous']['title'] = 'Previous';
			$paginate['previous']['url'] = $this->currentUrl($context, [
				$pageRequestKey => $this->currentPage - 1,
			]);
		}

		if ($this->currentPage < $this->totalPages) {
			$paginate['next']['title'] = 'Next';
			$paginate['next']['url'] = $this->currentUrl($context, [
				$pageRequestKey => $this->currentPage + 1,
			]);
		}

		$context->set('paginate', $paginate);

		$result = parent::render($context);

		$context->pop();

		return $result;
	}

	/**
	 * Returns the current page URL
	 *
	 * @param Context $context
	 * @param array $queryPart
	 *
	 * @return string
	 *
	 */
	public function currentUrl($context, $queryPart = [])
	{
		// From here we have $url->path and $url->query
		$url = (object) parse_url($context->get('REQUEST_URI'));

		// Let's merge the query part
		if (isset($url->query)) {
			parse_str($url->query, $url->query);
			$url->query = array_merge($url->query, $queryPart);
		} else {
			$url->query = $queryPart;
		}

		$url->query = http_build_query($url->query);

		$scheme = $context->get('HTTPS') == 'on' ? 'https' : 'http';

		return "$scheme://{$context->get('HTTP_HOST')}{$url->path}?{$url->query}";
	}
}
