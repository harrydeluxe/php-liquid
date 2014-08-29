<?php

namespace Liquid\Tag;

use Liquid\Context;

/**
 * Creates a comment; everything inside will be ignored
 *
 * Example:
 *
 *     {% comment %} This will be ignored {% endcomment %}
 */
class TagComment extends AbstractBlock
{
	/**
	 * Renders the block
	 *
	 * @param Context $context
	 *
	 * @return string empty string
	 */
	public function render(&$context) {
		return '';
	}
}
