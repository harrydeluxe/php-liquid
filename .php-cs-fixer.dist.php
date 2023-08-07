<?php

$header = <<<'EOF'
This file is part of the Liquid package.

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.

@package Liquid
EOF;

$config = new PhpCsFixer\Config();
$config
    ->setRiskyAllowed(true)
    ->setRules([
		'@PSR2' => true,
		'psr_autoloading' => true,
		'no_unreachable_default_argument_value' => true,
		'no_useless_else' => true,
		'no_useless_return' => true,
		'phpdoc_add_missing_param_annotation' => true,
		'phpdoc_order' => true,
		'semicolon_after_instruction' => true,
		'whitespace_after_comma_in_array' => true,
		'header_comment' => ['header' => $header],
		'php_unit_construct' => true,
		'php_unit_dedicate_assert' => true,
		'php_unit_dedicate_assert_internal_type' => true,
		'php_unit_expectation' => true,
		'php_unit_mock_short_will_return' => true,
		'php_unit_mock' => true,
		'php_unit_namespaced' => true,
		'php_unit_no_expectation_annotation' => true,
                "phpdoc_order_by_value" => ['annotations' => ['covers']],
		'php_unit_set_up_tear_down_visibility' => true,
		'php_unit_test_case_static_method_calls' => ['call_type' => 'this'],
	])
	->setIndent("\t")
	->setFinder(
		PhpCsFixer\Finder::create()
		->in(__DIR__)
	)
;


return $config;
