<?php

/*
 * This file is part of the Liquid package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Liquid
 */

namespace Liquid\Tag;

use Liquid\TestCase;
use Liquid\Liquid;

class TagPaginateTest extends TestCase
{
	/** System default values for the request and context page key */
	private static $requestKeyDefault;
	private static $contextKeyDefault;

	public static function setUpBeforeClass()
	{
		// save system default value for the escape flag before all tests
		self::$requestKeyDefault = Liquid::get('PAGINATION_REQUEST_KEY');
		self::$contextKeyDefault = Liquid::get('PAGINATION_CONTEXT_KEY');
	}

	public function tearDown()
	{
		// reset to the defaults after each test
		Liquid::set('PAGINATION_REQUEST_KEY', self::$requestKeyDefault);
		Liquid::set('PAGINATION_CONTEXT_KEY', self::$contextKeyDefault);
	}

	public function testWorks()
	{
		$text = "{% paginate products by 3 %}{% for product in products %} {{ product.id }} {% endfor %}{% endpaginate %}";
		$expected = " 1  2  3 ";
		$this->assertTemplateResult($expected, $text, array('products' => array(array('id' => 1), array('id' => 2), array('id' => 3), array('id' => 4), array('id' => 5))));
	}

	public function testVariables()
	{
		$text = " {% paginate search.products by 3 %}{{ paginate.page_size }} {{ paginate.current_page }} {{ paginate.current_offset }} {{ paginate.pages }} {{ paginate.items }} {{ paginate.next.url }}{% endpaginate %}";
		$expected = " 3 1 0 2 5 http://?page=2";
		$this->assertTemplateResult($expected, $text, array('search' => array('products' => new \ArrayIterator(array(array('id' => 1), array('id' => 2), array('id' => 3), array('id' => 4), array('id' => 5))))));
	}

	public function testNextPage()
	{
		$text = '{% paginate products by 1 %}{% for product in products %} {{ product.id }} {% endfor %}<a href="{{ paginate.next.url }}">{{ paginate.next.title }}</a>{% endpaginate %}';
		$expected = ' 2 <a href="https://example.com/products?page=3">Next</a>';
		$this->assertTemplateResult($expected, $text, array('HTTP_HOST' => 'example.com', 'REQUEST_URI' => '/products', 'HTTPS' => 'on', 'page' => 2, 'products' => array(array('id' => 1), array('id' => 2), array('id' => 3), array('id' => 4), array('id' => 5))));
	}

	/**
	 * @expectedException \Liquid\Exception\ParseException
	 */
	public function testSyntaxErrorCase()
	{
		$this->assertTemplateResult('', '{% paginate products %}{% endpaginate %}');
	}

	/**
	 * @expectedException \Liquid\Exception\RenderException
	 * @expectedExceptionMessage Missing collection
	 */
	public function testNoCollection()
	{
		$this->assertTemplateResult('', '{% paginate products by 1 %}{% for product in products %}{{ product.id }}{% endfor %}{% endpaginate %}');
	}

	public function testPaginationForRepeatedCalls()
	{
		$assigns = array(
			'articles' => array(array('title' => 1), array('title' => 2), array('title' => 3)),
		);

		$text = '{% for article in articles %}{{ article.title }},{% endfor %}';
		$expected = '1,2,3,';
		$this->assertTemplateResult($expected, $text, $assigns);

		$text = '{% paginate articles by 2 %}{% for article in articles %}{{ article.title }},{% endfor %}{% endpaginate %} '.$text;
		$expected = '1,2, 1,2,3,';
		$this->assertTemplateResult($expected, $text, $assigns);
	}

	public function testPaginationDoesntIncludePreviousIfFirst()
	{
		$assigns = array(
			'HTTP_HOST' => 'example.com', 'page' => 1, 'articles' => $this->provideArticleFixture()
		);

		$text = '{% paginate articles by 1 %}{% for article in articles %}{{article.title}}{% endfor %} {{paginate.previous.title}},{{paginate.previous.url}} {{paginate.next.title}},{{paginate.next.url}}{% endpaginate %}';

		$expected = '1 , Next,http://example.com?page=2';

		$this->assertTemplateResult($expected, $text, $assigns);
	}

	public function testPaginateDoesntIncludeNextIfLast()
	{
		$assigns = array(
			'HTTP_HOST' => 'example.com', 'page' => 3, 'articles' => $this->provideArticleFixture()
		);

		$text = '{% paginate articles by 1 %}{% for article in articles %}{{article.title}}{% endfor %} {{paginate.previous.title}},{{paginate.previous.url}} {{paginate.next.title}},{{paginate.next.url}}{% endpaginate %}';

		$expected = '3 Previous,http://example.com?page=2 ,';

		$this->assertTemplateResult($expected, $text, $assigns);
	}

	public function testPaginateUsingDifferentRequestParameterName()
	{
		$assigns = array(
			'HTTP_HOST' => 'example.com', 'page' => 2, 'articles' => $this->provideArticleFixture()
		);

		$text = '{% paginate articles by 1 %}{% for article in articles %}{{article.title}}{% endfor %} {{paginate.previous.title}},{{paginate.previous.url}} {{paginate.next.title}},{{paginate.next.url}}{% endpaginate %}';

		$expected = '2 Previous,http://example.com?pagina=1 Next,http://example.com?pagina=3';

		Liquid::set('PAGINATION_REQUEST_KEY', 'pagina');
		$this->assertTemplateResult($expected, $text, $assigns);
	}
	
	public function testPaginateUsingDifferentContextParameter()
	{
		$assigns = array(
			'HTTP_HOST' => 'example.com', 'the_current_page' => 2, 'articles' => $this->provideArticleFixture()
		);

		$text = '{% paginate articles by 1 %}{% for article in articles %}{{article.title}}{% endfor %} {{paginate.previous.title}},{{paginate.previous.url}} {{paginate.next.title}},{{paginate.next.url}}{% endpaginate %}';

		$expected = '2 Previous,http://example.com?page=1 Next,http://example.com?page=3';

		Liquid::set('PAGINATION_CONTEXT_KEY', 'the_current_page');
		$this->assertTemplateResult($expected, $text, $assigns);
	}

	private function provideArticleFixture()
	{
		return array(array('title' => 1), array('title' => 2), array('title' => 3));
	}
}
