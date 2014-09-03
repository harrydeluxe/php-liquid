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



class FileSystemTest extends LiquidTestcase
{
	
	function test_default()
	{
		$file_system = new LiquidBlankFileSystem();
		
		try 
		{
			$file_system->readTemplateFile('dummy');
			$this->fail("Exception was expected.");
		} 
		catch (Exception $e)
		{
			$this->assertEqual($e->getMessage(), "This liquid context does not allow includes.");
			$this->pass();
		}		
	}
	
	
	function test_local()
	{
		$root = dirname(__FILE__).'/templates/';
		
		$file_system = new LiquidLocalFileSystem($root);
		$this->assertEqual($root."mypartial.tpl", $file_system->fullPath("mypartial"));
		$this->assertEqual($root."dir/mypartial.tpl", $file_system->fullPath("dir/mypartial"));


		try 
		{
			$file_system->fullPath('../dir/mypartial');
			$this->fail();
		} 
		catch (Exception $e)
		{
			$this->assertEqual($e->getMessage(), "Illegal template name '../dir/mypartial'");
		}


		try 
		{
			$file_system->fullPath("/dir/../../dir/mypartial");
			$this->fail();
		} 
		catch (Exception $e)
		{
			$this->assertEqual($e->getMessage(), "Illegal template name '/dir/../../dir/mypartial'");
		}
		
		try 
		{
			$file_system->fullPath("/etc/passwd");
			$this->fail();
		} 
		catch (Exception $e)
		{
			$this->assertEqual($e->getMessage(), "Illegal template name '/etc/passwd'");
		}
	}
}