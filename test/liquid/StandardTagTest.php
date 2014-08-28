<?php
/**
 * Liquid for PHP
 * 
 * @package Liquid
 * @copyright Copyright (c) 2011 Harald Hanek, 
 * fork of php-liquid (c) 2006 Mateo Murphy,
 * based on Liquid for Ruby (c) 2006 Tobias Luetke
 * @license http://www.opensource.org/licenses/mit-license.php
 */





class LiquidTestFileSystem extends LiquidBlankFileSystem 
{
	
	function readTemplateFile($templatePath)
	{
		if ($templatePath == 'inner') {
			return "Inner: {{ inner }}{{ other }}";
			
		}		
	}
	
}


class LiquidStandardTagTest extends LiquidTestcase
{
	
	
	function test_no_transform()
	{
		
		$this->assert_template_result('this text should come out of the template without change...',
			'this text should come out of the template without change...');
			
 	    $this->assert_template_result('blah','blah');
 	    $this->assert_template_result('<blah>','<blah>');
 	    $this->assert_template_result('|,.:','|,.:');
 	    $this->assert_template_result('','');

 	    $text = "this shouldnt see any transformation either but has multiple lines
 	     	              as you can clearly see here ...";
 	    
 	    $this->assert_template_result($text, $text); 	    
	}
	
	function test_has_a_block_which_does_nothing()
	{
	    $this->assert_template_result("the comment block should be removed  .. right?",
 	                           "the comment block should be removed {%comment%} be gone.. {%endcomment%} .. right?");
 	   
 	    $this->assert_template_result('','{%comment%}{%endcomment%}');
 	    $this->assert_template_result('','{%comment%}{% endcomment %}');
	    $this->assert_template_result('','{% comment %}{%endcomment%}');
 	    $this->assert_template_result('','{% comment %}{% endcomment %}');
 	    $this->assert_template_result('','{%comment%}comment{%endcomment%}');
 	    $this->assert_template_result('','{% comment %}comment{% endcomment %}');
 	   
 	    $this->assert_template_result('foobar','foo{%comment%}comment{%endcomment%}bar');
 	    $this->assert_template_result('foobar','foo{% comment %}comment{% endcomment %}bar');
 	    $this->assert_template_result('foobar','foo{%comment%} comment {%endcomment%}bar');
 	    $this->assert_template_result('foobar','foo{% comment %} comment {% endcomment %}bar');
 	   
 	    $this->assert_template_result('foo  bar','foo {%comment%} {%endcomment%} bar');
 	    $this->assert_template_result('foo  bar','foo {%comment%}comment{%endcomment%} bar');
 	    $this->assert_template_result('foo  bar','foo {%comment%} comment {%endcomment%} bar');
 	   
 	    $this->assert_template_result('foobar','foo{%comment%}
 	                                     {%endcomment%}bar');				
	}
	
	function test_for()
	{
		$this->assert_template_result(' yo  yo  yo  yo ','{%for item in array%} yo {%endfor%}',array('array' =>array(1,2,3,4)));
		$this->assert_template_result('yoyo','{%for item in array%}yo{%endfor%}',array('array' =>array(1,2)));
		$this->assert_template_result(' yo ','{%for item in array%} yo {%endfor%}',array('array' =>array(1)));
		$this->assert_template_result('','{%for item in array%}{%endfor%}',array('array' =>array(1,2)));

		$expected = <<<HERE

  yo

  yo

  yo

HERE;
		$template = <<<HERE
{%for item in array%}
  yo
{%endfor%}
HERE;
		$this->assert_template_result($expected, $template, array('array' => array(1,2,3)));
		
	}
	
	function test_for_with_variable()
	{
		$this->assert_template_result(' 1  2  3 ', '{%for item in array%} {{item}} {%endfor%}',array('array' => array(1,2,3)));
		$this->assert_template_result('123', '{%for item in array%}{{item}}{%endfor%}',array('array' => array(1,2,3)));
		$this->assert_template_result('123', '{% for item in array %}{{item}}{% endfor %}',array('array' => array(1,2,3)));
		$this->assert_template_result('abcd', '{%for item in array%}{{item}}{%endfor%}',array('array' => array('a','b','c','d')));
		$this->assert_template_result('a b c', '{%for item in array%}{{item}}{%endfor%}',array('array' => array('a',' ','b',' ','c')));
		$this->assert_template_result('abc', '{%for item in array%}{{item}}{%endfor%}',array('array' => array('a','','b','','c')));
	}
	
	function test_for_helpers()
	{
		$assigns = array('array'=>array(1,2,3));
		
		$this->assert_template_result(' 1/3  2/3  3/3 ', '{%for item in array%} {{forloop.index}}/{{forloop.length}} {%endfor%}',$assigns);
		$this->assert_template_result(' 1  2  3 ', '{%for item in array%} {{forloop.index}} {%endfor%}',$assigns);
		$this->assert_template_result(' 0  1  2 ', '{%for item in array%} {{forloop.index0}} {%endfor%}',$assigns);
		$this->assert_template_result(' 2  1  0 ', '{%for item in array%} {{forloop.rindex0}} {%endfor%}',$assigns);
		$this->assert_template_result(' 3  2  1 ', '{%for item in array%} {{forloop.rindex}} {%endfor%}',$assigns);
		$this->assert_template_result(' 1  0  0 ', '{%for item in array%} {{forloop.first}} {%endfor%}',$assigns);
		$this->assert_template_result(' 0  0  1 ', '{%for item in array%} {{forloop.last}} {%endfor%}',$assigns);		
	}
	
	function test_for_and_if()
	{
		$assigns = array('array' =>array(1,2,3));
		$this->assert_template_result(' yay     ', '{%for item in array%} {% if forloop.first %}yay{% endif %} {%endfor%}', $assigns);
		$this->assert_template_result(' yay  boo  boo ', '{%for item in array%} {% if forloop.first %}yay{% else %}boo{% endif %} {%endfor%}', $assigns);
		$this->assert_template_result('   boo  boo ', '{%for item in array%} {% if forloop.first %}{% else %}boo{% endif %} {%endfor%}', $assigns);		
	}
	
	function test_limiting()
	{
	    $assigns = array('array' => array(1,2,3,4,5,6,7,8,9,0));
		$this->assert_template_result('12','{%for i in array limit:2 %}{{ i }}{%endfor%}',$assigns);
		$this->assert_template_result('1234','{%for i in array limit:4 %}{{ i }}{%endfor%}',$assigns);
		$this->assert_template_result('3456','{%for i in array limit:4 offset:2 %}{{ i }}{%endfor%}',$assigns);
		$this->assert_template_result('3456','{%for i in array limit: 4  offset: 2 %}{{ i }}{%endfor%}',$assigns);
		
		$assigns['limit'] = 2;
		$assigns['offset'] = 2;
		$this->assert_template_result('34','{%for i in array limit: limit offset: offset %}{{ i }}{%endfor%}',$assigns);		
	}
	
	function test_nested_for()
	{
		$assigns = array('array'=>array(array(1, 2), array(3,4), array(5, 6)));	
		$this->assert_template_result('123456','{%for item in array%}{%for i in item%}{{ i }}{%endfor%}{%endfor%}',$assigns);		
	}

	function test_offset_only()
	{
		$assigns = array('array'=>array(1,2,3,4,5,6,7,8,9,0));
		$this->assert_template_result('890','{%for i in array offset:7 %}{{ i }}{%endfor%}',$assigns);
	}
	
	function test_pause_resume()
	{
		$assigns = array('array'=>array('items'=>array(1,2,3,4,5,6,7,8,9,0)));
		
		$markup = <<<MKUP
{%for i in array.items limit: 3 %}{{i}}{%endfor%}
next
{%for i in array.items offset:continue limit: 3 %}{{i}}{%endfor%}
next
{%for i in array.items offset:continue limit: 3 %}{{i}}{%endfor%}
MKUP;
    	$expected = <<<XPCTD
123
next
456
next
789
XPCTD;
    	$this->assert_template_result($expected, $markup, $assigns);
	}
	
	function test_pause_resume_limit()
	{
		$assigns = array('array'=>array('items'=>array(1,2,3,4,5,6,7,8,9,0)));
		
		$markup = <<<MKUP
{%for i in array.items limit: 3 %}{{i}}{%endfor%}
next
{%for i in array.items offset:continue limit: 3 %}{{i}}{%endfor%}
next
{%for i in array.items offset:continue limit: 1 %}{{i}}{%endfor%}
MKUP;
    	$expected = <<<XPCTD
123
next
456
next
7
XPCTD;
    	$this->assert_template_result($expected, $markup, $assigns);
	}
	
	function test_pause_resume_BIG_limit()
	{
		$assigns = array('array'=>array('items'=>array(1,2,3,4,5,6,7,8,9,0)));
		
		$markup = <<<MKUP
{%for i in array.items limit: 3 %}{{i}}{%endfor%}
next
{%for i in array.items offset:continue limit: 3 %}{{i}}{%endfor%}
next
{%for i in array.items offset:continue limit: 1000 %}{{i}}{%endfor%}
MKUP;
    	$expected = <<<XPCTD
123
next
456
next
7890
XPCTD;
    	$this->assert_template_result($expected, $markup, $assigns);
	}
	
	function test_pause_resume_BIG_offset()
	{
		$assigns = array('array'=>array('items'=>array(1,2,3,4,5,6,7,8,9,0)));
		
		$markup = <<<MKUP
{%for i in array.items limit: 3 %}{{i}}{%endfor%}
next
{%for i in array.items offset:continue limit: 3 %}{{i}}{%endfor%}
next
{%for i in array.items offset:continue limit: 1000 offset:1000 %}{{i}}{%endfor%}
MKUP;
    	$expected = <<<XPCTD
123
next
456
next

XPCTD;
    	$this->assert_template_result($expected, $markup, $assigns);
	}	
	
	function test_assign()
	{
		$assigns = array('var' => 'content');
		$this->assert_template_result('var2:  var2:content','var2:{{var2}} {%assign var2 = var%} var2:{{var2}}',$assigns);
	}
	
	function test_capture()
	{
		$assigns = array('var' => 'content');
		$this->assert_template_result('content foo content foo ','{{ var2 }}{% capture var2 %}{{ var }} foo {% endcapture %}{{ var2 }}{{ var2 }}', $assigns);		
	}
	
	function test_case()
	{
		$assigns = array('condition' => 2 );
		$this->assert_template_result(' its 2 ','{% case condition %}{% when 1 %} its 1 {% when 2 %} its 2 {% endcase %}', $assigns);
		
		$assigns = array('condition' => 1 );
		$this->assert_template_result(' its 1 ','{% case condition %}{% when 1 %} its 1 {% when 2 %} its 2 {% endcase %}', $assigns);
		
		$assigns = array('condition' => 3 );
		$this->assert_template_result('','{% case condition %}{% when 1 %} its 1 {% when 2 %} its 2 {% endcase %}', $assigns);
		
		$assigns = array('condition' => "string here" );
		$this->assert_template_result(' hit ','{% case condition %}{% when "string here" %} hit {% endcase %}', $assigns);
		
		$assigns = array('condition' => "bad string here" );
		$this->assert_template_result('','{% case condition %}{% when "string here" %} hit {% endcase %}', $assigns);
	}
	
	function test_case_with_else()
	{
		$assigns = array('condition' => 5 );
		$this->assert_template_result(' hit ','{% case condition %}{% when 5 %} hit {% else %} else {% endcase %}', $assigns);
		
		$assigns = array('condition' => 6 );
		$this->assert_template_result(' else ','{% case condition %}{% when 5 %} hit {% else %} else {% endcase %}', $assigns);
	}
	
	function test_cycle()
	{
		$this->assert_template_result('one','{%cycle "one", "two"%}');
		$this->assert_template_result('one two','{%cycle "one", "two"%} {%cycle "one", "two"%}');
		$this->assert_template_result('one two one','{%cycle "one", "two"%} {%cycle "one", "two"%} {%cycle "one", "two"%}');
		
	}
	
	function test_multiple_cycles()
	{
		$this->assert_template_result('1 2 1 1 2 3 1','{%cycle 1,2%} {%cycle 1,2%} {%cycle 1,2%} {%cycle 1,2,3%} {%cycle 1,2,3%} {%cycle 1,2,3%} {%cycle 1,2,3%}');
	}
	
	function test_multiple_named_cycles()
	{
		$this->assert_template_result('one one two two one one','{%cycle 1: "one", "two" %} {%cycle 2: "one", "two" %} {%cycle 1: "one", "two" %} {%cycle 2: "one", "two" %} {%cycle 1: "one", "two" %} {%cycle 2: "one", "two" %}');
	}

	function test_multiple_named_cycles_with_names_from_context()
	{
		$assigns = array("var1" => 1, "var2" => 2 );
	    $this->assert_template_result('one one two two one one','{%cycle var1: "one", "two" %} {%cycle var2: "one", "two" %} {%cycle var1: "one", "two" %} {%cycle var2: "one", "two" %} {%cycle var1: "one", "two" %} {%cycle var2: "one", "two" %}', $assigns);
	}
	
	function test_size_of_array()
	{
		$assigns = array('array1' => array(1, 2, 3, 4));
		$this->assert_template_result('array has 4 elements', "array has {{ array1.size }} elements", $assigns, null);
	}
	
	// this test is a superflous, but we'll include it for completion's sake
	function test_size_of_hash()
	{
		$assigns = array("hash" => array('a' => 1, 'b' => 2, 'c' => 3, 'd' => 4));
		$this->assert_template_result('hash has 4 elements', "hash has {{ hash.size }} elements", $assigns);
	}
	
	function test_hash_can_override_size()
	{
		$assigns = array("hash" => array('a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'size' => '5000'));
		$this->assert_template_result('hash has 5000 elements', "hash has {{ hash.size }} elements", $assigns);
	}

	function test_include_tag()
	{
		$template = new Template();
		$template->setFileSystem(new LiquidTestFileSystem());
		
		$template->parse("Outer-{% include 'inner' with 'value' other:23 %}-Outer{% include 'inner' for var other:'loop' %}");
		
		$output = $template->render(array("var" => array(1,2,3)));
		
		$this->assertEqual("Outer-Inner: value23-OuterInner: 1loopInner: 2loopInner: 3loop", $output);
	}
	
	function test_include_tag_no_with()
	{
		$template = new Template();
		$template->setFileSystem(new LiquidTestFileSystem());
		
		$template->parse("Outer-{% include 'inner' %}-Outer-{% include 'inner' other:'23' %}");

		$output = $template->render(array("inner"=>"orig", "var" => array(1,2,3)));
		
		$this->assertEqual("Outer-Inner: orig-Outer-Inner: orig23", $output);
	}

}
