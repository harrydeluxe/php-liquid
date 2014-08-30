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
	 */
	public function __construct($markup, array $tokens, $fileSystem) {
		$this->markup = $markup;
		$this->fileSystem = $fileSystem;
		$this->parse($tokens);
	}

	/**
	 * Parse the given tokens.
	 *
	 * @param array $tokens
	 *
	 * todo: empty or abstract?
	 */
	public function parse(array $tokens) {
		// Do nothing by default
	}

	/**
	 * Render the tag with the given context.
	 *
	 * @param Context $context
	 *
	 * @return string
	 *
	 * todo: abstract?
	 */
	public function render(Context $context) {
		return '';
	}

	/**
	 * Extracts tag attributes from a markup string.
	 *
	 * @param string $markup
	 */
	protected function extractAttributes($markup) {
		$this->attributes = array();

		$attributeRegexp = new Regexp(Liquid::LIQUID_TAG_ATTRIBUTES);

		$matches = $attributeRegexp->scan($markup);

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
