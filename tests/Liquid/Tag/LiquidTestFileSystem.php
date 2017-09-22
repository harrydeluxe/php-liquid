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

use Liquid\FileSystem;

/**
 * Helper FileSytem
 */
class LiquidTestFileSystem implements FileSystem
{
	/**
	 * @param string $templatePath
	 *
	 * @return string
	 */
	public function readTemplateFile($templatePath) {
		if ($templatePath == 'inner') {
			return "Inner: {{ inner }}{{ other }}";
		}

		if ($templatePath == 'example') {
			return "Example: {% include 'inner' %}";
		}

		if ($templatePath == 'base') {
			return "{% block content %}{% endblock %}{% block footer %}{% endblock %}";
		}

		if ($templatePath == 'sub-base') {
			return "{% extends 'base' %}{% block content %}{% endblock %}{% block footer %} Boo! {% endblock %}";
		}

		return '';
	}
}