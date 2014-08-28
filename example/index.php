<?php
define('LIQUID_INCLUDE_SUFFIX', 'tpl');
define('LIQUID_INCLUDE_PREFIX', '');

require_once('../Liquid.class.php');

define('PROTECTED_PATH', dirname(__FILE__).'/protected/');


$liquid = new Template(PROTECTED_PATH.'templates/');

//$cache = array('cache' => 'file', 'cache_dir' => PROTECTED_PATH.'cache/');
//$cache = array('cache' => 'apc');

$liquid->setCache($cache);

$liquid->parse(file_get_contents(PROTECTED_PATH.'templates/index.tpl'));

$assigns = array(
		'document' => array(
			'title' => 'This is php-liquid',
			'content' => 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.',
			'copyright' => 'Harald Hanek - All rights reserved.'
			),
		'blog' => array(
					array(
						'title' => 'Blog Title 1',
						'content' => 'Nunc putamus parum claram',
                        'tags' => array('claram', 'parum'),
						'comments' => array(
										array(
											'title' => 'First Comment',
											'message' => 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr'
											)
									)
						),
					array(
						'title' => 'Blog Title 2',
						'content' => 'Nunc putamus parum claram',
                        'tags' => array('claram', 'parum', 'freestyle'),
						'comments' => array(
										array(
											'title' => 'First Comment',
											'message' => 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr'
											),
										array(
											'title' => 'Second Comment',
											'message' => 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr'
											)
									)
						)

					),
		'array' => array('one', 'two', 'three', 'four')
		);

print $liquid->render($assigns);

?>
