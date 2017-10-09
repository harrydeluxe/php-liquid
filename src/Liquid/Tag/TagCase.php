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
 * A switch statement
 *
 * Example:
 *
 *     {% case condition %}{% when foo %} foo {% else %} bar {% endcase %}
 */
class TagCase extends Decision
{
	/**
	 * Stack of nodelists
	 *
	 * @var array
	 */
	public $nodelists;

	/**
	 * The nodelist for the else (default) nodelist
	 *
	 * @var array
	 */
	public $elseNodelist;

	/**
	 * The left value to compare
	 *
	 * @var string
	 */
	public $left;

	/**
	 * The current right value to compare
	 *
	 * @var mixed
	 */
	public $right;

	/**
	 * Constructor
	 *
	 * @param string $markup
	 * @param array $tokens
	 * @param FileSystem $fileSystem
	 *
	 * @throws \Liquid\Exception\ParseException
	 */
	public function __construct($markup, array &$tokens, FileSystem $fileSystem = null)
	{
		$this->nodelists = array();
		$this->elseNodelist = array();

		parent::__construct($markup, $tokens, $fileSystem);

		$syntaxRegexp = new Regexp('/' . Liquid::get('QUOTED_FRAGMENT') . '/');

		if ($syntaxRegexp->match($markup)) {
			$this->left = $syntaxRegexp->matches[0];
		} else {
			throw new ParseException("Syntax Error in tag 'case' - Valid syntax: case [condition]"); // harry
		}
	}

	/**
	 * Pushes the last nodelist onto the stack
	 */
	public function endTag()
	{
		$this->pushNodelist();
	}

	/**
	 * Unknown tag handler
	 *
	 * @param string $tag
	 * @param string $params
	 * @param array $tokens
	 *
	 * @throws \Liquid\Exception\ParseException
	 */
	public function unknownTag($tag, $params, array $tokens)
	{
		$whenSyntaxRegexp = new Regexp('/' . Liquid::get('QUOTED_FRAGMENT') . '/');

		switch ($tag) {
			case 'when':
				// push the current nodelist onto the stack and prepare for a new one
				if ($whenSyntaxRegexp->match($params)) {
					$this->pushNodelist();
					$this->right = $whenSyntaxRegexp->matches[0];
					$this->nodelist = array();
				} else {
					throw new ParseException("Syntax Error in tag 'case' - Valid when condition: when [condition]"); // harry
				}
				break;

			case 'else':
				// push the last nodelist onto the stack and prepare to receive the else nodes
				$this->pushNodelist();
				$this->right = null;
				$this->elseNodelist = &$this->nodelist;
				$this->nodelist = array();
				break;

			default:
				parent::unknownTag($tag, $params, $tokens);
		}
	}

	/**
	 * Pushes the current right value and nodelist into the nodelist stack
	 */
	public function pushNodelist()
	{
		if (!is_null($this->right)) {
			$this->nodelists[] = array($this->right, $this->nodelist);
		}
	}

	/**
	 * Renders the node
	 *
	 * @param Context $context
	 *
	 * @return string
	 */
	public function render(Context $context)
	{
		$output = ''; // array();
		$runElseBlock = true;

		foreach ($this->nodelists as $data) {
			list($right, $nodelist) = $data;

			if ($this->equalVariables($this->left, $right, $context)) {
				$runElseBlock = false;

				$context->push();
				$output .= $this->renderAll($nodelist, $context);
				$context->pop();
			}
		}

		if ($runElseBlock) {
			$context->push();
			$output .= $this->renderAll($this->elseNodelist, $context);
			$context->pop();
		}

		return $output;
	}
}
