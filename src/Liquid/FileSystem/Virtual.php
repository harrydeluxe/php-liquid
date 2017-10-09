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

use Liquid\Exception\FilesystemException;
use Liquid\FileSystem;

/**
 * This implements a virtual file system with actual code used to find files injected from outside thus achieving inversion of control.
 */
class Virtual implements FileSystem
{
	/**
	 * @var callable
	 */
	private $callback;

	/**
	 * Constructor
	 *
	 * @param callable $callback Callback is responsible for providing content of requested templates. Should return template's text.
	 * @throws \Liquid\Exception\FilesystemException
	 */
	public function __construct($callback)
	{
		// Since a callback can only be set from the constructor, we check it once right here.
		if (!is_callable($callback)) {
			throw new FilesystemException("Not a callback provided");
		}

		$this->callback = $callback;
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
		return call_user_func($this->callback, $templatePath);
	}

	public function __sleep()
	{
		// we cannot serialize a closure
		if ($this->callback instanceof \Closure) {
			throw new FilesystemException("Virtual file system with a Closure as a callback cannot be used with a serializing cache");
		}

		return array_keys(get_object_vars($this));
	}
}
