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

class ContextDrop extends Drop
{
	function _beforeMethod($method)
	{
		return $this->_context->get($method);		
	}	
}


class TextDrop extends Drop
{
	function get_array()
	{
		return array('text1', 'text2');		
	}

	function text()
	{
		return 'text1';		
	}
}

class CatchallDrop extends Drop
{
	function _beforeMethod($method)
	{
		return 'method: '.$method;		
	}
	
}

class ProductDrop extends Drop
{
	function top_sales()
	{
		trigger_error('worked', E_USER_ERROR);		
	}
	
	function texts()
	{
		return new TextDrop();
		
	}
	
	function catchall()
	{
		return new CatchallDrop();
		
	}
	
	function context()
	{
		return new ContextDrop();
	}
	
	function callmenot()
	{
		return "protected";
		
	}	
}

class DropTest extends UnitTestCase
{
	
	function test_product_drop()
	{		
		$template = new Template;
		$template->parse('  ');
		//$template->render(array('product' => new ProductDrop));
		//$this->assertNoErrors();
        $this->assertTrue($template->render(array('product' => new ProductDrop)));
		
		
	    $template = new Template;
		$template->parse( ' {{ product.top_sales }} '  );
		$this->expectError('worked');
	    $template->render(array('product' => new ProductDrop));
	}

	function test_text_drop()
	{
		
		$template = new Template;
		$template->parse(' {{ product.texts.text }} ');
		$output = $template->render(array('product' => new ProductDrop()));	
		$this->assertEqual(' text1 ', $output);

		$template = new Template;
		$template->parse(' {{ product.catchall.unknown }} ');
		$output = $template->render(array('product' => new ProductDrop()));	
		$this->assertEqual(' method: unknown ', $output);				
	}
	
	// needed to rename call to array because array is a reserved word in php
	
	function test_text_array_drop()
	{
		$template = new Template;
		$template->parse('{% for text in product.texts.get_array %} {{text}} {% endfor %}');
		$output = $template->render(array('product' => new ProductDrop()));
		
		$this->assertEqual(' text1  text2 ', $output);		
	}
	
	
	function test_context_drop()
	{
		$template = new Template;
		$template->parse(' {{ context.bar }} ');
		$output = $template->render(array('context' => new ContextDrop(), 'bar'=>'carrot'));	
		$this->assertEqual(' carrot ', $output);				
	}
	
	function test_nested_context_drop()
	{
		$template = new Template;
		$template->parse(' {{ product.context.foo }} ');
		$output = $template->render(array('product' => new ProductDrop(), 'foo'=>'monkey'));	
		$this->assertEqual(' monkey ', $output);		
	}
}
