<?php

/**
 * This file is part of the Liquid package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Liquid
 */

namespace Liquid\Tag;

use Liquid\TestCase;

class TagCaseTest extends TestCase
{
	public function testCase() {
		$assigns = array('condition' => 2);
		$this->assertTemplateResult(' its 2 ', '{% case condition %}{% when 1 %} its 1 {% when 2 %} its 2 {% endcase %}', $assigns);

		$assigns = array('condition' => 1);
		$this->assertTemplateResult(' its 1 ', '{% case condition %}{% when 1 %} its 1 {% when 2 %} its 2 {% endcase %}', $assigns);

		$assigns = array('condition' => 3);
		$this->assertTemplateResult('', '{% case condition %}{% when 1 %} its 1 {% when 2 %} its 2 {% endcase %}', $assigns);

		$assigns = array('condition' => "string here");
		$this->assertTemplateResult(' hit ', '{% case condition %}{% when "string here" %} hit {% endcase %}', $assigns);

		$assigns = array('condition' => "bad string here");
		$this->assertTemplateResult('', '{% case condition %}{% when "string here" %} hit {% endcase %}', $assigns);
	}

	public function testCaseWithElse() {
		$assigns = array('condition' => 5);
		$this->assertTemplateResult(' hit ', '{% case condition %}{% when 5 %} hit {% else %} else {% endcase %}', $assigns);

		$assigns = array('condition' => 6);
		$this->assertTemplateResult(' else ', '{% case condition %}{% when 5 %} hit {% else %} else {% endcase %}', $assigns);
	}
}
