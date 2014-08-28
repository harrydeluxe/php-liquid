<?php

namespace Liquid\Tag;

use Liquid\Context;
use Liquid\Liquid;
use Liquid\LiquidException;
use Liquid\BlankFileSystem;
use Liquid\Regexp;

/**
 * A switch statememt
 *
 * @example
 * {% case condition %}{% when foo %} foo {% else %} bar {% endcase %}
 *
 * @package Liquid
 * @copyright Copyright (c) 2011-2012 Harald Hanek,
 * fork of php-liquid (c) 2006 Mateo Murphy,
 * based on Liquid for Ruby (c) 2006 Tobias Luetke
 * @license http://harrydeluxe.mit-license.org
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
	 * @param BlankFileSystem $fileSystem
	 */
	public function __construct($markup, &$tokens, &$fileSystem) {
		$this->nodelists = array();
		$this->elseNodelist = array();

		parent::__construct($markup, $tokens, $fileSystem);

		$syntaxRegexp = new Regexp('/' . Liquid::LIQUID_QUOTED_FRAGMENT . '/');

		if ($syntaxRegexp->match($markup)) {
			$this->left = $syntaxRegexp->matches[0];
		} else {
			throw new LiquidException("Syntax Error in tag 'case' - Valid syntax: case [condition]"); // harry
		}
	}

	/**
	 * Pushes the last nodelist onto the stack
	 *
	 */
	public function endTag() {
		$this->push_nodelist();
	}

	/**
	 * Unknown tag handler
	 *
	 * @param string $tag
	 * @param array $params
	 * @param array $tokens
	 */
	public function unknownTag($tag, $params, &$tokens) {
		$whenSyntaxRegexp = new Regexp('/' . Liquid::LIQUID_QUOTED_FRAGMENT . '/');

		switch ($tag) {
			case 'when':
				// push the current nodelist onto the stack and prepare for a new one
				if ($whenSyntaxRegexp->match($params)) {
					$this->push_nodelist();
					$this->right = $whenSyntaxRegexp->matches[0];
					$this->_nodelist = array();

				} else {
					throw new LiquidException("Syntax Error in tag 'case' - Valid when condition: when [condition]"); // harry
				}
				break;

			case 'else':
				// push the last nodelist onto the stack and prepare to recieve the else nodes
				$this->push_nodelist();
				$this->right = null;
				$this->elseNodelist = & $this->_nodelist;
				$this->_nodelist = array();
				break;

			default:
				parent::unknownTag($tag, $params, $tokens);
		}
	}

	/**
	 * Pushes the current right value and nodelist into the nodelist stack
	 *
	 */
	public function push_nodelist() {
		if (!is_null($this->right)) {
			$this->nodelists[] = array(
				$this->right, $this->_nodelist
			);
		}
	}

	/**
	 * Renders the node
	 *
	 * @param Context $context
	 */
	public function render(&$context) {
		$output = ''; // array();
		$run_else_block = true;

		foreach ($this->nodelists as $data) {
			list($right, $nodelist) = $data;

			if ($this->_equalVariables($this->left, $right, $context)) {
				$run_else_block = false;

				$context->push();
				$output .= $this->renderAll($nodelist, $context);
				$context->pop();
			}
		}

		if ($run_else_block) {
			$context->push();
			$output .= $this->renderAll($this->elseNodelist, $context);
			$context->pop();
		}

		return $output;
	}
}
