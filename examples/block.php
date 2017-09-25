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

Liquid::set('INCLUDE_ALLOW_EXT', true);

$protectedPath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'protected' . DIRECTORY_SEPARATOR;

$liquid = new Template($protectedPath . 'templates' . DIRECTORY_SEPARATOR);

// Uncomment the following lines to enable cache
//$cache = array('cache' => 'file', 'cache_dir' => $protectedPath . 'cache' . DIRECTORY_SEPARATOR);
// or if you have APC installed
//$cache = array('cache' => 'apc');
//$liquid->setCache($cache);

$liquid->parse(file_get_contents($protectedPath . 'templates' . DIRECTORY_SEPARATOR . 'child.tpl'));

$assigns = array(
	'document' => array(
		'title' => 'This is php-liquid',
		'content' => 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.',
		'copyright' => '&copy; Copyright 2014 Guz Alexander - All rights reserved.',
	),
);

echo $liquid->render($assigns);
