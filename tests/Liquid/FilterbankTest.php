<?php

/*
 * This file is part of the Liquid package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Liquid
 */

namespace {

/**
 * Global function acts as a filter.
 *
 * @param $value
 *
 * @return string
 */
function functionFilter($value)
{
	return 'worked';
}

/**
 * Global filter class
 */
class ClassFilter
{
	private $variable = 'not set';

	public static function static_test()
	{
		return "worked";
	}

	public function instance_test_one()
	{
		$this->variable = 'set';
		return 'set';
	}

	public function instance_test_two()
	{
		return $this->variable;
	}
}

} // global namespace

namespace Liquid {

use Liquid\Cache\File;

class NamespacedClassFilter
{
	public static function static_test2($var)
	{
		return "good {$var}";
	}
}

class FilterbankTest extends TestCase
{
	/** @var FilterBank */
	private $filterBank;

	/** @var Context */
	private $context;

	protected function setup()
	{
		parent::setUp();

		$this->context = new Context();
		$this->filterBank = new FilterBank($this->context);
	}

	protected function tearDown()
	{
		// have to destroy these else PHP goes nuts
		unset($this->context);
		unset($this->filterBank);
	}

	/**
	 * @expectedException \Liquid\Exception\WrongArgumentException
	 */
	public function testAddFilterNotObjectAndString()
	{
		$this->filterBank->addFilter(array());
	}

	/**
	 * @expectedException \Liquid\Exception\WrongArgumentException
	 */
	public function testAddFilterNoFunctionOrClass()
	{
		$this->filterBank->addFilter('no_such_function_or_class');
	}

	public function testInvokeNoFilter()
	{
		$value = 'value';
		$this->assertEquals($value, $this->filterBank->invoke('non_existing_filter', $value));
	}

	/**
	 * Test using a simple function
	 */
	public function testFunctionFilter()
	{
		$var = new Variable('var | functionFilter');
		$this->context->set('var', 1000);
		$this->context->addFilters('functionFilter');
		$this->assertEquals('worked', $var->render($this->context));
	}

	/**
	 * Test using a namespaced static class
	 */
	public function testNamespacedStaticClassFilter()
	{
		$var = new Variable('var | static_test2');
		$this->context->set('var', 1000);
		$this->context->addFilters(NamespacedClassFilter::class);
		$this->assertEquals('good 1000', $var->render($this->context));
	}

	/**
	 * Test using a static class
	 */
	public function testStaticClassFilter()
	{
		$var = new Variable('var | static_test');
		$this->context->set('var', 1000);
		$this->context->addFilters(\ClassFilter::class);
		$this->assertEquals('worked', $var->render($this->context));
	}

	/**
	 * Test with instance method on a static class
	 */
	public function testStaticMixedClassFilter()
	{
		$var = new Variable('var | instance_test_one');
		$this->context->set('var', 'foo');
		$this->context->addFilters(\ClassFilter::class);
		$this->assertEquals('foo', $var->render($this->context));
	}

	/**
	 * Test using an object as a filter; an object fiter will retain its state
	 * between calls to its filters.
	 */
	public function testObjectFilter()
	{
		$var = new Variable('var | instance_test_one');
		$this->context->set('var', 1000);
		$this->context->addFilters(new \ClassFilter());
		$this->assertEquals('set', $var->render($this->context));

		$var = new Variable('var | instance_test_two');
		$this->assertEquals('set', $var->render($this->context));

		$var = new Variable('var | static_test');
		$this->assertEquals('worked', $var->render($this->context));
	}

	public function testCallbackFilter()
	{
		$var = new Variable('var | my_callback');
		$this->context->set('var', 1000);
		$this->context->addFilters('my_callback', function ($var) {
			return $var * 2;
		});
		$this->assertEquals('2000', $var->render($this->context));
	}

	/**
	 * Closures are not to be serialized. Let's check that.
	 */
	public function testWithSerializingCache()
	{
		$template = new Template();
		$template->registerFilter('foo', function ($arg) {
			return "Foo $arg";
		});
		$template->setCache(new File(array(
			'cache_dir' => __DIR__.'/cache_dir/',
		)));
		$template->parse("{{'test' | foo }}");
		$this->assertEquals('Foo test', $template->render());

		$template->parse("{{'bar' | foo }}");
		$this->assertEquals('Foo bar', $template->render());
	}
}

} // Liquid namespace
