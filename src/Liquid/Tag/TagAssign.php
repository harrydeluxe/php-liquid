<?php

namespace Liquid\Tag;

use Liquid\Liquid;
use Liquid\LiquidException;
use Liquid\BlankFileSystem;
use Liquid\Regexp;
use Liquid\Context;

/**
 * Performs an assignment of one variable to another
 *
 * @example
 * {% assign var = var %}
 * {% assign var = "hello" | upcase %}
 *
 * @package Liquid
 * @copyright Copyright (c) 2011-2012 Harald Hanek,
 * fork of php-liquid (c) 2006 Mateo Murphy,
 * based on Liquid for Ruby (c) 2006 Tobias Luetke
 * @license http://harrydeluxe.mit-license.org
 */
class TagAssign extends AbstractTag
{
	/**
	 * @var string The variable to assign from
	 */
	private $_from;

	/**
	 * @var string The variable to assign to
	 */
	private $_to;

	/**
	 * Constructor
	 *
	 * @param string $markup
	 * @param array $tokens
	 * @param BlankFileSystem $fileSystem
	 */
	public function __construct($markup, &$tokens, &$fileSystem) {
		$syntaxRegexp = new Regexp('/(\w+)\s*=\s*(' . LIQUID_QUOTED_FRAGMENT . '+)/');

		$filterSeperatorRegexp = new Regexp('/' . LIQUID_FILTER_SEPARATOR . '\s*(.*)/');
		$filterSplitRegexp = new Regexp('/' . LIQUID_FILTER_SEPARATOR . '/');
		$filterNameRegexp = new Regexp('/\s*(\w+)/');
		$filterArgumentRegexp = new Regexp('/(?:' . LIQUID_FILTER_ARGUMENT_SEPARATOR . '|' . LIQUID_ARGUMENT_SEPARATOR . ')\s*(' . LIQUID_QUOTED_FRAGMENT . ')/');

		$this->filters = array();

		if ($filterSeperatorRegexp->match($markup)) {
			$filters = $filterSplitRegexp->split($filterSeperatorRegexp->matches[1]);

			foreach ($filters as $filter) {
				$filterNameRegexp->match($filter);
				$filtername = $filterNameRegexp->matches[1];

				$filterArgumentRegexp->match_all($filter);
				$matches = Liquid::array_flatten($filterArgumentRegexp->matches[1]);

				array_push($this->filters, array(
					$filtername, $matches
				));
			}
		}

		if ($syntaxRegexp->match($markup)) {
			$this->_to = $syntaxRegexp->matches[1];
			$this->_from = $syntaxRegexp->matches[2];
		} else {
			throw new LiquidException("Syntax Error in 'assign' - Valid syntax: assign [var] = [source]");
		}
	}

	/**
	 * Renders the tag
	 *
	 * @param Context $context
	 */
	public function render(&$context) {
		$output = $context->get($this->_from);

		foreach ($this->filters as $filter) {
			list($filtername, $filterArgKeys) = $filter;

			$filterArgValues = array();

			foreach ($filterArgKeys as $arg_key) {
				$filterArgValues[] = $context->get($arg_key);
			}

			$output = $context->invoke($filtername, $output, $filterArgValues);
		}

		$context->set($this->_to, $output, true);
	}
}
