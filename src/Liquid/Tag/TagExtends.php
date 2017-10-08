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

use Liquid\AbstractTag;
use Liquid\Document;
use Liquid\Exception\MissingFilesystemException;
use Liquid\Exception\ParseException;
use Liquid\Liquid;
use Liquid\Context;
use Liquid\FileSystem;
use Liquid\Regexp;
use Liquid\Template;

/**
 * Extends a template by another one.
 *
 * Example:
 *
 *     {% extends "base" %}
 */
class TagExtends extends AbstractTag
{
	/**
	 * @var string The name of the template
	 */
	private $templateName;

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
	 * @throws \Liquid\Exception\ParseException
	 */
	public function __construct($markup, array &$tokens, FileSystem $fileSystem = null)
	{
		$regex = new Regexp('/("[^"]+"|\'[^\']+\')?/');

		if ($regex->match($markup) && isset($regex->matches[1])) {
			$this->templateName = substr($regex->matches[1], 1, strlen($regex->matches[1]) - 2);
		} else {
			throw new ParseException("Error in tag 'extends' - Valid syntax: extends '[template name]'");
		}

		parent::__construct($markup, $tokens, $fileSystem);
	}

	/**
	 * @param array $tokens
	 *
	 * @return array
	 */
	private function findBlocks(array $tokens)
	{
		$blockstartRegexp = new Regexp('/^' . Liquid::get('TAG_START') . '\s*block (\w+)\s*(.*)?' . Liquid::get('TAG_END') . '$/');
		$blockendRegexp = new Regexp('/^' . Liquid::get('TAG_START') . '\s*endblock\s*?' . Liquid::get('TAG_END') . '$/');

		$b = array();
		$name = null;

		foreach ($tokens as $token) {
			if ($blockstartRegexp->match($token)) {
				$name = $blockstartRegexp->matches[1];
				$b[$name] = array();
			} elseif ($blockendRegexp->match($token)) {
				$name = null;
			} else {
				if ($name !== null) {
					array_push($b[$name], $token);
				}
			}
		}

		return $b;
	}

	/**
	 * Parses the tokens
	 *
	 * @param array $tokens
	 *
	 * @throws \Liquid\Exception\MissingFilesystemException
	 */
	public function parse(array &$tokens)
	{
		if ($this->fileSystem === null) {
			throw new MissingFilesystemException("No file system");
		}

		// read the source of the template and create a new sub document
		$source = $this->fileSystem->readTemplateFile($this->templateName);

		// tokens in this new document
		$maintokens = Template::tokenize($source);

		$eRegexp = new Regexp('/^' . Liquid::get('TAG_START') . '\s*extends (.*)?' . Liquid::get('TAG_END') . '$/');
		foreach ($maintokens as $maintoken) {
			if ($eRegexp->match($maintoken)) {
				$m = $eRegexp->matches[1];
				break;
			}
		}

		if (isset($m)) {
			$rest = array_merge($maintokens, $tokens);
		} else {
			$childtokens = $this->findBlocks($tokens);

			$blockstartRegexp = new Regexp('/^' . Liquid::get('TAG_START') . '\s*block (\w+)\s*(.*)?' . Liquid::get('TAG_END') . '$/');
			$blockendRegexp = new Regexp('/^' . Liquid::get('TAG_START') . '\s*endblock\s*?' . Liquid::get('TAG_END') . '$/');

			$name = null;

			$rest = array();
			$keep = false;

			for ($i = 0; $i < count($maintokens); $i++) {
				if ($blockstartRegexp->match($maintokens[$i])) {
					$name = $blockstartRegexp->matches[1];

					if (isset($childtokens[$name])) {
						$keep = true;
						array_push($rest, $maintokens[$i]);
						foreach ($childtokens[$name] as $item) {
							array_push($rest, $item);
						}
					}
				}
				if (!$keep) {
					array_push($rest, $maintokens[$i]);
				}

				if ($blockendRegexp->match($maintokens[$i]) && $keep === true) {
					$keep = false;
					array_push($rest, $maintokens[$i]);
				}
			}
		}

		$cache = Template::getCache();

		if (!$cache) {
			$this->document = new Document($rest, $this->fileSystem);
			return;
		}

		$this->hash = md5($source);

		$this->document = $cache->read($this->hash);

		if ($this->document == false || $this->document->hasIncludes() == true) {
			$this->document = new Document($rest, $this->fileSystem);
			$cache->write($this->hash, $this->document);
		}
	}

	/**
	 * Check for cached includes; if there are - do not use cache
	 *
	 * @see Document::hasIncludes()
	 * @return boolean
	 */
	public function hasIncludes()
	{
		if ($this->document->hasIncludes() == true) {
			return true;
		}

		$source = $this->fileSystem->readTemplateFile($this->templateName);

		if (Template::getCache()->exists(md5($source)) && $this->hash === md5($source)) {
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
	public function render(Context $context)
	{
		$context->push();
		$result = $this->document->render($context);
		$context->pop();
		return $result;
	}
}
