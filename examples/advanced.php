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
use Liquid\Cache\Local;

Liquid::set('INCLUDE_SUFFIX', '');
Liquid::set('INCLUDE_PREFIX', '');
Liquid::set('INCLUDE_ALLOW_EXT', true);
Liquid::set('ESCAPE_BY_DEFAULT', true);

$template = new Template(__DIR__.'/protected/templates/');

$template->parse("Hello, {% include 'honorific.html' %}{{ plain-html | raw }} {{ comment-with-xss }}\n");
$template->setCache(new Local());

echo $template->render([
	'name' => 'Alex',
	'plain-html' => '<b>Your comment was:</b>',
	'comment-with-xss' => '<script>alert();</script>',
]);
