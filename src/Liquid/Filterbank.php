<?php

/*
 * This file is part of the Liquid package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Liquid
 */

namespace Liquid;

use Liquid\Exception\WrongArgumentException;

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
	public function __construct(Context $context)
	{
		$this->context = $context;

		$this->addFilter(\Liquid\StandardFilters::class);
		$this->addFilter(\Liquid\CustomFilters::class);
	}

	/**
	 * Adds a filter to the bank
	 *
	 * @param mixed $filter Can either be an object, the name of a class (in which case the
	 *						filters will be called statically) or the name of a function.
	 *
	 * @throws \Liquid\Exception\WrongArgumentException
	 * @return bool
	 */
	public function addFilter($filter, callable $callback = null)
	{
		// If it is a callback, save it as it is
		if (is_string($filter) && $callback) {
			$this->methodMap[$filter] = $callback;
			return true;
		}

		// If the filter is a class, register all its static methods
		if (is_string($filter) && class_exists($filter)) {
			$reflection = new \ReflectionClass($filter);
			foreach ($reflection->getMethods(\ReflectionMethod::IS_STATIC) as $method) {
				$this->methodMap[$method->name] = $method->class;
			}

			return true;
		}

		// If it's a global function, register it simply
		if (is_string($filter) && function_exists($filter)) {
			$this->methodMap[$filter] = false;
			return true;
		}

		// If it isn't an object an isn't a string either, it's a bad parameter
		if (!is_object($filter)) {
			throw new WrongArgumentException("Parameter passed to addFilter must be an object or a string");
		}

		// If the passed filter was an object, store the object for future reference.
		$filter->context = $this->context;
		$className = get_class($filter);
		$this->filters[$className] = $filter;

		// Then register all public static and not methods as filters
		foreach (get_class_methods($filter) as $method) {
			$this->methodMap[$method] = $className;
		}

		return true;
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
	public function invoke($name, $value, array $args = array())
	{
		// workaround for a single standard filter being a reserved keyword - we can't use overloading for static calls
		if ($name == 'default') {
			$name = '_default';
		}

		array_unshift($args, $value);

		// Consult the mapping
		if (!isset($this->methodMap[$name])) {
			return $value;
		}

		$class = $this->methodMap[$name];

		// If we have a callback
		if (is_callable($class)) {
			return call_user_func_array($class, $args);
		}

		// If we have a registered object for the class, use that instead
		if (isset($this->filters[$class])) {
			$class = $this->filters[$class];
		}

		// If we're calling a function
		if ($class === false) {
			return call_user_func_array($name, $args);
		}

		// Call a class or an instance method
		return call_user_func_array(array($class, $name), $args);
	}
}
