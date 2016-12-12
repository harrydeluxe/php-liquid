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
	const TEMPLATES_DIR = 'templates';

	/**
	 * @var mixed Filters
	 */
	public $filters;

	protected function setUp() {
		parent::setUp();

		$defaultConfig = array(
			'HAS_PROPERTY_METHOD' => 'field_exists',
			'GET_PROPERTY_METHOD' => 'get',
			'FILTER_SEPARATOR' => '\|',
			'ARGUMENT_SEPARATOR' => ',',
			'FILTER_ARGUMENT_SEPARATOR' => ':',
			'VARIABLE_ATTRIBUTE_SEPARATOR' => '.',
			'INCLUDE_ALLOW_EXT' => false,
			'INCLUDE_SUFFIX' => 'liquid',
			'INCLUDE_PREFIX' => '_',
			'TAG_START' => '{%',
			'TAG_END' => '%}',
			'VARIABLE_START' => '{{',
			'VARIABLE_END' => '}}',
			'ALLOWED_VARIABLE_CHARS' => '[a-zA-Z_.-]',
			'QUOTED_STRING' => '"[^":]*"|\'[^\':]*\'',
		);

		foreach ($defaultConfig as $configKey => $configValue) {
			Liquid::set($configKey, $configValue);
		}
	}

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
