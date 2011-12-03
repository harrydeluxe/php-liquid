<?php

define('SIMPLETEST_PATH', dirname(__FILE__).'/simpletest/');

define('LIQUID_INCLUDE_SUFFIX', 'tpl');
define('LIQUID_INCLUDE_PREFIX', '');

require_once(dirname(__FILE__).'/../Liquid.class.php'); // include library


require_once(SIMPLETEST_PATH.'autorun.php'); // include test classes


include __DIR__ . '/LiquidTestcase.php';


$test = new TestSuite('All liquid tests');

$path = dirname(__FILE__).'/liquid/';

// include all classes
$dir = dir($path);

while(($file = $dir->read()) !== false )
{
	if(substr($file, 0, 1) == '.') {
		continue;
	}
	
	if (is_file($path.$file) && substr($file, -8) == 'Test.php')
	{
		$test->addFile($path.$file);
	}
}

class ShowPasses extends HtmlReporter {
    
    function paintPass($message) {
        parent::paintPass($message);
        print "<span class=\"pass\">Pass</span>: ";
        $breadcrumb = $this->getTestList();
        array_shift($breadcrumb);
       // print implode("->", $breadcrumb);
        print "->$message<br />\n";
    }
    
    protected function getCss() {
        return parent::getCss() . ' .pass { color: green; }';
    }
}

$test->run(new ShowPasses());
$test->run(new HtmlReporter());