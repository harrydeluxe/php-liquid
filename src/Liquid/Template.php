<?php

/*
 * This file is part of the Liquid package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Liquid
 */

namespace Liquid;

use Liquid\Exception\CacheException;
use Liquid\Exception\MissingFilesystemException;

/**
 * The Template class.
 *
 * Example:
 *
 *     $tpl = new \Liquid\Template();
 *     $tpl->parse(template_source);
 *     $tpl->render(array('foo'=>1, 'bar'=>2);
 */
class Template
{
	const CLASS_PREFIX = '\Liquid\Cache\\';

	/**
	 * @var Document The root of the node tree
	 */
	private $root;

	/**
	 * @var FileSystem The file system to use for includes
	 */
	private $fileSystem;

	/**
	 * @var array Globally included filters
	 */
	private $filters = array();

	/**
	 * @var array Custom tags
	 */
	private static $tags = array();

	/**
	 * @var Cache
	 */
	private static $cache;

	/**
	 * Constructor.
	 *
	 * @param string $path
	 * @param array|Cache $cache
	 *
	 * @return Template
	 */
	public function __construct($path = null, $cache = null)
	{
		$this->fileSystem = $path !== null
			? new LocalFileSystem($path)
			: null;

		$this->setCache($cache);
	}

	/**
	 * @param FileSystem $fileSystem
	 */
	public function setFileSystem(FileSystem $fileSystem)
	{
		$this->fileSystem = $fileSystem;
	}

	/**
	 * @param array|Cache $cache
	 *
	 * @throws \Liquid\Exception\CacheException
	 */
	public static function setCache($cache)
	{
		if (is_array($cache)) {
			if (isset($cache['cache']) && class_exists($classname = self::CLASS_PREFIX . ucwords($cache['cache']))) {
				self::$cache = new $classname($cache);
			} else {
				throw new CacheException('Invalid cache options!');
			}
		}

		if ($cache instanceof Cache) {
			self::$cache = $cache;
		}

		if (is_null($cache)) {
			self::$cache = null;
		}
	}

	/**
	 * @return Cache
	 */
	public static function getCache()
	{
		return self::$cache;
	}

	/**
	 * @return Document
	 */
	public function getRoot()
	{
		return $this->root;
	}

	/**
	 * Register custom Tags
	 *
	 * @param string $name
	 * @param string $class
	 */
	public function registerTag($name, $class)
	{
		self::$tags[$name] = $class;
	}

	/**
	 * @return array
	 */
	public static function getTags()
	{
		return self::$tags;
	}

	/**
	 * Register the filter
	 *
	 * @param string $filter
	 */
	public function registerFilter($filter, callable $callback = null)
	{
		// Store callback for later use
		if ($callback) {
			$this->filters[] = [$filter, $callback];
		} else {
			$this->filters[] = $filter;
		}
	}

	/**
	 * Tokenizes the given source string
	 *
	 * @param string $source
	 *
	 * @return array
	 */
	public static function tokenize($source)
	{
		return empty($source)
			? array()
			: preg_split(Liquid::get('TOKENIZATION_REGEXP'), $source, null, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
	}

	/**
	 * Parses the given source string
	 *
	 * @param string $source
	 *
	 * @return Template
	 */
	public function parse($source)
	{
		if (!self::$cache) {
			return $this->parseAlways($source);
		}

		$hash = md5($source);
		$this->root = self::$cache->read($hash);

		// if no cached version exists, or if it checks for includes
		if ($this->root == false || $this->root->hasIncludes() == true) {
			$this->parseAlways($source);
			self::$cache->write($hash, $this->root);
		}

		return $this;
	}

	/**
	 * Parses the given source string regardless of caching
	 *
	 * @param string $source
	 *
	 * @return Template
	 */
	private function parseAlways($source)
	{
		$tokens = Template::tokenize($source);
		$this->root = new Document($tokens, $this->fileSystem);

		return $this;
	}

	/**
	 * Parses the given template file
	 *
	 * @param string $templatePath
	 * @throws \Liquid\Exception\MissingFilesystemException
	 * @return Template
	 */
	public function parseFile($templatePath)
	{
		if (!$this->fileSystem) {
			throw new MissingFilesystemException("Could not load a template without an initialized file system");
		}

		return $this->parse($this->fileSystem->readTemplateFile($templatePath));
	}

	/**
	 * Renders the current template
	 *
	 * @param array $assigns an array of values for the template
	 * @param array $filters additional filters for the template
	 * @param array $registers additional registers for the template
	 *
	 * @return string
	 */
	public function render(array $assigns = array(), $filters = null, array $registers = array())
	{
		$context = new Context($assigns, $registers);

		if (!is_null($filters)) {
			if (is_array($filters)) {
				$this->filters = array_merge($this->filters, $filters);
			} else {
				$this->filters[] = $filters;
			}
		}

		foreach ($this->filters as $filter) {
			if (is_array($filter)) {
				// Unpack a callback saved as second argument
				$context->addFilters(...$filter);
			} else {
				$context->addFilters($filter);
			}
		}

		return $this->root->render($context);
	}
}
