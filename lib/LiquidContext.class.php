<?php
/**
 * Context keeps the variable stack and resolves variables, as well as keywords
 * 
 * @package Liquid
 * @copyright Copyright (c) 2011-2012 Harald Hanek, 
 * fork of php-liquid (c) 2006 Mateo Murphy,
 * based on Liquid for Ruby (c) 2006 Tobias Luetke
 * @license http://harrydeluxe.mit-license.org
 */

class LiquidContext
{
    /**
     * Local scopes
     *
     * @var array
     */
    private $_assigns;

    /**
     * Registers for non-variable state data
     *
     * @var array
     */
    public $registers;

    /**
     * The filterbank holds all the filters
     *
     * @var LiquidFilterbank
     */
    private $_filterbank;

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
    public function __construct($assigns = null, $registers = array())
    {
        $this->_assigns = (isset($assigns)) ? array(
            $assigns
        ) : array(
            array()
        );
        $this->registers = $registers;
        $this->_filterbank = new LiquidFilterbank($this);
    }


    /**
     * Add a filter to the context
     *
     * @param mixed $filter
     */
    public function addFilters($filter)
    {
        $this->_filterbank->addFilter($filter);
    }


    /**
     * Invoke the filter that matches given name
     *
     * @param string $name The name of the filter
     * @param mixed $value The value to filter
     * @param array $args Additional arguments for the filter
     * @return string
     */
    public function invoke($name, $value, $args = null)
    {
        return $this->_filterbank->invoke($name, $value, $args);
    }


    /**
     * Merges the given assigns into the current assigns
     *
     * @param array $newAssigns
     */
    public function merge($newAssigns)
    {
        $this->_assigns[0] = array_merge($this->_assigns[0], $newAssigns);
    }


    /**
     * Push new local scope on the stack.
     *
     * @return bool
     */
    public function push()
    {
        array_unshift($this->_assigns, array());
        return true;
    }


    /**
     * Pops the current scope from the stack.
     *
     * @return bool
     */
    public function pop()
    {
        if (count($this->_assigns) == 1)
        {
            throw new LiquidException('No elements to pop');
        }

        array_shift($this->_assigns);
    }


    /**
     * Replaces []
     * 
     * @param string
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
        if ( is_numeric($value) )
        {
            $value = (int) $value;
        }
        
        if ( $global )
        {
            for($i = 0; $i < count($this->_assigns); $i++)
            {
                $this->_assigns[$i][$key] = $value;
            }
        }
        else
        {
            $this->_assigns[0][$key] = $value;
        }
    }


    /**
     * Returns true if the given key will properly resolve
     *
     * @param string $key
     * @return bool
     */
    public function hasKey($key)
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
    public function resolve($key)
    {
        // this shouldn't happen
        if (is_array($key))
        {
            throw new LiquidException("Cannot resolve arrays as key");
        }

        if (is_null($key) || $key == 'null')
        {
            return null;
        }

        if ($key == 'true')
        {
            return true;
        }

        if ($key == 'false')
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
    public function fetch($key)
    {
        foreach($this->environments as $environment)
        {
            if (array_key_exists($key, $environment))
            {
                return $environment[$key];
            }
        }

        foreach($this->_assigns as $scope)
        {
            if (array_key_exists($key, $scope))
            {
                $obj = $scope[$key];

                if ($obj instanceof LiquidDrop)
                    $obj->setContext($this);

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
    public function variable($key)
    {
        /* Support [0] style array indicies */
        if (preg_match("|\[[0-9]+\]|", $key))
        {
            $key = preg_replace("|\[([0-9]+)\]|", ".$1", $key);
        }

        $parts = explode(LIQUID_VARIABLE_ATTRIBUTE_SEPARATOR, $key);

        $object = $this->fetch(array_shift($parts));

        if (is_object($object))
        {
            if (!method_exists($object, 'toLiquid'))
                throw new LiquidException("Method 'toLiquid' not exists!");

            $object = $object->toLiquid();
        }

        if (!is_null($object))
        {
            while(count($parts) > 0)
            {
                if ($object instanceof LiquidDrop)
                    $object->setContext($this);

                $nextPartName = array_shift($parts);

                if (is_array($object))
                {
                    // if the last part of the context variable is .size we just return the count
                    if ($nextPartName == 'size' && count($parts) == 0 && !array_key_exists('size', $object))
                    {
                        return count($object);
                    }

                    if (array_key_exists($nextPartName, $object))
                    {
                        $object = $object[$nextPartName];
                    }
                    else
                    {
                        return null;
                    }

                }
                elseif (is_object($object))
                {
                    if ($object instanceof LiquidDrop)
                    {
                        // if the object is a drop, make sure it supports the given method
                        if (!$object->hasKey($nextPartName))
                        {
                            return null;
                        }

                        // php4 doesn't support array access, so we have
                        // to use the invoke method instead
                        $object = $object->invokeDrop($nextPartName);

                    }
                    elseif (method_exists($object, LIQUID_HAS_PROPERTY_METHOD))
                    {

                        if (!call_user_method(LIQUID_HAS_PROPERTY_METHOD, $object, $nextPartName))
                        {
                            return null;
                        }

                        $object = call_user_method(LIQUID_GET_PROPERTY_METHOD, $object, $nextPartName);


                    }
                    else
                    {
                        // if it's just a regular object, attempt to access a property
                        if (!property_exists($object, $nextPartName))
                        {
                            return null;
                        }

                        $object = $object->$nextPartName;
                    }
                }

                if (is_object($object) && method_exists($object, 'toLiquid'))
                {
                    $object = $object->toLiquid();
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