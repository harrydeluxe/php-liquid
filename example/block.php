<?php

define('LIQUID_INCLUDE_ALLOW_EXT', true);

require_once('../Liquid.class.php');

define('PROTECTED_PATH', dirname(__FILE__).'/protected/');


$liquid = new Template(PROTECTED_PATH.'templates/');

$cache = array('cache' => 'file', 'cache_dir' => PROTECTED_PATH.'cache/');
//$cache = array('cache' => 'apc');

//$liquid->setCache($cache);

$liquid->parse(file_get_contents(PROTECTED_PATH.'templates/child.tpl'));

$assigns = array(
		'document' => array(
			'title' => 'This is php-liquid',
			'content' => 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.',
			'copyright' => '&copy; Copyright 2012 Harald Hanek - All rights reserved.'
			)
		);
		
echo $liquid->render($assigns);
