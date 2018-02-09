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

	const PAGINATION_ASSIGNS = array(
		'HTTP_HOST' => 'example.com',
		'HTTPS' => 'on',
		'page' => 1,
		'articles' => array(array('title' => 1), array('title' => 2), array('title' => 3)),
	);

	public function testPaginationForRepeatedCalls()
	{
		$text = '{% for article in articles %}{{ article.title }},{% endfor %}';
		$expected = '1,2,3,';
		$this->assertTemplateResult($expected, $text, self::PAGINATION_ASSIGNS);

		$text = '{% paginate articles by 2 %}{% for article in articles %}{{ article.title }},{% endfor %}{% endpaginate %} '.$text;
		$expected = '1,2, 1,2,3,';
		$this->assertTemplateResult($expected, $text, self::PAGINATION_ASSIGNS);
	}

	public function testPaginationDoesntIncludePreviousIfFirst()
	{
		$assigns = self::PAGINATION_ASSIGNS;

		$text = '{% paginate articles by 1 %}{% for article in articles %}{{article.title}}{% endfor %} {{paginate.previous.title}},{{paginate.previous.url}} {{paginate.next.title}},{{paginate.next.url}}{% endpaginate %}';
		$expected = '1 , Next,https://example.com?page=2';
		$this->assertTemplateResult($expected, $text, $assigns);
	}

	public function testPaginateDoesntIncludeNextIfLast()
	{
		$assigns = self::PAGINATION_ASSIGNS;
		$assigns['page'] = 3;

		$text = '{% paginate articles by 1 %}{% for article in articles %}{{article.title}}{% endfor %} {{paginate.previous.title}},{{paginate.previous.url}} {{paginate.next.title}},{{paginate.next.url}}{% endpaginate %}';
		$expected = '3 Previous,https://example.com?page=2 ,';
		$this->assertTemplateResult($expected, $text, $assigns);
	}

	public function testPaginateUsingDifferentRequestParameterName()
	{
		Liquid::set('PAGINATION_REQUEST_KEY', 'pagina');

		$assigns = self::PAGINATION_ASSIGNS;
		$assigns['page'] = 2;

		$text = '{% paginate articles by 1 %}{% for article in articles %}{{article.title}}{% endfor %} {{paginate.previous.title}},{{paginate.previous.url}} {{paginate.next.title}},{{paginate.next.url}}{% endpaginate %}';
		$expected = '2 Previous,https://example.com?pagina=1 Next,https://example.com?pagina=3';
		$this->assertTemplateResult($expected, $text, $assigns);
	}

	public function testPaginateUsingDifferentContextParameter()
	{
		Liquid::set('PAGINATION_CONTEXT_KEY', 'the_current_page');

		$assigns = self::PAGINATION_ASSIGNS;
		$assigns['the_current_page'] = 2;

		$text = '{% paginate articles by 1 %}{% for article in articles %}{{article.title}}{% endfor %} {{paginate.previous.title}},{{paginate.previous.url}} {{paginate.next.title}},{{paginate.next.url}}{% endpaginate %}';
		$expected = '2 Previous,https://example.com?page=1 Next,https://example.com?page=3';
		$this->assertTemplateResult($expected, $text, $assigns);
	}

	public function testPaginateUrlGenerationPreservesParams()
	{
		$assigns = self::PAGINATION_ASSIGNS;
		$assigns['REQUEST_URI'] = '/testfile.php?someparam=1';

		$text = '{% paginate articles by 1 %}{{ paginate.next.url }}{% endpaginate %}';
		$expected = 'https://example.com/testfile.php?someparam=1&page=2';
		$this->assertTemplateResult($expected, $text, $assigns);
	}

	public function testPaginateUrlGenerationReplacesPageKey()
	{
		$assigns = self::PAGINATION_ASSIGNS;
		$assigns['REQUEST_URI'] = '/testfile.php?someparam=1&page=1';

		$text = '{% paginate articles by 1 %}{{ paginate.next.url }}{% endpaginate %}';
		$expected = 'https://example.com/testfile.php?someparam=1&page=2';
		$this->assertTemplateResult($expected, $text, $assigns);
	}

	public function testPaginateUrlGenerationRespectsPageParameterKey()
	{
		Liquid::set('PAGINATION_REQUEST_KEY', 'pagina');

		$assigns = self::PAGINATION_ASSIGNS;
		$assigns['REQUEST_URI'] = '/testfile.php?someparam=1&page=hello&pagina=1';

		$text = '{% paginate articles by 1 %}{{ paginate.next.url }}{% endpaginate %}';
		$expected = 'https://example.com/testfile.php?someparam=1&page=hello&pagina=2';
		$this->assertTemplateResult($expected, $text, $assigns);
	}

	public function testPaginateUrlGenerationWithoutHTTPS()
	{
		$assigns = self::PAGINATION_ASSIGNS;
		$assigns['REQUEST_URI'] = '/';
		$assigns['HTTPS'] = '';

		$text = '{% paginate articles by 1 %}{{ paginate.next.url }}{% endpaginate %}';
		$expected = 'http://example.com/?page=2';
		$this->assertTemplateResult($expected, $text, $assigns);
	}

	public function testPaginateDoesntIncludeNextIfAfterLast()
	{
		$assigns = self::PAGINATION_ASSIGNS;
		$assigns['page'] = 42;

		$text = '{% paginate articles by 1 %}{% for article in articles %}{{article.title}}{% endfor %} {{paginate.previous.title}},{{paginate.previous.url}} {{paginate.next.title}},{{paginate.next.url}}{% endpaginate %}';
		$expected = '3 Previous,https://example.com?page=2 ,';
		$this->assertTemplateResult($expected, $text, $assigns);
	}

	public function testPaginateDoesntIncludePreviousIfBeforeFirst()
	{
		$assigns = self::PAGINATION_ASSIGNS;
		$assigns['page'] = 0;

		$text = '{% paginate articles by 1 %}{% for article in articles %}{{article.title}}{% endfor %} {{paginate.previous.title}},{{paginate.previous.url}} {{paginate.next.title}},{{paginate.next.url}}{% endpaginate %}';
		$expected = '1 , Next,https://example.com?page=2';
		$this->assertTemplateResult($expected, $text, $assigns);
	}

	public function testPaginateIgnoresNonNumbers()
	{
		$assigns = self::PAGINATION_ASSIGNS;
		$assigns['page'] = 'foo';

		$text = '{% paginate articles by 1 %}{% for article in articles %}{{article.title}}{% endfor %} {{paginate.previous.title}},{{paginate.previous.url}} {{paginate.next.title}},{{paginate.next.url}}{% endpaginate %}';
		$expected = '1 , Next,https://example.com?page=2';
		$this->assertTemplateResult($expected, $text, $assigns);
	}
}
