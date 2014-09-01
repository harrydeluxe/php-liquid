<?php

namespace Liquid\Tag;

use Liquid\Liquid;
use Liquid\Context;
use Liquid\LiquidException;
use Liquid\FileSystem;
use Liquid\Regexp;

/**
 * Quickly create a table from a collection
 */
class TagTablerow extends AbstractBlock
{
	/**
	 * The variable name of the table tag
	 *
	 * @var string
	 */
	public $variableName;

	/**
	 * The collection name of the table tags
	 *
	 * @var string
	 */
	public $collectionName;

	/**
	 * Additional attributes
	 *
	 * @var array
	 */
	public $attributes;

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

		$syntax = new Regexp("/(\w+)\s+in\s+(" . Liquid::LIQUID_ALLOWED_VARIABLE_CHARS . "+)/");

		if ($syntax->match($markup)) {
			$this->variableName = $syntax->matches[1];
			$this->collectionName = $syntax->matches[2];

			$this->extractAttributes($markup);
		} else {
			throw new LiquidException("Syntax Error in 'table_row loop' - Valid syntax: table_row [item] in [collection] cols=3");
		}
	}

	/**
	 * Renders the current node
	 *
	 * @param Context $context
	 *
	 * @return string
	 */
	public function render(Context $context) {
		$collection = $context->get($this->collectionName);

		if (!is_array($collection)) {
			die('not array, ' . var_export($collection, true));
		}

		// discard keys
		$collection = array_values($collection);

		if (isset($this->attributes['limit']) || isset($this->attributes['offset'])) {
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

		foreach ($collection as $index => $item) {
			$context->set($this->variableName, $item);
			$context->set('tablerowloop', array(
				'length' => $length,
				'index' => $index + 1,
				'index0' => $index,
				'rindex' => $length - $index,
				'rindex0' => $length - $index - 1,
				'first' => (int)($index == 0),
				'last' => (int)($index == $length - 1)
			));

			$result .= "<td class=\"col" . (++$col) . "\">" . $this->renderAll($this->nodelist, $context) . "</td>";

			if ($col == $cols && !($index == $length - 1)) {
				$col = 0;
				$result .= "</tr>\n<tr class=\"row" . (++$row) . "\">";
			}
		}

		$context->pop();

		$result .= "</tr>\n";

		return $result;
	}
}
