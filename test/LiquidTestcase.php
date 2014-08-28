<?php


class LiquidTestcase extends UnitTestCase
{

	public $filters;
	
	public function assert_template_result($expected, $template, $assigns = null, $message = "%s", $debug = false) {
	
		if (is_null($assigns)) {
			$assigns = array();
		}
		
		$result = new Template;

		$result->parse($template);

		if ($debug) {
			debug($result);
		}
		
		$this->assertEqual($expected, $result->render($assigns, $this->filters), $message);
	}

	
	public function assertTrueHelper($templateString, $expected, $data = array())
	{
		$template = new Template;
		$template->parse($templateString);
		$this->assertTrue($template->render($data) === $expected);
	}
}
