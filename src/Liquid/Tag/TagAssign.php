<?php

/**
 * This file is part of the Liquid package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Liquid
 */

namespace Liquid\Tag;

use Liquid\AbstractTag;
use Liquid\Liquid;
use Liquid\LiquidException;
use Liquid\FileSystem;
use Liquid\Regexp;
use Liquid\Context;

/**
 * Performs an assignment of one variable to another
 *
 * Example:
 *
 *     {% assign var = var %}
 *     {% assign var = "hello" | upcase %}
 */
class TagAssign extends AbstractTag
{
	/**
	 * @var string The variable to assign from
	 */
	private $from;

	/**
	 * @var string The variable to assign to
	 */
	private $to;

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
		$syntaxRegexp = new Regexp('/(\w+)\s*=\s*(' . Liquid::get('QUOTED_FRAGMENT') . '+)/');

		$filterSeperatorRegexp = new Regexp('/' . Liquid::get('FILTER_SEPARATOR') . '\s*(.*)/');
		$filterSplitRegexp = new Regexp('/' . Liquid::get('FILTER_SEPARATOR') . '/');
		$filterNameRegexp = new Regexp('/\s*(\w+)/');
		$filterArgumentRegexp = new Regexp('/(?:' . Liquid::get('FILTER_ARGUMENT_SEPARATOR') . '|' . Liquid::get('ARGUMENT_SEPARATOR') . ')\s*(' . Liquid::get('QUOTED_FRAGMENT') . ')/');

		$this->filters = array();

		if ($filterSeperatorRegexp->match($markup)) {
			$filters = $filterSplitRegexp->split($filterSeperatorRegexp->matches[1]);

			foreach ($filters as $filter) {
				$filterNameRegexp->match($filter);
				$filtername = $filterNameRegexp->matches[1];

				$filterArgumentRegexp->matchAll($filter);
				$matches = Liquid::arrayFlatten($filterArgumentRegexp->matches[1]);

				array_push($this->filters, array($filtername, $matches));
			}
		}

		if ($syntaxRegexp->match($markup)) {
			$this->to = $syntaxRegexp->matches[1];
			$this->from = $syntaxRegexp->matches[2];
		} else {
			throw new LiquidException("Syntax Error in 'assign' - Valid syntax: assign [var] = [source]");
		}
	}

	/**
	 * Renders the tag
	 *
	 * @param Context $context
	 *
	 * @return string|void
	 */
	public function render(Context $context) {
		$output = $context->get($this->from);

		foreach ($this->filters as $filter) {
			list($filtername, $filterArgKeys) = $filter;

			$filterArgValues = array();

			foreach ($filterArgKeys as $arg_key) {
				$filterArgValues[] = $context->get($arg_key);
			}

			$output = $context->invoke($filtername, $output, $filterArgValues);
		}

		$context->set($this->to, $output, true);
	}
}
