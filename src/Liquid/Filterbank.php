<?php

/**
 * This file is part of the Liquid package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Liquid
 */

namespace Liquid;

/**
 * The filter bank is where all registered filters are stored, and where filter invocation is handled
 * it supports a variety of different filter types; objects, class, and simple methods.
 */
class Filterbank
{
	/**
	 * The registered filter objects
	 *
	 * @var array
	 */
	private $filters;

	/**
	 * A map of all filters and the class that contain them (in the case of methods)
	 *
	 * @var array
	 */
	private $methodMap;

	/**
	 * Reference to the current context object
	 *
	 * @var Context
	 */
	private $context;

	/**
	 * Constructor
	 *
	 * @param $context
	 */
	public function __construct(Context $context) {
		$this->context = $context;

		$this->addFilter('\Liquid\StandardFilters');
		$this->addFilter('\Liquid\CustomFilters');
	}

	/**
	 * Adds a filter to the bank
	 *
	 * @param mixed $filter Can either be an object, the name of a class (in which case the
	 * 						filters will be called statically) or the name of a function.
	 *
	 * @throws LiquidException
	 * @return bool
	 */
	public function addFilter($filter) {
		// If the passed filter was an object, store the object for future reference.
		if (is_object($filter)) {
			$filter->context = $this->context;
			$name = get_class($filter);
			$this->filters[$name] = $filter;
			$filter = $name;
		}

		// If it wasn't an object an isn't a string either, it's a bad parameter
		if (!is_string($filter)) {
			throw new LiquidException("Parameter passed to addFilter must be an object or a string");
		}

		// If the filter is a class, register all its methods
		if (class_exists($filter)) {
			$methods = array_flip(get_class_methods($filter));
			foreach ($methods as $method => $null) {
				$this->methodMap[$method] = $filter;
			}

			return true;
		}

		// If it's a function register it simply
		if (function_exists($filter)) {
			$this->methodMap[$filter] = false;
			return true;
		}

		throw new LiquidException("Parameter passed to addFilter must a class or a function");
	}

	/**
	 * Invokes the filter with the given name
	 *
	 * @param string $name The name of the filter
	 * @param string $value The value to filter
	 * @param array $args The additional arguments for the filter
	 *
	 * @return string
	 */
	public function invoke($name, $value, array $args = array()) {
		array_unshift($args, $value);

		// Consult the mapping
		if (isset($this->methodMap[$name])) {
			$class = $this->methodMap[$name];

			// If we have a registered object for the class, use that instead
			if (isset($this->filters[$class])) {
				$class = $this->filters[$class];
			}

			// If we're calling a function
			if ($class === false) {
				return call_user_func_array($name, $args);
			} else {
				return call_user_func_array(array($class, $name), $args);
			}
		}

		return $value;
	}
}
