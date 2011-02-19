<?php

require_once('../Liquid.class.php');

$liquid = new LiquidTemplate();
$liquid->parse('{{ hello }} {{ goback }}');
print $liquid->render(array('hello' => 'hello world', 'goback' => '<a href=".">index</a>'));
?>