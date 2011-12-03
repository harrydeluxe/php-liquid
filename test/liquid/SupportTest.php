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



class property_exists_class
{
	
	var $one = 1;
	
	var $null;
	
	function property_exists_class()
	{
		$this->two = 2;		
	}	
}


class SupportTest extends LiquidTestcase
{
	
	function test_property_exists()
	{
		$this->assertTrue(property_exists('property_exists_class', 'one'));
		$this->assertFalse(property_exists('property_exists_class', 'two'));
		$this->assertTrue(property_exists('property_exists_class', 'null'));
		
		$object = new property_exists_class();
		$this->assertTrue(property_exists($object, 'one'));
		$this->assertTrue(property_exists($object, 'two'));
		$this->assertFalse(property_exists($object, 'three'));
		$this->assertTrue(property_exists($object, 'null'));	
	}
	
}