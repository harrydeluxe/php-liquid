<?php

namespace Liquid\Tag;

use Liquid\Context;
use Liquid\LiquidException;
use Liquid\FileSystem;
use Liquid\Regexp;

/**
 * Captures the output inside a block and assigns it to a variable
 *
 * Example:
 *
 *     {% capture foo %} bar {% endcapture %}
 */
class TagCapture extends AbstractBlock
{
	/**
	 * The variable to assign to
	 *
	 * @var string
	 */
	private $to;

	/**
	 * Constructor
	 *
	 * @param string $markup
	 * @param Array $tokens
	 * @param FileSystem $fileSystem
	 *
	 * @throws \Liquid\LiquidException
	 */
	public function __construct($markup, array &$tokens, FileSystem $fileSystem = null) {
		$syntaxRegexp = new Regexp('/(\w+)/');

		if ($syntaxRegexp->match($markup)) {
			$this->to = $syntaxRegexp->matches[1];
			parent::__construct($markup, $tokens, $fileSystem);
		} else {
			throw new LiquidException("Syntax Error in 'capture' - Valid syntax: assign [var] = [source]"); // harry
		}
	}

	/**
	 * Renders the block
	 *
	 * @param Context $context
	 *
	 * @return string
	 */
	public function render(Context $context) {
		$output = parent::render($context);

		$context->set($this->to, $output);
		return '';
	}
}
