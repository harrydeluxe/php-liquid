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

class TemplateTest extends TestCase
{
	const CACHE_DIR = 'cache_dir';

	/** @var string full path to cache dir  */
	protected $cacheDir;

	protected function setUp()
	{
		parent::setUp();

		$this->cacheDir = __DIR__ . DIRECTORY_SEPARATOR . self::CACHE_DIR;
	}

	protected function tearDown()
	{
		parent::tearDown();

		// Remove tmp cache files
		array_map('unlink', glob($this->cacheDir . DIRECTORY_SEPARATOR . '*'));
	}

	/**
	 * @expectedException \Liquid\LiquidException
	 */
	public function testSetCacheInvalidKey()
	{
		$template = new Template();
		$template->setCache(array());
	}

	/**
	 * @expectedException \Liquid\LiquidException
	 */
	public function testSetCacheInvalidClass()
	{
		$template = new Template();
		$template->setCache(array('cache' => 'no_such_class'));
	}

	public function testSetCacheThroughArray()
	{
		$template = new Template();
		$template->setCache(array('cache' => 'file', 'cache_dir' => $this->cacheDir));
		$this->assertInstanceOf(\Liquid\Cache\File::class, $template::getCache());
	}

	public function testSetCacheThroughCacheObject()
	{
		$template = new Template();
		$cache = new Cache\File(array('cache_dir' => $this->cacheDir));
		$template->setCache($cache);
		$this->assertEquals($cache, $template::getCache());
	}

	public function testTokenizeStrings()
	{
		$this->assertEquals(array(' '), Template::tokenize(' '));
		$this->assertEquals(array('hello world'), Template::tokenize('hello world'));
	}

	public function testTokenizeVariables()
	{
		$this->assertEquals(array('{{funk}}'), Template::tokenize('{{funk}}'));
		$this->assertEquals(array(' ', '{{funk}}', ' '), Template::tokenize(' {{funk}} '));
		$this->assertEquals(array(' ', '{{funk}}', ' ', '{{so}}', ' ', '{{brother}}', ' '), Template::tokenize(' {{funk}} {{so}} {{brother}} '));
		$this->assertEquals(array(' ', '{{  funk  }}', ' '), Template::tokenize(' {{  funk  }} '));
	}

	public function testTokenizeBlocks()
	{
		$this->assertEquals(array('{%comment%}'), Template::tokenize('{%comment%}'));
		$this->assertEquals(array(' ', '{%comment%}', ' '), Template::tokenize(' {%comment%} '));
		$this->assertEquals(array(' ', '{%comment%}', ' ', '{%endcomment%}', ' '), Template::tokenize(' {%comment%} {%endcomment%} '));
		$this->assertEquals(array('  ', '{% comment %}', ' ', '{% endcomment %}', ' '), Template::tokenize("  {% comment %} {% endcomment %} "));
	}

	public function testBlackspace()
	{
		$template = new Template();
		$template->parse('  ');

		$nodelist = $template->getRoot()->getNodelist();

		$this->assertEquals(array('  '), $nodelist);
	}

	public function testVariableBeginning()
	{
		$template = new Template();
		$template->parse('{{funk}}  ');

		$nodelist = $template->getRoot()->getNodelist();

		$this->assertEquals(2, count($nodelist));
		$this->assertInstanceOf(\Liquid\Variable::class, $nodelist[0]);
		$this->assertInternalType('string', $nodelist[1]);
	}

	public function testVariableEnd()
	{
		$template = new Template();
		$template->parse('  {{funk}}');

		$nodelist = $template->getRoot()->getNodelist();

		$this->assertEquals(2, count($nodelist));
		$this->assertInternalType('string', $nodelist[0]);
		$this->assertInstanceOf(\Liquid\Variable::class, $nodelist[1]);
	}

	public function testVariableMiddle()
	{
		$template = new Template();
		$template->parse('  {{funk}}  ');

		$nodelist = $template->getRoot()->getNodelist();

		$this->assertEquals(3, count($nodelist));
		$this->assertInternalType('string', $nodelist[0]);
		$this->assertInstanceOf(\Liquid\Variable::class, $nodelist[1]);
		$this->assertInternalType('string', $nodelist[2]);
	}

	public function testVariableManyEmbeddedFragments()
	{
		$template = new Template();
		$template->parse('  {{funk}}  {{soul}}  {{brother}} ');

		$nodelist = $template->getRoot()->getNodelist();

		$this->assertEquals(7, count($nodelist));
		$this->assertInternalType('string', $nodelist[0]);
		$this->assertInstanceOf(\Liquid\Variable::class, $nodelist[1]);
		$this->assertInternalType('string', $nodelist[2]);
		$this->assertInstanceOf(\Liquid\Variable::class, $nodelist[3]);
		$this->assertInternalType('string', $nodelist[4]);
		$this->assertInstanceOf(\Liquid\Variable::class, $nodelist[5]);
		$this->assertInternalType('string', $nodelist[6]);
	}

	public function testWithBlock()
	{
		$template = new Template();
		$template->parse('  {% comment %}  {% endcomment %} ');

		$nodelist = $template->getRoot()->getNodelist();

		$this->assertEquals(3, count($nodelist));
		$this->assertInternalType('string', $nodelist[0]);
		$this->assertInstanceOf(\Liquid\Tag\TagComment::class, $nodelist[1]);
		$this->assertInternalType('string', $nodelist[2]);
	}
}
