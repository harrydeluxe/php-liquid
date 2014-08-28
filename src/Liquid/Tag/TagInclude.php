<?php

namespace Liquid\Tag;

use Liquid\Context;
use Liquid\Liquid;
use Liquid\LiquidException;
use Liquid\BlankFileSystem;
use Liquid\Regexp;
use Liquid\Template;

/**
 * Includes another, partial, template
 *
 * @example
 * {% include 'foo' %}
 *
 * Will include the template called 'foo'
 *
 * {% include 'foo' with 'bar' %}
 *
 * Will include the template called 'foo', with a variable called foo that will have the value of 'bar'
 *
 * {% include 'foo' for 'bar' %}
 *
 * Will loop over all the values of bar, including the template foo, passing a variable called foo
 * with each value of bar
 *
 * @package Liquid
 * @copyright Copyright (c) 2011-2012 Harald Hanek,
 * fork of php-liquid (c) 2006 Mateo Murphy,
 * based on Liquid for Ruby (c) 2006 Tobias Luetke
 * @license http://harrydeluxe.mit-license.org
 */
class TagInclude extends AbstractTag
{
	/**
	 * @var string The name of the template
	 */
	private $_templateName;

	/**
	 * @var bool True if the variable is a collection
	 */
	private $_collection;

	/**
	 * @var mixed The value to pass to the child template as the template name
	 */
	private $_variable;

	/**
	 * @var Document The Document that represents the included template
	 */
	private $_document;

	/**
	 * @var string The Source Hash
	 */
	protected $_hash;

	/**
	 * Constructor
	 *
	 * @param string $markup
	 * @param array $tokens
	 * @param BlankFileSystem $fileSystem
	 */
	public function __construct($markup, &$tokens, &$fileSystem) {
		$regex = new Regexp('/("[^"]+"|\'[^\']+\')(\s+(with|for)\s+(' . Liquid::LIQUID_QUOTED_FRAGMENT . '+))?/');

		if ($regex->match($markup)) {

			$this->_templateName = substr($regex->matches[1], 1, strlen($regex->matches[1]) - 2);

			if (isset($regex->matches[1])) {
				$this->_collection = (isset($regex->matches[3])) ? ($regex->matches[3] == "for") : null;
				$this->_variable = (isset($regex->matches[4])) ? $regex->matches[4] : null;
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
	 */
	public function parse(&$tokens) {
		if (!isset($this->_fileSystem)) {
			throw new LiquidException("No file system");
		}

		// read the source of the template and create a new sub document
		$source = $this->_fileSystem->readTemplateFile($this->_templateName);

		$this->_hash = md5($source);

		$cache = Template::getCache();

		if (isset($cache)) {
			if (($this->_document = $cache->read($this->_hash)) != false && $this->_document->checkIncludes() != true) {
			} else {
				$this->_document = new Document(Template::tokenize($source), $this->_fileSystem);
				$cache->write($this->_hash, $this->_document);
			}
		} else {
			$this->_document = new Document(Template::tokenize($source), $this->_fileSystem);
		}
	}

	/**
	 * check for cached includes
	 *
	 * @return string
	 */
	public function checkIncludes() {
		$cache = Template::getCache();

		if ($this->_document->checkIncludes() == true)
			return true;

		$source = $this->_fileSystem->readTemplateFile($this->_templateName);

		if ($cache->exists(md5($source)) && $this->_hash == md5($source))
			return false;

		return true;
	}

	/**
	 * Renders the node
	 *
	 * @param Context $context
	 */
	public function render(&$context) {
		$result = '';
		$variable = $context->get($this->_variable);

		$context->push();

		foreach ($this->_attributes as $key => $value) {
			$context->set($key, $context->get($value));
		}

		if ($this->_collection) {
			foreach ($variable as $item) {
				$context->set($this->_templateName, $item);
				$result .= $this->_document->render($context);
			}
		} else {
			if (!is_null($this->_variable)) {
				$context->set($this->_templateName, $variable);
			}

			$result .= $this->_document->render($context);
		}

		$context->pop();

		return $result;
	}
}
