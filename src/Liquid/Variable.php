<?php

namespace Liquid;

/**
 * Implements a template variable.
 */
class Variable
{
	/**
	 * @var array The filters to execute on the variable
	 */
	private $filters;

	/**
	 * @var string The name of the variable
	 */
	private $name;

	/**
	 * @var string The markup of the variable
	 */
	private $markup;

	/**
	 * Constructor
	 *
	 * @param string $markup
	 */
	public function __construct($markup) {
		$this->markup = $markup;

		$quotedFragmentRegexp = new Regexp('/\s*(' . Liquid::LIQUID_QUOTED_FRAGMENT . ')/');
		$filterSeperatorRegexp = new Regexp('/' . Liquid::LIQUID_FILTER_SEPARATOR . '\s*(.*)/');
		$filterSplitRegexp = new Regexp('/' . Liquid::LIQUID_FILTER_SEPARATOR . '/');
		$filterNameRegexp = new Regexp('/\s*(\w+)/');
		$filterArgumentRegexp = new Regexp('/(?:' . Liquid::LIQUID_FILTER_ARGUMENT_SEPARATOR . '|' . Liquid::LIQUID_ARGUMENT_SEPARATOR . ')\s*(' . Liquid::LIQUID_QUOTED_FRAGMENT . ')/');

		$quotedFragmentRegexp->match($markup);

		$this->name = (isset($quotedFragmentRegexp->matches[1])) ? $quotedFragmentRegexp->matches[1] : null;

		if ($filterSeperatorRegexp->match($markup)) {
			$filters = $filterSplitRegexp->split($filterSeperatorRegexp->matches[1]);

			foreach ($filters as $filter) {
				$filterNameRegexp->match($filter);
				$filtername = $filterNameRegexp->matches[1];

				$filterArgumentRegexp->matchAll($filter);
				$matches = Liquid::array_flatten($filterArgumentRegexp->matches[1]);

				$this->filters[] = array($filtername, $matches);
			}

		} else {
			$this->filters = array();
		}
	}

	/**
	 * Gets the variable name
	 *
	 * @return string The name of the variable
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Gets all Filters
	 *
	 * @return array
	 */
	public function getFilters() {
		return $this->filters;
	}

	/**
	 * Renders the variable with the data in the context
	 *
	 * @param Context $context
	 *
	 * @return mixed|string
	 */
	public function render(Context $context) {
		$output = $context->get($this->name);

		foreach ($this->filters as $filter) {
			list($filtername, $filterArgKeys) = $filter;

			$filterArgValues = array();

			foreach ($filterArgKeys as $arg_key) {
				$filterArgValues[] = $context->get($arg_key);
			}

			$output = $context->invoke($filtername, $output, $filterArgValues);
		}

		return $output;
	}
}
