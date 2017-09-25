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

use Liquid\AbstractTag;
use Liquid\Context;

/**
 * Skips the current iteration of the current loop
 *
 * Example:
 *
 *     {% for i in (1..5) %}
 *       {% if i == 4 %}
 *         {% continue %}
 *       {% endif %}
 *       {{ i }}
 *     {% endfor %}
 */
class TagContinue extends AbstractTag
{
	/**
	 * Renders the tag
	 *
	 * @param Context $context
	 *
	 * @return string|void
	 */
	public function render(Context $context)
	{
		$context->registers['continue'] = true;
	}
}
