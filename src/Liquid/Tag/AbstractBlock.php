<?php

namespace Liquid\Tag;

use Liquid\Liquid;
use Liquid\Regexp;
use Liquid\LiquidException;
use Liquid\Context;
use Liquid\Template;
use Liquid\Variable;

/**
 * Base class for blocks.
 */
class AbstractBlock extends AbstractTag
{
	/**
	 * @var AbstractTag[]
	 */
	protected $nodelist = array();

	/**
	 * @return array
	 */
	public function getNodelist() {
		return $this->nodelist;
	}

	/**
	 * Parses the given tokens
	 *
	 * @param array $tokens
	 *
	 * @throws \Liquid\LiquidException
	 * @return array|bool|void
	 */
	public function parse(array $tokens) {
		$startRegexp = new Regexp('/^' . Liquid::LIQUID_TAG_START . '/');
		$tagRegexp = new Regexp('/^' . Liquid::LIQUID_TAG_START . '\s*(\w+)\s*(.*)?' . Liquid::LIQUID_TAG_END . '$/');
		$variableStartRegexp = new Regexp('/^' . Liquid::LIQUID_VARIABLE_START . '/');

		$this->nodelist = array();

		if (!is_array($tokens)) {
			return array();
		}

		$tags = Template::getTags();

		while (count($tokens)) {
			$token = array_shift($tokens);

			if ($startRegexp->match($token)) {
				if ($tagRegexp->match($token)) {
					// If we found the proper block delimitor just end parsing here and let the outer block proceed
					if ($tagRegexp->matches[1] == $this->blockDelimiter()) {
						return $this->endTag();
					}

					if (array_key_exists($tagRegexp->matches[1], $tags)) {
						$tagName = $tags[$tagRegexp->matches[1]];
					} else {
						$tagName = 'LiquidTag' . ucwords($tagRegexp->matches[1]);
						// Search for a defined class of the right name, instead of searching in an array
						$tagName = (Liquid::classExists($tagName) === true) ? $tagName : null;
					}

					if (class_exists($tagName)) {
						$this->nodelist[] = new $tagName($tagRegexp->matches[2], $tokens, $this->fileSystem);
						if ($tagRegexp->matches[1] == 'extends') {
							return true;
						}
					} else {
						$this->unknownTag($tagRegexp->matches[1], $tagRegexp->matches[2], $tokens);
					}
				} else {
					throw new LiquidException("Tag $token was not properly terminated"); // harry
				}

			} elseif ($variableStartRegexp->match($token)) {
				$this->nodelist[] = $this->createVariable($token);

			} elseif ($token != '') {
				$this->nodelist[] = $token;
			}
		}

		$this->assertMissingDelimitation();

		// todo: return?
	}

	/**
	 * Render the block.
	 *
	 * @param Context $context
	 *
	 * @return string
	 */
	public function render(Context $context) {
		return $this->renderAll($this->nodelist, $context);
	}

	/**
	 * Renders all the given nodelist's nodes
	 *
	 * @param array $list
	 * @param Context $context
	 *
	 * @return string
	 */
	protected function renderAll(array $list, Context $context) {
		$result = '';

		// todo: token objects
		foreach ($list as $token) {
			$result .= (is_object($token) && method_exists($token, 'render')) ? $token->render($context) : $token;
		}

		return $result;
	}

	/**
	 * An action to execute when the end tag is reached
	 *
	 * todo: return value
	 */
	protected function endTag() {
		// Do nothing by default
	}

	/**
	 * Handler for unknown tags
	 *
	 * @param string $tag
	 * @param array $params
	 * @param array $tokens
	 *
	 * @throws \Liquid\LiquidException
	 *
	 * todo: reference
	 */
	protected function unknownTag($tag, array $params, array $tokens) {
		switch ($tag) {
			case 'else':
				throw new LiquidException($this->blockName() . " does not expect else tag");
			case 'end':
				throw new LiquidException("'end' is not a valid delimiter for " . $this->blockName() . " tags. Use " . $this->blockDelimiter());
			default:
				throw new LiquidException("Unkown tag $tag");
		}
	}

	/**
	 * This method is called at the end of parsing, and will through an error unless
	 * this method is subclassed, like it is for Document
	 *
	 * @throws \Liquid\LiquidException
	 * @return bool
	 */
	protected function assertMissingDelimitation() {
		throw new LiquidException($this->blockName() . " tag was never closed");
	}

	/**
	 * Returns the string that delimits the end of the block
	 *
	 * @return string
	 */
	protected function blockDelimiter() {
		return "end" . $this->blockName();
	}

	/**
	 * Returns the name of the block
	 *
	 * @return string
	 */
	private function blockName() {
		return str_replace('liquidtag', '', strtolower(get_class($this)));
	}

	/**
	 * Create a variable for the given token
	 *
	 * @param string $token
	 *
	 * @throws \Liquid\LiquidException
	 * @return Variable
	 */
	private function createVariable($token) {
		$variableRegexp = new Regexp('/^' . Liquid::LIQUID_VARIABLE_START . '(.*)' . Liquid::LIQUID_VARIABLE_END . '$/');
		if ($variableRegexp->match($token)) {
			return new Variable($variableRegexp->matches[1]);
		}

		throw new LiquidException("Variable $token was not properly terminated");
	}
}
