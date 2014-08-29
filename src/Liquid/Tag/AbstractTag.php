<?php

namespace Liquid\Tag;

use Liquid\BlankFileSystem;
use Liquid\Context;
use Liquid\Liquid;
use Liquid\Regexp;

/**
 * Base class for tags.
 */
abstract class AbstractTag
{
	/**
	 * The markup for the tag
	 *
	 * @var string
	 */
	protected $markup;

	/**
	 * Filesystem object is used to load included template files
	 *
	 * @var BlankFileSystem
	 */
	protected $fileSystem;

	/**
	 * Additional attributes
	 *
	 * @var array
	 */
	protected $attributes = array();

	/**
	 * Constructor.
	 *
	 * @param string $markup
	 * @param array $tokens
	 * @param BlankFileSystem $fileSystem
	 *
	 * todo: return?
	 */
	public function __construct($markup, &$tokens, &$fileSystem) {
		$this->markup = $markup;
		$this->fileSystem = $fileSystem;
		return $this->parse($tokens);
	}

	/**
	 * Parse the given tokens.
	 *
	 * @param array $tokens
	 *
	 * todo: reference? empty or abstract?
	 */
	public function parse(&$tokens) {
		// Do nothing by default
	}

	/**
	 * Render the tag with the given context.
	 *
	 * @param Context $context
	 *
	 * @return string
	 *
	 * todo: reference, abstract?
	 */
	public function render(&$context) {
		return '';
	}

	/**
	 * Extracts tag attributes from a markup string.
	 *
	 * @param string $markup
	 */
	protected function extractAttributes($markup) {
		$this->attributes = array();

		$attribute_regexp = new Regexp(Liquid::LIQUID_TAG_ATTRIBUTES);

		$matches = $attribute_regexp->scan($markup);

		foreach ($matches as $match) {
			$this->attributes[$match[0]] = $match[1];
		}
	}

	/**
	 * Returns the name of the tag.
	 *
	 * @return string
	 */
	protected function name() {
		return strtolower(get_class($this));
	}
}
