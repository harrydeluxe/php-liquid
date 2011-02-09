<?php
/**
 * Liquid for PHP
 * 
 * @package Liquid
 * @copyright Copyright (c) 2011 Harald Hanek, 
 * fork of php-liquid (c) 2006 Mateo Murphy,
 * based on Liquid for Ruby (c) 2006 Tobias Luetke
 * @license http://www.opensource.org/licenses/mit-license.php
 */


/**
 * Context keeps the variable stack and resolves variables, as well as keywords
 *
 * @package Liquid
 */
class LiquidContext
{
	
	/**
	 * Local scopes
	 *
	 * @var array
	 */
	var $assigns;

	/**
	 * Registers for non-variable state data
	 *
	 * @var array
	 */
	var $registers;
	
	/**
	 * The filterbank holds all the filters
	 *
	 * @var LiquidFilterbank
	 */
	var $filterbank;


	/**
	 * Constructor
	 *
	 * @param array $assigns
	 * @param array $registers
	 * @return LiquidContext
	 */
	public function __construct($assigns = null, $registers = array())
	{
		$this->assigns = (isset($assigns)) ? array($assigns) : array(array());
		$this->registers = $registers;
		$this->filterbank = new LiquidFilterbank($this);
	}


	/**
	 * Add a filter to the context
	 *
	 * @param mixed $filter
	 */
	function add_filters($filter)
	{
		$this->filterbank->add_filter($filter);
	}
	
	/**
	 * Invoke the filter that matches given name
	 *
	 * @param string $name The name of the filter
	 * @param mixed $value The value to filter
	 * @param array $args Additional arguments for the filter
	 * @return string
	 */
	function invoke($name, $value, $args = null)
	{
		return $this->filterbank->invoke($name, $value, $args);
	}


	/**
	 * Merges the given assigns into the current assigns
	 *
	 * @param array $new_assigns
	 */
	function merge($new_assigns)
	{
		$this->assigns[0] = array_merge($this->assigns[0], $new_assigns);
	}	


	/**
	 * Push new local scope on the stack.
	 *
	 * @return bool
	 */
	function push()
	{
		array_unshift($this->assigns, array());
		return true;
	}


	/**
	 * Pops the current scope from the stack.
	 *
	 * @return bool
	 */
	function pop()
	{
		if(count($this->assigns) == 1)
		{
			throw new LiquidException('No elements to pop');
		}
		
		array_shift($this->assigns);
	}


	/**
	 * Replaces []
	 * 
	 * @param string
	 * @return mixed
	 */
	function get($key)
	{
		return $this->resolve($key);
	}


	/**
	 * Replaces []=
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	function set($key, $value)
	{
		$this->assigns[0][$key] = $value;
	}


	/**
	 * Returns true if the given key will properly resolve
	 *
	 * @param string $key
	 * @return bool
	 */
	function has_key($key)
	{
		return (!is_null($this->resolve($key)));
	}


	/**
	 * Resolve a key by either returning the appropriate literal or by looking up the appropriate variable
	 * 
	 * Test for empty has been moved to interpret condition, in LiquidDecisionBlock
	 *
	 * @param string $key
	 * @return mixed
	 */
	function resolve($key)
	{
		// this shouldn't happen
		if(is_array($key))
		{
			throw new LiquidException("Cannot resolve arrays as key");
		}
		
		if (is_null($key) || $key == 'null') {
			return null;
		}
	
		if($key == 'true')
		{
			return true;
		}
		
		if($key == 'false')
		{
			return false;
		}
		
		if (preg_match('/^\'(.*)\'$/', $key, $matches))
		{
			return $matches[1];
		}

		if (preg_match('/^"(.*)"$/', $key, $matches))
		{
			return $matches[1];
		}

		if (preg_match('/^(\d+)$/', $key, $matches))
		{
			return $matches[1];
		}

		if (preg_match('/^(\d[\d\.]+)$/', $key, $matches))
		{
			return $matches[1];
		}			
		
		return $this->variable($key);
	}


	/**
	 * Fetches the current key in all the scopes
	 *
	 * @param string $key
	 * @return mixed
	 */
	function fetch($key)
	{
		foreach ($this->assigns as $scope)
		{
			if(array_key_exists($key, $scope))
			{
				$obj = $scope[$key];
				
				//if(is_a($obj, 'LiquidDrop'))
				if($obj instanceof LiquidDrop)	// harry
				{
					$obj->context = $this;
				}
				
				return $obj;
			}
		}
	}


	/**
	 * Resolved the namespaced queries gracefully.
	 *
	 * @param string $key
	 * @return mixed
	 */
	function variable($key)
	{
		$parts = explode(LIQUID_VARIABLE_ATTRIBUTE_SEPERATOR, $key);
		
		$object = $this->fetch(array_shift($parts));
		
		if (is_object($object))
		{
			$object = $object->to_liquid();
		}
		
		
		if (!is_null($object))
		{
			while (count($parts) > 0)
			{
				//if (is_a($object, 'LiquidDrop'))
				if ($object instanceof LiquidDrop)	// harry
				{
					$object->context = $this;
				}	
				
				$next_part_name = array_shift($parts);
				
				if (is_array($object))
				{
					
					// if the last part of the context variable is .size we just return the count
					if ($next_part_name == 'size' && count($parts) == 0 && !array_key_exists('size', $object))
					{
						return count($object);	
					}					
					
					if (array_key_exists($next_part_name, $object))
					{
						$object = $object[$next_part_name];
					}
					else
					{
						return null;
					}
					
				}
				elseif (is_object($object))
				{
					if($object instanceof LiquidDrop)
					{
						// if the object is a drop, make sure it supports the given method
						if (!$object->has_key($next_part_name))
						{
							return null;
						}
						
						// php4 doesn't support array access, so we have
						// to use the invoke method instead
						$object = $object->invoke_drop($next_part_name);
						
					}
					elseif(method_exists($object, LIQUID_HAS_PROPERTY_METHOD))
					{
						
						if (!call_user_method(LIQUID_HAS_PROPERTY_METHOD, $object, $next_part_name))
						{
							return null;
						}
						
						$object = call_user_method(LIQUID_GET_PROPERTY_METHOD, $object, $next_part_name);
						
						
					}
					else
					{
						// if it's just a regular object, attempt to access a property
						if (!property_exists($object, $next_part_name))
						{
							return null;	
						}
						
						$object = $object->$next_part_name;	
					}
				}

				if (is_object($object) && method_exists($object, 'to_liquid'))
				{
					$object = $object->to_liquid();
				}
			}

			return $object;		
		}
		else
		{
			return null;
		}
	}	
}