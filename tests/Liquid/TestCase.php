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

class TestCase extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var mixed Filters
	 */
	public $filters;

	/**
	 * @param mixed $expected
	 * @param string $templateString
	 * @param array $assigns
	 * @param string $message
	 */
	public function assertTemplateResult($expected, $templateString, array $assigns = array(), $message = "%s") {
		$template = new Template();
		$template->parse($templateString);

		$this->assertEquals($expected, $template->render($assigns, $this->filters), $message);
	}
}
