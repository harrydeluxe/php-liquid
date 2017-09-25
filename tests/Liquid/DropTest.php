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

class ContextDrop extends Drop
{
	public function beforeMethod($method)
	{
		return $this->context->get($method);
	}
}

class TextDrop extends Drop
{
	public function get_array()
	{
		return array('text1', 'text2');
	}

	public function text()
	{
		return 'text1';
	}
}

class CatchallDrop extends Drop
{
	public function beforeMethod($method)
	{
		return 'method: ' . $method;
	}
}

class ProductDrop extends Drop
{
	public function top_sales()
	{
		throw new \Exception("worked");
	}

	public function texts()
	{
		return new TextDrop();
	}

	public function catchall()
	{
		return new CatchallDrop();
	}

	public function context()
	{
		return new ContextDrop();
	}

	public function callmenot()
	{
		return "protected";
	}

	public function hasKey($name)
	{
		return $name != 'unknown' && $name != 'false';
	}
}

class DropTest extends TestCase
{
	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage worked
	 */
	public function testProductDrop()
	{
		$template = new Template();
		$template->parse(' {{ product.top_sales }} ');
		$template->render(array('product' => new ProductDrop));
	}

	public function testNoKeyDrop()
	{
		$template = new Template();
		$template->parse(' {{ product.invalid.unknown }}{{ product.false }} ');
		$output = $template->render(array('product' => new ProductDrop));
		$this->assertEquals('  ', $output);
	}

	public function testTextDrop()
	{
		$template = new Template();
		$template->parse(' {{ product.texts.text }} ');
		$output = $template->render(array('product' => new ProductDrop()));
		$this->assertEquals(' text1 ', $output);

		$template = new Template();
		$template->parse(' {{ product.catchall.unknown }} ');
		$output = $template->render(array('product' => new ProductDrop()));
		$this->assertEquals(' method: unknown ', $output);
	}

	public function testTextArrayDrop()
	{
		$template = new Template();
		$template->parse('{% for text in product.texts.get_array %} {{text}} {% endfor %}');
		$output = $template->render(array('product' => new ProductDrop()));

		$this->assertEquals(' text1  text2 ', $output);
	}

	public function testContextDrop()
	{
		$template = new Template();
		$template->parse(' {{ context.bar }} ');
		$output = $template->render(array('context' => new ContextDrop(), 'bar' => 'carrot'));
		$this->assertEquals(' carrot ', $output);
	}

	public function testNestedContextDrop()
	{
		$template = new Template();
		$template->parse(' {{ product.context.foo }} ');
		$output = $template->render(array('product' => new ProductDrop(), 'foo' => 'monkey'));
		$this->assertEquals(' monkey ', $output);
	}

	public function testToString()
	{
		$this->assertEquals(ProductDrop::class, strval(new ProductDrop()));
	}
}
