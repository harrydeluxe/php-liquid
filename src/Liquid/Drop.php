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
 * A drop in liquid is a class which allows you to to export DOM like things to liquid.
 * Methods of drops are callable.
 * The main use for liquid drops is the implement lazy loaded objects.
 * If you would like to make data available to the web designers which you don't want loaded unless needed then
 * a drop is a great way to do that
 *
 * Example:
 *
 *     class ProductDrop extends LiquidDrop {
 *         public function topSales() {
 *             Products::find('all', array('order' => 'sales', 'limit' => 10 ));
 *         }
 *     }
 *
 * tmpl = Liquid::Template.parse( ' {% for product in product.top_sales %} {{ product.name }} {%endfor%} '  )
 * tmpl.render('product' => ProductDrop.new ) // will invoke topSales query.
 *
 * Your drop can either implement the methods sans any parameters or implement the beforeMethod(name) method which is a
 * catch all.
 */
abstract class Drop
{
	/**
	 * @var Context
	 */
	protected $context;

	/**
	 * Catch all method that is invoked before a specific method
	 *
	 * @param string $method
	 *
	 * @return null
	 */
	protected function beforeMethod($method)
	{
		return null;
	}

	/**
	 * @param Context $context
	 */
	public function setContext(Context $context)
	{
		$this->context = $context;
	}

	/**
	 * Invoke a specific method
	 *
	 * @param string $method
	 *
	 * @return mixed
	 */
	public function invokeDrop($method)
	{
		$result = $this->beforeMethod($method);

		if (is_null($result) && is_callable(array($this, $method))) {
			$result = $this->$method();
		}

		return $result;
	}

	/**
	 * Returns true if the drop supports the given method
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public function hasKey($name)
	{
		return true;
	}

	/**
	 * @return Drop
	 */
	public function toLiquid()
	{
		return $this;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return get_class($this);
	}
}
