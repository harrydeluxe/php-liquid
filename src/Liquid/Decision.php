<?php

namespace Liquid;

/**
 * Base class for blocks that make logical decisions.
 */
class Decision extends AbstractBlock
{
	/**
	 * The current left variable to compare
	 *
	 * @var string
	 */
	public $left;

	/**
	 * The current right variable to compare
	 *
	 * @var string
	 */
	public $right;

	/**
	 * Returns a string value of an array for comparisons
	 *
	 * @param mixed $value
	 *
	 * @throws \Liquid\LiquidException
	 * @return string
	 */
	private function stringValue($value) {
		// todo: to_string?
		// Objects should have a to_string a value to compare to
		if (is_object($value)) {
			if (method_exists($value, 'to_string')) {
				$value = $value->to_string();
			} else {
				throw new LiquidException("Cannot convert $value to string"); // harry
			}

		}

		// Arrays simply return true
		if (is_array($value)) {
			return $value;
		}

		return $value;
	}

	/**
	 * Check to see if to variables are equal in a given context
	 *
	 * @param string $left
	 * @param string $right
	 * @param Context $context
	 *
	 * @return bool
	 */
	protected function equalVariables($left, $right, Context $context) {
		$left = $this->stringValue($context->get($left));
		$right = $this->stringValue($context->get($right));

		return ($left == $right);
	}

	/**
	 * Interpret a comparison
	 *
	 * @param string $left
	 * @param string $right
	 * @param string $op
	 * @param Context $context
	 *
	 * @throws \Liquid\LiquidException
	 * @return bool
	 */
	protected function interpretCondition($left, $right, $op = null, Context $context) {
		if (is_null($op)) {
			$value = $this->stringValue($context->get($left));
			return $value;
		}

		// values of 'empty' have a special meaning in array comparisons
		if ($right == 'empty' && is_array($context->get($left))) {
			$left = count($context->get($left));
			$right = 0;

		} elseif ($left == 'empty' && is_array($context->get($right))) {
			$right = count($context->get($right));
			$left = 0;

		} else {
			$left = $context->get($left);
			$right = $context->get($right);

			$left = $this->stringValue($left);
			$right = $this->stringValue($right);
		}

		// special rules for null values
		if (is_null($left) || is_null($right)) {
			// null == null returns true
			if ($op == '==' && is_null($left) && is_null($right)) {
				return true;
			}

			// null != anything other than null return true
			if ($op == '!=' && (!is_null($left) || !is_null($right))) {
				return true;
			}

			// everything else, return false;
			return false;
		}

		// regular rules
		switch ($op) {
			case '==':
				return ($left == $right);

			case '!=':
				return ($left != $right);

			case '>':
				return ($left > $right);

			case '<':
				return ($left < $right);

			case '>=':
				return ($left >= $right);

			case '<=':
				return ($left <= $right);

			case 'contains':
				return is_array($left) ? in_array($right, $left) : ($left == $right || strpos($left, $right));

			default:
				throw new LiquidException("Error in tag '" . $this->name() . "' - Unknown operator $op");
		}
	}
}
