<?php

namespace Liquid\Tag;

use Liquid\Liquid;
use Liquid\Context;
use Liquid\LiquidException;
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
	 * Constructor
	 *
	 * @param string $markup
	 * @param array $tokens
	 * @param FileSystem $fileSystem
	 *
	 * @throws \Liquid\LiquidException
	 */
	public function __construct($markup, array &$tokens, FileSystem $fileSystem = null) {
		parent::__construct($markup, $tokens, $fileSystem);

		$syntaxRegexp = new Regexp('/(\w+)\s+in\s+(' . Liquid::LIQUID_ALLOWED_VARIABLE_CHARS . '+)/');

		if ($syntaxRegexp->match($markup)) {
			$this->variableName = $syntaxRegexp->matches[1];
			$this->collectionName = $syntaxRegexp->matches[2];
			$this->name = $syntaxRegexp->matches[1] . '-' . $syntaxRegexp->matches[2];
			$this->extractAttributes($markup);
		} else {
			throw new LiquidException("Syntax Error in 'for loop' - Valid syntax: for [item] in [collection]");
		}
	}

	/**
	 * Renders the tag
	 *
	 * @param Context $context
	 *
	 * @return null|string
	 */
	public function render(Context $context) {
		if (!isset($context->registers['for'])) {
			$context->registers['for'] = array();
		}

		$collection = $context->get($this->collectionName);

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

		 // todo: If $segment keys are not integer, forloop not work
		 // array_values is only a little help without being tested.
		$segment = array_values($segment);

		foreach ($segment as $index => $item) {
			$context->set($this->variableName, $item);
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
		}

		$context->pop();

		return $result;
	}
}
