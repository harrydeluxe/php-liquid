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

use Liquid\Context;

/**
 * An if statement
 *
 * Example:
 *
 *     {% unless true %} YES {% else %} NO {% endunless %}
 *
 *     will return:
 *     NO
 */

class TagUnless extends TagIf{

	/**
	 * Replace first finded key in $subject to value
	 *
	 * @param array $replacer (key => value array)
	 * @param string $subject
	 * @return string
	 */
	protected function strReplaceOne($replacer, $subject) {
		$res = $subject;
		foreach($replacer as $from => $to) {
			$res = str_ireplace($from, $to, $subject, $count);
			if ($count > 0) {
				break;
			}
		}
		return $res;
	}

	/**
	 * Method revert operators in string
	 * before
	 *  a == 1 and b == 2
	 * after
	 *  a != 1 or b != 2
	 */
	protected function revertOperators() {
		
		// replace
		$replacerOperators = array(
			'==' => '!=',
			'<=' => '>',
			'>=' => '<',
			'>'  => '<=',
			'<'  => '>=',
			'!=' => '=='
		);
		
		$replacerLogicalOperators = array(
			'or' => 'and',
			'and' => 'or'
		);
		
		if (count($this->blocks) > 0) {
			if (count($this->blocks[0]) > 1)  {
				$condition = $this->blocks[0][1];

				$condition = $this->strReplaceOne($replacerOperators, $condition);
				$condition = $this->strReplaceOne($replacerLogicalOperators, $condition);

				// if no operators was changed, then it means there is no operators
				// soo make condition ==false
				if ($this->blocks[0][1] === $condition) {
					$condition .= '== false';
				}
				$this->blocks[0][1] = $condition;
			}
		}
	}

	/**
	 * Render the tag
	 *
	 * @param Context $context
	 *
	 * @throws \Liquid\LiquidException
	 * @return string
	 */
	public function render(Context $context) {
		$this->revertOperators();
		$res = parent::render($context);
		return $res;
	}

}