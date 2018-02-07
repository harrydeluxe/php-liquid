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

/**
 * Loops over an array, assigning the current value to a given variable
 *
 * Example:
 *
 *     {%for item in array%} {{item}} {%endfor%}
 *
 *     With an array of 1, 2, 3, 4, will return 1 2 3 4
 *
 *     or
 *
 *     {%for i in (1..10)%} {{i}} {%endfor%}
 *     {%for i in (1..variable)%} {{i}} {%endfor%}
 *
 */
class TagFor extends AbstractBlock
{
	/**
	 * @var array The collection to loop over
	 */
	private $collectionName;

	/**
	 * @var string The variable name to assign collection elements to
	 */
	private $variableName;

	/**
	 * @var string The name of the loop, which is a compound of the collection and variable names
	 */
	private $name;

	/**
	 * @var string The type of the loop (collection or digit)
	 */
	private $type = 'collection';

	/**
	 * Constructor
	 *
	 * @param string $markup
	 * @param array $tokens
	 * @param FileSystem $fileSystem
	 *
	 * @throws \Liquid\Exception\ParseException
	 */
	public function __construct($markup, array &$tokens, FileSystem $fileSystem = null)
	{
		parent::__construct($markup, $tokens, $fileSystem);

		$syntaxRegexp = new Regexp('/(\w+)\s+in\s+(' . Liquid::get('VARIABLE_NAME') . ')/');

		if ($syntaxRegexp->match($markup)) {
			$this->variableName = $syntaxRegexp->matches[1];
			$this->collectionName = $syntaxRegexp->matches[2];
			$this->name = $syntaxRegexp->matches[1] . '-' . $syntaxRegexp->matches[2];
			$this->extractAttributes($markup);
		} else {
			$syntaxRegexp = new Regexp('/(\w+)\s+in\s+\((\d+|' . Liquid::get('VARIABLE_NAME') . ')\s*\.\.\s*(\d+|' . Liquid::get('VARIABLE_NAME') . ')\)/');
			if ($syntaxRegexp->match($markup)) {
				$this->type = 'digit';
				$this->variableName = $syntaxRegexp->matches[1];
				$this->start = $syntaxRegexp->matches[2];
				$this->collectionName = $syntaxRegexp->matches[3];
				$this->name = $syntaxRegexp->matches[1].'-digit';
				$this->extractAttributes($markup);
			} else {
				throw new ParseException("Syntax Error in 'for loop' - Valid syntax: for [item] in [collection]");
			}
		}
	}

	/**
	 * Renders the tag
	 *
	 * @param Context $context
	 *
	 * @return null|string
	 */
	public function render(Context $context)
	{
		if (!isset($context->registers['for'])) {
			$context->registers['for'] = array();
		}

		if ($this->type == 'digit') {
			return $this->renderDigit($context);
		}

		// that's the default
		return $this->renderCollection($context);
	}

	private function renderCollection(Context $context)
	{
		$collection = $context->get($this->collectionName);

		if ($collection instanceof \Traversable) {
			$collection = iterator_to_array($collection);
		}

		if (is_null($collection) || !is_array($collection) || count($collection) == 0) {
			return '';
		}

		$range = array(0, count($collection));

		if (isset($this->attributes['limit']) || isset($this->attributes['offset'])) {
			$offset = 0;

			if (isset($this->attributes['offset'])) {
				$offset = ($this->attributes['offset'] == 'continue') ? $context->registers['for'][$this->name] : $context->get($this->attributes['offset']);
			}

			$limit = (isset($this->attributes['limit'])) ? $context->get($this->attributes['limit']) : null;
			$rangeEnd = $limit ? $limit : count($collection) - $offset;
			$range = array($offset, $rangeEnd);

			$context->registers['for'][$this->name] = $rangeEnd + $offset;
		}

		$result = '';
		$segment = array_slice($collection, $range[0], $range[1]);
		if (!count($segment)) {
			return null;
		}

		$context->push();
		$length = count($segment);

		$index = 0;
		foreach ($segment as $key => $item) {
			$value = is_numeric($key) ? $item : array($key, $item);
			$context->set($this->variableName, $value);
			$context->set('forloop', array(
					'name' => $this->name,
					'length' => $length,
					'index' => $index + 1,
					'index0' => $index,
					'rindex' => $length - $index,
					'rindex0' => $length - $index - 1,
					'first' => (int)($index == 0),
					'last' => (int)($index == $length - 1)
			));

			$result .= $this->renderAll($this->nodelist, $context);

			$index++;

			if (isset($context->registers['break'])) {
				unset($context->registers['break']);
				break;
			}
			if (isset($context->registers['continue'])) {
				unset($context->registers['continue']);
			}
		}

		$context->pop();

		return $result;
	}

	private function renderDigit(Context $context)
	{
		$start = $this->start;
		if (!is_integer($this->start)) {
			$start = $context->get($this->start);
		}

		$end = $this->collectionName;
		if (!is_integer($this->collectionName)) {
			$end = $context->get($this->collectionName);
		}

		$range = array($start, $end);

		$context->push();
		$result = '';
		$index = 0;
		$length = $range[1] - $range[0];
		for ($i = $range[0]; $i <= $range[1]; $i++) {
			$context->set($this->variableName, $i);
			$context->set('forloop', array(
				'name'		=> $this->name,
				'length'	=> $length,
				'index'		=> $index + 1,
				'index0'	=> $index,
				'rindex'	=> $length - $index,
				'rindex0'	=> $length - $index - 1,
				'first'		=> (int)($index == 0),
				'last'		=> (int)($index == $length - 1)
			));

			$result .= $this->renderAll($this->nodelist, $context);

			$index++;

			if (isset($context->registers['break'])) {
				unset($context->registers['break']);
				break;
			}
			if (isset($context->registers['continue'])) {
				unset($context->registers['continue']);
			}
		}

		$context->pop();

		return $result;
	}
}
