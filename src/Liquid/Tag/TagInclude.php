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

use Liquid\AbstractTag;
use Liquid\Document;
use Liquid\Context;
use Liquid\Liquid;
use Liquid\LiquidException;
use Liquid\FileSystem;
use Liquid\Regexp;
use Liquid\Template;

/**
 * Includes another, partial, template
 *
 * Example:
 *
 *     {% include 'foo' %}
 *
 *     Will include the template called 'foo'
 *
 *     {% include 'foo' with 'bar' %}
 *
 *     Will include the template called 'foo', with a variable called foo that will have the value of 'bar'
 *
 *     {% include 'foo' for 'bar' %}
 *
 *     Will loop over all the values of bar, including the template foo, passing a variable called foo
 *     with each value of bar
 */
class TagInclude extends AbstractTag
{
	/**
	 * @var string The name of the template
	 */
	private $templateName;

	/**
	 * @var bool True if the variable is a collection
	 */
	private $collection;

	/**
	 * @var mixed The value to pass to the child template as the template name
	 */
	private $variable;

	/**
	 * @var Document The Document that represents the included template
	 */
	private $document;

	/**
	 * @var string The Source Hash
	 */
	protected $hash;

	/**
	 * Constructor
	 *
	 * @param string $markup
	 * @param array $tokens
	 * @param FileSystem $fileSystem
	 *
	 * @throws \Liquid\LiquidException
	 */
	public function __construct($markup, array &$tokens, FileSystem $fileSystem = null) {
		$regex = new Regexp('/("[^"]+"|\'[^\']+\')(\s+(with|for)\s+(' . Liquid::get('QUOTED_FRAGMENT') . '+))?/');

		if ($regex->match($markup)) {
			$this->templateName = substr($regex->matches[1], 1, strlen($regex->matches[1]) - 2);

			if (isset($regex->matches[1])) {
				$this->collection = (isset($regex->matches[3])) ? ($regex->matches[3] == "for") : null;
				$this->variable = (isset($regex->matches[4])) ? $regex->matches[4] : null;
			}

			$this->extractAttributes($markup);
		} else {
			throw new LiquidException("Error in tag 'include' - Valid syntax: include '[template]' (with|for) [object|collection]");
		}

		parent::__construct($markup, $tokens, $fileSystem);
	}

	/**
	 * Parses the tokens
	 *
	 * @param array $tokens
	 *
	 * @throws \Liquid\LiquidException
	 */
	public function parse(array &$tokens) {
		if ($this->fileSystem === null) {
			throw new LiquidException("No file system");
		}

		// read the source of the template and create a new sub document
		$source = $this->fileSystem->readTemplateFile($this->templateName);

		$this->hash = md5($source);

		$cache = Template::getCache();

		if (isset($cache)) {
			if (($this->document = $cache->read($this->hash)) != false && $this->document->checkIncludes() != true) {
			} else {
				$templateTokens = Template::tokenize($source);
				$this->document = new Document($templateTokens, $this->fileSystem);
				$cache->write($this->hash, $this->document);
			}
		} else {
			$templateTokens = Template::tokenize($source);
			$this->document = new Document($templateTokens, $this->fileSystem);
		}
	}

	/**
	 * check for cached includes
	 *
	 * @return boolean
	 */
	public function checkIncludes() {
		$cache = Template::getCache();

		if ($this->document->checkIncludes() == true) {
			return true;
		}

		$source = $this->fileSystem->readTemplateFile($this->templateName);

		if ($cache->exists(md5($source)) && $this->hash == md5($source)) {
			return false;
		}

		return true;
	}

	/**
	 * Renders the node
	 *
	 * @param Context $context
	 *
	 * @return string
	 */
	public function render(Context $context) {
		$result = '';
		$variable = $context->get($this->variable);

		$context->push();

		foreach ($this->attributes as $key => $value) {
			$context->set($key, $context->get($value));
		}

		if ($this->collection) {
			foreach ($variable as $item) {
				$context->set($this->templateName, $item);
				$result .= $this->document->render($context);
			}
		} else {
			if (!is_null($this->variable)) {
				$context->set($this->templateName, $variable);
			}

			$result .= $this->document->render($context);
		}

		$context->pop();

		return $result;
	}
}
