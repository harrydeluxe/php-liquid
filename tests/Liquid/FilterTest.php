<?php

/**
 * This file is part of the Liquid package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Liquid
 */

namespace Liquid;

class MoneyFilter
{
	function money($value) {
		return sprintf(' %d$ ', $value);
	}

	function money_with_underscore($value) {
		return sprintf(' %d$ ', $value);
	}

}

class CanadianMoneyFilter
{
	function money($value) {
		return sprintf(' %d$ CAD ', $value);
	}

}

class FilterTest extends TestCase
{
	/**
	 * The current context
	 *
	 * @var Context
	 */
	var $context;

	function setup() {
		$this->context = new Context();
	}

	function test_local_filter() {
		$var = new Variable('var | money');
		$this->context->set('var', 1000);
		$this->context->addFilters(new MoneyFilter());
		$this->assertIdentical(' 1000$ ', $var->render($this->context));
	}

	function test_underscore_in_filter_name() {
		$var = new Variable('var | money_with_underscore ');
		$this->context->set('var', 1000);
		$this->context->addFilters(new MoneyFilter());
		$this->assertIdentical(' 1000$ ', $var->render($this->context));
	}

	function test_second_filter_overwrites_first() {
		$var = new Variable('var | money ');
		$this->context->set('var', 1000);
		$this->context->addFilters(new MoneyFilter(), 'money');
		$this->context->addFilters(new CanadianMoneyFilter(), 'money');
		$this->assertIdentical(' 1000$ CAD ', $var->render($this->context));
	}

	function test_size() {
		$var = new Variable("var | size");
		$this->context->set('var', 1000);
		//$this->context->addFilters(new MoneyFilter());
		$this->assertEqual(4, $var->render($this->context));
	}

	function test_join() {
		$var = new Variable("var | join");

		$this->context->set('var', array(1, 2, 3, 4));
		$this->assertEqual("1 2 3 4", $var->render($this->context));
	}

	function test_strip_html() {
		$var = new Variable("var | strip_html");

		$this->context->set('var', "<b>bla blub</a>");
		$this->assertEqual("bla blub", $var->render($this->context));
	}
}


class LiquidFiltersInTemplate extends TestCase
{
	function test_local_global() {
		$template = new Template;
		$template->registerFilter(new MoneyFilter());

		$template->parse('{{1000 | money}}');
		$this->assertIdentical(' 1000$ ', $template->render());
		$this->assertIdentical(' 1000$ CAD ', $template->render(array(null), new CanadianMoneyFilter()));
	}
}
