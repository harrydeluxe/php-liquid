<?php
define('LIQUID_INCLUDE_SUFFIX', 'tpl');
define('LIQUID_INCLUDE_PREFIX', '');

require_once('../Liquid.class.php');

$liquid = new Template();
$liquid->parse('{{ hello }} {{ goback }}');
print $liquid->render(array('hello' => 'hello world', 'goback' => '<a href=".">index</a>'));
?>
