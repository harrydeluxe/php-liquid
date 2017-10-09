<?php

/*
 * This file is part of the Liquid package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Liquid
 */

require __DIR__ . '/../vendor/autoload.php';

use Liquid\Liquid;
use Liquid\Template;

$template = new Template();
$template->registerFilter('absolute_url', function ($arg) {
	return "https://www.example.com$arg";
});
$template->parse("{{ my_url | absolute_url }}");
echo $template->render(array(
	'my_url' => '/test'
));
// expect: https://www.example.com/test
