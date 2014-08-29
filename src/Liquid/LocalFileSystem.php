<?php

namespace Liquid;

/**
 * This implements an abstract file system which retrieves template files named in a manner similar to Rails partials,
 * ie. with the template name prefixed with an underscore. The extension ".liquid" is also added.
 *
 * For security reasons, template paths are only allowed to contain letters, numbers, and underscore.
 */
class LocalFileSystem extends BlankFileSystem
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
	 */
	public function __construct($root) {
		$this->root = $root;
	}

	/**
	 * Retrieve a template file
	 *
	 * @param string $templatePath
	 *
	 * @throws LiquidException
	 * @return string template content
	 */
	public function readTemplateFile($templatePath) {
		if (!($fullPath = $this->fullPath($templatePath))) {
			throw new LiquidException("No such template '$templatePath'");
		}

		return file_get_contents($fullPath);
	}

	/**
	 * Resolves a given path to a full template file path, making sure it's valid
	 *
	 * @param string $templatePath
	 *
	 * @throws LiquidException
	 * @return string
	 */
	public function fullPath($templatePath) {
		$nameRegex = Liquid::LIQUID_INCLUDE_ALLOW_EXT
			? new Regexp('/^[^.\/][a-zA-Z0-9_\.\/]+$/')
			: new Regexp('/^[^.\/][a-zA-Z0-9_\/]+$/');

		if (!$nameRegex->match($templatePath)) {
			throw new LiquidException("Illegal template name '$templatePath'");
		}

		if (strpos($templatePath, '/') !== false) {
			$fullPath = Liquid::LIQUID_INCLUDE_ALLOW_EXT
				? $this->root . dirname($templatePath) . '/' . basename($templatePath)
				: $this->root . dirname($templatePath) . '/' . Liquid::LIQUID_INCLUDE_PREFIX . basename($templatePath) . '.' . Liquid::LIQUID_INCLUDE_SUFFIX;
		} else {
			$fullPath = Liquid::LIQUID_INCLUDE_ALLOW_EXT
				? $this->root . $templatePath
				: $this->root . Liquid::LIQUID_INCLUDE_PREFIX . $templatePath . '.' . Liquid::LIQUID_INCLUDE_SUFFIX;
		}

		$rootRegex = new Regexp('/' . preg_quote(realpath($this->root), '/') . '/');

		if (!$rootRegex->match(realpath($fullPath))) {
			throw new LiquidException("Illegal template path '" . realpath($fullPath) . "'");
		}

		return $fullPath;
	}
}
