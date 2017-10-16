<?php

/*
 * This file is part of the Liquid package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Liquid
 */

namespace Liquid\Tag;

use Liquid\Decision;
use Liquid\Context;
use Liquid\Exception\ParseException;
use Liquid\Liquid;
use Liquid\FileSystem;
use Liquid\Regexp;

/**
 * An if statement
 *
 * Example:
 *
 *     {% if true %} YES {% else %} NO {% endif %}
 *
 *     will return:
 *     YES
 */
class TagIf extends Decision
{
	/**
	 * Array holding the nodes to render for each logical block
	 *
	 * @var array
	 */
	private $nodelistHolders = array();

	/**
	 * Array holding the block type, block markup (conditions) and block nodelist
	 *
	 * @var array
	 */
	protected $blocks = array();

	/**
	 * Constructor
	 *
	 * @param string $markup
	 * @param array $tokens
	 * @param FileSystem $fileSystem
	 */
	public function __construct($markup, array &$tokens, FileSystem $fileSystem = null)
	{
		$this->nodelist = & $this->nodelistHolders[count($this->blocks)];

		array_push($this->blocks, array('if', $markup, &$this->nodelist));

		parent::__construct($markup, $tokens, $fileSystem);
	}

	/**
	 * Handler for unknown tags, handle else tags
	 *
	 * @param string $tag
	 * @param array $params
	 * @param array $tokens
	 */
	public function unknownTag($tag, $params, array $tokens)
	{
		if ($tag == 'else' || $tag == 'elsif') {
			// Update reference to nodelistHolder for this block
			$this->nodelist = & $this->nodelistHolders[count($this->blocks) + 1];
			$this->nodelistHolders[count($this->blocks) + 1] = array();

			array_push($this->blocks, array($tag, $params, &$this->nodelist));
		} else {
			parent::unknownTag($tag, $params, $tokens);
		}
	}

	/**
	 * Render the tag
	 *
	 * @param Context $context
	 *
	 * @throws \Liquid\Exception\ParseException
	 * @return string
	 */
	public function render(Context $context)
	{
		$context->push();

		$logicalRegex = new Regexp('/\s+(and|or)\s+/');
		$conditionalRegex = new Regexp('/(' . Liquid::get('QUOTED_FRAGMENT') . ')\s*([=!<>a-z_]+)?\s*(' . Liquid::get('QUOTED_FRAGMENT') . ')?/');

		$result = '';
		foreach ($this->blocks as $block) {
			if ($block[0] == 'else') {
				$result = $this->renderAll($block[2], $context);

				break;
			}

			if ($block[0] == 'if' || $block[0] == 'elsif') {
				// Extract logical operators
				$logicalRegex->matchAll($block[1]);

				$logicalOperators = $logicalRegex->matches;
				$logicalOperators = $logicalOperators[1];
				// Extract individual conditions
				$temp = $logicalRegex->split($block[1]);

				$conditions = array();

				foreach ($temp as $condition) {
					if ($conditionalRegex->match($condition)) {
						$left = (isset($conditionalRegex->matches[1])) ? $conditionalRegex->matches[1] : null;
						$operator = (isset($conditionalRegex->matches[2])) ? $conditionalRegex->matches[2] : null;
						$right = (isset($conditionalRegex->matches[3])) ? $conditionalRegex->matches[3] : null;

						array_push($conditions, array(
							'left' => $left,
							'operator' => $operator,
							'right' => $right
						));
					} else {
						throw new ParseException("Syntax Error in tag 'if' - Valid syntax: if [condition]");
					}
				}
				if (count($logicalOperators)) {
					// If statement contains and/or
					$display = $this->interpretCondition($conditions[0]['left'], $conditions[0]['right'], $conditions[0]['operator'], $context);
					foreach ($logicalOperators as $k => $logicalOperator) {
						if ($logicalOperator == 'and') {
							$display = ($display && $this->interpretCondition($conditions[$k + 1]['left'], $conditions[$k + 1]['right'], $conditions[$k + 1]['operator'], $context));
						} else {
							$display = ($display || $this->interpretCondition($conditions[$k + 1]['left'], $conditions[$k + 1]['right'], $conditions[$k + 1]['operator'], $context));
						}
					}
				} else {
					// If statement is a single condition
					$display = $this->interpretCondition($conditions[0]['left'], $conditions[0]['right'], $conditions[0]['operator'], $context);
				}

				// hook for unless tag
				$display = $this->negateIfUnless($display);

				if ($display) {
					$result = $this->renderAll($block[2], $context);

					break;
				}
			}
		}

		$context->pop();

		return $result;
	}

	protected function negateIfUnless($display)
	{
		// no need to negate a condition in a regular `if` tag (will do that in `unless` tag)
		return $display;
	}
}
