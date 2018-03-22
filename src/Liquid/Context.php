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

/**
 * Context keeps the variable stack and resolves variables, as well as keywords.
 */
class Context
{
	/**
	 * Local scopes
	 *
	 * @var array
	 */
	protected $assigns;

	/**
	 * Registers for non-variable state data
	 *
	 * @var array
	 */
	public $registers;

	/**
	 * The filterbank holds all the filters
	 *
	 * @var Filterbank
	 */
	protected $filterbank;

	/**
	 * Global scopes
	 *
	 * @var array
	 */
	public $environments = array();

	/**
	 * Constructor
	 *
	 * @param array $assigns
	 * @param array $registers
	 */
	public function __construct(array $assigns = array(), array $registers = array())
	{
		$this->assigns = array($assigns);
		$this->registers = $registers;
		$this->filterbank = new Filterbank($this);
		// first empty array serves as source for overrides, e.g. as in TagDecrement
		$this->environments = array(array(), $_SERVER);
	}

	/**
	 * Add a filter to the context
	 *
	 * @param mixed $filter
	 */
	public function addFilters($filter, callable $callback = null)
	{
		$this->filterbank->addFilter($filter, $callback);
	}

	/**
	 * Invoke the filter that matches given name
	 *
	 * @param string $name The name of the filter
	 * @param mixed $value The value to filter
	 * @param array $args Additional arguments for the filter
	 *
	 * @return string
	 */
	public function invoke($name, $value, array $args = array())
	{
		return $this->filterbank->invoke($name, $value, $args);
	}

	/**
	 * Merges the given assigns into the current assigns
	 *
	 * @param array $newAssigns
	 */
	public function merge($newAssigns)
	{
		$this->assigns[0] = array_merge($this->assigns[0], $newAssigns);
	}

	/**
	 * Push new local scope on the stack.
	 *
	 * @return bool
	 */
	public function push()
	{
		array_unshift($this->assigns, array());
		return true;
	}

	/**
	 * Pops the current scope from the stack.
	 *
	 * @throws LiquidException
	 * @return bool
	 */
	public function pop()
	{
		if (count($this->assigns) == 1) {
			throw new LiquidException('No elements to pop');
		}

		array_shift($this->assigns);
	}

	/**
	 * Replaces []
	 *
	 * @param string
	 * @param mixed $key
	 *
	 * @return mixed
	 */
	public function get($key)
	{
		return $this->resolve($key);
	}

	/**
	 * Replaces []=
	 *
	 * @param string $key
	 * @param mixed $value
	 * @param bool $global
	 */
	public function set($key, $value, $global = false)
	{
		if ($global) {
			for ($i = 0; $i < count($this->assigns); $i++) {
				$this->assigns[$i][$key] = $value;
			}
		} else {
			$this->assigns[0][$key] = $value;
		}
	}

	/**
	 * Returns true if the given key will properly resolve
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function hasKey($key)
	{
		return (!is_null($this->resolve($key)));
	}

	/**
	 * Resolve a key by either returning the appropriate literal or by looking up the appropriate variable
	 *
	 * Test for empty has been moved to interpret condition, in Decision
	 *
	 * @param string $key
	 *
	 * @throws LiquidException
	 * @return mixed
	 */
	private function resolve($key)
	{
		// This shouldn't happen
		if (is_array($key)) {
			throw new LiquidException("Cannot resolve arrays as key");
		}

		if (is_null($key) || $key == 'null') {
			return null;
		}

		if ($key == 'true') {
			return true;
		}

		if ($key == 'false') {
			return false;
		}

		if (preg_match('/^\'(.*)\'$/', $key, $matches)) {
			return $matches[1];
		}

		if (preg_match('/^"(.*)"$/', $key, $matches)) {
			return $matches[1];
		}

		if (preg_match('/^(-?\d+)$/', $key, $matches)) {
			return $matches[1];
		}

		if (preg_match('/^(-?\d[\d\.]+)$/', $key, $matches)) {
			return $matches[1];
		}

		return $this->variable($key);
	}

	/**
	 * Fetches the current key in all the scopes
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	private function fetch($key)
	{
		// TagDecrement depends on environments being checked before assigns
		foreach ($this->environments as $environment) {
			if (array_key_exists($key, $environment)) {
				return $environment[$key];
			}
		}

		foreach ($this->assigns as $scope) {
			if (array_key_exists($key, $scope)) {
				$obj = $scope[$key];

				if ($obj instanceof Drop) {
					$obj->setContext($this);
				}

				return $obj;
			}
		}

		return null;
	}

	/**
	 * Resolved the namespaced queries gracefully.
	 *
	 * @param string $key
	 *
	 * @see Decision::stringValue
	 * @see AbstractBlock::renderAll
	 *
	 * @throws LiquidException
	 * @return mixed
	 */
	private function variable($key)
	{
		// Support numeric and variable array indicies
		if (preg_match("|\[[0-9]+\]|", $key)) {
			$key = preg_replace("|\[([0-9]+)\]|", ".$1", $key);
		} elseif (preg_match("|\[[0-9a-z._]+\]|", $key, $matches)) {
			$index = $this->get(str_replace(array("[", "]"), "", $matches[0]));
			if (strlen($index)) {
				$key = preg_replace("|\[([0-9a-z._]+)\]|", ".$index", $key);
			}
		}

		$parts = explode(Liquid::get('VARIABLE_ATTRIBUTE_SEPARATOR'), $key);

		$object = $this->fetch(array_shift($parts));

		while (count($parts) > 0) {
			// since we still have a part to consider
			// and since we can't dig deeper into plain values
			// it can be thought as if it has a property with a null value
			if (!is_object($object) && !is_array($object)) {
				return null;
			}

			// first try to cast an object to an array or value
			if (method_exists($object, 'toLiquid')) {
				$object = $object->toLiquid();
			} elseif (method_exists($object, 'toArray')) {
				$object = $object->toArray();
			}

			if (is_null($object)) {
				return null;
			}

			if ($object instanceof Drop) {
				$object->setContext($this);
			}

			$nextPartName = array_shift($parts);

			if (is_array($object)) {
				// if the last part of the context variable is .size we just return the count
				if ($nextPartName == 'size' && count($parts) == 0 && !array_key_exists('size', $object)) {
					return count($object);
				}

				// no key - no value
				if (!array_key_exists($nextPartName, $object)) {
					return null;
				}

				$object = $object[$nextPartName];
				continue;
			}

			if (!is_object($object)) {
				// we got plain value, yet asked to resolve a part
				// think plain values have a null part with any name
				return null;
			}

			if ($object instanceof Drop) {
				// if the object is a drop, make sure it supports the given method
				if (!$object->hasKey($nextPartName)) {
					return null;
				}

				$object = $object->invokeDrop($nextPartName);
				continue;
			}

			// if it has `get` or `field_exists` methods
			if (method_exists($object, Liquid::get('HAS_PROPERTY_METHOD'))) {
				if (!call_user_func(array($object, Liquid::get('HAS_PROPERTY_METHOD')), $nextPartName)) {
					return null;
				}

				$object = call_user_func(array($object, Liquid::get('GET_PROPERTY_METHOD')), $nextPartName);
				continue;
			}

			// if it's just a regular object, attempt to access a public method
			if (is_callable(array($object, $nextPartName))) {
				$object = call_user_func(array($object, $nextPartName));
				continue;
			}

			// Inexistent property is a null, PHP-speak
			if (!property_exists($object, $nextPartName)) {
				return null;
			}

			// then try a property (independent of accessibility)
			if (property_exists($object, $nextPartName)) {
				$object = $object->$nextPartName;
				continue;
			}

			// we'll try casting this object in the next iteration
		}

		// lastly, try to get an embedded value of an object
		// value could be of any type, not just string, so we have to do this
		// conversion here, not later in AbstractBlock::renderAll
		if (method_exists($object, 'toLiquid')) {
			$object = $object->toLiquid();
		}

		/*
		 * Before here were checks for object types and object to string conversion.
		 *
		 * Now we just return what we have:
		 * - Traversable objects are taken care of inside filters
		 * - Object-to-string conversion is handled at the last moment in Decision::stringValue, and in AbstractBlock::renderAll
		 *
		 * This way complex objects could be passed between templates and to filters
		 */

		return $object;
	}
}
