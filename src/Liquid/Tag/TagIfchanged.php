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

use Liquid\AbstractBlock;
use Liquid\Liquid;
use Liquid\Context;
use Liquid\LiquidException;
use Liquid\FileSystem;
use Liquid\Regexp;

/**
 * Quickly create a table from a collection
 */
class TagIfchanged extends AbstractBlock
{
	/**
	 * The last value
	 *
	 * @var string
	 */
	private $lastValue = '';

	/**
	 * Constructor
	 *
	 * @param string $markup
	 * @param Array $tokens
	 * @param FileSystem $fileSystem
	 *
	 * @throws \Liquid\LiquidException
	 */
	public function __construct($markup, array &$tokens, FileSystem $fileSystem = null) {
		$syntaxRegexp = new Regexp('/(\w+)/');

		if ($syntaxRegexp->match($markup)) {
			$this->to = $syntaxRegexp->matches[1];
			parent::__construct($markup, $tokens, $fileSystem);
		} else {
			throw new LiquidException("Syntax Error in 'ifchanged' - Valid syntax: ifchanged [value]");
		}
	}

	/**
	 * Renders the block
	 *
	 * @param Context $context
	 *
	 * @return string
	 */
	public function render(Context $context) {
		$output = parent::render($context);

		if ($this->lastValue == $output){
			return '';
		} else {
			$this->lastValue = $output;
			return $this->lastValue;		
		}
		
	}
}
