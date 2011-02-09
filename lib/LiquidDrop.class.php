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
 * A drop in liquid is a class which allows you to to export DOM like things to liquid
 * Methods of drops are callable. 
 * The main use for liquid drops is the implement lazy loaded objects. 
 * If you would like to make data available to the web designers which you don't want loaded unless needed then 
 * a drop is a great way to do that
 *
 * Example:
 *
 * class ProductDrop extends LiquidDrop {
 *   function top_sales() {
 *      Products::find('all', array('order' => 'sales', 'limit' => 10 ));
 *   }
 * }
 *  
 * tmpl = Liquid::Template.parse( ' {% for product in product.top_sales %} {{ product.name }} {%endfor%} '  )
 * tmpl.render('product' => ProductDrop.new ) * will invoke top_sales query. 
 *
 * Your drop can either implement the methods sans any parameters or implement the before_method(name) method which is a 
 * catch all
 * 
 * @package Liquid
 */

class LiquidDrop
{
	
	/**
	 * @var LiquidContext
	 */
	var $context;


	/**
	 * Catch all method that is invoked before a specific method
	 *
	 * @param string $method
	 * @return mixed
	 */
	function before_method($method)
	{
		return null;
	}


	/**
	 * Invoke a specific method
	 *
	 * @param string $method
	 * @return mixed
	 */
	function invoke_drop($method)
	{
		$result = $this->before_method($method);
		
		if (is_null($result) && method_exists($this, $method))
		{
			$result = $this->$method();
		}
		
		return $result;
	}
	
	/**
	 * Returns true if the drop supports the given method
	 *
	 * @param unknown_type $name
	 * @return bool
	 */
	function has_key($name)
	{
		return true;
	}
	
	/**
	 * Enter description here...
	 *
	 * @return unknown
	 */
	function to_liquid()
	{
		return $this;
		
	}
}