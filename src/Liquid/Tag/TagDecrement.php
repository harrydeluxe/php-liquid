<?php

namespace Liquid\Tag;

use Liquid\Liquid;
use Liquid\Context;
use Liquid\LiquidException;
use Liquid\BlankFileSystem;
use Liquid\Regexp;

/**
 * Used to decrement a counter into a template
 *
 * @example
 * {% decrement value %}
 *
 * @author Viorel Dram
 *
 * @package Liquid
 */
class TagDecrement extends AbstractTag
{
	/**
	 * Name of the variable to decrement
	 *
	 * @var int
	 */
	private $_toDecrement;

	/**
	 * Constructor
	 *
	 * @param string $markup
	 * @param array $tokens
	 * @param BlankFileSystem $fileSystem
	 */
	public function __construct($markup, &$tokens, &$fileSystem) {
		$syntax = new Regexp("/(" . Liquid::LIQUID_ALLOWED_VARIABLE_CHARS . "+)/");

		if ($syntax->match($markup)) {
			$this->_toDecrement = $syntax->matches[0];
		} else {
			throw new LiquidException("Syntax Error in 'decrement' - Valid syntax: decrement [var]");
		}
	}

	/**
	 * Renders the tag
	 *
	 * @param Context $context
	 */
	public function render(&$context) {
		// if the value is not set in the environment check to see if it
		// exists in the context, and if not set it to 0
		if (!isset($context->environments[0][$this->_toDecrement])) {
			// check for a context value
			$from_context = $context->get($this->_toDecrement);

			// we already have a value in the context
			$context->environments[0][$this->_toDecrement] = (null !== $from_context) ? $from_context : 0;
		}

		// decrement the environment value
		$context->environments[0][$this->_toDecrement]--;
	}
}
