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
	private $_filters;

	/**
	 * @var string The name of the variable
	 */
	private $_name;

	/**
	 * @var string The markup of the variable
	 */
	private $_markup;

	/**
	 * Constructor
	 *
	 * @param string $markup
	 */
	public function __construct($markup) {
		$this->_markup = $markup;

		$quotedFragmentRegexp = new Regexp('/\s*(' . Liquid::LIQUID_QUOTED_FRAGMENT . ')/');
		$filterSeperatorRegexp = new Regexp('/' . Liquid::LIQUID_FILTER_SEPARATOR . '\s*(.*)/');
		$filterSplitRegexp = new Regexp('/' . Liquid::LIQUID_FILTER_SEPARATOR . '/');
		$filterNameRegexp = new Regexp('/\s*(\w+)/');
		$filterArgumentRegexp = new Regexp('/(?:' . Liquid::LIQUID_FILTER_ARGUMENT_SEPARATOR . '|' . Liquid::LIQUID_ARGUMENT_SEPARATOR . ')\s*(' . Liquid::LIQUID_QUOTED_FRAGMENT . ')/');

		$quotedFragmentRegexp->match($markup);

		$this->_name = (isset($quotedFragmentRegexp->matches[1])) ? $quotedFragmentRegexp->matches[1] : null;


		if ($filterSeperatorRegexp->match($markup)) {
			$filters = $filterSplitRegexp->split($filterSeperatorRegexp->matches[1]);

			foreach ($filters as $filter) {
				$filterNameRegexp->match($filter);
				$filtername = $filterNameRegexp->matches[1];

				$filterArgumentRegexp->match_all($filter);
				$matches = Liquid::array_flatten($filterArgumentRegexp->matches[1]);

				$this->_filters[] = array(
					$filtername, $matches
				);
			}

		} else {
			$this->_filters = array();
		}
	}

	/**
	 * Gets the variable name
	 *
	 * @return string The name of the variable
	 */
	public function getName() {
		return $this->_name;
	}

	/**
	 * Gets all Filters
	 *
	 * @return array
	 */
	public function getFilters() {
		return $this->_filters;
	}

	/**
	 * Renders the variable with the data in the context
	 *
	 * @param Context $context
	 */
	function render($context) {
		$output = $context->get($this->_name);

		foreach ($this->_filters as $filter) {
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
