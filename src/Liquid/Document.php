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

use Liquid\Tag\TagInclude;
use Liquid\Tag\TagExtends;
use Liquid\Tag\TagBlock;

/**
 * This class represents the entire template document.
 */
class Document extends AbstractBlock
{
	/**
	 * Constructor.
	 *
	 * @param array $tokens
	 * @param FileSystem $fileSystem
	 */
	public function __construct(array &$tokens, FileSystem $fileSystem = null)
	{
		$this->fileSystem = $fileSystem;
		$this->parse($tokens);
	}

	/**
	 * Check for cached includes; if there are - do not use cache
	 *
	 * @see \Liquid\Tag\TagInclude::hasIncludes()
	 * @see \Liquid\Tag\TagExtends::hasIncludes()
	 * @return bool if need to discard cache
	 */
	public function hasIncludes()
	{
		$seenExtends = false;
		$seenBlock = false;

		foreach ($this->nodelist as $token) {
			if ($token instanceof TagExtends) {
				$seenExtends = true;
			} elseif ($token instanceof TagBlock) {
				$seenBlock = true;
			}
		}

		/*
		 * We try to keep the base templates in cache (that not extend anything).
		 *
		 * At the same time if we re-render all other blocks we see, we avoid most
		 * if not all related caching quirks. This may be suboptimal.
		 */
		if ($seenBlock && !$seenExtends) {
			return true;
		}

		foreach ($this->nodelist as $token) {
			// check any of the tokens for includes
			if ($token instanceof TagInclude && $token->hasIncludes()) {
				return true;
			}

			if ($token instanceof TagExtends && $token->hasIncludes()) {
				return true;
			}
		}

		return false;
	}

	/**
	 * There isn't a real delimiter
	 *
	 * @return string
	 */
	protected function blockDelimiter()
	{
		return '';
	}

	/**
	 * Document blocks don't need to be terminated since they are not actually opened
	 */
	protected function assertMissingDelimitation()
	{
	}
}
