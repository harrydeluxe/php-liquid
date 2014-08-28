<?php

namespace Liquid;

/**
 * A Liquid file system is way to let your templates retrieve other templates for use with the include tag.
 *
 * You can implement subclasses that retrieve templates from the database, from the file system using a different
 * path structure, you can provide them as hard-coded inline strings, or any manner that you see fit.
 *
 * You can add additional instance variables, arguments, or methods as needed.
 */
class BlankFileSystem
{
	/**
	 * Retrieve a template file.
	 *
	 * @param string $templatePath
	 *
	 * @throws LiquidException
	 * @return string
	 */
	public function readTemplateFile($templatePath) {
		throw new LiquidException('This liquid context does not allow includes.');
	}
}
