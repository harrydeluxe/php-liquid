<?php

/**
 * This file is part of the Liquid package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Liquid
 */

namespace Liquid\Tag;

use Liquid\TestCase;

class TagPaginateTest extends TestCase
{
	
	public function testWorks() {
		$text = "{% paginate products by 3 %}{% for product in products %} {{ product.id }} {% endfor %}{% endpaginate %}";
		$expected = " 1  2  3 ";
		$this->assertTemplateResult($expected, $text, array('products' => array(array('id' => 1), array('id' => 2), array('id' => 3), array('id' => 4), array('id' => 5))));
	}
	
	public function testVariables() {
		$text = " {% paginate products by 3 %}{{ paginate.page_size }} {{ paginate.current_page }} {{ paginate.current_offset }} {{ paginate.pages }} {{ paginate.items }} {% endpaginate %}";
		$expected = " 3 1 0 2 5 ";
		$this->assertTemplateResult($expected, $text, array('products' => array(array('id' => 1), array('id' => 2), array('id' => 3), array('id' => 4), array('id' => 5))));
	}
	
}
