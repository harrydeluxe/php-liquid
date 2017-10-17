<?php

/*
 * This file is part of the Liquid package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Liquid
 */

namespace Liquid\FileSystem;

use Liquid\Exception\NotFoundException;
use Liquid\Exception\ParseException;
use Liquid\FileSystem;
use Liquid\Regexp;
use Liquid\Liquid;

/**
 * This implements an abstract file system which retrieves template files named in a manner similar to Rails partials,
 * ie. with the template name prefixed with an underscore. The extension ".liquid" is also added.
 *
 * For security reasons, template paths are only allowed to contain letters, numbers, and underscore.
 */
class Local implements FileSystem
{
	/**
	 * The root path
	 *
	 * @var string
	 */
	private $root;

	/**
	 * Constructor
	 *
	 * @param string $root The root path for templates
	 * @throws \Liquid\Exception\NotFoundException
	 */
	public function __construct($root)
	{
		// since root path can only be set from constructor, we check it once right here
		if (!empty($root)) {
			$realRoot = realpath($root);
			if ($realRoot === false) {
				throw new NotFoundException("Root path could not be found: '$root'");
			}
			$root = $realRoot;
		}

		$this->root = $root;
	}

	/**
	 * Retrieve a template file
	 *
	 * @param string $templatePath
	 *
	 * @return string template content
	 */
	public function readTemplateFile($templatePath)
	{
		return file_get_contents($this->fullPath($templatePath));
	}

	/**
	 * Resolves a given path to a full template file path, making sure it's valid
	 *
	 * @param string $templatePath
	 *
	 * @throws \Liquid\Exception\ParseException
	 * @throws \Liquid\Exception\NotFoundException
	 * @return string
	 */
	public function fullPath($templatePath)
	{
		if (empty($templatePath)) {
			throw new ParseException("Empty template name");
		}

		$nameRegex = Liquid::get('INCLUDE_ALLOW_EXT')
		? new Regexp('/^[^.\/][a-zA-Z0-9_\.\/-]+$/')
		: new Regexp('/^[^.\/][a-zA-Z0-9_\/-]+$/');

		if (!$nameRegex->match($templatePath)) {
			throw new ParseException("Illegal template name '$templatePath'");
		}

		$templateDir = dirname($templatePath);
		$templateFile = basename($templatePath);

		if (!Liquid::get('INCLUDE_ALLOW_EXT')) {
			$templateFile = Liquid::get('INCLUDE_PREFIX') . $templateFile . '.' . Liquid::get('INCLUDE_SUFFIX');
		}

		$fullPath = join(DIRECTORY_SEPARATOR, array($this->root, $templateDir, $templateFile));

		$realFullPath = realpath($fullPath);
		if ($realFullPath === false) {
			throw new NotFoundException("File not found: $fullPath");
		}

		if (strpos($realFullPath, $this->root) !== 0) {
			throw new NotFoundException("Illegal template full path: {$realFullPath} not under {$this->root}");
		}

		return $realFullPath;
	}
}
