<?php

namespace Liquid\Tag;

use Liquid\LiquidException;
use Liquid\BlankFileSystem;
use Liquid\Regexp;

/**
 * Marks a section of a template as being reusable.
 *
 * Example:
 *
 *     {% block foo %} bar {% endblock %}
 */
class TagBlock extends AbstractBlock
{
	/**
	 * The variable to assign to
	 *
	 * @var string
	 */
	private $block;

	/**
	 * Constructor
	 *
	 * @param string $markup
	 * @param Array $tokens
	 * @param BlankFileSystem $fileSystem
	 *
	 * @throws \Liquid\LiquidException
	 * @return \Liquid\Tag\TagBlock
	 */
	public function __construct($markup, array $tokens, $fileSystem) {
		$syntaxRegexp = new Regexp('/(\w+)/');

		if ($syntaxRegexp->match($markup)) {
			$this->block = $syntaxRegexp->matches[1];
			parent::__construct($markup, $tokens, $fileSystem);
		} else {
			throw new LiquidException("Syntax Error in 'block' - Valid syntax: block [name]");
		}
	}
}
